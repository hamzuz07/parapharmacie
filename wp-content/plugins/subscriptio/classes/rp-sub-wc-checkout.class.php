<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WooCommerce Checkout Functional Controller
 *
 * @class RP_SUB_WC_Checkout
 * @package Subscriptio
 * @author RightPress
 */

class RP_SUB_WC_Checkout
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

        // Maybe activate payment gateways for trial checkout
        add_filter('woocommerce_cart_needs_payment', array($this, 'maybe_activate_payment_gateways'), 100, 2);

        // Process checkout
        add_action('woocommerce_checkout_order_processed', array($this, 'process_checkout'), 100, 3);

        // Maybe force customer registration during Checkout
        RightPress_Help::add_late_filter('woocommerce_checkout_registration_required', array($this, 'maybe_force_customer_registration'));
        RightPress_Help::add_late_filter('woocommerce_checkout_registration_enabled', array($this, 'maybe_force_customer_registration'));

        // Maybe copy cart item reference to order item
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'maybe_copy_cart_item_reference_to_order_item'), 10, 4);

        // Hide cart item reference
        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hide_order_item_cart_item_reference'));
    }

    /**
     * Maybe activate payment gateways if at least one of the recurring carts total is not zero
     *
     * @access public
     * @param bool $is_active
     * @param object $cart
     * @return bool
     */
    public function maybe_activate_payment_gateways($is_active, $cart)
    {

        // TODO: We should only do this when one of the supported payment gateway extensions are present and automatic payment is desired

        // Payment gateways are not active
        if (!$is_active) {

            // Check if any recurring carts are set
            if (isset($cart->rp_sub_recurring_carts) && is_array($cart->rp_sub_recurring_carts)) {

                // Iterate over recurring carts
                foreach ($cart->rp_sub_recurring_carts as $recurring_cart) {

                    // Recurring cart total is not zero
                    if ($recurring_cart->get_total('edit') > 0) {

                        // Set flag
                        $is_active = true;
                    }
                }
            }
        }

        return $is_active;
    }

    /**
     * Process checkout
     *
     * @access public
     * @param int $order_id
     * @param array $posted_data
     * @param object $order
     * @return void
     */
    public function process_checkout($order_id, $posted_data, $order)
    {

        // Cart does not contain subscription products
        if (!subscriptio_cart_contains_subscription_product()) {
            return;
        }

        // Subscription was already created from this order (issue #435)
        if (subscriptio_is_subscription_order($order) || subscriptio_get_subscriptions_related_to_order($order)) {
            return;
        }

        // Get customer id
        $customer_id = $order->get_customer_id();

        // Unknown customer
        if (!$customer_id || !RightPress_Help::wp_user_exists($customer_id)) {

            // Log unexpected error
            RP_SUB_Log_Entry_Controller::add_log_entry(array(
                'event_type'    => 'unexpected_error',
                'order_id'      => $order_id,
                'status'        => 'error',
                'notes'         => array(
                    esc_html__('Failed creating subscription(s) from new order: customer does not exist.', 'subscriptio'),
                    esc_html__('Make sure customers are forced to create an account during checkout.', 'subscriptio'),
                ),
            ));

            // Throw exception to interrupt Checkout and show notice to customer
            throw new Exception(esc_html__('There was a problem creating your subscription. Please try again.', 'subscriptio'));
        }

        $subscription_created = false;

        // Iterate over recurring carts
        foreach (WC()->cart->rp_sub_recurring_carts as $recurring_cart) {

            // Create subscription
            $subscription_created = $this->create_subscription($recurring_cart, $order, $posted_data) ? true : $subscription_created;
        }

        // Set subscription initial order flag
        if ($subscription_created) {
            $order->add_meta_data('_rp_sub:initial_order', 'yes');
            $order->save();
        }
    }

    /**
     * Create subscription from recurring cart and order data
     *
     * Throws exceptions in case of error which are handled in WC_Checkout::process_checkout
     *
     * @access private
     * @param object $recurring_cart
     * @param object $order
     * @param array $posted_data
     * @return object|null
     */
    private function create_subscription($recurring_cart, $order, $posted_data)
    {

        global $wpdb;

        $subscription = null;

        // Start transaction, if supported
        wc_transaction_query('start');

        // Start logging
        $log_entry = RP_SUB_Log_Entry_Controller::create_log_entry(array(
            'event_type'    => 'new_subscription',
            'order_id'      => $order->get_id(),
            'notes'         => array(
                sprintf(esc_html__('Order #%d placed via checkout, creating new subscription.', 'subscriptio'), $order->get_id()),
            ),
        ));

        try {

            // Create new subscription
            $subscription = subscriptio_create_subscription(array(

                // Subscription args
                'billing_cycle' => $recurring_cart->rp_sub_billing_cycle,
                'free_trial'    => $recurring_cart->rp_sub_free_trial,
                'lifespan'      => $recurring_cart->rp_sub_lifespan,

                // Suborder args
                'currency'              => $order->get_currency('edit')             ? $order->get_currency('edit')              : get_woocommerce_currency(),
                'prices_include_tax'    => $order->get_prices_include_tax('edit')   ? $order->get_prices_include_tax('edit')    : (get_option('woocommerce_prices_include_tax') === 'yes'),
                'customer_id'           => $order->get_customer_id('edit'),
                'customer_note'         => $order->get_customer_note('edit')        ? $order->get_customer_note('edit')         : '',
                'customer_ip_address'   => $order->get_customer_ip_address('edit')  ? $order->get_customer_ip_address('edit')   : '',
                'customer_user_agent'   => $order->get_customer_user_agent('edit')  ? $order->get_customer_user_agent('edit')   : '',
                'payment_method'        => $order->get_payment_method('edit')       ? $order->get_payment_method('edit')        : '',
                'created_via'           => $order->get_created_via('edit')          ? $order->get_created_via('edit')           : '',
            ));

            // Add subscription id to log entry and set log entry to subscription
            $log_entry->add_subscription_id($subscription->get_id());
            $subscription->set_log_entry($log_entry);

            // Set initial order id
            $subscription->set_initial_order_id($order->get_id());

            // Reference suborder
            $suborder = $subscription->get_suborder();

            // Set totals
            $suborder->set_props(array(
                'shipping_total'        => $recurring_cart->get_shipping_total(),
                'shipping_tax'          => $recurring_cart->get_shipping_tax(),
                'discount_total'        => $recurring_cart->get_discount_total(),
                'discount_tax'          => $recurring_cart->get_discount_tax(),
                'cart_tax'              => ($recurring_cart->get_cart_contents_tax() + $recurring_cart->get_fee_tax()),
                'total'                 => $recurring_cart->get_total('edit'),
            ));

            // Copy addresses from order
            RP_SUB_WC_Order::copy_addresses_between_orders($order, $suborder);

            // Copy meta data from order (only copies explicitly whitelisted meta entries)
            RP_SUB_WC_Order::copy_meta_data_between_orders($order, $suborder);

            // Set VAT exempt flag
            $suborder->add_meta_data('is_vat_exempt', (WC()->cart->get_customer()->get_is_vat_exempt() ? 'yes' : 'no'));

            // Add line items
            WC()->checkout()->create_order_line_items($suborder, $recurring_cart);

            // Add fees
            WC()->checkout()->create_order_fee_lines($suborder, $recurring_cart);

            // Create subscription shipping lines
            $this->create_subscription_shipping_lines($suborder, $recurring_cart);

            // Add taxes
            WC()->checkout()->create_order_tax_lines($suborder, $recurring_cart);

            // Add coupons
            WC()->checkout()->create_order_coupon_lines($suborder, $recurring_cart);

            // Save subscription
            $subscription->save();

            // Commit transaction, if supported
            wc_transaction_query('commit');

            // Trigger action
            do_action('subscriptio_subscription_created', $subscription);
            do_action('subscriptio_subscription_created_via_checkout', $subscription, $order, $recurring_cart);
        }
        catch (Exception $e) {

            // Rollback transaction, if supported
            wc_transaction_query('rollback');

            // Handle caught exception
            $log_entry->handle_caught_exception($e);

            // End logging
            $log_entry->end_logging($subscription);

            // Throw exception to interrupt Checkout and show notice to customer
            throw new Exception(esc_html__('There was a problem setting up your subscription. If the problem persists, please get in touch with us.', 'subscriptio'));
        }

        // End logging
        $log_entry->end_logging($subscription);

        // Return subscription
        return $subscription;
    }

    /**
     * Create subscription shipping lines
     *
     * TODO: Possibly need to monitor changes to create_order_shipping_lines()
     *
     * @access public
     * @param object $suborder
     * @param object $recurring_cart
     * @return void
     */
    public function create_subscription_shipping_lines(&$suborder, $recurring_cart)
    {

        // Shipping is not recurring
        if (!RP_SUB_Settings::is('shipping_is_recurring')) {
            return;
        }

        // Get chosen shipping methods
        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');

        // Get packages
        // TODO: WC gets it from WC()->shipping()->get_packages()
        $packages = $recurring_cart->rp_sub_shipping_packages;

        // Create subscription shipping lines
        WC()->checkout()->create_order_shipping_lines($suborder, $chosen_shipping_methods, $packages);
    }

    /**
     * Force customer registration during Checkout
     *
     * @access public
     * @param bool $force
     * @return bool
     */
    public function maybe_force_customer_registration($force)
    {

        // Force customer registration if cart contains subscription product
        if (subscriptio_cart_contains_subscription_product()) {
            return true;
        }

        return $force;
    }

    /**
     * Maybe copy cart item reference to order item
     *
     * @access public
     * @param WC_Order_Item_Product $order_item
     * @param string $cart_item_key
     * @param array $values
     * @param WC_Order $order
     * @return void
     */
    public function maybe_copy_cart_item_reference_to_order_item($order_item, $cart_item_key, $values, $order)
    {

        // Get product id
        $product_id = $order_item->get_variation_id() ? $order_item->get_variation_id() : $order_item->get_product_id();

        // Check if product is subscription product
        if (subscriptio_is_subscription_product($product_id)) {

            // Iterate over recurring carts
            foreach (WC()->cart->rp_sub_recurring_carts as $recurring_cart) {

                // Check if cart item exists in current recurring cart
                if (isset($recurring_cart->cart_contents[$cart_item_key])) {

                    // Copy cart item reference to order item meta
                    $order_item->add_meta_data('_rp_sub_cart_item_reference', $recurring_cart->cart_contents[$cart_item_key]['rp_sub_cart_item_reference'], true);
                }
            }
        }
    }

    /**
     *
     *
     * @access public
     * @param array $hidden_order_itemmeta
     * @return array
     */
    public function hide_order_item_cart_item_reference($hidden_order_itemmeta)
    {

        $hidden_order_itemmeta[] = '_rp_sub_cart_item_reference';

        return $hidden_order_itemmeta;
    }





}

RP_SUB_WC_Checkout::get_instance();
