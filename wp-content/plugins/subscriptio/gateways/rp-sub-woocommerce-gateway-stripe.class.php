<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WooCommerce Stripe payment gateway extension integration
 *
 * https://wordpress.org/plugins/woocommerce-gateway-stripe/
 *
 * @class RP_SUB_WooCommerce_Gateway_Stripe
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_WooCommerce_Gateway_Stripe
{

    private $min_supported_version = '4.5';

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

        // Define support for automatic payments
        add_filter('subscriptio_stripe_automatic_payments_ready', '__return_true');

        // Copy payment details from subscription to subscription order
        add_action('subscriptio_creating_order_from_subscription', array($this, 'copy_payment_details_from_subscription_to_order'), 10, 3);

        // Process automatic renewal payment
        add_filter('subscriptio_automatic_payment_stripe', array($this, 'process_renewal_payment'), 10, 3);

        // Maybe add Stripe payment intent meta data
        add_filter('wc_stripe_generate_create_intent_request', array($this, 'maybe_add_payment_intent_meta_data'), 10, 3);

        // Maybe force save source during checkout
        add_filter('wc_stripe_force_save_source', array($this, 'maybe_force_save_source'));

        // Maybe hide save payment method checkbox during checkout
        add_filter('wc_stripe_display_save_payment_method_checkbox', array($this, 'maybe_hide_save_payment_method_checkbox'));

        // Maybe save order payment method details to subscriptions
        add_action('woocommerce_payment_complete', array($this, 'maybe_save_order_payment_method_details_to_subscriptions'));

        // Maybe print update subscriptions payment method checkbox on payment method change
        add_action('wc_stripe_cards_payment_fields', array($this, 'maybe_display_update_subscriptions_payment_method_checkbox'));

        // Maybe save new payment method details to subscriptions
        add_action('wc_stripe_add_payment_method_stripe_success', array($this, 'maybe_save_new_payment_method_details_to_subscriptions'), 10, 2);

        // Maybe display version warning
        add_action('admin_notices', array($this, 'maybe_display_version_warning'), -1);
    }

    /**
     * Get Stripe payment gateway object
     *
     * @access public
     * @return WC_Gateway_Stripe|null
     */
    public static function get_payment_gateway()
    {

        $payment_gateway = null;

        // Get payment gateway
        if (WC()->payment_gateways()) {

            $payment_gateways = WC()->payment_gateways()->payment_gateways();

            if (isset($payment_gateways['stripe']) && is_a($payment_gateways['stripe'], 'WC_Gateway_Stripe')) {
                $payment_gateway = $payment_gateways['stripe'];
            }
        }

        return $payment_gateway;
    }

    /**
     * Copy payment details from subscription to subscription order
     *
     * Runs when new subscription related order is generated (usually subscription renewal order)
     *
     * @access public
     * @param WC_Order $order
     * @param RP_SUB_Subscription $subscription
     * @param string $order_type
     * @return void
     */
    public function copy_payment_details_from_subscription_to_order($order, $subscription, $order_type)
    {

        // Copy Stripe customer id
        if ($stripe_customer_id = $subscription->get_payment_gateway_option('_stripe_customer_id')) {
            $order->update_meta_data('_stripe_customer_id', $stripe_customer_id);
        }

        // Copy Stripe source id
        if ($stripe_source_id = $subscription->get_payment_gateway_option('_stripe_source_id')) {
            $order->update_meta_data('_stripe_source_id', $stripe_source_id);
        }
    }

    /**
     * Process automatic subscription renewal payment
     *
     * Based on WC_Stripe_Subs_Compat::process_subscription_payment()
     *
     * @access public
     * @param bool $payment_successful
     * @param array $order
     * @param array $subscription
     * @return bool
     */
    public function process_renewal_payment($payment_successful, $order, $subscription)
    {

        try {

            // Get payment gateway
            $payment_gateway = RP_SUB_WooCommerce_Gateway_Stripe::get_payment_gateway();

            // No payment gateway or some of the required methods are no longer callable
            if (!$payment_gateway
                || !is_callable(array($payment_gateway, 'prepare_order_source'))
                || !is_callable(array($payment_gateway, 'generate_payment_request'))
                || !is_callable(array($payment_gateway, 'process_response'))
                || !is_callable(array('WC_Stripe_Helper', 'get_stripe_amount'))
                || !is_callable(array('WC_Stripe_API', 'request'))
                || !is_callable(array('WC_Stripe_Helper', 'get_localized_messages'))
                || !is_callable(array('WC_Stripe_Logger', 'log'))
                || !class_exists('WC_Stripe_Exception')) {
                // TODO: Might be wise to log this event
                return false;
            }

            // Stripe customer id and source id are not set on order
            if (!$order->get_meta('_stripe_customer_id', true) && !$order->get_meta('_stripe_source_id', true)) {

                // Get legacy Stripe customer id from customer meta
                if ($stripe_customer_id = RightPress_WC::customer_get_meta($order->get_customer_id(), '_subscriptio_stripe_customer_id', true)) {

                    // Set Stripe customer id
                    // Note: We are setting it in two different ways to make sure they are immediately available everywhere
                    $order->update_meta_data('_stripe_customer_id', $stripe_customer_id);
                    update_post_meta($order->get_id(), '_stripe_customer_id', $stripe_customer_id);

                    // Get legacy Stripe default card from customer meta
                    if ($stripe_customer_default_card = RightPress_WC::customer_get_meta($order->get_customer_id(), '_subscriptio_stripe_customer_default_card', true)) {

                        // Set Stripe source id
                        $order->update_meta_data('_stripe_source_id', $stripe_customer_default_card);
                        update_post_meta($order->get_id(), '_stripe_source_id', $stripe_customer_default_card);
                    }
                }
            }

            // Prepare order source
            $prepared_source = $payment_gateway->prepare_order_source($order);

            // Unable to prepare order source or customer is not set
            if (!is_object($prepared_source) || !$prepared_source->customer) {
                return false;
            }

            // Write to Stripe log
            WC_Stripe_Logger::log("Info: Begin processing subscription payment for order {$order->get_id()} for the amount of {$order->get_total()}");

            // Create and confirm payment intent
            $response = $payment_gateway->create_and_confirm_intent_for_off_session($order, $prepared_source, $order->get_total());

            // Error occurred
            if (!empty($response->error)) {

                // Get localized messages
                $localized_messages = WC_Stripe_Helper::get_localized_messages();

                // Get localized message
                if ($response->error->type === 'card_error') {
                    $localized_message = isset($localized_messages[$response->error->code]) ? $localized_messages[$response->error->code] : $response->error->message;
                }
                else {
                    $localized_message = isset($localized_messages[$response->error->type]) ? $localized_messages[$response->error->type] : $response->error->message;
                }

                // Add order note
                $order->add_order_note($localized_message);

                // Throw exception
                throw new WC_Stripe_Exception(print_r($response, true), $localized_message);
            }

            // Call process payment action
            do_action('wc_gateway_stripe_process_payment', $response, $order);

            // Process response
            $payment_gateway->process_response(end($response->charges->data), $order);

            // Subscription automatic payment processed successfully
            return true;
        }
        catch (WC_Stripe_Exception $e) {

            // Write to Stripe log
            WC_Stripe_Logger::log('Error: ' . $e->getMessage());

            // Call error action
            do_action('wc_gateway_stripe_process_payment_error', $e, $order);

            // Update order status
            $order->update_status('failed');

            // Subscription automatic payment failed to process
            return false;
        }
    }

    /**
     * Maybe add Stripe payment intent meta data
     *
     * @access public
     * @param array $request
     * @param WC_Order $order
     * @param object $prepared_source
     * @return array
     */
    public function maybe_add_payment_intent_meta_data($request, $order, $prepared_source)
    {

        // Check if order is subscription renewal order
        if (subscriptio_is_subscription_renewal_order($order)) {

            // Get subscription from renewal order
            if ($subscription = subscriptio_get_subscription_related_to_order($order)) {

                // Set request meta data
                $request['metadata']['subscription_id'] = $subscription->get_id();
                $request['metadata']['payment_type']    = 'recurring';
                $request['metadata']['site_url']        = esc_url(get_site_url());
            }
        }

        return $request;
    }

    /**
     * Maybe force save source
     *
     * Note: We are essentially guessing that the order customer is paying for is subscription
     * order since the hook that we are using is limited (does not provide order details)
     *
     * @access public
     * @param bool $force
     * @return bool
     */
    public function maybe_force_save_source($force)
    {

        // Get unpaid statuses
        $wc_order_statuses  = array_keys(wc_get_order_statuses());
        $wc_paid_statuses   = wc_get_is_paid_statuses();
        $unpaid_statuses    = array_diff($wc_order_statuses, $wc_paid_statuses);

        // Get customer's unpaid orders
        $unpaid_orders = wc_get_orders(array(
            'customer'  => get_current_user_id(),
            'status'    => $unpaid_statuses,
            'limit'     => -1,
            'orderby'   => 'date',
            'order'     => 'DESC',
            'return'    => 'ids',
        ));

        // Iterate over unpaid orders
        foreach ($unpaid_orders as $unpaid_order_id) {

            // Check if order is related to subscription
            if (subscriptio_is_subscription_order($unpaid_order_id)) {

                // Make a bet that current payment is for this unpaid subscription order and force save source
                $force = true;
            }
        }

        return $force;
    }

    /**
     * Maybe hide save payment method checkbox
     *
     * Checkbox is hidden since payment token will be saved in any case when cart contains subscription
     *
     * @access public
     * @param bool $display
     * @return bool
     */
    public function maybe_hide_save_payment_method_checkbox($display)
    {

        global $wp_query;

        if (subscriptio_cart_contains_subscription_product() || (!empty($_GET['pay_for_order']) && !empty($wp_query->query_vars['order-pay']) && subscriptio_get_subscriptions_related_to_order($wp_query->query_vars['order-pay']))) {
            $display = false;
        }

        return $display;
    }

    /**
     * Maybe save order payment method details to subscriptions
     *
     * @access public
     * @param int $order_id
     * @return void
     */
    public function maybe_save_order_payment_method_details_to_subscriptions($order_id)
    {

        // Load order object
        if ($order = wc_get_order($order_id)) {

            // Check payment method
            if ($order->get_payment_method() === 'stripe') {

                // Get Stripe customer and source ids
                $stripe_customer_id = $order->get_meta('_stripe_customer_id', true);
                $stripe_source_id   = $order->get_meta('_stripe_source_id', true);

                // Check if payment details are set
                if ($stripe_customer_id || $stripe_source_id) {

                    // Get subscriptions related to order
                    if ($subscriptions = subscriptio_get_subscriptions_related_to_order($order)) {

                        // Iterate over subscriptions
                        foreach ($subscriptions as $subscription) {

                            // Save Stripe customer id
                            if ($stripe_customer_id) {
                                $subscription->add_payment_gateway_option('_stripe_customer_id', $stripe_customer_id);
                            }

                            // Save Stripe source id
                            if ($stripe_source_id) {
                                $subscription->add_payment_gateway_option('_stripe_source_id', $stripe_source_id);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Maybe print update subscriptions payment method checkbox on payment method change
     *
     * @access public
     * @param string $gateway_id
     * @return void
     */
    public function maybe_display_update_subscriptions_payment_method_checkbox($gateway_id)
    {

        // Only display this on add payment method page for 'stripe' payment gateway
        if (is_add_payment_method_page() && $gateway_id === 'stripe') {

            // Customer has non-terminated subscriptions
            if (subscriptio_customer_has_subscription()) {

                // Open container
                echo '<div class="form-row form-row-wide">';

                // Print input
                echo '<input type="checkbox" id="wc-stripe-update-subscriptio-subscriptions-payment-method" name="wc-stripe-update-subscriptio-subscriptions-payment-method" value="yes">';

                // Print label
                echo '<label for="wc-stripe-update-subscriptio-subscriptions-payment-method" style="display: inline;">' . esc_html__('Use this payment method for all of my subscriptions', 'subscriptio') . '</label>';

                // Close container
                echo '</div>';
            }
        }
    }

    /**
     * Maybe save new payment method details to subscriptions
     *
     * @access public
     * @param string $source_id
     * @param object $source_object
     * @return void
     */
    public function maybe_save_new_payment_method_details_to_subscriptions($source_id, $source_object)
    {

        // Get payment gateway
        if ($payment_gateway = RP_SUB_WooCommerce_Gateway_Stripe::get_payment_gateway()) {

            // Customer opted to update payment method on subscriptions
            if (!empty($_POST['wc-stripe-update-subscriptio-subscriptions-payment-method'])) {

                // Iterate over customer's non-terminated subscriptions
                foreach (subscriptio_get_customer_subscriptions() as $subscription) {

                    // Update Stripe source id
                    $subscription->add_payment_gateway_option('_stripe_source_id', $source_id);

                    // Set payment method to subscription
                    $subscription->get_suborder()->set_payment_method($payment_gateway);

                    // Save updated subscription details
                    $subscription->save();

                    // Update pending renewal order
                    if ($renewal_order = $subscription->get_pending_renewal_order()) {
                        $renewal_order->update_meta_data('_stripe_source_id', $source_id);
                        $renewal_order->save();
                    }
                }
            }
        }
    }

    /**
     * Maybe display version warning
     *
     * @access public
     * @return void
     */
    public function maybe_display_version_warning()
    {

        if (defined('WC_STRIPE_VERSION') && !version_compare(WC_STRIPE_VERSION, $this->min_supported_version, '>=')) {
            echo '<div class="error"><p>' . sprintf(esc_html__('%1$s integrates with WooCommerce Stripe Payment Gateway extension to enable automatic recurring payments, however the lowest supported version is %2$s. Please update WooCommerce Stripe Payment Gateway extension to the latest version at your earliest convenience. If you use have active subscriptions that uses this payment gateway extension, automatic payments will not be processed until the update is complete.', 'subscriptio'), '<strong>Subscriptio</strong>', $this->min_supported_version) . ' ' . sprintf(esc_html__('If you have any questions, please contact %s.', 'subscriptio'), ('<a href="http://url.rightpress.net/new-support-ticket">' . esc_html__('RightPress Support', 'subscriptio') . '</a>')) . '</p></div>';
        }
    }





}

RP_SUB_WooCommerce_Gateway_Stripe::get_instance();
