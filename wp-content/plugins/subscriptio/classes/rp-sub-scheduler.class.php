<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Scheduler
 *
 * @class RP_SUB_Scheduler
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Scheduler extends RightPress_Scheduler
{

    // Define group
    protected $group = 'subscriptio';

    // Define prefix
    protected $prefix = RP_SUB_PLUGIN_PRIVATE_PREFIX;

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Hook into events
        add_action('subscriptio_subscription_status_changed', array($this, 'subscription_status_changed'), 10, 3);
        add_action('subscriptio_subscription_payment_applied', array($this, 'subscription_payment_applied'));
        add_action('subscriptio_subscription_set_property_lifespan', array($this, 'subscription_set_property_lifespan'), 10, 2);

        // Unschedule all actions when subscription is being terminated
        add_action('subscriptio_subscription_status_changing_to_cancelled', array('RP_SUB_Scheduler', 'unschedule_all_actions'), 1);
        add_action('subscriptio_subscription_status_changing_to_expired', array('RP_SUB_Scheduler', 'unschedule_all_actions'), 1);

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Register actions
     *
     * @access public
     * @return array
     */
    public function register_actions()
    {

        /**
         * IMPORTANT:
         * If scheduled action has a corresponding log entry event type, then scheduled action and log entry event type names must match
         * See RP_SUB_Log_Entry_Controller::define_taxonomies_with_terms()
         */

        return array(

            'schedule_revision' => array(
                'label' => esc_html__('Schedule revision', 'subscriptio'),
            ),

            'renewal_order' => array(
                'label' => esc_html__('Renewal order', 'subscriptio'),
            ),

            'renewal_payment' => array(
                'label' => esc_html__('Renewal payment', 'subscriptio'),
            ),

            'payment_retry' => array(
                'label' => esc_html__('Payment retry', 'subscriptio'),
            ),

            'payment_reminder' => array(
                'label' => esc_html__('Payment reminder', 'subscriptio'),
            ),

            'subscription_resume' => array(
                'label' => esc_html__('Resumption', 'subscriptio'),
            ),

            'subscription_suspend' => array(
                'label' => esc_html__('Suspension', 'subscriptio'),
            ),

            'subscription_cancel' => array(
                'label' => esc_html__('Cancellation', 'subscriptio'),
            ),

            'subscription_expire' => array(
                'label' => esc_html__('Expiration', 'subscriptio'),
            ),
        );
    }


    /**
     * =================================================================================================================
     * EVENT BASED SCHEDULING
     * =================================================================================================================
     */

    /**
     * Subscription status changed event
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @param string $old_status
     * @param string $new_status
     * @return void
     */
    public function subscription_status_changed($subscription, $old_status, $new_status)
    {

        // Schedule schedule revision if subscription is not terminated
        if (!$subscription->is_terminated()) {
            RP_SUB_Scheduler::schedule_schedule_revision($subscription, strtotime('+30 seconds'));
        }
    }

    /**
     * Subscription payment applied event
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function subscription_payment_applied($subscription)
    {

        // Clear all scheduled actions (except schedule revision) just to be sure we don't cancel subscription seconds after it's paid etc
        RP_SUB_Scheduler::unschedule_actions($subscription, array_diff(array_keys($this->get_actions()), array('schedule_revision')));

        // Schedule schedule revision if subscription status has not changed, otherwise status change callback is used instead
        if (!$subscription->just_changed_status()) {
            RP_SUB_Scheduler::schedule_schedule_revision($subscription, strtotime('+30 seconds'));
        }
    }

    /**
     * Lifespan property change event
     *
     * @access public
     * @param string $lifespan
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function subscription_set_property_lifespan($lifespan, $subscription)
    {

        // Schedule schedule revision
        RP_SUB_Scheduler::schedule_schedule_revision($subscription, strtotime('+30 seconds'));
    }


    /**
     * =================================================================================================================
     * SUBSCRIPTION STATE CONTROL
     * =================================================================================================================
     */

    /**
     * Revise subscription schedule
     *
     * Returns a list of log entry notes
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return array
     */
    public static function revise_subscription_schedule($subscription)
    {

        $instance = RP_SUB_Scheduler::get_instance();

        // Hold log entry notes
        $log_entry_notes = array();

        // Get expected subscription state
        $expected_state = RP_SUB_Scheduler::get_expected_subscription_state($subscription);

        // Subscription status does not match expected status
        if (!$subscription->has_status($expected_state['status'])) {

            // Get old status label
            $old_status_label = $subscription->get_status_label();

            // Set new subscription status
            $subscription->set_status($expected_state['status']);

            // Add log entry note
            $log_entry_notes[] = sprintf(esc_html__('Incorrect subscription status detected, changing status from %1$s to %2$s.', 'subscriptio'), $old_status_label, $subscription->get_status_label());
        }

        // Iterate over all possible scheduler actions
        foreach ($instance->get_actions() as $current_action => $current_action_data) {

            // Get next scheduled timestamp for current action
            // Note: This method may return timestamp of the next occurrence, boolean true if action is being processed (or is in async queue) or false if there's no such scheduled action
            $next_scheduled = $instance->next_scheduled($instance->prefix_hook($current_action), array('subscription_id' => $subscription->get_id()), $instance->group);

            // Action is expected to be scheduled
            if (isset($expected_state['scheduled_actions'][$current_action]['datetime'])) {

                // Reference expected action datetime
                $expected_datetime = $expected_state['scheduled_actions'][$current_action]['datetime'];

                // Ensure scheduled renewal order is at least 30 minutes in the future
                if ($current_action === 'renewal_order') {
                    RP_SUB_Time::ensure_future_datetime($expected_datetime, ('+' . ceil(RP_SUB_Time::get_day_length_in_seconds() / 48) . 'second'));
                }
                // Ensure scheduled renewal payment is at least 2 hours in the future
                else if ($current_action === 'renewal_payment') {
                    RP_SUB_Time::ensure_future_datetime($expected_datetime, ('+' . ceil(RP_SUB_Time::get_day_length_in_seconds() / 12) . 'second'));
                }
                // Ensure scheduled subscription expiration is at least 2 hours in the future
                else if ($current_action === 'subscription_expire') {
                    RP_SUB_Time::ensure_future_datetime($expected_datetime, ('+' . ceil(RP_SUB_Time::get_day_length_in_seconds() / 12) . 'second'));
                }
                // Ensure other actions are at least 1 minute in the future
                else {
                    RP_SUB_Time::ensure_future_datetime($expected_datetime, '+' . ceil(RP_SUB_Time::get_day_length_in_seconds() / 1440) . 'second');
                }

                // Action is not scheduled or has an incorrect datetime
                if (!$next_scheduled || ($next_scheduled !== true && $next_scheduled !== $expected_datetime->getTimestamp())) {

                    // Schedule action
                    RP_SUB_Scheduler::{"schedule_{$current_action}"}($subscription, $expected_datetime);

                    // Add log entry note
                    $note = !$next_scheduled ? esc_html__('%s scheduled.', 'subscriptio') : esc_html__('%s rescheduled.', 'subscriptio');
                    $log_entry_notes[] = sprintf($note, $current_action_data['label']);
                }
            }
            // Action is not expected to be scheduled but is scheduled
            else if ($next_scheduled && $next_scheduled !== true) {

                // Unschedule action
                RP_SUB_Scheduler::{"unschedule_$current_action"}($subscription);

                // Add log entry note
                $log_entry_notes[] = sprintf(esc_html__('%s unscheduled.', 'subscriptio'), $current_action_data['label']);
            }
        }

        // Return log entry notes
        return $log_entry_notes;
    }

    /**
     * Get expected subscription state
     *
     * Expected subscription state is based on the current datetime, last payment datetime, prepaid billing cycle,
     * settings that are in place as well as some previous actions taken by customer or shop manager (e.g. pausing).
     *
     * This method is a central hub for controlling subscription state/schedule. All the logic is defined here and
     * other methods only act out the state changes, events etc.
     *
     * Returns array with expected subscription state based on the data available
     * Returns false if subscription does not exist / could not be loaded
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return array|false
     */
    public static function get_expected_subscription_state($subscription)
    {

        // Define structure
        $expected_state = array(
            'status'            => $subscription->get_status(),     // Expected subscription status, set to current status by default
            'scheduled_actions' => array(),                         // Actions that are supposed to be scheduled
        );

        // Get current datetime
        $current_datetime = new RP_SUB_DateTime();

        // Load subscription object
        $subscription = is_a($subscription, 'RP_SUB_Subscription') ? $subscription : subscriptio_get_subscription($subscription);

        // Unable to load subscription object
        if (!is_a($subscription, 'RP_SUB_Subscription')) {
            return false;
        }

        // Subscription has not started or has been terminated, no events to take place
        if ($subscription->has_status('pending') || $subscription->is_terminated()) {
            return $expected_state;
        }

        // Subscription is paused
        if ($subscription->has_status('paused')) {

            // Check if subscription was paused by customer
            if ($subscription->get_status_by() === 'customer') {

                // Check if pause duration is limited and get pause duration
                if ($pause_duration = RP_SUB_Settings::get('customer_pausing_duration_limit')) {

                    // Set scheduled subscription resume action
                    $expected_state['scheduled_actions']['subscription_resume']['datetime'] = $subscription->get_status_since()->modify("+{$pause_duration} " . RP_SUB_Time::get_day_name());;
                }
            }

            // No events to take place while subscription is paused
            return $expected_state;
        }

        // Calculate next renewal payment datetime
        $next_renewal_payment_datetime = $subscription->calculate_next_renewal_payment_datetime();

        // Subscription is set to be cancelled
        if ($subscription->has_status('set-to-cancel')) {

            // Set scheduled subscription cancellation action
            $expected_state['scheduled_actions']['subscription_cancel']['datetime'] = $next_renewal_payment_datetime;
        }

        // Get subscription expiration datetime, if any
        if ($expiration_datetime = $subscription->calculate_expiration_datetime()) {

            // Set scheduled subscription expiration action
            $expected_state['scheduled_actions']['subscription_expire']['datetime'] = $expiration_datetime;
        }

        // No other events to take place if subscription terminates before or around the end of current prepaid term
        if ($subscription->terminates_with_prepaid_term()) {
            return $expected_state;
        }

        // Renewal payment due date has not passed yet - status is expected to be trial or active
        if ($current_datetime < $next_renewal_payment_datetime) {

            // Set expected status to active in case current status is not trial or active
            // Note: This can only be due to a previous technical issue or so
            if (!$subscription->has_status('trial', 'active')) {
                $expected_state['status'] = 'active';
            }

            // Set scheduled renewal payment action
            $expected_state['scheduled_actions']['renewal_payment']['datetime'] = $next_renewal_payment_datetime;

            // Subscriptions with no automatic payments may have payment reminders sent if subscription has pending renewal order
            if (!$subscription->has_automatic_payments() && RP_SUB_Settings::is('renewal_order_offset') && $subscription->get_pending_renewal_order()) {

                // Get next payment reminder datetime, if any
                if ($next_payment_reminder_datetime = RP_SUB_Scheduler::get_next_payment_retry_or_reminder_datetime(RP_SUB_Settings::get('payment_reminders'), $next_renewal_payment_datetime, 'before')) {

                    // Set scheduled payment reminder action
                    $expected_state['scheduled_actions']['payment_reminder']['datetime'] = $next_payment_reminder_datetime;
                }
            }
        }
        // Renewal payment due date has passed - status is expected to be overdue, suspended or cancelled
        else {

            // Get overdue and suspension period end datetimes
            $overdue_period_end_datetime    = RP_SUB_Scheduler::get_overdue_period_end_datetime($subscription);
            $suspension_period_end_datetime = RP_SUB_Scheduler::get_suspension_period_end_datetime($subscription);

            // Subscription is expected to be overdue if overdue period is configured and its end date is in the future
            if ($overdue_period_end_datetime && $current_datetime < $overdue_period_end_datetime) {

                // Set expected status
                $expected_state['status'] = 'overdue';

                // Select and set next scheduled action
                $next_scheduled_action = $suspension_period_end_datetime ? 'subscription_suspend' : 'subscription_cancel';
                $expected_state['scheduled_actions'][$next_scheduled_action]['datetime'] = $overdue_period_end_datetime;

                // If subscription has automatic payments and is overdue, it must have payment retries configured
                if ($subscription->has_automatic_payments()) {

                    // Get next payment retry datetime
                    if ($next_payment_retry_datetime = RP_SUB_Scheduler::get_next_payment_retry_datetime($subscription)) {

                        // Set scheduled payment retry action
                        $expected_state['scheduled_actions']['payment_retry']['datetime'] = $next_payment_retry_datetime;
                    }
                }
                // Subscriptions with manual payments may have payment reminders configured
                else {

                    // Get next payment reminder datetime, if any
                    if ($next_overdue_payment_reminder_datetime = RP_SUB_Scheduler::get_next_payment_retry_or_reminder_datetime(RP_SUB_Settings::get('overdue_payment_reminders'), $overdue_period_end_datetime, 'before')) {

                        // Set scheduled payment reminder action
                        $expected_state['scheduled_actions']['payment_reminder']['datetime'] = $next_overdue_payment_reminder_datetime;
                    }
                }
            }
            // Subscription is expected to be suspended if suspension period is configured and its end date is in the future
            else if ($suspension_period_end_datetime && $current_datetime < $suspension_period_end_datetime) {

                // Set expected status
                $expected_state['status'] = 'suspended';

                // Set scheduled subscription cancellation action
                $expected_state['scheduled_actions']['subscription_cancel']['datetime'] = $suspension_period_end_datetime;

                // Get next payment reminder datetime, if any
                if ($next_suspension_payment_reminder_datetime = RP_SUB_Scheduler::get_next_payment_retry_or_reminder_datetime(RP_SUB_Settings::get('suspend_payment_reminders'), $suspension_period_end_datetime, 'before')) {

                    // Set scheduled payment reminder action
                    $expected_state['scheduled_actions']['payment_reminder']['datetime'] = $next_suspension_payment_reminder_datetime;
                }
            }
            // Otherwise, subscription is expected to be cancelled
            else {

                // Set expected status
                $expected_state['status'] = 'cancelled';
            }
        }

        // Subscription that is not expected to be cancelled and that uses manual payments does not have a pending renewal order
        if ($expected_state['status'] !== 'cancelled' && !$subscription->has_automatic_payments() && RP_SUB_Settings::get('renewal_order_offset') && !$subscription->get_pending_renewal_order()) {

            // Set scheduled renewal order action
            $expected_state['scheduled_actions']['renewal_order']['datetime'] = $subscription->calculate_next_renewal_order_datetime();
        }

        // Get payment cut-off datetime if subscription is set to expire
        // Note: This is for actions that fall after renewal payment, e.g. payment retries, payment reminders during suspension period etc.
        if ($cutoff_datetime = $subscription->calculate_payment_cutoff_datetime()) {

            // Iterate over scheduled actions
            foreach ($expected_state['scheduled_actions'] as $scheduled_action_key => $scheduled_action_data) {

                // Check all actions but subscription expiration (itself) and renewal payment (checked above)
                if (!in_array($scheduled_action_key, array('subscription_expire', 'renewal_payment'), true)) {

                    // Check if current scheduled action falls after payment cut-off datetime
                    if ($cutoff_datetime < $scheduled_action_data['datetime']) {

                        // This action is not expected to be scheduled due to subscription expiration
                        unset($expected_state['scheduled_actions'][$scheduled_action_key]);
                    }
                }
            }
        }

        return $expected_state;
    }


    /**
     * =================================================================================================================
     * PLANNED SCHEDULED ACTION DATETIMES FOR DISPLAY
     * =================================================================================================================
     */

    /**
     * Get expected scheduled renewal order datetime for display
     * Value provided by this method should only be used for display in frontend, emails etc.
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_expected_scheduled_renewal_order_datetime_for_display($subscription)
    {

        return RP_SUB_Scheduler::get_expected_scheduled_action_datetime_for_display($subscription, 'renewal_order');
    }

    /**
     * Get expected scheduled renewal payment datetime for display
     * Value provided by this method should only be used for display in frontend, emails etc.
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_expected_scheduled_renewal_payment_datetime_for_display($subscription)
    {

        return RP_SUB_Scheduler::get_expected_scheduled_action_datetime_for_display($subscription, 'renewal_payment');
    }

    /**
     * Get expected scheduled payment retry datetime for display
     * Value provided by this method should only be used for display in frontend, emails etc.
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_expected_scheduled_payment_retry_datetime_for_display($subscription)
    {

        return RP_SUB_Scheduler::get_expected_scheduled_action_datetime_for_display($subscription, 'payment_retry');
    }

    /**
     * Get expected scheduled payment reminder datetime for display
     * Value provided by this method should only be used for display in frontend, emails etc.
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_expected_scheduled_payment_reminder_datetime_for_display($subscription)
    {

        return RP_SUB_Scheduler::get_expected_scheduled_action_datetime_for_display($subscription, 'payment_reminder');
    }

    /**
     * Get expected scheduled subscription resume datetime for display
     * Value provided by this method should only be used for display in frontend, emails etc.
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_expected_scheduled_subscription_resume_datetime_for_display($subscription)
    {

        return RP_SUB_Scheduler::get_expected_scheduled_action_datetime_for_display($subscription, 'subscription_resume');
    }

    /**
     * Get expected scheduled subscription suspend datetime for display
     * Value provided by this method should only be used for display in frontend, emails etc.
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_expected_scheduled_subscription_suspend_datetime_for_display($subscription)
    {

        return RP_SUB_Scheduler::get_expected_scheduled_action_datetime_for_display($subscription, 'subscription_suspend');
    }

    /**
     * Get expected scheduled subscription cancel datetime for display
     * Value provided by this method should only be used for display in frontend, emails etc.
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_expected_scheduled_subscription_cancel_datetime_for_display($subscription)
    {

        return RP_SUB_Scheduler::get_expected_scheduled_action_datetime_for_display($subscription, 'subscription_cancel');
    }

    /**
     * Get expected scheduled subscription expire datetime for display
     * Value provided by this method should only be used for display in frontend, emails etc.
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_expected_scheduled_subscription_expire_datetime_for_display($subscription)
    {

        return RP_SUB_Scheduler::get_expected_scheduled_action_datetime_for_display($subscription, 'subscription_expire');
    }

    /**
     * Get expected scheduled action datetime for display
     * Value provided by this method should only be used for display in frontend, emails etc.
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @param string $action_key
     * @return RP_SUB_DateTime|null
     */
    public static function get_expected_scheduled_action_datetime_for_display($subscription, $action_key)
    {

        // Get expected subscription state
        $expected_state = RP_SUB_Scheduler::get_expected_subscription_state($subscription);

        // Check if action is expected to be scheduled
        if (isset($expected_state['scheduled_actions'][$action_key])) {

            // Return expected scheduled action datetime
            return $expected_state['scheduled_actions'][$action_key]['datetime'];
        }

        // Action is not expected to be scheduled
        return null;
    }



    /**
     * =================================================================================================================
     * SCHEDULING METHODS
     * =================================================================================================================
     */

    /**
     * Schedule schedule revision
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param RP_SUB_DateTime|int $datetime
     * @return int
     */
    public static function schedule_schedule_revision($subscription, $datetime)
    {

        return RP_SUB_Scheduler::schedule_action('schedule_revision', $subscription, $datetime);
    }

    /**
     * Schedule renewal order
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param RP_SUB_DateTime|int $datetime
     * @return int
     */
    public static function schedule_renewal_order($subscription, $datetime)
    {

        return RP_SUB_Scheduler::schedule_action('renewal_order', $subscription, $datetime);
    }

    /**
     * Schedule renewal payment
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param RP_SUB_DateTime|int $datetime
     * @return int
     */
    public static function schedule_renewal_payment($subscription, $datetime)
    {

        return RP_SUB_Scheduler::schedule_action('renewal_payment', $subscription, $datetime);
    }

    /**
     * Schedule payment retry
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param RP_SUB_DateTime|int $datetime
     * @return int
     */
    public static function schedule_payment_retry($subscription, $datetime)
    {

        return RP_SUB_Scheduler::schedule_action('payment_retry', $subscription, $datetime);
    }

    /**
     * Schedule payment reminder
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param RP_SUB_DateTime|int $datetime
     * @return int
     */
    public static function schedule_payment_reminder($subscription, $datetime)
    {

        return RP_SUB_Scheduler::schedule_action('payment_reminder', $subscription, $datetime);
    }

    /**
     * Schedule resumption of a manually paused subscription
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param RP_SUB_DateTime|int $datetime
     * @return int
     */
    public static function schedule_subscription_resume($subscription, $datetime)
    {

        return RP_SUB_Scheduler::schedule_action('subscription_resume', $subscription, $datetime);
    }

    /**
     * Schedule subscription suspension
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param RP_SUB_DateTime|int $datetime
     * @return int
     */
    public static function schedule_subscription_suspend($subscription, $datetime)
    {

        return RP_SUB_Scheduler::schedule_action('subscription_suspend', $subscription, $datetime);
    }

    /**
     * Schedule subscription cancellation
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param RP_SUB_DateTime|int $datetime
     * @return int
     */
    public static function schedule_subscription_cancel($subscription, $datetime)
    {

        return RP_SUB_Scheduler::schedule_action('subscription_cancel', $subscription, $datetime);
    }

    /**
     * Schedule subscription expiration
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param RP_SUB_DateTime|int $datetime
     * @return int
     */
    public static function schedule_subscription_expire($subscription, $datetime)
    {

        return RP_SUB_Scheduler::schedule_action('subscription_expire', $subscription, $datetime);
    }

    /**
     * Schedule action
     *
     * @access public
     * @param string $action
     * @param RP_SUB_Subscription|int $subscription
     * @param RP_SUB_DateTime|int $datetime
     * @return int
     */
    public static function schedule_action($action, $subscription, $datetime)
    {

        $instance = RP_SUB_Scheduler::get_instance();

        // Get subscription object
        $subscription = $instance->get_subscription($subscription);

        // Clear any existing entries, only one entry per action/subscription is allowed
        RP_SUB_Scheduler::{"unschedule_$action"}($subscription);

        // Schedule subscription action
        $result = $instance->schedule_single($datetime, $instance->prefix_hook($action), array('subscription_id' => $subscription->get_id()), $instance->group);

        // Update subscription scheduled action datetime property
        $subscription->{"set_scheduled_$action"}($datetime);
        $subscription->save();

        // Return scheduled action id
        return $result;
    }


    /**
     * =================================================================================================================
     * UNSCHEDULING METHODS
     * =================================================================================================================
     */

    /**
     * Unschedule schedule revision
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return void
     */
    public static function unschedule_schedule_revision($subscription)
    {

        RP_SUB_Scheduler::unschedule_action('schedule_revision', $subscription);
    }

    /**
     * Unschedule renewal order
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return void
     */
    public static function unschedule_renewal_order($subscription)
    {

        RP_SUB_Scheduler::unschedule_action('renewal_order', $subscription);
    }

    /**
     * Unschedule renewal payment
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return void
     */
    public static function unschedule_renewal_payment($subscription)
    {

        RP_SUB_Scheduler::unschedule_action('renewal_payment', $subscription);
    }

    /**
     * Unschedule payment retry
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return void
     */
    public static function unschedule_payment_retry($subscription)
    {

        RP_SUB_Scheduler::unschedule_action('payment_retry', $subscription);
    }

    /**
     * Unschedule payment reminder
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return void
     */
    public static function unschedule_payment_reminder($subscription)
    {

        RP_SUB_Scheduler::unschedule_action('payment_reminder', $subscription);
    }

    /**
     * Unschedule resumption of a manually paused subscription
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return void
     */
    public static function unschedule_subscription_resume($subscription)
    {

        RP_SUB_Scheduler::unschedule_action('subscription_resume', $subscription);
    }

    /**
     * Unschedule subscription suspension
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return void
     */
    public static function unschedule_subscription_suspend($subscription)
    {

        RP_SUB_Scheduler::unschedule_action('subscription_suspend', $subscription);
    }

    /**
     * Unschedule subscription cancellation
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return void
     */
    public static function unschedule_subscription_cancel($subscription)
    {

        RP_SUB_Scheduler::unschedule_action('subscription_cancel', $subscription);
    }

    /**
     * Unschedule subscription expiration
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return void
     */
    public static function unschedule_subscription_expire($subscription)
    {

        RP_SUB_Scheduler::unschedule_action('subscription_expire', $subscription);
    }

    /**
     * Unschedule action
     *
     * @access public
     * @param string $action
     * @param RP_SUB_Subscription|int $subscription
     * @return int
     */
    public static function unschedule_action($action, $subscription)
    {

        $instance = RP_SUB_Scheduler::get_instance();

        // Get subscription object
        $subscription = $instance->get_subscription($subscription);

        // Unschedule subscription action
        $instance->unschedule($instance->prefix_hook($action), array('subscription_id' => $subscription->get_id()), $instance->group);

        // Clear subscription scheduled action datetime property
        $subscription->{"set_scheduled_$action"}(null);
        $subscription->save();
    }

    /**
     * Unschedule all actions
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return void
     */
    public static function unschedule_all_actions($subscription)
    {

        $instance = RP_SUB_Scheduler::get_instance();

        // Unschedule all actions
        RP_SUB_Scheduler::unschedule_actions($subscription, array_keys($instance->get_actions()));
    }

    /**
     * Unschedule specific actions
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param array $actions
     * @return void
     */
    public static function unschedule_actions($subscription, $actions)
    {

        $instance = RP_SUB_Scheduler::get_instance();

        // Iterate over actions to unschedule
        foreach ((array) $actions as $action) {

            // Unschedule action
            RP_SUB_Scheduler::{"unschedule_$action"}($subscription);
        }
    }


    /**
     * =================================================================================================================
     * SCHEDULED ACTION CALLBACKS
     * =================================================================================================================
     */

    /**
     * Scheduled schedule revision
     *
     * @access public
     * @param int $subscription_id
     * @return void
     */
    public function scheduled_schedule_revision($subscription_id)
    {

        try {

            // Load subscription object
            $subscription = $this->get_subscription($subscription_id);

            // Revise subscription schedule
            if ($log_entry_notes = RP_SUB_Scheduler::revise_subscription_schedule($subscription)) {

                // Create a log entry
                RP_SUB_Log_Entry_Controller::add_log_entry(array(
                    'event_type'        => 'schedule_revision',
                    'subscription_id'   => $subscription_id,
                    'status'            => 'success',
                    'notes'             => $log_entry_notes,
                ));
            }

            // Clear scheduled datetime
            $subscription->set_scheduled_schedule_revision(null);
            $subscription->save();
        }
        catch (Exception $e) {

            // Create log entry
            $log_entry = RP_SUB_Log_Entry_Controller::create_log_entry(array(
                'event_type'        => 'schedule_revision',
                'subscription_id'   => $subscription_id,
            ));

            // Handle caught exception
            $log_entry->handle_caught_exception($e, null, 'error');
        }
    }

    /**
     * Scheduled renewal order
     *
     * @access public
     * @param int $subscription_id
     * @return void
     */
    public function scheduled_renewal_order($subscription_id)
    {

        // Define log entry notes
        $start_note = esc_html__('Creating new renewal order according to subscription schedule.', 'subscriptio');

        // Process scheduled subscription action
        $this->process_scheduled_subscription_action($subscription_id, 'renewal_order', $start_note);
    }

    /**
     * Scheduled renewal payment
     *
     * @access public
     * @param int $subscription_id
     * @return void
     */
    public function scheduled_renewal_payment($subscription_id)
    {

        // Define log entry notes
        $start_note = esc_html__('Processing scheduled renewal payment.', 'subscriptio');

        // Process scheduled subscription action
        $this->process_scheduled_subscription_action($subscription_id, 'renewal_payment', $start_note);
    }

    /**
     * Scheduled payment retry
     *
     * @access public
     * @param int $subscription_id
     * @return void
     */
    public function scheduled_payment_retry($subscription_id)
    {

        // Define log entry notes
        $start_note = esc_html__('Processing scheduled payment retry.', 'subscriptio');

        // Process scheduled subscription action
        $this->process_scheduled_subscription_action($subscription_id, 'payment_retry', $start_note);
    }

    /**
     * Scheduled payment reminder
     *
     * @access public
     * @param int $subscription_id
     * @return void
     */
    public function scheduled_payment_reminder($subscription_id)
    {

        // Define log entry notes
        $start_note = esc_html__('Sending scheduled payment reminder.', 'subscriptio');

        // Process scheduled subscription action
        $this->process_scheduled_subscription_action($subscription_id, 'payment_reminder', $start_note);
    }

    /**
     * Scheduled subscription resumption of a manually paused subscription
     *
     * @access public
     * @param int $subscription_id
     * @return void
     */
    public function scheduled_subscription_resume($subscription_id)
    {

        // Define log entry notes
        $start_note = esc_html__('Resuming paused subscription automatically.', 'subscriptio');

        // Process scheduled subscription action
        $this->process_scheduled_subscription_action($subscription_id, 'subscription_resume', $start_note);
    }

    /**
     * Scheduled subscription suspension
     *
     * @access public
     * @param int $subscription_id
     * @return void
     */
    public function scheduled_subscription_suspend($subscription_id)
    {

        // Define log entry notes
        $start_note = esc_html__('Processing scheduled subscription suspension.', 'subscriptio');

        // Process scheduled subscription action
        $this->process_scheduled_subscription_action($subscription_id, 'subscription_suspend', $start_note);
    }

    /**
     * Scheduled subscription cancellation
     *
     * @access public
     * @param int $subscription_id
     * @return void
     */
    public function scheduled_subscription_cancel($subscription_id)
    {

        // Define log entry notes
        $start_note = esc_html__('Processing scheduled subscription cancellation.', 'subscriptio');

        // Process scheduled subscription action
        $this->process_scheduled_subscription_action($subscription_id, 'subscription_cancel', $start_note);
    }

    /**
     * Scheduled subscription expiration
     *
     * @access public
     * @param int $subscription_id
     * @return void
     */
    public function scheduled_subscription_expire($subscription_id)
    {

        // Define log entry notes
        $start_note = esc_html__('Processing scheduled subscription expiration.', 'subscriptio');

        // Process scheduled subscription action
        $this->process_scheduled_subscription_action($subscription_id, 'subscription_expire', $start_note);
    }


    /**
     * =================================================================================================================
     * SCHEDULED ACTION HANDLERS
     * =================================================================================================================
     */

    /**
     * Process scheduled subscription action
     *
     * @access public
     * @param int $subscription_id
     * @param string $action
     * @param string $start_note
     * @param string $end_note
     * @return void
     */
    protected function process_scheduled_subscription_action($subscription_id, $action, $start_note = null, $end_note = null)
    {

        $subscription = null;

        // Start logging
        $log_entry = RP_SUB_Log_Entry_Controller::create_log_entry(array(
            'event_type'        => $action,
            'subscription_id'   => $subscription_id,
        ));

        // Add start note to log entry
        if ($start_note !== null) {
            $log_entry->add_note($start_note);
        }

        try {

            // Load subscription object
            $subscription = $this->get_subscription($subscription_id);

            // Set log entry to subscription
            $subscription->set_log_entry($log_entry);

            // Action is not supposed to be executed
            if (!$subscription->{"get_scheduled_$action"}()) {
                throw new RightPress_Exception('rp_sub_unexpected_scheduled_action', esc_html__('Action is not supposed to be executed, aborting.', 'subscriptio'));
            }

            // Scheduled action is being executed prematurely
            if ((new RP_SUB_DateTime()) < $subscription->{"get_scheduled_$action"}()) {
                throw new RightPress_Exception('rp_sub_scheduled_action_executed_prematurely', esc_html__('Action executed prematurely, aborting.', 'subscriptio'));
            }

            // Clear scheduled datetime
            $subscription->{"set_scheduled_$action"}(null);
            $subscription->save();

            // Call handler
            $this->{"process_scheduled_$action"}($subscription);

            // Add end note to log entry
            if ($end_note !== null) {
                $log_entry->add_note($end_note);
            }
        }
        catch (Exception $e) {

            // Check if action was executed prematurely
            $premature_execution = is_a($e, 'RightPress_Exception') && $e->is_error_code('rp_sub_scheduled_action_executed_prematurely');

            // Handle caught exception
            $log_entry->handle_caught_exception($e, null, ($premature_execution ? 'warning' : 'error'));

            // Reschedule prematurely executed action
            if ($premature_execution) {

                // Reschedule action
                RP_SUB_Scheduler::{"schedule_{$action}"}($subscription, $subscription->{"get_scheduled_$action"}());

                // Add log entry note
                $log_entry->add_note(esc_html__('Action rescheduled for the correct date.', 'subscriptio'));
            }
        }

        // End logging
        $log_entry->end_logging($subscription);
    }

    /**
     * Process scheduled renewal order
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function process_scheduled_renewal_order($subscription)
    {

        // Create renewal order
        $renewal_order = RP_SUB_WC_Order::create_renewal_order($subscription);

        // Unable to create renewal order
        if (!is_a($renewal_order, 'WC_Order')) {
            throw new RightPress_Exception('rp_sub_scheduler_unable_to_create_renewal_order', esc_html__('Unable to create renewal order. Reason unknown.', 'subscriptio'));
        }

        // Schedule schedule revision if order was not paid right away (payment reminders)
        if (!$renewal_order->is_paid()) {
            RP_SUB_Scheduler::schedule_schedule_revision($subscription, strtotime('+30 seconds'));
        }
    }

    /**
     * Process scheduled renewal payment
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function process_scheduled_renewal_payment($subscription)
    {

        $this->process_scheduled_renewal_payment_or_payment_retry($subscription, false);
    }

    /**
     * Process scheduled payment retry
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function process_scheduled_payment_retry($subscription)
    {

        $this->process_scheduled_renewal_payment_or_payment_retry($subscription, true);
    }

    /**
     * Process scheduled renewal payment
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @param bool $is_retry
     * @return void
     */
    public function process_scheduled_renewal_payment_or_payment_retry($subscription, $is_retry = false)
    {

        // Subscription is not pending renewal payment
        if (!$subscription->is_pending_renewal_payment()) {

            // Add note to log entry
            $subscription->add_log_entry_note(esc_html__('Subscription is not pending renewal payment, aborting.', 'subscription'));

            // Change log entry status to warning
            $subscription->update_log_entry_status('warning');

            // Schedule schedule revision to make sure we have a correct renewal payment event scheduled
            RP_SUB_Scheduler::schedule_schedule_revision($subscription, strtotime('+30 seconds'));

            // Do not proceed any further
            return;
        }

        // Subscription renewal payment date is still in the future
        if ((new RP_SUB_DateTime()) < $subscription->calculate_next_renewal_payment_datetime()) {

            // Add note to log entry
            $subscription->add_log_entry_note(esc_html__('Subscription payment deadline is still in the future, aborting.', 'subscription'));

            // Change log entry status to warning
            $subscription->update_log_entry_status('warning');

            // Schedule schedule revision to make sure we have a correct renewal payment event scheduled
            RP_SUB_Scheduler::schedule_schedule_revision($subscription, strtotime('+30 seconds'));

            // Do not proceed any further
            return;
        }

        // Get pending renewal order
        $renewal_order = $subscription->get_pending_renewal_order();

        // Renewal order does not exist
        if (!is_a($renewal_order, 'WC_Order')) {

            // Add note to log entry
            $subscription->add_log_entry_note(esc_html__('Creating renewal order.', 'subscription'));

            // Create renewal order
            $renewal_order = RP_SUB_WC_Order::create_renewal_order($subscription);

            // Unable to create renewal order
            if (!is_a($renewal_order, 'WC_Order')) {
                throw new RightPress_Exception('rp_sub_scheduler_unable_to_create_renewal_order', esc_html__('Unable to create renewal order. Reason unknown.', 'subscriptio'));
            }
        }

        // Add order id to log entry
        $subscription->add_log_entry_property('order_id', $renewal_order->get_id());

        // Renewal order is already paid
        // Note: This is not an expected behaviour since we apply payments to subscriptions as soon as related orders are marked
        // paid, however, it is safe to just apply the payment now since we have protection from duplicate payment application
        if ($renewal_order->is_paid()) {

            // Add note to log entry
            $subscription->add_log_entry_note(esc_html__('Order seems to be paid, applying payment to subscription.', 'subscription'));

            // Apply payment to subscription
            $subscription->apply_payment($renewal_order);

            // Do not proceed any further
            return;
        }

        // Subscription has automatic payments
        if ($subscription->has_automatic_payments() && apply_filters('subscriptio_process_automatic_payment', RP_SUB_Main_Site_Controller::is_main_site(), $renewal_order, $subscription)) {

            // Attempt to process automatic payment
            $automatic_payment_processed = RP_SUB_Payment_Controller::process_automatic_payment($renewal_order, $subscription);

            // Automatic payment processed successfully
            if ($automatic_payment_processed) {

                // Add note to log entry
                $subscription->add_log_entry_note(esc_html__('Processing automatic payment.', 'subscription'));

                // Do not proceed any further
                return;
            }
            // Automatic payment failed
            else {

                // Add note to log entry
                $subscription->add_log_entry_note(esc_html__('Automatic payment failed.', 'subscription'));

                // Update log entry status
                $subscription->update_log_entry_status('failed');

                // Trigger action to send email
                do_action('subscriptio_subscription_automatic_payment_failed', $renewal_order, $subscription);
            }
        }

        // Check if subscription is still pending renewal payment
        if ($subscription->is_pending_renewal_payment()) {

            // Add note to log entry
            $subscription->add_log_entry_note(esc_html__('Subscription payment not received.', 'subscription'));

            // Get expected subscription state
            $expected_state = RP_SUB_Scheduler::get_expected_subscription_state($subscription);

            // Subscription status does not match expected status, set new subscription status - normally overdue, suspended or cancelled
            if (!$subscription->has_status($expected_state['status'])) {
                $subscription->set_status($expected_state['status']);
            }

            // Schedule schedule revision (payment retries)
            RP_SUB_Scheduler::schedule_schedule_revision($subscription, strtotime('+30 seconds'));
        }
    }

    /**
     * Process scheduled payment reminder
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function process_scheduled_payment_reminder($subscription)
    {

        // Subscription is no longer pending renewal payment
        if (!$subscription->is_pending_renewal_payment()) {
            return;
        }

        // Get pending renewal order
        $renewal_order = $subscription->get_pending_renewal_order();

        // Pending renewal order does not exist
        if (!$renewal_order) {
            return;
        }

        // Pending renewal order appears to be paid
        if ($renewal_order->is_paid()) {
            return;
        }

        // Trigger subscription payment reminder
        do_action('subscriptio_send_payment_reminder', $renewal_order);

        // Schedule schedule revision (payment reminders)
        RP_SUB_Scheduler::schedule_schedule_revision($subscription, strtotime('+30 seconds'));
    }

    /**
     * Process scheduled subscription resumption
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function process_scheduled_subscription_resume($subscription)
    {

        $subscription->resume();
    }

    /**
     * Process scheduled subscription suspension
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function process_scheduled_subscription_suspend($subscription)
    {

        $subscription->suspend();
    }

    /**
     * Process scheduled subscription cancellation
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function process_scheduled_subscription_cancel($subscription)
    {

        $subscription->cancel();
    }

    /**
     * Process scheduled subscription expiration
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function process_scheduled_subscription_expire($subscription)
    {

        try {

            // Expire subscription
            $subscription->expire();
        }
        catch (Exception $e) {

            // Subscription expiring too early
            if ($e->is_error_code('rp_sub_subscription_expiring_too_early')) {

                // Schedule subscription revision
                RP_SUB_Scheduler::schedule_schedule_revision($subscription, strtotime('+30 seconds'));
            }

            // Propagate further
            throw $e;
        }
    }


    /**
     * =================================================================================================================
     * OTHER METHODS
     * =================================================================================================================
     */

    /**
     * Get subscription object
     *
     * Throws exception if subscription object can't be loaded
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return RP_SUB_Subscription
     */
    public function get_subscription($subscription)
    {

        // Get subscription id
        $subscription_id = (is_numeric($subscription) && $subscription) ? $subscription : null;

        // Load subscription object
        $subscription = is_a($subscription, 'RP_SUB_Subscription') ? $subscription : subscriptio_get_subscription($subscription);

        // Unable to load subscription object
        if (!is_a($subscription, 'RP_SUB_Subscription')) {

            $error_message = esc_html__('Unable to load subscription object.', 'subscriptio');

            // No such post?
            if ($subscription_id !== null && !RightPress_Help::post_exists($subscription_id)) {
                $error_message .= ' ' . esc_html__('Subscription no longer exists.', 'subscriptio');
            }
            // Reason unknown
            else {
                $error_message .= ' ' . esc_html__('Reason unknown.', 'subscriptio');
            }

            // Throw exception
            throw new RightPress_Exception('rp_sub_scheduler_unable_to_load_subscription', $error_message);
        }

        // No scheduled actions should be performed on terminated subscriptions
        if ($subscription->is_terminated()) {
            throw new RightPress_Exception('rp_sub_scheduler_subscription_terminated', esc_html__('Subscription is cancelled or expired - no further actions allowed.', 'subscriptio'));
        }

        return $subscription;
    }

    /**
     * Get next payment retry datetime
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_next_payment_retry_datetime($subscription)
    {

        $datetime = null;

        // Check if subscription uses automatic payments
        if ($subscription->has_automatic_payments()) {

            // Get next payment retry datetime, if any
            $datetime = RP_SUB_Scheduler::get_next_payment_retry_or_reminder_datetime(RP_SUB_Settings::get('payment_retries'), $subscription->calculate_next_renewal_payment_datetime(), 'after');
        }

        return $datetime;
    }

    /**
     * Get last payment retry datetime
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_last_payment_retry_datetime($subscription)
    {

        $datetime = null;

        // Check if subscription uses automatic payments
        if ($subscription->has_automatic_payments()) {

            // Get array of payment retry days
            if ($retries = RP_SUB_Settings::get('payment_retries')) {

                // Sort retry days from smallest to largest
                sort($retries);

                // Get last retry day
                $last_retry_day = array_pop($retries);

                // Get renewal payment datetime
                $datetime = $subscription->calculate_next_renewal_payment_datetime();

                // Calculate last retry datetime
                $datetime->modify("+{$last_retry_day} " . RP_SUB_Time::get_day_name());
            }
        }

        return $datetime;
    }

    /**
     * Get next payment retry or reminder datetime
     *
     * @access public
     * @param array $days
     * @param RP_SUB_DateTime $reference_datetime
     * @param string $direction
     * @return RP_SUB_DateTime|null
     */
    public static function get_next_payment_retry_or_reminder_datetime($days, $reference_datetime, $direction = 'after')
    {

        $selected_datetime = null;

        // Check if any retries or reminders are configured
        if ($days) {

            // Get current datetime
            $current_datetime = new RP_SUB_DateTime();

            // Select operator
            $operator = $direction === 'before' ? '-' : '+';

            // Sort days from largest to smallest
            if ($direction === 'before') {
                rsort($days);
            }
            // Sort days from smallest to largest
            else {
                sort($days);
            }

            // Iterate over days
            foreach ($days as $day) {

                // Calculate datetime
                $datetime = clone $reference_datetime;
                $datetime->modify("{$operator}{$day} " . RP_SUB_Time::get_day_name());

                // Check if datetime is in the future
                if ($current_datetime < $datetime) {

                    // Next datetime found
                    $selected_datetime = $datetime;
                    break;
                }
            }
        }

        return $selected_datetime;
    }

    /**
     * Calculate overdue period length in days
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return int|null
     */
    public static function calculate_overdue_period_length($subscription)
    {

        // Automatic subscriptions
        if ($subscription->has_automatic_payments()) {

            // Get array of payment retry days
            if ($retries = RP_SUB_Settings::get('payment_retries')) {

                // Sort retry days from smallest to largest
                sort($retries);

                // Overdue period equals a number of days between renewal payment day and last payment retry day
                $overdue_period = array_pop($retries);
            }
        }
        // Manual subscriptions
        else {

            // Just take value from the setting
            $overdue_period = RP_SUB_Settings::get('overdue_period');
        }

        return (isset($overdue_period) && $overdue_period) ? $overdue_period : null;
    }

    /**
     * Get overdue period end datetime
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_overdue_period_end_datetime($subscription)
    {

        $datetime = null;

        // Calculate overdue period length
        if ($overdue_period_length = RP_SUB_Scheduler::calculate_overdue_period_length($subscription)) {

            // Get next renewal payment datetime
            $datetime = $subscription->calculate_next_renewal_payment_datetime();

            // Calculate and set overdue period end datetime
            RP_SUB_Time::add_period_length_to_datetime($datetime, ($overdue_period_length . ' ' . RP_SUB_Time::get_day_name()));
        }

        // Return overdue period end datetime
        return $datetime;
    }

    /**
     * Get suspension period end datetime
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return RP_SUB_DateTime|null
     */
    public static function get_suspension_period_end_datetime($subscription)
    {

        $datetime = null;

        // Get suspension period in days
        if ($suspension_period = RP_SUB_Settings::get('suspension_period')) {

            // If overdue period is configured, suspension period starts at the end of overdue period
            if ($overdue_period_end_datetime = RP_SUB_Scheduler::get_overdue_period_end_datetime($subscription)) {

                // Set reference datetime
                $datetime = $overdue_period_end_datetime;
            }
            // Otherwise, suspension period starts immediately after next renewal payment datetime
            else {

                // Set reference datetime
                $datetime = $subscription->calculate_next_renewal_payment_datetime();
            }

            // Add suspension period length to reference datetime
            RP_SUB_Time::add_period_length_to_datetime($datetime, ("$suspension_period " . RP_SUB_Time::get_day_name()));
        }

        // Return suspension period end datetime
        return $datetime;
    }





}

RP_SUB_Scheduler::get_instance();
