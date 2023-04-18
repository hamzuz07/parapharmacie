<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Payment Controller
 *
 * @class RP_SUB_Payment_Controller
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Payment_Controller
{

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

        // Load supported payment gateways
        require_once RP_SUB_PLUGIN_PATH . 'gateways/rp-sub-woocommerce-gateway-stripe.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'gateways/paypal-ec/rp-sub-paypal-ec-loader.class.php';

        // Maybe trigger failed payment notification
        add_action('subscriptio_subscription_automatic_payment_failed', array($this, 'maybe_trigger_failed_payment_notification'), 10, 2);
    }

    /**
     * Process automatic payment
     *
     * @access public
     * @param WC_Order $order
     * @param RP_SUB_Subscription $subscription
     * @return bool
     */
    public static function process_automatic_payment($order, $subscription)
    {

        // Get payment gateway by order
        if ($payment_gateway = wc_get_payment_gateway_by_order($order)) {

            // Attempt to process automatic payment via selected payment gateway
            if (apply_filters("subscriptio_automatic_payment_{$payment_gateway->id}", false, $order, $subscription)) {

                // Automatic payment processed
                return true;
            }
        }
        // Unable to get payment gateway
        else {

            // Add note to log entry
            $subscription->add_log_entry_note(esc_html__('Unable to load payment gateway extension.', 'subscription'));
        }

        // Could not process automatic payment
        return false;
    }

    /**
     * Maybe trigger failed payment notification
     *
     * Note: We have this method to differentiate between failed payment attempts with upcoming retries and attempts without
     * upcoming retries (email should not be sent on the last payment retry or first payment attempt when retries are not set)
     *
     * @access public
     * @param WC_Order $renewal_order
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function maybe_trigger_failed_payment_notification($renewal_order, $subscription)
    {

        // Check if we have further payment retries
        if (RP_SUB_Scheduler::get_next_payment_retry_datetime($subscription)) {

            // Trigger notification action
            do_action('subscriptio_subscription_automatic_payment_failed_notification', $renewal_order);
        }
    }





}

RP_SUB_Payment_Controller::get_instance();
