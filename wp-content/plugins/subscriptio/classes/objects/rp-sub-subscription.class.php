<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wc-custom-order-object.class.php';

/**
 * Subscription
 *
 * Note: We use two types of objects for subscriptions:
 *  - RP_SUB_Suborder which is a custom WooCommerce order type and holds most of the data in a format
 *    that is easy to copy data from/to regular WooCommerce orders and lets us reuse WooCommerce order interface
 *  - RP_SUB_Subscription which is a wrapper to add our own functionality so that we can use method/property names
 *    without prefixes and don't fear that they will clash with those in WC_Order in the future
 *
 * @class RP_SUB_Subscription
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Subscription extends RP_SUB_WC_Custom_Order_Object
{

    // Define properties with default values
    protected $data = array(
        'billing_cycle'                         => null,        // Billing cycle length and period combined into string, e.g. '1 month'
        'prepaid_billing_cycle'                 => null,        // Same as billing_cycle but for one that customer has paid for, may differ from billing_cycle if admin changed it since
        'free_trial'                            => null,        // Free trial length and period combined into string, e.g. '2 week'
        'lifespan'                              => null,        // Lifespan length and period combined into string, e.g. '12 month'
        'first_payment'                         => null,        // This is the date from which end of lifespan can be (re)calculated; can be the first "payment" before free trial started
        'last_payment'                          => null,        // This is the date from which all recurring billing schedules can be (re)calculated; can be different from actual payment date due to multiple reasons
        'status_since'                          => null,        // Datetime when current status was set
        'status_by'                             => null,        // Either 'system' or 'admin' or 'customer' - setter sets it to 'system' by default, other methods must change it to their liking after the status is set
        'previous_status'                       => null,        // Previous subscription status is stored so that we can resume paused subscriptions or reactivate subscriptions that are set to cancel and assign them a correct (previous) status
        'initial_order_id'                      => null,
        'pending_renewal_order_id'              => null,        // Subscriptions with this property set to renewal order id are considered pending payment; this property is cleared as soon as the order is paid for
        'last_renewal_order_id'                 => null,        // This property is set every time a renewal order is generated; this property does not have setter and sanitizer, it's set from the pending_renewal_order_id setter
        'payment_applied_order_ids'             => array(),     // List of ids of orders which were used to apply payment to subscription, keeping track to make sure we don't apply the same payment more than once; does not have its own setter, is updated via add_payment_applied_order_id
        'payment_gateway_options'               => array(),     // Payment gateway options used for automatic payments, managed by payment gateway extension
        'customer_pause_count'                  => 0,
        'scheduled_schedule_revision'           => null,        // This is set by scheduler when renewal order is scheduled         Should only be used for display/reference purposes and to prevent premature scheduled event execution
        'scheduled_renewal_order'               => null,        // This is set by scheduler when renewal order is scheduled         Should only be used for display/reference purposes and to prevent premature scheduled event execution
        'scheduled_renewal_payment'             => null,        // This is set by scheduler when renewal payment is scheduled       Should only be used for display/reference purposes and to prevent premature scheduled event execution
        'scheduled_payment_retry'               => null,        // This is set by scheduler when payment retry is scheduled         Should only be used for display/reference purposes and to prevent premature scheduled event execution
        'scheduled_payment_reminder'            => null,        // This is set by scheduler when payment reminder is scheduled      Should only be used for display/reference purposes and to prevent premature scheduled event execution
        'scheduled_subscription_resume'         => null,        // This is set by scheduler when resumption is scheduled            Should only be used for display/reference purposes and to prevent premature scheduled event execution
        'scheduled_subscription_suspend'        => null,        // This is set by scheduler when suspension is scheduled            Should only be used for display/reference purposes and to prevent premature scheduled event execution
        'scheduled_subscription_cancel'         => null,        // This is set by scheduler when cancellation is scheduled          Should only be used for display/reference purposes and to prevent premature scheduled event execution
        'scheduled_subscription_expire'         => null,        // This is set by scheduler when expiration is scheduled            Should only be used for display/reference purposes and to prevent premature scheduled event execution
    );

    // Define datetime properties
    protected $datetime_properties = array(
        'first_payment',
        'last_payment',
        'status_since',
        'scheduled_schedule_revision',
        'scheduled_renewal_order',
        'scheduled_renewal_payment',
        'scheduled_payment_retry',
        'scheduled_payment_reminder',
        'scheduled_subscription_resume',
        'scheduled_subscription_suspend',
        'scheduled_subscription_cancel',
        'scheduled_subscription_expire',
    );

    // Define helper properties
    private $just_terminated            = false;
    private $just_changed_status        = false;
    public $last_payment_just_voided    = false;

    /**
     * Constructor
     *
     * @access public
     * @param mixed $object
     * @param object $data_store
     * @param object $controller
     * @return void
     */
    public function __construct($object, $data_store, $controller)
    {

        // Call parent cosntructor
        parent::__construct($object, $data_store, $controller);
    }


    /**
     * =================================================================================================================
     * GETTER ALIASES
     * =================================================================================================================
     */

    /**
     * Get suborder object
     *
     * Alias for get_wc_order()
     *
     * @access public
     * @return WC_Order
     */
    public function get_suborder()
    {

        return $this->get_wc_order();
    }

    /**
     * Get recurring total
     *
     * @access public
     * @param string $context
     * @return float
     */
    public function get_recurring_total($context = 'view')
    {

        return $this->get_suborder()->get_total($context);
    }

    /**
     * Get customer subscription notes
     *
     * @access public
     * @return array
     */
    public function get_customer_subscription_notes()
    {

        return $this->get_suborder()->get_customer_order_notes();
    }

    /**
     * Get totals for display on pages and in emails
     *
     * @access public
     * @param string $tax_display
     * @return array
     */
    public function get_subscription_item_totals($tax_display = '')
    {

        return $this->get_order_item_totals($tax_display);
    }


    /**
     * =================================================================================================================
     * GETTERS
     * =================================================================================================================
     */

    /**
     * Get billing cycle
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return string
     */
    public function get_billing_cycle($context = 'view', $args = array())
    {

        return $this->get_property('billing_cycle', $context, $args);
    }

    /**
     * Get prepaid billing cycle
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return string
     */
    public function get_prepaid_billing_cycle($context = 'view', $args = array())
    {

        return $this->get_property('prepaid_billing_cycle', $context, $args);
    }

    /**
     * Get free trial
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return string
     */
    public function get_free_trial($context = 'view', $args = array())
    {

        return $this->get_property('free_trial', $context, $args);
    }

    /**
     * Get lifespan
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return string
     */
    public function get_lifespan($context = 'view', $args = array())
    {

        return $this->get_property('lifespan', $context, $args);
    }

    /**
     * Get first payment datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return string
     */
    public function get_first_payment($context = 'view', $args = array())
    {

        return $this->get_datetime_property('first_payment', $context, $args);
    }

    /**
     * Get last payment datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return string
     */
    public function get_last_payment($context = 'view', $args = array())
    {

        return $this->get_datetime_property('last_payment', $context, $args);
    }

    /**
     * Get status since datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return RightPress_DateTime
     */
    public function get_status_since($context = 'view', $args = array())
    {

        return $this->get_datetime_property('status_since', $context, $args);
    }

    /**
     * Get status by
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return string
     */
    public function get_status_by($context = 'view', $args = array())
    {

        return $this->get_property('status_by', $context, $args);
    }

    /**
     * Get previous status
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return string
     */
    public function get_previous_status($context = 'view', $args = array())
    {

        return $this->get_property('previous_status', $context, $args);
    }

    /**
     * Get initial order id
     *
     * Note: This will be empty if subscription is created directly (i.e. not via regular checkout)
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return int
     */
    public function get_initial_order_id($context = 'view', $args = array())
    {

        return $this->get_property('initial_order_id', $context, $args);
    }

    /**
     * Get pending renewal order id
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return int
     */
    public function get_pending_renewal_order_id($context = 'view', $args = array())
    {

        // Get value
        $pending_renewal_order_id = $this->get_property('pending_renewal_order_id', $context, $args);

        // Payment on this order has already been applied
        if ($pending_renewal_order_id !== null && in_array($pending_renewal_order_id, $this->get_payment_applied_order_ids(), true)) {

            // Unset pending renewal order id
            $pending_renewal_order_id = null;

            // Preserve the change
            $this->set_pending_renewal_order_id($pending_renewal_order_id);
            $this->save();
        }

        // Return pending renewal order id
        return $pending_renewal_order_id;
    }

    /**
     * Get last renewal order id
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return int
     */
    public function get_last_renewal_order_id($context = 'view', $args = array())
    {

        return $this->get_property('last_renewal_order_id', $context, $args);
    }

    /**
     * Get payment applied order ids
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return int
     */
    public function get_payment_applied_order_ids($context = 'view', $args = array())
    {

        return $this->get_property('payment_applied_order_ids', $context, $args);
    }

    /**
     * Get payment gateway options
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return array
     */
    public function get_payment_gateway_options($context = 'view', $args = array())
    {

        return $this->get_property('payment_gateway_options', $context, $args);
    }

    /**
     * Get customer pause count
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return int
     */
    public function get_customer_pause_count($context = 'view', $args = array())
    {

        return $this->get_property('customer_pause_count', $context, $args);
    }

    /**
     * Get scheduled schedule revision datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return RightPress_DateTime
     */
    public function get_scheduled_schedule_revision($context = 'view', $args = array())
    {

        return $this->get_datetime_property('scheduled_schedule_revision', $context, $args);
    }

    /**
     * Get scheduled renewal order datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return RightPress_DateTime
     */
    public function get_scheduled_renewal_order($context = 'view', $args = array())
    {

        return $this->get_datetime_property('scheduled_renewal_order', $context, $args);
    }

    /**
     * Get scheduled renewal payment datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return RightPress_DateTime
     */
    public function get_scheduled_renewal_payment($context = 'view', $args = array())
    {

        return $this->get_datetime_property('scheduled_renewal_payment', $context, $args);
    }

    /**
     * Get scheduled payment retry datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return RightPress_DateTime
     */
    public function get_scheduled_payment_retry($context = 'view', $args = array())
    {

        return $this->get_datetime_property('scheduled_payment_retry', $context, $args);
    }

    /**
     * Get scheduled payment reminder datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return RightPress_DateTime
     */
    public function get_scheduled_payment_reminder($context = 'view', $args = array())
    {

        return $this->get_datetime_property('scheduled_payment_reminder', $context, $args);
    }

    /**
     * Get scheduled subscription resumption datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return RightPress_DateTime
     */
    public function get_scheduled_subscription_resume($context = 'view', $args = array())
    {

        return $this->get_datetime_property('scheduled_subscription_resume', $context, $args);
    }

    /**
     * Get scheduled subscription suspension datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return RightPress_DateTime
     */
    public function get_scheduled_subscription_suspend($context = 'view', $args = array())
    {

        return $this->get_datetime_property('scheduled_subscription_suspend', $context, $args);
    }

    /**
     * Get scheduled subscription cancellation datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return RightPress_DateTime
     */
    public function get_scheduled_subscription_cancel($context = 'view', $args = array())
    {

        return $this->get_datetime_property('scheduled_subscription_cancel', $context, $args);
    }

    /**
     * Get scheduled subscription expiration datetime
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return RightPress_DateTime
     */
    public function get_scheduled_subscription_expire($context = 'view', $args = array())
    {

        return $this->get_datetime_property('scheduled_subscription_expire', $context, $args);
    }


    /**
     * =================================================================================================================
     * SETTERS
     * =================================================================================================================
     */

    /**
     * Set billing cycle
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function set_billing_cycle($value)
    {

        // Sanitize billing cycle
        $value = $this->sanitize_billing_cycle($value);

        // Set property
        $this->set_property('billing_cycle', $value);
    }

    /**
     * Set prepaid billing cycle
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function set_prepaid_billing_cycle($value)
    {

        // Sanitize billing cycle
        $value = $this->sanitize_prepaid_billing_cycle($value);

        // Set property
        $this->set_property('prepaid_billing_cycle', $value);
    }

    /**
     * Set free trial
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function set_free_trial($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_free_trial($value);

        // Set property
        $this->set_property('free_trial', $value);
    }

    /**
     * Set lifespan
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function set_lifespan($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_lifespan($value);

        // Set property
        $this->set_property('lifespan', $value);
    }

    /**
     * Set first payment datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_first_payment($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_first_payment($value);

        // Set property
        $this->set_property('first_payment', $value);
    }

    /**
     * Set last payment datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_last_payment($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_last_payment($value);

        // Set property
        $this->set_property('last_payment', $value);
    }

    /**
     * Set status since datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_status_since($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_status_since($value);

        // Set property
        $this->set_property('status_since', $value);
    }

    /**
     * Set status by
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function set_status_by($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_status_by($value);

        // Set property
        $this->set_property('status_by', $value);
    }

    /**
     * Set previous status
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function set_previous_status($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_previous_status($value);

        // Set property
        $this->set_property('previous_status', $value);
    }

    /**
     * Set initial order id
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return void
     */
    public function set_initial_order_id($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_initial_order_id($value);

        // Get current value
        $current_value = $this->get_initial_order_id('edit');

        // New initial order id is being set
        if ($this->is_data_ready() && $value !== $current_value) {

            // Current value is not empty - once initial order id is set, it must never be changed
            if ($current_value) {
                throw new RightPress_Exception('rp_sub_subscription_initial_order_id_already_set', 'Initial order ID is already set.');
            }

            // This method must not be called before subscription is saved to the database
            if (!$this->get_id()) {
                throw new RightPress_Exception('rp_sub_subscription_must_be_saved_before_setting_initial_order_id', 'Subscription object must be saved before setting initial order ID.');
            }

            // Add order cross reference
            $this->add_order_cross_reference($value);
        }

        // Set property
        $this->set_property('initial_order_id', $value);
    }

    /**
     * Set pending renewal order id
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return void
     */
    public function set_pending_renewal_order_id($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_pending_renewal_order_id($value);

        // Add order cross reference
        if ($this->is_data_ready() && $value && $value !== $this->get_pending_renewal_order_id('edit')) {
            $this->add_order_cross_reference($value);
        }

        // Maybe set last_renewal_order_id property
        if ($value !== null) {
            $this->set_property('last_renewal_order_id', $value);
        }

        // Set property
        $this->set_property('pending_renewal_order_id', $value);
    }

    /**
     * Set last renewal order id
     *
     * Note: Only used when constructing existing objects, direct modification is forbidden
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return void
     */
    public function set_last_renewal_order_id($value)
    {

        // Data is ready, direct modification is forbidden
        if ($this->is_data_ready()) {
            return;
        }

        // Sanitize integer
        $value = $this->sanitize_int($value, 'last_renewal_order_id');

        // Set last_renewal_order_id property
        $this->set_property('last_renewal_order_id', $value);
    }

    /**
     * Set payment applied order ids
     *
     * Note: Only used when constructing existing objects, direct modification is forbidden
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return void
     */
    public function set_payment_applied_order_ids($value)
    {

        // Data is ready, direct modification is forbidden
        if ($this->is_data_ready()) {
            return;
        }

        // Set payment_applied_order_ids property
        $this->set_property('payment_applied_order_ids', (array) $value);
    }

    /**
     * Set payment gateway options
     *
     * @access public
     * @param array $value
     * @return void
     */
    public function set_payment_gateway_options($value)
    {

        // Set payment_gateway_options property
        $this->set_property('payment_gateway_options', (array) $value);
    }

    /**
     * Set customer pause count
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return void
     */
    public function set_customer_pause_count($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_customer_pause_count($value);

        // Set property
        $this->set_property('customer_pause_count', $value);
    }

    /**
     * Set scheduled schedule revision datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_scheduled_schedule_revision($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_scheduled_schedule_revision($value);

        // Set property
        $this->set_property('scheduled_schedule_revision', $value);
    }

    /**
     * Set scheduled renewal order datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_scheduled_renewal_order($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_scheduled_renewal_order($value);

        // Set property
        $this->set_property('scheduled_renewal_order', $value);
    }

    /**
     * Set scheduled renewal payment datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_scheduled_renewal_payment($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_scheduled_renewal_payment($value);

        // Set property
        $this->set_property('scheduled_renewal_payment', $value);
    }

    /**
     * Set scheduled payment retry datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_scheduled_payment_retry($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_scheduled_payment_retry($value);

        // Set property
        $this->set_property('scheduled_payment_retry', $value);
    }

    /**
     * Set scheduled payment reminder datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_scheduled_payment_reminder($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_scheduled_payment_reminder($value);

        // Set property
        $this->set_property('scheduled_payment_reminder', $value);
    }

    /**
     * Set scheduled subscription resumption datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_scheduled_subscription_resume($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_scheduled_subscription_resume($value);

        // Set property
        $this->set_property('scheduled_subscription_resume', $value);
    }

    /**
     * Set scheduled subscription suspension datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_scheduled_subscription_suspend($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_scheduled_subscription_suspend($value);

        // Set property
        $this->set_property('scheduled_subscription_suspend', $value);
    }

    /**
     * Set scheduled subscription cancellation datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_scheduled_subscription_cancel($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_scheduled_subscription_cancel($value);

        // Set property
        $this->set_property('scheduled_subscription_cancel', $value);
    }

    /**
     * Set scheduled subscription expiration datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return void
     */
    public function set_scheduled_subscription_expire($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_scheduled_subscription_expire($value);

        // Set property
        $this->set_property('scheduled_subscription_expire', $value);
    }


    /**
     * =================================================================================================================
     * SANITIZERS - VALIDATORS
     * =================================================================================================================
     */

    /**
     * Sanitize billing cycle
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return string
     */
    public function sanitize_billing_cycle($value)
    {

        // Sanitize period length
        $value = $this->sanitize_period_length($value);

        // Subscription must have a billing cycle
        if (!$value) {
            throw new RightPress_Exception('rp_sub_subscription_must_have_billing_cycle', esc_html__('Subscription must have a billing cycle set.', 'subscriptio'));
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize prepaid billing cycle
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return string
     */
    public function sanitize_prepaid_billing_cycle($value)
    {

        // Sanitize period length
        $value = $this->sanitize_period_length($value);

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize free trial
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return string
     */
    public function sanitize_free_trial($value)
    {

        // Sanitize period length
        $value = $this->sanitize_period_length($value);

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize lifespan
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return string
     */
    public function sanitize_lifespan($value)
    {

        // Sanitize period length
        $value = $this->sanitize_period_length($value);

        // Additional validation against next renewal payment datetime
        if ($value !== null) {

            // Check if first payment datetime is set
            if ($datetime = $this->get_first_payment()) {

                // Add lifespan length to first payment datetime to get expiration datetime
                RP_SUB_Time::add_period_length_to_datetime($datetime, $value);

                // Expiration datetime must not be before next renewal payment datetime
                if ($datetime < $this->calculate_next_renewal_payment_datetime()) {
                    throw new RightPress_Exception('rp_sub_subscription_invalid_lifespan', esc_html__('Subscription lifespan must not end before the next renewal payment date.', 'subscriptio'));
                }
            }
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize period length
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return int
     */
    public function sanitize_period_length($value)
    {

        // Value is set
        if ($value !== null) {

            // Split into length and period
            $data = explode(' ', $value);

            // Period length string expects exactly two values separated by space character - length and period, e.g. '2 week'
            if (!is_array($data) || count($data) !== 2) {
                throw new RightPress_Exception('rp_sub_subscription_invalid_period_length', esc_html__('Invalid subscription period length.', 'subscriptio'));
            }

            // Get length and period
            $length = $this->sanitize_int($data[0], 'period_length');
            $period = $this->sanitize_string($data[1], 'period_length');

            // Length must be a positive whole number
            if (!RightPress_Help::is_whole_number($length) || $length < 1) {
                throw new RightPress_Exception('rp_sub_subscription_invalid_period_length', esc_html__('Invalid subscription period length.', 'subscriptio'));
            }

            // Period must be one of the defined values
            if (!RP_SUB_Time::validate_time_period($period)) {
                throw new RightPress_Exception('rp_sub_subscription_invalid_period_length', esc_html__('Invalid subscription period length.', 'subscriptio'));
            }

            // Combine length and period back into period length string
            $value = $length . ' ' . $period;
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize first payment datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_first_payment($value)
    {

        // Sanitize past datetime
        $value = $this->sanitize_past_datetime($value, 'first_payment');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize last payment datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_last_payment($value)
    {

        // Sanitize datetime
        // Note: We can't use sanitize_past_datetime() here since customers could pay their renewal orders earlier than the actual payment due date
        $value = $this->sanitize_datetime($value, 'last_payment');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize status since datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_status_since($value)
    {

        // Sanitize past datetime
        $value = $this->sanitize_past_datetime($value, 'status_since');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize status by
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_status_by($value)
    {

        // Validate value
        if (!in_array($value, array('system', 'admin', 'customer'), true)) {
            throw new RightPress_Exception('rp_sub_subscription_invalid_status_by', 'Subscription property status_by can only be set to system, admin or customer.');
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize previous status
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return string
     */
    public function sanitize_previous_status($value)
    {

        // Sanitize string
        $value = $this->sanitize_string($value, 'previous_status');

        // Get all statuses
        $statuses = RP_SUB_Subscription_Controller::get_subscription_statuses();

        // Invalid status
        if (!isset($statuses[$value]) && !in_array($value, array('trash', 'draft', 'auto-draft'), true)) {
            throw new RightPress_Exception('rp_sub_subscription_invalid_previous_status', esc_html__('Invalid subscription previous status.', 'subscriptio'));
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize initial_order id
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return int
     */
    public function sanitize_initial_order_id($value)
    {

        // Sanitize integer
        $value = $this->sanitize_int($value, 'initial_order_id');

        // Extra checks when changing initial order id value
        if ($value !== null && $this->is_data_ready()) {

            // Invalid order id
            if (!wc_get_order($value)) {
                throw new RightPress_Exception('rp_sub_subscription_invalid_initial_order_id', esc_html__('Invalid subscription initial order ID.', 'subscriptio'));
            }

            // Payment on this order has already been applied to subscription
            if (in_array($value, $this->get_payment_applied_order_ids(), true)) {
                throw new RightPress_Exception('rp_sub_subscription_invalid_initial_order_id', esc_html__('Payment on this order has already been applied to this subscription.', 'subscriptio'));
            }
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize pending renewal order id
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return int
     */
    public function sanitize_pending_renewal_order_id($value)
    {

        // Sanitize integer
        $value = $this->sanitize_int($value, 'pending_renewal_order_id');

        // Invalid order id
        if ($value !== null && !wc_get_order($value)) {
            throw new RightPress_Exception('rp_sub_subscription_invalid_pending_renewal_order_id', esc_html__('Invalid subscription pending renewal order ID.', 'subscriptio'));
        }

        // Payment on this order has already been applied to subscription
        if (in_array($value, $this->get_payment_applied_order_ids(), true)) {
            throw new RightPress_Exception('rp_sub_subscription_invalid_pending_renewal_order_id', esc_html__('Payment on this order has already been applied to this subscription.', 'subscriptio'));
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize customer pause count
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return int
     */
    public function sanitize_customer_pause_count($value)
    {

        // Sanitize integer
        $value = $this->sanitize_int($value, 'customer_pause_count');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize scheduled schedule revision datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_scheduled_schedule_revision($value)
    {

        // Sanitize future datetime
        $value = $this->sanitize_future_datetime($value, 'scheduled_schedule_revision');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize scheduled renewal order datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_scheduled_renewal_order($value)
    {

        // Sanitize future datetime
        $value = $this->sanitize_future_datetime($value, 'scheduled_renewal_order');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize scheduled renewal payment datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_scheduled_renewal_payment($value)
    {

        // Sanitize future datetime
        $value = $this->sanitize_future_datetime($value, 'scheduled_renewal_payment');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize scheduled payment retry datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_scheduled_payment_retry($value)
    {

        // Sanitize future datetime
        $value = $this->sanitize_future_datetime($value, 'scheduled_payment_retry');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize scheduled payment reminder datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_scheduled_payment_reminder($value)
    {

        // Sanitize future datetime
        $value = $this->sanitize_future_datetime($value, 'scheduled_payment_reminder');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize scheduled subscription resumption datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_scheduled_subscription_resume($value)
    {

        // Sanitize future datetime
        $value = $this->sanitize_future_datetime($value, 'scheduled_subscription_resume');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize scheduled subscription suspension datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_scheduled_subscription_suspend($value)
    {

        // Sanitize future datetime
        $value = $this->sanitize_future_datetime($value, 'scheduled_subscription_suspend');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize scheduled subscription cancellation datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_scheduled_subscription_cancel($value)
    {

        // Sanitize future datetime
        $value = $this->sanitize_future_datetime($value, 'scheduled_subscription_cancel');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize scheduled subscription expiration datetime
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public function sanitize_scheduled_subscription_expire($value)
    {

        // Sanitize future datetime
        $value = $this->sanitize_future_datetime($value, 'scheduled_subscription_expire');

        // Return sanitized value
        return $value;
    }


    /**
     * =================================================================================================================
     * STATUS TRANSITIONING
     * =================================================================================================================
     */

    /**
     * Start subscription trial - proxy to set_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access public
     * @param string $actor
     * @return void
     */
    public function start_trial($actor = 'system')
    {

        // Attempt to set status to trial
        $this->set_status('trial', $actor);
    }

    /**
     * Activate subscription - proxy to set_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access public
     * @param string $actor
     * @return void
     */
    public function activate($actor = 'system')
    {

        // Attempt to set status to active
        $this->set_status('active', $actor);
    }

    /**
     * Pause subscription - proxy to set_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access public
     * @param string $actor
     * @return void
     */
    public function pause($actor = 'system')
    {

        // Attempt to set status to paused
        $this->set_status('paused', $actor);
    }

    /**
     * Resume subscription - proxy to set_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access public
     * @param string $actor
     * @return void
     */
    public function resume($actor = 'system')
    {

        // Subscription is not paused
        if (!$this->has_status('paused')) {
            throw new RightPress_Exception('rp_sub_subscription_resume_not_paused', esc_html__('Trying to resume subscription that is not paused.', 'subscriptio'));
        }

        // Get previous status
        $previous_status = $this->get_previous_status();

        // Attempt to set previous status
        $this->set_status($previous_status, $actor);
    }

    /**
     * Mark subscription overdue - proxy to set_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access public
     * @param string $actor
     * @return void
     */
    public function mark_overdue($actor = 'system')
    {

        // Attempt to set status to overdue
        $this->set_status('overdue', $actor);
    }

    /**
     * Suspend subscription - proxy to set_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access public
     * @param string $actor
     * @return void
     */
    public function suspend($actor = 'system')
    {

        // Attempt to set status to suspended
        $this->set_status('suspended', $actor);
    }

    /**
     * Set subscription to cancel at the end of billing cycle - proxy to set_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access public
     * @param string $actor
     * @return void
     */
    public function set_to_cancel($actor = 'system')
    {

        // Attempt to set status to set-to-cancel
        $this->set_status('set-to-cancel', $actor);
    }

    /**
     * Reactivate subscription after setting it to cancel - proxy to set_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access public
     * @param string $actor
     * @return void
     */
    public function reactivate($actor = 'system')
    {

        // Subscription is not set to cancel
        if (!$this->has_status('set-to-cancel')) {
            throw new RightPress_Exception('rp_sub_subscription_reactivate_not_set_to_cancel', esc_html__('Trying to reactivate subscription that is not set to cancel.', 'subscriptio'));
        }

        // Get previous status
        $previous_status = $this->get_previous_status();

        // Attempt to set previous status
        $this->set_status($previous_status, $actor);
    }

    /**
     * Cancel subscription - proxy to set_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access public
     * @param string $actor
     * @return void
     */
    public function cancel($actor = 'system')
    {

        // Attempt to set status to cancelled
        $this->set_status('cancelled', $actor);
    }

    /**
     * Expire subscription - proxy to set_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access public
     * @param string $actor
     * @return void
     */
    public function expire($actor = 'system')
    {

        // Attempt to set status to expired
        $this->set_status('expired', $actor);
    }

    /**
     * Set status
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access public
     * @param string $new_status
     * @param string $actor
     * @return void
     */
    public function set_status($new_status, $actor = 'system')
    {

        // Get current status
        $current_status = $this->get_status('edit');

        // Status has not changed, nothing to do
        if ($new_status === $current_status) {
            return;
        }

        try {

            // Mark new subscription as pending
            if ($current_status === 'draft') {
                $this->handle_mark_pending($current_status, $actor);
            }
            // Resume subscription that is paused
            else if ($current_status === 'paused' && $new_status !== 'cancelled') {
                $this->handle_resume($current_status, $actor);
            }
            // Reactive subscription that is set to cancel
            else if ($current_status === 'set-to-cancel' && !in_array($new_status, array('cancelled', 'expired'), true)) {
                $this->handle_reactivate($current_status, $actor);
            }
            // Start trial
            else if ($new_status === 'trial') {
                $this->handle_start_trial($current_status, $actor);
            }
            // Activate subscription
            else if ($new_status === 'active') {
                $this->handle_activate($current_status, $actor);
            }
            // Pause subscription
            else if ($new_status === 'paused') {
                $this->handle_pause($current_status, $actor);
            }
            // Mark overdue
            else if ($new_status === 'overdue') {
                $this->handle_mark_overdue($current_status, $actor);
            }
            // Suspend subscription
            else if ($new_status === 'suspended') {
                $this->handle_suspend($current_status, $actor);
            }
            // Set to cancel
            else if ($new_status === 'set-to-cancel') {
                $this->handle_set_to_cancel($current_status, $actor);
            }
            // Cancel subscription
            else if ($new_status === 'cancelled') {
                $this->handle_cancel($current_status, $actor);
            }
            // Expire subscription
            else if ($new_status === 'expired') {
                $this->handle_expire($current_status, $actor);
            }
        }
        catch (Exception $e) {

            // Log this event if logging is not active (otherwise we expect parent method to log this exception properly)
            if (!$this->get_log_entry()) {

                RP_SUB_Log_Entry_Controller::add_log_entry(array(
                    'event_type'        => 'unexpected_error',
                    'subscription_id'   => $this->get_id(),
                    'status'            => 'error',
                    'error_details'     => $e->getTraceAsString(),
                    'notes'             => array(
                        esc_html__('Error occurred while setting subscription status.', 'subscriptio'),
                        $e->getMessage()
                    ),
                ));
            }

            // Throw exception as we want this to be noticed as soon as possible and we want no further operations to be performed during current request (invalid status changes are never expected)
            throw $e;
        }
    }

    /**
     * Mark new subscription as pending - proxy to transition_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access private
     * @param string $current_status
     * @param string $actor
     * @return void
     */
    private function handle_mark_pending($current_status, $actor = 'system')
    {

        // Transition status to 'pending' if operation is permitted
        if ($this->can_be_marked_pending($actor, true)) {
            $this->transition_status($current_status, 'pending', $actor);
        }
    }

    /**
     * Handle start trial - proxy to transition_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access private
     * @param string $current_status
     * @param string $actor
     * @return void
     */
    private function handle_start_trial($current_status, $actor = 'system')
    {

        // Transition status to 'trial' if operation is permitted
        if ($this->can_have_trial_started($actor, true)) {
            $this->transition_status($current_status, 'trial', $actor);
        }
    }

    /**
     * Handle activate - proxy to transition_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access private
     * @param string $current_status
     * @param string $actor
     * @return void
     */
    private function handle_activate($current_status, $actor = 'system')
    {

        // Transition status to 'active' if operation is permitted
        if ($this->can_be_activated($actor, true)) {
            $this->transition_status($current_status, 'active', $actor);
        }
    }

    /**
     * Handle pause - proxy to transition_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access private
     * @param string $current_status
     * @param string $actor
     * @return void
     */
    private function handle_pause($current_status, $actor = 'system')
    {

        // Check if subscription can be paused
        if ($this->can_be_paused($actor, true)) {

            // Transition status to paused
            $this->transition_status($current_status, 'paused', $actor);

            // Increment customer pause count
            $this->increment_customer_pause_count();
        }
    }

    /**
     * Handle resume - proxy to transition_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access private
     * @param string $current_status
     * @param string $actor
     * @return void
     */
    private function handle_resume($current_status, $actor = 'system')
    {

        // Check if subscription can be resumed
        if ($this->can_be_resumed($actor, true)) {

            // Maybe add pause days to payment schedule
            $this->maybe_add_days_to_payment_schedule();

            // Proceed depending on previous status
            switch ($this->get_previous_status()) {

                // Restart trial
                case 'trial':
                    $this->handle_start_trial($current_status, $actor);
                    break;

                // Activate
                case 'active':
                    $this->handle_activate($current_status, $actor);
                    break;

                // Mark overdue
                case 'overdue':
                    $this->handle_mark_overdue($current_status, $actor);
                    break;

                // Suspend
                case 'suspended':
                    $this->handle_suspend($current_status, $actor);
                    break;

                // Undefined previous status
                default:
                    throw new RightPress_Exception('rp_sub_subscription_resume_undefined_previous_status', esc_html__('Undefined previous subscription status.', 'subscriptio'));
            }
        }
    }

    /**
     * Handle mark overdue - proxy to transition_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access private
     * @param string $current_status
     * @param string $actor
     * @return void
     */
    private function handle_mark_overdue($current_status, $actor = 'system')
    {

        // Transition status to 'overdue' if operation is permitted
        if ($this->can_be_marked_overdue($actor, true)) {
            $this->transition_status($current_status, 'overdue', $actor);
        }
    }

    /**
     * Handle suspend - proxy to transition_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access private
     * @param string $current_status
     * @param string $actor
     * @return void
     */
    private function handle_suspend($current_status, $actor = 'system')
    {

        // Transition status to 'suspended' if operation is permitted
        if ($this->can_be_suspended($actor, true)) {
            $this->transition_status($current_status, 'suspended', $actor);
        }
    }

    /**
     * Handle set to cancel - proxy to transition_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access private
     * @param string $current_status
     * @param string $actor
     * @return void
     */
    private function handle_set_to_cancel($current_status, $actor = 'system')
    {

        // Transition status to 'set-to-cancel' if operation is permitted
        if ($this->can_be_set_to_cancel($actor, true)) {
            $this->transition_status($current_status, 'set-to-cancel', $actor);
        }
    }

    /**
     * Handle reactivate - proxy to transition_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access private
     * @param string $current_status
     * @param string $actor
     * @return void
     */
    private function handle_reactivate($current_status, $actor = 'system')
    {

        // Check if subscription can be reactivated
        if ($this->can_be_reactivated($actor, true)) {

            // Proceed depending on previous status
            switch ($this->get_previous_status()) {

                // Restart trial
                case 'trial':
                    $this->handle_start_trial($current_status, $actor);
                    break;

                // Activate
                case 'active':
                    $this->handle_activate($current_status, $actor);
                    break;

                // Undefined previous status
                default:
                    throw new RightPress_Exception('rp_sub_subscription_reactivate_undefined_previous_status', esc_html__('Undefined previous subscription status.', 'subscriptio'));
            }
        }
    }

    /**
     * Handle cancel - proxy to transition_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access private
     * @param string $current_status
     * @param string $actor
     * @return void
     */
    private function handle_cancel($current_status, $actor = 'system')
    {

        // Check if subscription can be cancelled
        if ($this->can_be_cancelled($actor, true, in_array($actor, array('admin', 'customer'), true))) {

            // Set flag for the remainder of current request
            $this->just_terminated = true;

            // Transition status to cancelled
            $this->transition_status($current_status, 'cancelled', $actor);
        }
    }

    /**
     * Handle expire - proxy to transition_status()
     *
     * Throws RightPress_Exception if status transition is not permitted or error occurs
     *
     * @access private
     * @param string $current_status
     * @param string $actor
     * @return void
     */
    private function handle_expire($current_status, $actor = 'system')
    {

        // Check if subscription can be expired
        if ($this->can_be_expired($actor, true)) {

            // Set flag for the remainder of current request
            $this->just_terminated = true;

            // Transition status to expired
            $this->transition_status($current_status, 'expired', $actor);
        }
    }

    /**
     * Transition status
     *
     * @access private
     * @param string $old_status
     * @param string $new_status
     * @param string $actor
     * @return bool
     */
    private function transition_status($old_status, $new_status, $actor = 'system')
    {

        // Get old status label
        $old_status_label = $this->get_status_label();

        // Trigger subscription status changing actions
        // Note: Order of these action calls must not be changed as other areas of the plugin might depend on this particular sequence
        do_action('subscriptio_subscription_status_changing', $this, $old_status, $new_status);
        do_action('subscriptio_subscription_status_changing_from_' . $old_status, $this, $new_status);
        do_action('subscriptio_subscription_status_changing_to_' . $new_status, $this, $old_status);
        do_action('subscriptio_subscription_status_changing_from_' . $old_status . '_to_' . $new_status, $this);

        // Set new status
        $this->get_suborder()->set_status($new_status);

        // Set status since property
        $this->set_status_since(time());

        // Set status by property
        $this->set_status_by($actor);

        // Set previous status
        $this->set_previous_status($old_status);

        // Set flag
        $this->just_changed_status = true;

        // Save subscription
        $this->save();

        // Add note to log entry
        $this->add_log_entry_note(sprintf(esc_html__('Subscription status changed from %1$s to %2$s.', 'subscriptio'), $old_status_label, $this->get_status_label()));

        // Trigger subscription status changed actions
        // Note: Order of these action calls must not be changed as other areas of the plugin might depend on this particular sequence
        do_action('subscriptio_subscription_status_changed', $this, $old_status, $new_status);
        do_action('subscriptio_subscription_status_changed_from_' . $old_status, $this, $new_status);
        do_action('subscriptio_subscription_status_changed_to_' . $new_status, $this, $old_status);
        do_action('subscriptio_subscription_status_changed_from_' . $old_status . '_to_' . $new_status, $this);
    }

    /**
     * Check if subscription can be marked pending
     *
     * @access public
     * @param string $actor
     * @param bool $throw_exception
     * @return bool|void
     */
    public function can_be_marked_pending($actor = 'system', $throw_exception = false)
    {

        // Subscription must have an owner
        if (!$this->get_suborder()->get_customer_id()) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_missing_required_properties', esc_html__('Subscription must have a customer set.', 'subscriptio'));
            }

            // Subscription can't bet marked pending
            return false;
        }

        // Subscription must have at least one item
        if (!$this->get_suborder()->get_items()) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_missing_required_properties', esc_html__('Subscription must have at least one item.', 'subscriptio'));
            }

            // Subscription can't bet marked pending
            return false;
        }

        // Subscription must have a billing cycle
        if (!$this->get_billing_cycle()) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_missing_required_properties', esc_html__('Subscription must have a billing cycle set.', 'subscriptio'));
            }

            // Subscription can't bet marked pending
            return false;
        }

        // Subscription can be marked pending
        return true;
    }

    /**
     * Check if subscription can have trial started
     *
     * @access public
     * @param string $actor
     * @param bool $throw_exception
     * @return bool|void
     */
    public function can_have_trial_started($actor = 'system', $throw_exception = false)
    {

        // Subscription is already in trial, nothing to do
        if ($this->has_status('trial')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_setting_existing_status', esc_html__('Subscription already has this status, nothing to do.', 'subscriptio'));
            }

            // Subscription can't have trial started
            return false;
        }

        // Check if subscription can have current status changed to trial
        if (!$this->can_have_status_changed_to('trial')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_prohibited_status_transition', esc_html__('Subscription status change from current status to the requested status is prohibited.', 'subscriptio'));
            }

            // Subscription can't have trial started
            return false;
        }

        // Free trial is not configured
        if (!$this->get_free_trial()) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_trial_not_configured', esc_html__('Subscription cannot have a trial started because trial is not configured.', 'subscriptio'));
            }

            // Subscription can't have trial started
            return false;
        }

        // Check customer trial limits if trial is starting for the first time for this subscription (existing trials can restart after a pause)
        if ($this->has_status('pending')) {

            $limit_reached = false;

            // Iterate over subscription items
            foreach ($this->get_items() as $item) {

                // Get absolute product id
                $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();

                // Customer is not eligible for a free trial of this product
                if (!RP_SUB_Customer::customer_is_eligible_for_trial($product_id, $this->get_customer_id(), $this->get_id())) {

                    // Set flag
                    $limit_reached = true;
                }
            }

            // Customer is not eligible for a free trial
            if ($limit_reached) {

                // Maybe throw exception
                if ($throw_exception) {
                    throw new RightPress_Exception('rp_sub_subscription_trial_limit_reached', esc_html__('Subscription cannot have a trial started because customer has reached their trial limit.', 'subscriptio'));
                }

                // Subscription can't have trial started
                return false;
            }
        }

        // Subscription can have trial started
        return true;
    }

    /**
     * Check if subscription can be activated
     *
     * @access public
     * @param string $actor
     * @param bool $throw_exception
     * @return bool|void
     */
    public function can_be_activated($actor = 'system', $throw_exception = false)
    {

        // Subscription is already active, nothing to do
        if ($this->has_status('active')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_setting_existing_status', esc_html__('Subscription already has this status, nothing to do.', 'subscriptio'));
            }

            // Subscription can't be activated
            return false;
        }

        // Check if subscription can have current status changed to active
        if (!$this->can_have_status_changed_to('active')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_prohibited_status_transition', esc_html__('Subscription status change from current status to the requested status is prohibited.', 'subscriptio'));
            }

            // Subscription can't be activated
            return false;
        }

        // Subscription can be activated
        return true;
    }

    /**
     * Check if subscription can be paused
     *
     * @access public
     * @param string $actor
     * @param bool $throw_exception
     * @return bool|void
     */
    public function can_be_paused($actor = 'system', $throw_exception = false)
    {

        // Subscription is already paused, nothing to do
        if ($this->has_status('paused')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_setting_existing_status', esc_html__('Subscription already has this status, nothing to do.', 'subscriptio'));
            }

            // Subscription can't be paused
            return false;
        }

        // Check if subscription can have current status changed to paused
        if (!$this->can_have_status_changed_to('paused', $actor)) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_prohibited_status_transition', esc_html__('Subscription status change from current status to the requested status is prohibited.', 'subscriptio'));
            }

            // Subscription can't be paused
            return false;
        }

        // Subscription can be paused
        return true;
    }

    /**
     * Check if subscription can be resumed
     *
     * @access public
     * @param string $actor
     * @param bool $throw_exception
     * @return bool|void
     */
    public function can_be_resumed($actor = 'system', $throw_exception = false)
    {

        // Subscription is not paused
        if (!$this->has_status('paused')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_resuming_non_paused', esc_html__('Subscription cannot bet resumed because it is not paused.', 'subscriptio'));
            }

            // Subscription can't be resumed
            return false;
        }

        // Get previous status
        $previous_status = $this->get_previous_status('edit');

        // Subscription can't have current status changed to previous status
        if (!$this->can_have_status_changed_to($previous_status, $actor)) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_prohibited_status_transition', esc_html__('Subscription status change from current status to the requested status is prohibited.', 'subscriptio'));
            }

            // Subscription can't be resumed
            return false;
        }

        // Subscription can be resumed
        return true;
    }

    /**
     * Check if subscription can be marked overdue
     *
     * @access public
     * @param string $actor
     * @param bool $throw_exception
     * @return bool|void
     */
    public function can_be_marked_overdue($actor = 'system', $throw_exception = false)
    {

        // Subscription is already overdue, nothing to do
        if ($this->has_status('overdue')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_setting_existing_status', esc_html__('Subscription already has this status, nothing to do.', 'subscriptio'));
            }

            // Subscription can't be marked overdue
            return false;
        }

        // Check if subscription can have current status changed to overdue
        if (!$this->can_have_status_changed_to('overdue')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_prohibited_status_transition', esc_html__('Subscription status change from current status to the requested status is prohibited.', 'subscriptio'));
            }

            // Subscription can't be marked overdue
            return false;
        }

        // Subscription can be marked overdue
        return true;
    }

    /**
     * Check be suspended
     *
     * @access public
     * @param string $actor
     * @param bool $throw_exception
     * @return bool|void
     */
    public function can_be_suspended($actor = 'system', $throw_exception = false)
    {

        // Subscription is already suspended, nothing to do
        if ($this->has_status('suspended')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_setting_existing_status', esc_html__('Subscription already has this status, nothing to do.', 'subscriptio'));
            }

            // Subscription can't be suspended
            return false;
        }

        // Check if subscription can have current status changed to suspended
        if (!$this->can_have_status_changed_to('suspended')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_prohibited_status_transition', esc_html__('Subscription status change from current status to the requested status is prohibited.', 'subscriptio'));
            }

            // Subscription can't be suspended
            return false;
        }

        // Subscription is not pending renewal payment
        if (!$this->is_pending_renewal_payment(true)) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_suspending_paid_subscription', esc_html__('Subscription cannot be suspended because it is not pending payment.', 'subscriptio'));
            }

            // Subscription can't be suspended
            return false;
        }

        // Subscription can be suspended
        return true;
    }

    /**
     * Check if subscription can be set to cancel at the end of billing cycle
     *
     * @access public
     * @param string $actor
     * @param bool $throw_exception
     * @return bool|void
     */
    public function can_be_set_to_cancel($actor = 'system', $throw_exception = false)
    {

        // Subscription is already set to cancel, nothing to do
        if ($this->has_status('set-to-cancel')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_setting_existing_status', esc_html__('Subscription already has this status, nothing to do.', 'subscriptio'));
            }

            // Subscription can't be set to cancel
            return false;
        }

        // Check if subscription can have current status changed to set-to-cancel
        if (!$this->can_have_status_changed_to('set-to-cancel')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_prohibited_status_transition', esc_html__('Subscription status change from current status to the requested status is prohibited.', 'subscriptio'));
            }

            // Subscription can't be set to cancel
            return false;
        }

        // Subscription can be set to cancel
        return true;
    }

    /**
     * Check if subscription can be reactivated
     *
     * @access public
     * @param string $actor
     * @param bool $throw_exception
     * @return bool|void
     */
    public function can_be_reactivated($actor = 'system', $throw_exception = false)
    {

        // Subscription is not set to cancel
        if (!$this->has_status('set-to-cancel')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_reactivating_not_set_to_cancel', esc_html__('Subscription cannot be reactivated as it is not set to cancel.', 'subscriptio'));
            }

            // Subscription can't be reactivated
            return false;
        }

        // Get previous status
        $previous_status = $this->get_previous_status('edit');

        // Subscription can't have current status changed to previous status
        if (!$this->can_have_status_changed_to($previous_status, $actor)) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_prohibited_status_transition', esc_html__('Subscription status change from current status to the requested status is prohibited.', 'subscriptio'));
            }

            // Subscription can't be reactivated
            return false;
        }

        // Subscription can be reactivated
        return true;
    }

    /**
     * Check if subscription can be cancelled
     *
     * @access public
     * @param string $actor
     * @param bool $throw_exception
     * @param bool $manual_request
     * @return bool|void
     */
    public function can_be_cancelled($actor = 'system', $throw_exception = false, $manual_request = false)
    {

        // Subscription is cancelled, nothing to do
        if ($this->has_status('cancelled')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_setting_existing_status', esc_html__('Subscription already has this status, nothing to do.', 'subscriptio'));
            }

            // Subscription can't be cancelled
            return false;
        }

        // Check if subscription can have current status changed to cancelled
        if (!$this->can_have_status_changed_to('cancelled', $actor)) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_prohibited_status_transition', esc_html__('Subscription status change from current status to the requested status is prohibited.', 'subscriptio'));
            }

            // Subscription can't be cancelled
            return false;
        }

        // Subscription has started and is neither set to cancel, nor pending payment, cancellation is not manual or caused by a voided last payment
        if (!$this->has_status('pending') && !$this->has_status('set-to-cancel') && !$this->is_pending_renewal_payment() && !$manual_request && !$this->last_payment_just_voided) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_unexpected_cancelling', esc_html__('Subscription cannot be cancelled because it is fully paid and is not set to cancel.', 'subscriptio'));
            }

            // Subscription can't be cancelled
            return false;
        }

        // Subscription can be cancelled
        return true;
    }

    /**
     * Check if subscription can be expired
     *
     * @access public
     * @param string $actor
     * @param bool $throw_exception
     * @return bool|void
     */
    public function can_be_expired($actor = 'system', $throw_exception = false)
    {

        // Subscription is expired, nothing to do
        if ($this->has_status('expired')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_setting_existing_status', esc_html__('Subscription already has this status, nothing to do.', 'subscriptio'));
            }

            // Subscription can't be expired
            return false;
        }

        // Subscription can't have current status changed to expired
        if (!$this->can_have_status_changed_to('expired')) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_prohibited_status_transition', esc_html__('Subscription status change from current status to the requested status is prohibited.', 'subscriptio'));
            }

            // Subscription can't be expired
            return false;
        }

        // Subscription does not have a limit on its lifespan
        if (!$this->get_lifespan()) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_expiring_no_lifespan_limit', esc_html__('Subscription cannot be expired because it does not have a lifespan limit set.', 'subscriptio'));
            }

            // Subscription can't be expired
            return false;
        }

        // Subscription expiration date is still in the future
        if (RP_SUB_Time::is_future($this->calculate_expiration_datetime())) {

            // Maybe throw exception
            if ($throw_exception) {
                throw new RightPress_Exception('rp_sub_subscription_expiring_too_early', esc_html__('Subscription cannot be expired now as its expiration date is in the future.', 'subscriptio'));
            }

            // Subscription can't be expired
            return false;
        }

        // Subscription can be expired
        return true;
    }

    /**
     * Check if subscription can have status changed to new status
     *
     * Throws RightPress_Exception if undefined statuses are detected
     *
     * @access public
     * @param string $new_status
     * @param string $actor
     * @return bool
     */
    public function can_have_status_changed_to($new_status, $actor = 'system')
    {

        // Get current status
        $current_status = $this->get_status('edit');

        // Get all statuses
        $statuses = RP_SUB_Subscription_Controller::get_subscription_statuses();

        // Special handling for 'auto-draft' and 'draft' statuses - these can only be changed to 'pending'
        if (in_array($current_status, array('auto-draft', 'draft'), true)) {
            return ($new_status === 'pending');
        }

        // Invalid current status
        if (!isset($statuses[$current_status])) {
            throw new RightPress_Exception('rp_sub_subscription_invalid_current_status', esc_html__('Invalid current subscription status.', 'subscriptio'));
        }

        // Invalid new status
        if (!isset($statuses[$new_status])) {
            throw new RightPress_Exception('rp_sub_subscription_invalid_new_status', esc_html__('Invalid new subscription status.', 'subscriptio'));
        }

        // Such status transition is not allowed in general
        if (!isset($statuses[$current_status]["{$actor}_change_to"]) || !in_array($new_status, $statuses[$current_status]["{$actor}_change_to"], true)) {
            return false;
        }

        // Allow developers to prevent status change
        if (!apply_filters('subscriptio_subscription_can_have_status_changed', true, $new_status, $current_status, $actor, $this)) {
            return false;
        }

        // Don't allow resuming or reactivating to a different status than subscription had before
        if (($current_status === 'paused' || $current_status === 'set-to-cancel') && !in_array($new_status, array('cancelled', 'expired'), true) && $new_status !== $this->get_previous_status('edit')) {
            return false;
        }

        // Specific limitations to changes made by customer
        if ($actor === 'customer') {

            // Don't allow pausing and resuming if pausing is not allowed in settings
            if (($new_status === 'paused' || $current_status === 'paused') && RP_SUB_Settings::is('customer_pausing', 'not_allowed')) {
                return false;
            }

            // Don't allow pausing if pause limit was reached
            if ($new_status === 'paused' && $this->pause_limit_reached()) {
                return false;
            }

            // TODO: Don't allow resuming if paused by admin and customer resuming is not allowed in such cases

            // Don't allow cancelling if cancelling is not allowed in settings
            if (in_array($new_status, array('set-to-cancel', 'cancelled'), true) && RP_SUB_Settings::is('customer_cancelling', 'not_allowed')) {
                return false;
            }

            // TODO: Don't allow reactivating if set to cancel by admin and customer reactivating is not allowed in such cases
        }

        // Status can be changed
        return true;
    }

    /**
     * Check if pause limit was reached by customer
     *
     * @access public
     * @return bool
     */
    public function pause_limit_reached()
    {

        // Get pause limit
        $limit = RP_SUB_Settings::get('customer_pausing_number_limit');

        // Check pause count against limit
        return $limit && $this->get_customer_pause_count() >= $limit;
    }

    /**
     * Increment customer pause count
     *
     * @access private
     * @return void
     */
    private function increment_customer_pause_count()
    {

        // Get new pause count
        $new_pause_count = $this->get_customer_pause_count('edit') + 1;

        // Set new pause count
        $this->set_customer_pause_count($new_pause_count);

        // Save subscription
        $this->save();
    }


    /**
     * =================================================================================================================
     * DOWNLOADABLE PRODUCT MANAGEMENT
     * =================================================================================================================
     */

    /**
     * Checks if product download is permitted
     *
     * @access public
     * @return bool
     */
    public function is_download_permitted()
    {

        return apply_filters('subscriptio_subscription_is_download_permitted', $this->gives_access(), $this);
    }


    /**
     * =================================================================================================================
     * ORDER AND PAYMENT RELATED METHODS
     * =================================================================================================================
     */

    /**
     * Get initial order object
     *
     * @access public
     * @return WC_Order|null
     */
    public function get_initial_order()
    {

        $initial_order = null;

        // Get initial order id
        if ($initial_order_id = $this->get_initial_order_id('edit')) {

            // Load order object
            if ($order = wc_get_order($initial_order_id)) {

                // Set initial order object
                $initial_order = $order;
            }
        }

        return $initial_order;
    }

    /**
     * Get pending renewal order object
     *
     * @access public
     * @return WC_Order|null
     */
    public function get_pending_renewal_order()
    {

        $renewal_order = null;

        // Get pending renewal order id
        if ($renewal_order_id = $this->get_pending_renewal_order_id('edit')) {

            $log_entry_notes = array();

            // Load renewal order object
            $renewal_order = wc_get_order($renewal_order_id);

            // Renewal order has been loaded
            if (is_a($renewal_order, 'WC_Order')) {

                // Renewal order is trashed
                if ($renewal_order->has_status('trash')) {

                    // Add log entry note
                    $log_entry_notes[] = esc_html__('Order set as subscription renewal order appears to be trashed. Removing this order.', 'subscriptio');

                    // Set renewal order to null
                    $renewal_order = null;
                }
                // Renewal order is cancelled
                else if ($renewal_order->has_status('cancelled')) {

                    // Add log entry note
                    $log_entry_notes[] = esc_html__('Order set as subscription renewal order appears to be cancelled. Removing this order.', 'subscriptio');

                    // Set renewal order to null
                    $renewal_order = null;
                }
                // Renewal order does not have a valid renewal order flag set
                else if ($renewal_order->get_meta('_rp_sub:renewal_order') !== 'yes') {

                    // Add log entry note
                    $log_entry_notes[] = esc_html__('Order set as subscription renewal order does not have a proper renewal order flag. Removing this order.', 'subscriptio');

                    // Set renewal order to null
                    $renewal_order = null;
                }
                // Renewal order is not linked to a subscription
                else if ((int) $renewal_order->get_meta('_rp_sub:related_subscription') !== $this->get_id()) {

                    // Add log entry note
                    $log_entry_notes[] = esc_html__('Order set as subscription renewal order is not linked to this subscription. Removing this order.', 'subscriptio');

                    // Set renewal order to null
                    $renewal_order = null;
                }
            }

            // Unable to load renewal order object
            if (!is_a($renewal_order, 'WC_Order')) {

                // Get error note
                $log_entry_notes[] = sprintf(esc_html__('Subscription has pending renewal order id set to #%d but system was not able to load this order. Removing pending renewal order id from subscription.', 'subscriptio'), $renewal_order_id);

                // Add note to existing log entry
                if ($this->has_log_entry()) {

                    $this->add_log_entry_note($log_entry_notes);
                }
                // Add new log entry
                else {

                    RP_SUB_Log_Entry_Controller::add_log_entry(array(
                        'event_type'        => 'unexpected_error',
                        'subscription_id'   => $this->get_id(),
                        'order_id'          => $renewal_order_id,
                        'status'            => 'error',
                        'notes'             => $log_entry_notes,
                    ));
                }

                // Unset pending renewal order id property
                $this->set_pending_renewal_order_id(null);
                $this->save();

                // Trash post in case there is some malformed leftover post that fails to load
                if (RightPress_Help::post_exists($renewal_order_id) && RightPress_Help::post_type_is($renewal_order_id, 'shop_order')) {
                    wp_trash_post($renewal_order_id);
                }

                // Set renewal order to null
                $renewal_order = null;
            }
        }

        return $renewal_order;
    }

    /**
     * Check if subscription has pending renewal order
     *
     * @access public
     * @return bool
     */
    public function has_pending_renewal_order()
    {

        return (bool) $this->get_pending_renewal_order();
    }

    /**
     * Apply subscription payment
     *
     * Important! This must remain the only method through which payments are applied and payment schedule is advanced.
     *
     * @access public
     * @param WC_Order $order
     * @return void
     */
    public function apply_payment($order)
    {

        // Order is not valid
        if (!is_a($order, 'WC_Order')) {
            throw new RightPress_Exception('rp_sub_subscription_apply_payment_invalid_order', esc_html__('Invalid order provided when applying payment to subscription.', 'subscriptio'));
        }

        // Payment on this order has already been applied to this subscription
        if ($this->paid_by_order($order->get_id())) {
            throw new RightPress_Exception('rp_sub_subscription_apply_payment_order_already_applied', sprintf(esc_html__('Payment on order #%d has already been applied to this subscription, aborting.', 'subscriptio'), $order->get_id()));
        }

        // Subscription does not need payment
        if (!$this->needs_payment(true)) {
            throw new RightPress_Exception('rp_sub_subscription_apply_payment_already_paid', esc_html__('Attempted to apply payment to subscription that does not need payment.', 'subscriptio'));
        }

        // Get current time
        $time = time();

        // Subscription is pending initial payment
        if ($this->is_pending_initial_payment()) {

            // Subscription is pending payment but not on this order
            if ($this->get_initial_order_id() !== $order->get_id()) {
                throw new RightPress_Exception('rp_sub_subscription_apply_payment_wrong_order', esc_html__('Wrong order provided when applying payment to subscription.', 'subscriptio'));
            }

            // Set first and last payment datetimes
            $this->set_first_payment($time);
            $this->set_last_payment($time);

            // Subscription can have a free trial
            if ($this->can_have_trial_started()) {

                // Start free trial
                $this->start_trial();
            }
            // Subscription can't have a free trial
            else {

                // Free trial is configured
                if ($this->get_free_trial()) {
                    $this->add_log_entry_note(esc_html__('Customer is not eligible for a free trial.', 'subscriptio'));
                }

                // Activate subscription
                $this->activate();
            }

            // Trigger subscription initial payment applied action
            do_action('subscriptio_subscription_initial_payment_applied', $this, $order);
        }
        // Subscription is pending renewal payment
        else {

            // Subscription is pending payment but not on this order
            if ($this->get_pending_renewal_order_id() !== $order->get_id()) {
                throw new RightPress_Exception('rp_sub_subscription_apply_payment_wrong_order', esc_html__('Wrong order provided when applying payment to subscription.', 'subscriptio'));
            }

            // Clear pending renewal order id
            $this->set_pending_renewal_order_id(null);

            // Maybe account for time subscription has been suspended
            if ($this->has_status('suspended')) {
                $this->maybe_add_days_to_payment_schedule();
            }

            // Advance payment datetime
            $this->advance_last_payment();

            // Maybe activate subscription
            if (!$this->has_status(array('active', 'paused'))) {
                $this->activate();
            }

            // Trigger subscription renewal payment applied action
            do_action('subscriptio_subscription_renewal_payment_applied', $this, $order);
        }

        // Add payment applied order id
        $this->add_payment_applied_order_id($order->get_id());

        // Trigger subscription payment applied action
        do_action('subscriptio_subscription_payment_applied', $this, $order);

        // Save any changes
        $this->save();

        // Add note to order
        $order->add_order_note(sprintf(esc_html__('Payment on this order applied to related subscription #%d.', 'subscriptio'), $this->get_id()));

        // Add note to log entry
        $this->add_log_entry_note(esc_html__('Payment applied to subscription.', 'subscriptio'));
    }

    /**
     * Add payment applied order id
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $order_id
     * @return void
     */
    public function add_payment_applied_order_id($order_id)
    {

        $order_id = (int) $order_id;

        // Get current list of payment applied order ids
        $order_ids = $this->get_payment_applied_order_ids('edit');

        // Add to list
        if (!in_array($order_id, $order_ids, true)) {
            $order_ids[] = $order_id;
        }

        // Set property
        $this->set_property('payment_applied_order_ids', $order_ids);

        // Save object
        $this->save();
    }

    /**
     * Check if subscription was paid by specific order in the past
     *
     * @access public
     * @param WC_Order|int $order
     * @return bool
     */
    public function paid_by_order($order)
    {

        // Get order id
        $order_id = (int) (is_a($order, 'WC_Order') ? $order->get_id() : $order);

        // Get payment applied order ids
        $order_ids = $this->get_payment_applied_order_ids();

        // Check if subscription was paid by order
        return in_array($order_id, $order_ids, true);
    }

    /**
     * Check if subscription needs payment
     *
     * @access public
     * @param bool $include_paused
     * @return bool
     */
    public function needs_payment($include_paused = false)
    {

        return $this->is_pending_initial_payment() || $this->is_pending_renewal_payment($include_paused);
    }

    /**
     * Check if subscription is pending initial payment
     *
     * @access public
     * @return bool
     */
    public function is_pending_initial_payment()
    {

        return $this->get_suborder()->has_status('pending');
    }

    /**
     * Check if subscription is pending renewal payment
     *
     * @access public
     * @param bool $include_paused
     * @return bool
     */
    public function is_pending_renewal_payment($include_paused = false)
    {

        // Define statuses that subscription may have if it is pending payment
        $statuses = array('trial', 'active', 'overdue', 'suspended');

        // Maybe include paused subscriptions
        if ($include_paused) {
            $statuses[] = 'paused';
        }

        // Only subscriptions that have status trial, active, overdue or suspended can be pending payment
        if ($this->has_status($statuses)) {

            // Subscription has pending renewal order id set
            if ($pending_renewal_order_id = $this->get_pending_renewal_order_id()) {

                // Subscription has not been paid by this order yet
                if (!$this->paid_by_order($pending_renewal_order_id)) {
                    return true;
                }
            }

            // Subscription should have a pending renewal order set
            if ($this->get_last_payment() && ($this->calculate_next_renewal_order_datetime() <= new RightPress_DateTime())) {
                return true;
            }
        }

        // Subscription is not considered pending renewal payment
        return false;
    }

    /**
     * Check if subscription's payment gateway supports automatic payments
     *
     * @access public
     * @return bool
     */
    public function has_automatic_payments()
    {

        // Get payment gateway
        if ($payment_gateway = wc_get_payment_gateway_by_order($this->get_suborder())) {

            // Check if automatic payments support is defined for this payment gateway
            if (($payment_gateway->supports('subscriptio') || $payment_gateway->supports('subscriptions') || ($payment_gateway->id === 'stripe' && class_exists('WC_Gateway_Stripe'))) && apply_filters("subscriptio_{$payment_gateway->id}_automatic_payments_ready", false, $this)) {

                // Subscription uses automatic payments
                return true;
            }
        }

        // Subscription does not use automatic payments
        return false;
    }

    /**
     * Get order that is currently pending payment
     *
     * Only used for some UI functionality, does not attempt to fix missing order etc.
     *
     * @access public
     * @return WC_Order|null
     */
    public function get_order_pending_payment()
    {

        $order_pending_payment = null;

        // Subscription is pending initial payment
        if ($this->is_pending_initial_payment()) {

            // Get initial order
            if ($order = $this->get_initial_order()) {

                // Check if order is pending payment
                if ($order->needs_payment()) {

                    // Set order
                    $order_pending_payment = $order;
                }
            }
        }
        // Subscription is pending renewal payment
        else if ($this->is_pending_renewal_payment()) {

            // Get pending renewal order
            if ($order = $this->get_pending_renewal_order()) {

                // Check if order is pending payment
                if ($order->needs_payment()) {

                    // Set order
                    $order_pending_payment = $order;
                }
            }
        }

        // Subscription is not pending payment or order is missing
        return $order_pending_payment;
    }

    /**
     * Get payment gateway option from payment gateway options array
     *
     * @access public
     * @param string $key
     * @return mixed|null
     */
    public function get_payment_gateway_option($key)
    {

        // Get payment gateway options
        $payment_gateway_options = $this->get_payment_gateway_options();

        // Payment gateway option is set
        if (isset($payment_gateway_options[$key])) {

            // Return payment gateway option
            return $payment_gateway_options[$key];
        }

        // Payment gateway option is not set
        return null;
    }

    /**
     * Add payment gateway option to payment gateway options array
     *
     * @access public
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function add_payment_gateway_option($key, $value)
    {

        $key = (string) $key;

        // Get current payment gateway options
        $payment_gateway_options = $this->get_payment_gateway_options('edit');

        // Add setting
        $payment_gateway_options[$key] = $value;

        // Set property
        $this->set_payment_gateway_options($payment_gateway_options);

        // Save object
        $this->save();
    }


    /**
     * =================================================================================================================
     * DATETIME RELATED METHODS
     * =================================================================================================================
     */

    /**
     * Advance last payment datetime by one billing cycle or datetime modifier if provided
     *
     * @access public
     * @param string $datetime_modifier         E.g. '1 day' or '3 weeks' without + or - (always uses +)
     * @return void
     */
    public function advance_last_payment($datetime_modifier = null)
    {

        // Get current last payment datetime
        if ($last_payment = $this->get_last_payment('edit')) {

            // Add datetime modifier to last payment datetime - used to account for the time subscription was paused
            if ($datetime_modifier !== null) {

                $last_payment->modify("+$datetime_modifier");
            }
            // Add one billing cycle or free trial period length to last payment datetime - used to set last payment datetime when subscription payment is applied
            else {

                // Get billing cycle length
                $billing_cycle = $this->get_billing_cycle();

                // Get correct period length to add to last payment
                $period_length = $this->has_status('trial') ? $this->get_free_trial() : $billing_cycle;

                // Advance last payment datetime by one billing cycle length
                RP_SUB_Time::add_period_length_to_datetime($last_payment, $period_length);

                // Set prepaid billing cycle length
                $this->set_prepaid_billing_cycle($billing_cycle);
            }
        }
        // Unable to get current last payment datetime
        else {

            // Set log entry status to warning
            $this->update_log_entry_status('warning');

            // Add note to log entry
            $this->add_log_entry_note(esc_html__('Unable to calculate new payment date from previous payment date. Setting new payment date to current date as a fallback.', 'subscriptio'));

            // Use current datetime as a fallback
            $last_payment = new RP_SUB_DateTime();
        }

        // Set new last payment datetime
        $this->set_last_payment($last_payment);
    }

    /**
     * Calculate next renewal payment datetime
     *
     * @access public
     * @return RightPress_DateTime
     */
    public function calculate_next_renewal_payment_datetime()
    {

        // Reference last payment datetime
        $datetime = $this->get_last_payment();

        // Get correct period length
        if ($this->has_status('trial')) {
            $period_length = $this->get_free_trial();
        }
        else if ($this->get_prepaid_billing_cycle()) {
            $period_length = $this->get_prepaid_billing_cycle();
        }
        else {
            $period_length = $this->get_billing_cycle();
        }

        // Add one billing cycle to last payment datetime to get next payment datetime
        RP_SUB_Time::add_period_length_to_datetime($datetime, $period_length);

        // Return next renewal payment datetime
        return $datetime;
    }

    /**
     * Calculate next renewal order datetime
     *
     * @access public
     * @return object
     */
    public function calculate_next_renewal_order_datetime()
    {

        // Calculate next renewal payment datetime - this will be our renewal order datetime if subscription uses automatic payments
        $datetime = $this->calculate_next_renewal_payment_datetime();

        // Subscription does not use automatic payments
        if (!$this->has_automatic_payments()) {

            // Get renewal order offset in days
            $offset_days = (int) RP_SUB_Settings::get('renewal_order_offset');

            // Offset renewal payment datetime by a number of days to get renewal order datetime
            $datetime->modify("-{$offset_days} " . RP_SUB_Time::get_day_name());
        }

        // Return next renewal order datetime
        return $datetime;
    }

    /**
     * Calculate expiration datetime
     *
     * @access protected
     * @return object
     */
    public function calculate_expiration_datetime()
    {

        $datetime = null;

        // Check if lifespan is set
        if ($lifespan = $this->get_lifespan()) {

            // Check if first payment datetime is set
            if ($datetime = $this->get_first_payment()) {

                // Add lifespan length to first payment datetime to get expiration datetime
                RP_SUB_Time::add_period_length_to_datetime($datetime, $lifespan);

                // Calculate next renewal payment datetime
                $next_renewal_payment_datetime = $this->calculate_next_renewal_payment_datetime();

                // Check if subscription is set to expire before the end of prepaid term
                if ($datetime < $next_renewal_payment_datetime) {

                    // Set subscription to expire at the end of prepaid term
                    $datetime = $next_renewal_payment_datetime;
                }
            }
        }

        return $datetime;
    }

    /**
     * Calculate payment cut-off datetime if subscription is set to expire
     *
     * All payment related actions are stopped 6 hours before subscription expiration datetime
     *
     * @access public
     * @return RP_SUB_DateTime|bool
     */
    public function calculate_payment_cutoff_datetime()
    {

        // Get subscription expiration datetime, if subscription is set to expire
        if ($expiration_datetime = $this->calculate_expiration_datetime()) {

            // Calculate payment cut-off datetime
            return $expiration_datetime->modify('-' . (RP_SUB_Time::get_day_length_in_seconds() / 4) . 'second');
        }

        // Subscription is not set to expire
        return false;
    }

    /**
     * Check if subscription terminates before or around the end of current prepaid term
     *
     * If this method returns true, no further subscription events (other than terminating) should take place
     *
     * @access public
     * @return bool
     */
    public function terminates_with_prepaid_term()
    {

        // Subscription is already terminated
        if ($this->is_terminated()) {
            return true;
        }

        // Subscription is set to cancel
        if ($this->has_status('set-to-cancel')) {
            return true;
        }

        // Get payment cut-off datetime if subscription is set to expire
        if ($cutoff_datetime = $this->calculate_payment_cutoff_datetime()) {

            // Cut-off datetime has passed or renewal payment datetime falls after it
            if ($cutoff_datetime < (new RP_SUB_DateTime()) || $cutoff_datetime < $this->calculate_next_renewal_payment_datetime()) {
                return true;
            }
        }

        // Subscription does not terminate before or around the end of current prepaid term
        return false;
    }

    /**
     * Maybe add pause days to payment schedule
     *
     * Used to account for days subscription spent paused or suspended
     *
     * @access public
     * @return void
     */
    public function maybe_add_days_to_payment_schedule()
    {

        // Check if we need to add days
        if (($this->has_status('paused') && RP_SUB_Settings::is('add_paused_days')) || ($this->has_status('suspended') && RP_SUB_Settings::is('add_suspended_days'))) {

            // Get number of days subscription spent in current status
            if ($days = $this->calculate_days_in_current_status()) {

                // Advance last payment date by the amount of days subscription has been paused or suspended
                $this->advance_last_payment("$days " . RP_SUB_Time::get_day_name());

                // Add note to log entry
                if ($this->has_status('paused')) {
                    $note = sprintf(_n('Subscription has been paused for %d day.', 'Subscription has been paused for %d days.', $days, 'subscriptio'), $days);
                }
                else {
                    $note = sprintf(_n('Subscription has been suspended for %d day.', 'Subscription has been suspended for %d days.', $days, 'subscriptio'), $days);
                }

                $this->add_log_entry_note($note . ' ' . esc_html__('Payment schedule adjusted accordingly.', 'subscriptio'));
            }
        }
    }


    /**
     * =================================================================================================================
     * OTHER METHODS
     * =================================================================================================================
     */

    /**
     * Get subscription number
     *
     * @access public
     * @return string
     */
    public function get_subscription_number()
    {

        $number = esc_html_x('#', 'hash before subscription number', 'subscriptio') . $this->get_id();
        return apply_filters('subscriptio_formatted_subscription_number', $number, $this);
    }

    /**
     * Get status label
     *
     * @access public
     * @return string
     */
    public function get_status_label()
    {

        // Get status label
        return RP_SUB_Subscription_Controller::get_subscription_status_label($this);
    }

    /**
     * Check whether customer has access to subscriber's privileges, e.g. product downloads, member access etc.
     *
     * @access public
     * @return bool
     */
    public function gives_access()
    {

        // Get all statuses
        $statuses = RP_SUB_Subscription_Controller::get_subscription_statuses();

        // Get status
        $status = $this->get_status();

        // Check value and return
        return (bool) apply_filters('subscriptio_subscription_gives_access', (isset($statuses[$status]) ? $statuses[$status]['gives_access'] : false), $this);
    }

    /**
     * Get formatted recurring total
     *
     * @access public
     * @return string
     */
    public function get_formatted_recurring_total()
    {

        // Get formatted order total
        $formatted_order_total = $this->get_suborder()->get_formatted_order_total();

        // Get billing cycle length and period
        $billing_cycle_length = RP_SUB_Time::get_length_from_period_length($this->get_billing_cycle());
        $billing_cycle_period = RP_SUB_Time::get_period_from_period_length($this->get_billing_cycle());

        // Get formatted recurring total
        $formatted_recurring_total = RP_SUB_Pricing::format_recurring_amount_for_display($formatted_order_total, $billing_cycle_length, $billing_cycle_period);

        // Allow developers to override and return
        return apply_filters('subscriptio_subscription_formatted_recurring_total', $formatted_recurring_total, $this);
    }

    /**
     * Get formatted product name
     *
     * @access public
     * @param int $limit
     * @param bool $show_count
     * @return string
     */
    public function get_formatted_product_name($limit = 1, $show_count = true)
    {

        // TODO: Maybe merge this logic with logic used for products in RP_SUB_Subscription_Controller::print_column_value

        // Get order items
        $order_items = $this->get_suborder()->get_items('line_item');

        // Count items
        $item_count = count($order_items);

        // Get count to display
        $display_count = $show_count && ($item_count - $limit) > 0 ? ($item_count - $limit) : false;

        // Get names of all order items
        $all_names = array();

        foreach ($order_items as $order_item) {
            $all_names[] = $order_item->get_name();
        }

        // Get subset of names to display
        $display_names = array_slice($all_names, 0, $limit);

        // Format subscription product name string
        $formatted_product_name = join(', ', $display_names);

        // Maybe append count of other products
        if ($display_count) {
            $formatted_product_name .= ' ' . sprintf(_n('and %d other product', 'and %d other products', $display_count, 'subscriptio'), $display_count);
        }

        // Allow developers to override and return
        return apply_filters('subscriptio_subscription_formatted_product_name', $formatted_product_name, $this, $limit, $show_count);
    }

    /**
     * Get formatted billing cycle
     *
     * @access public
     * @return string
     */
    public function get_formatted_billing_cycle()
    {

        $value = '';

        if ($billing_cycle = $this->get_billing_cycle()) {
            $period = RP_SUB_Time::get_period_from_period_length($billing_cycle);
            $length = RP_SUB_Time::get_length_from_period_length($billing_cycle);
            $value  = RP_SUB_Time::get_formatted_time_period_string($length, $period, false);
        }

        return $value;
    }

    /**
     * Get formatted free trial
     *
     * @access public
     * @return string
     */
    public function get_formatted_free_trial()
    {

        $value = '';

        if ($free_trial = $this->get_free_trial()) {
            $period = RP_SUB_Time::get_period_from_period_length($free_trial);
            $length = RP_SUB_Time::get_length_from_period_length($free_trial);
            $value  = RP_SUB_Time::get_formatted_time_period_string($length, $period);
        }

        return $value;
    }

    /**
     * Get formatted lifespan
     *
     * @access public
     * @return string
     */
    public function get_formatted_lifespan()
    {

        $value = '';

        if ($lifespan = $this->get_lifespan()) {
            $period = RP_SUB_Time::get_period_from_period_length($lifespan);
            $length = RP_SUB_Time::get_length_from_period_length($lifespan);
            $value  = RP_SUB_Time::get_formatted_time_period_string($length, $period);
        }

        return $value;
    }

    /**
     * Check if subscription is considered terminated
     *
     * @access public
     * @return bool
     */
    public function is_terminated()
    {

        return $this->has_status(array('cancelled', 'expired', 'trash'));
    }

    /**
     * Check if currently loaded subscription object has just been terminated
     *
     * @access public
     * @return bool
     */
    public function just_terminated()
    {

        return $this->just_terminated;
    }

    /**
     * Check if currently loaded subscription object has just changes status
     *
     * @access public
     * @return bool
     */
    public function just_changed_status()
    {

        return $this->just_changed_status;
    }

    /**
     * Check how many days subscription spent in current status
     *
     * Note: Days are rounded mathematically, e.g. e.g. 4 hours = 0 days, 18 hours = 1 days
     *
     * @access public
     * @return int
     */
    public function calculate_days_in_current_status()
    {

        $days = 0;

        // Check if property is set
        if ($this->get_status_since()) {

            // Calculate days since last status change
            $days = round(((new RP_SUB_DateTime())->getTimestamp() - $this->get_status_since()->getTimestamp()) / RP_SUB_Time::get_day_length_in_seconds());
        }

        return $days;
    }

    /**
     * Save subscription
     *
     * @access public
     * @return int
     */
    public function save()
    {

        // Terminated subscriptions can no longer be changed
        if ($this->is_terminated() && !$this->just_terminated()) {

            // Write to error log
            RightPress_Help::doing_it_wrong('RP_SUB_Subscription::save()', 'Terminated subscriptions must not be modified.', '3.0');

            // Do not proceed
            return;
        }

        // Proceed saving subscription
        parent::save();
    }





}
