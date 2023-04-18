<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Mailer
 *
 * @class RP_SUB_Mailer
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Mailer
{

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    // Define email types
    protected $email_types = array(

        // Customer - New subscription
        'customer_new_subscription' => array(
            'class' => 'RP_SUB_Email_Customer_New_Subscription',
            'hooks' => array(
                'subscriptio_subscription_created',
            ),
        ),

        // Customer - Subscription activated
        'customer_activated_subscription' => array(
            'class' => 'RP_SUB_Email_Customer_Activated_Subscription',
            'hooks' => array(
                'subscriptio_subscription_status_changed_from_pending_to_active',
            ),
        ),

        // Customer - Subscription trial started
        'customer_subscription_trial_started' => array(
            'class' => 'RP_SUB_Email_Customer_Subscription_Trial_Started',
            'hooks' => array(
                'subscriptio_subscription_status_changed_from_pending_to_trial',
            ),
        ),

        // Customer - Paused subscription
        'customer_paused_subscription' => array(
            'class' => 'RP_SUB_Email_Customer_Paused_Subscription',
            'hooks' => array(
                'subscriptio_subscription_status_changed_to_paused',
            ),
        ),

        // Customer - Reactivated Subscription
        'customer_reactivated_subscription' => array(
            'class' => 'RP_SUB_Email_Customer_Reactivated_Subscription',
            'hooks' => array(
                'subscriptio_subscription_status_changed_from_set-to-cancel_to_trial',
                'subscriptio_subscription_status_changed_from_set-to-cancel_to_active',
            ),
        ),

        // Customer - Resumed Subscription
        'customer_resumed_subscription' => array(
            'class' => 'RP_SUB_Email_Customer_Resumed_Subscription',
            'hooks' => array(
                'subscriptio_subscription_status_changed_from_paused_to_trial',
                'subscriptio_subscription_status_changed_from_paused_to_active',
                'subscriptio_subscription_status_changed_from_paused_to_overdue',
                'subscriptio_subscription_status_changed_from_paused_to_suspended',
            ),
        ),

        // Customer - Overdue subscription
        'customer_overdue_subscription' => array(
            'class' => 'RP_SUB_Email_Customer_Overdue_Subscription',
            'hooks' => array(
                'subscriptio_subscription_status_changed_to_overdue',
            ),
        ),

        // Customer - Suspended subscription
        'customer_suspended_subscription' => array(
            'class' => 'RP_SUB_Email_Customer_Suspended_Subscription',
            'hooks' => array(
                'subscriptio_subscription_status_changed_to_suspended',
            ),
        ),

        // Customer - Subscription set to cancel
        'customer_subscription_set_to_cancel' => array(
            'class' => 'RP_SUB_Email_Customer_Subscription_Set_To_Cancel',
            'hooks' => array(
                'subscriptio_subscription_status_changed_to_set-to-cancel',
            ),
        ),

        // Customer - Cancelled subscription
        'customer_cancelled_subscription' => array(
            'class' => 'RP_SUB_Email_Customer_Cancelled_Subscription',
            'hooks' => array(
                'subscriptio_subscription_status_changed_to_cancelled',
            ),
        ),

        // Customer - Expired subscription
        'customer_expired_subscription' => array(
            'class' => 'RP_SUB_Email_Customer_Expired_Subscription',
            'hooks' => array(
                'subscriptio_subscription_status_changed_to_expired',
            ),
        ),

        // Customer - New renewal order
        'customer_new_renewal_order' => array(
            'class' => 'RP_SUB_Email_Customer_New_Renewal_Order',
            'hooks' => array(
                'subscriptio_created_renewal_order_notification',
            ),
        ),

        // Customer - Processing renewal order
        'customer_processing_renewal_order' => array(
            'class' => 'RP_SUB_Email_Customer_Processing_Renewal_Order',
            'hooks' => array(
                'woocommerce_order_status_cancelled_to_processing_notification',
                'woocommerce_order_status_failed_to_processing_notification',
                'woocommerce_order_status_on-hold_to_processing_notification',
                'woocommerce_order_status_pending_to_processing_notification',
            ),
        ),

        // Customer - Completed renewal order
        'customer_completed_renewal_order' => array(
            'class' => 'RP_SUB_Email_Customer_Completed_Renewal_Order',
            'hooks' => array(
                'woocommerce_order_status_completed_notification'
            ),
        ),

        // Customer - Subscription payment reminder
        'customer_subscription_payment_reminder' => array(
            'class' => 'RP_SUB_Email_Customer_Subscription_Payment_Reminder',
            'hooks' => array(
                'subscriptio_send_payment_reminder',
            ),
        ),

        // Customer - Subscription payment failed
        'customer_subscription_payment_failed' => array(
            'class' => 'RP_SUB_Email_Customer_Subscription_Payment_Failed',
            'hooks' => array(
                'subscriptio_subscription_automatic_payment_failed_notification',
            ),
        ),

        // Customer - Subscription note
        'customer_subscription_note' => array(
            'class' => 'RP_SUB_Email_Customer_Subscription_Note',
            'hooks' => array(
                'woocommerce_new_customer_note_notification',
            ),
        ),
    );

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Register email types
        add_action('woocommerce_email_classes', array($this, 'register_email_types'));

        // Register WooCommerce email actions
        add_filter('woocommerce_email_actions', array($this, 'register_email_actions'));

        // Subscription details template hook
        add_action('subscriptio_email_subscription_details', array($this, 'subscription_downloads'), 5, 4);
        add_action('subscriptio_email_subscription_details', array($this, 'subscription_details'), 10, 4);

        // Subscription items template hook
        add_action('subscriptio_email_subscription_items', array($this,'subscription_items'), 10, 4);

        // Subscription meta template hook
        add_action('subscriptio_email_subscription_meta', array($this, 'subscription_meta'), 10, 4);

        // Customer details template hook
        add_action('subscriptio_email_customer_details', array($this, 'customer_details_template_hook'), 10, 4);

        // Maybe disable WooCommerce emails dynamically
        RightPress_Help::add_late_filter('woocommerce_email_enabled_customer_processing_order', array($this, 'wc_email_is_enabled'), 3);
        RightPress_Help::add_late_filter('woocommerce_email_enabled_customer_completed_order', array($this, 'wc_email_is_enabled'), 3);
        RightPress_Help::add_late_filter('woocommerce_email_enabled_customer_note', array($this, 'wc_email_is_enabled'), 3);
    }

    /**
     * Register email types
     *
     * @access public
     * @param array $emails
     * @return array
     */
    public function register_email_types($emails)
    {

        foreach ($this->email_types as $email_type => $email_type_data) {
            $emails[$email_type_data['class']] = include RP_SUB_PLUGIN_PATH . 'classes/emails/rp-sub-email-' . str_replace('_', '-', $email_type) . '.class.php';
        }

        return $emails;
    }

    /**
     * Register email actions
     *
     * @access public
     * @param array $actions
     * @return array
     */
    public function register_email_actions($actions)
    {

        // Get WC_Emails instance
        $wc_emails = WC()->mailer();

        // Iterate over email actions
        foreach ($this->email_types as $email_type => $email_type_data) {
            foreach ($email_type_data['hooks'] as $hook) {

                // Register email action with WooCommerce
                if (!in_array($hook, $actions, true)) {
                    $actions[] = $hook;
                }

                // Add notification callback
                add_action(($hook . '_notification'), array($wc_emails->emails[$email_type_data['class']], 'trigger'));
            }
        }

        // Return WooCommerce email actions
        return $actions;
    }

    /**
     * Maybe disable WooCommerce emails dynamically
     *
     * @access public
     * @param bool $is_enabled
     * @param object $object
     * @param object $email
     * @return bool
     */
    public function wc_email_is_enabled($is_enabled, $object, $email)
    {

        // Prevent customer note email on suborders
        if (current_filter() === 'woocommerce_email_enabled_customer_note' && $object) {
            if (is_a($object, 'RP_SUB_Suborder')) {
                return false;
            }
        }

        // Prevent processing and completed order emails on subscription renewal orders
        if (in_array(current_filter(), array('woocommerce_email_enabled_customer_processing_order', 'woocommerce_email_enabled_customer_completed_order'), true) && $object) {
            if (subscriptio_is_subscription_renewal_order($object)) {
                return false;
            }
        }

        return $is_enabled;
    }

    /**
     * Maybe prevent default WooCommerce email
     *
     * @access public
     * @param int $order_id
     * @return void
     */
    public function maybe_prevent_default_wc_email($order_id)
    {

        // TBD: This has no references elsewhere - do we still need this?

        // Load order
        if ($order = wc_get_order($order_id)) {

            // Order is subscription renewal order
            if (subscriptio_is_subscription_renewal_order($order)) {

                // Get email class
                $class = (current_action() === 'woocommerce_order_status_completed_notification') ? 'WC_Email_Customer_Completed_Order' : 'WC_Email_Customer_Processing_Order';

                // Remove default notification trigger
                // TODO: Won't this cause problems if a bunch of emails are sent for different orders? We remove this callback and never add it back?
                remove_action(current_action(), array(WC()->mailer()->emails[$class], 'trigger'));
            }
        }
    }

    /**
     * Show subscription downloads in a table
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @param bool $sent_to_admin
     * @param bool $plain_text
     * @param object $email
     * @return void
     */
    public function subscription_downloads($subscription, $sent_to_admin, $plain_text, $email)
    {

        // Call WooCommerce method
        WC_Emails::instance()->order_downloads($subscription->get_suborder(), $sent_to_admin, $plain_text, $email);
    }

    /**
     * Show subscription details table
     *
     * Based on WooCommerce 3.7 WC_Emails::order_downloads()
     *
     * @access public
     * @param object $subscription
     * @param bool $sent_to_admin
     * @param bool $plain_text
     * @param object $email
     * @return void
     */
    public function subscription_details($subscription, $sent_to_admin, $plain_text, $email)
    {

        // Get template path
        $template_path = $plain_text ? 'emails/plain/email-subscription-details.php' : 'emails/email-subscription-details.php';

        // Include template
        RP_SUB_Help::include_template($template_path, array(
            'subscription'  => $subscription,
            'sent_to_admin' => $sent_to_admin,
            'plain_text'    => $plain_text,
            'email'         => $email,
        ));
    }

    /**
     * Show subscription item rows
     *
     * Based on WooCommerce 3.7 wc_get_email_order_items()
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @param bool $sent_to_admin
     * @param bool $plain_text
     * @param object $email
     * @return void
     */
    public function subscription_items($subscription, $sent_to_admin, $plain_text, $email)
    {

        // Get template path
        $template_path = $plain_text ? 'emails/plain/email-subscription-items.php' : 'emails/email-subscription-items.php';

        // Include template
        RP_SUB_Help::include_template($template_path, apply_filters('subscriptio_email_subscription_items_args', array(
            'subscription'          => $subscription,
            'items'                 => $subscription->get_items(),
            'show_download_links'   => $subscription->is_download_permitted() && !$sent_to_admin,
            'show_sku'              => false,
            'show_purchase_note'    => $subscription->has_status(array('trial', 'active', 'overdue')) && !$sent_to_admin,
            'show_image'            => false,
            'image_size'            => array(32, 32),
            'sent_to_admin'         => $sent_to_admin,
            'plain_text'            => $plain_text,
            'email'                 => $email,
        )));
    }

    /**
     * Add subscription meta to email templates
     *
     * Based on WooCommerce 3.7 WC_Emails::order_meta()
     *
     * @access public
     * @param object $subscription
     * @param bool $sent_to_admin
     * @param bool $plain_text
     * @param object $email
     * @return void
     */
    public function subscription_meta($subscription, $sent_to_admin, $plain_text, $email)
    {

        // Get fields to display
        $fields = apply_filters('subscriptio_email_subscription_meta_fields', array(), $sent_to_admin, $subscription);

        // Check if any fields should be displayed
        if ($fields) {

            // Iterate over fields
            foreach ($fields as $field) {

                // Check if label and value are set
                if (isset($field['label']) && isset($field['value']) && $field['value']) {

                    // Email is plain text
                    if ($plain_text) {
                        echo $field['label'] . ': ' . $field['value'] . "\n";
                    }
                    // Email is not plain text
                    else {
                        echo '<p><strong>' . $field['label'] . ':</strong> ' . $field['value'] . '</p>';
                    }
                }
            }
        }
    }

    /**
     * Customer details template hook
     *
     * @access public
     * @param object $subscription
     * @param bool $sent_to_admin
     * @param bool $plain_text
     * @param object $email
     * @return void
     */
    public function customer_details_template_hook($subscription, $sent_to_admin, $plain_text, $email)
    {

        // TODO: Probably we need to have our own handling here?
        do_action('woocommerce_email_customer_details', $subscription->get_suborder(), $sent_to_admin, $plain_text, $email);
    }





}

RP_SUB_Mailer::get_instance();
