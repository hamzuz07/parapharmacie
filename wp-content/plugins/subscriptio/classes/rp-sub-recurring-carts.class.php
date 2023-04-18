<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Recurring Carts Controller
 *
 * Prepares recurring prices, totals, shipping fees etc to set on subscriptions after checkout
 *
 * @class RP_SUB_Recurring_Carts
 * @package Subscriptio
 * @author RightPress
 */

class RP_SUB_Recurring_Carts
{

    private $updating_recurring_carts = false;

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

        // Update recurring carts
        RightPress_Help::add_late_action('woocommerce_after_calculate_totals', array($this, 'update_recurring_carts'));

        // Maybe remove cart discounts and checkout fees
        RightPress_Help::add_late_action('woocommerce_before_calculate_totals', array($this, 'maybe_remove_cart_discounts'));
        RightPress_Help::add_late_action('woocommerce_cart_calculate_fees', array($this, 'maybe_remove_checkout_fees'));

        // Maybe disable recurring cart shipping
        RightPress_Help::add_late_filter('woocommerce_cart_needs_shipping', array($this, 'maybe_disable_recurring_cart_shipping'));

        // Maybe set price to regular
        // Note: We are using late position to make sure we reset discounts made by (pretty much) all 3rd party plugins but still leave a few positions for custom price modifications if needed
        RightPress_Help::add_late_filter('woocommerce_product_get_price', array($this, 'maybe_set_price_to_regular'), 2, -10);
        RightPress_Help::add_late_filter('woocommerce_product_variation_get_price', array($this, 'maybe_set_price_to_regular'), 2, -10);
    }

    /**
     * Update recurring carts
     *
     * @access public
     * @param object $cart
     * @return void
     */
    public function update_recurring_carts($cart)
    {

        // Cart does not contain subscription product
        if (!subscriptio_cart_contains_subscription_product()) {
            return;
        }

        // Already updating recurring carts
        if ($this->updating_recurring_carts) {
            return;
        }

        // Store recurring carts
        $recurring_carts = array();

        // Set flag
        $this->updating_recurring_carts = true;

        // Iterate over all cart items
        foreach ($cart->get_cart_contents() as $cart_item_key => $cart_item) {

            // Check if cart item product is subscription product
            if (subscriptio_is_subscription_product($cart_item['data'])) {

                // Load subscription product
                if ($subscription_product = subscriptio_get_subscription_product($cart_item['data'])) {

                    // Get recurring cart hash for cart item
                    if ($recurring_cart_hash = $this->get_recurring_cart_hash($subscription_product, $cart_item)) {

                        // Create new recurring cart if it does not exist yet
                        if (!isset($recurring_carts[$recurring_cart_hash])) {

                            // Get cart clone
                            $recurring_cart = $this->get_cart_clone($cart);

                            // Set flag
                            $recurring_cart->rp_sub_recurring_cart = true;

                            // Set billing cycle
                            $recurring_cart->rp_sub_billing_cycle = $subscription_product->get_billing_cycle_length() . ' ' . $subscription_product->get_billing_cycle_period();

                            // Maybe set lifespan
                            if ($subscription_product->get_lifespan_length()) {
                                $recurring_cart->rp_sub_lifespan = $subscription_product->get_lifespan_length() . ' ' . $subscription_product->get_lifespan_period();
                            }
                            else {
                                $recurring_cart->rp_sub_lifespan = null;
                            }

                            // Check if free trial should be set
                            if ($subscription_product->get_free_trial_length() && RP_SUB_Customer::customer_is_eligible_for_trial($subscription_product->get_wc_product())) {
                                $recurring_cart->rp_sub_free_trial = $subscription_product->get_free_trial_length() . ' ' . $subscription_product->get_free_trial_period();
                            }
                            else {
                                $recurring_cart->rp_sub_free_trial = null;
                            }

                            // Set recurring cart to main array
                            $recurring_carts[$recurring_cart_hash] = $recurring_cart;
                        }

                        // Flag current cart item so it is not removed on cleanup
                        $recurring_carts[$recurring_cart_hash]->cart_contents[$cart_item_key]['rp_sub_recurring_cart_hash'] = $recurring_cart_hash;

                        // Set cart item price at current point for later reference
                        $recurring_carts[$recurring_cart_hash]->cart_contents[$cart_item_key]['rp_sub_recurring_price_reference'] = $recurring_carts[$recurring_cart_hash]->cart_contents[$cart_item_key]['data']->get_price();

                        // Set cart item identifier for later reference
                        // Note: We are doing this because $cart_item_key may be the same next time the same product is purchased with the same settings
                        $recurring_carts[$recurring_cart_hash]->cart_contents[$cart_item_key]['rp_sub_cart_item_reference'] = RightPress_Help::get_hash() . '_' . $cart_item_key;
                    }
                }
            }
        }

        // Copy current chosen shipping methods
        $chosen_shipping_methods =  WC()->session->get('chosen_shipping_methods', array());

        // Iterate over recurring carts
        foreach ($recurring_carts as $recurring_cart_hash => $recurring_cart) {

            // Iterate over all cart items
            foreach ($recurring_cart->get_cart_contents() as $cart_item_key => $cart_item) {

                // Check if current cart item belongs to current recurring cart
                if (empty($cart_item['rp_sub_recurring_cart_hash']) || $cart_item['rp_sub_recurring_cart_hash'] !== $recurring_cart_hash) {

                    // Remove cart item that does not belong to current recurring cart
                    unset($recurring_carts[$recurring_cart_hash]->cart_contents[$cart_item_key]);
                }
            }

            // Reset shipping
            WC()->shipping()->reset_shipping();

            // Restore shipping methods
            WC()->session->set('chosen_shipping_methods', $chosen_shipping_methods);

            // Refresh totals
            $recurring_carts[$recurring_cart_hash]->calculate_totals();

            // Save packages
            $recurring_cart->rp_sub_shipping_packages = WC()->shipping()->get_packages();
        }

        // Unset flag
        // Note: This flag must be unset earlier than we fix shipping on the main cart
        $this->updating_recurring_carts = false;

        // Set updated recurring carts
        $cart->rp_sub_recurring_carts = $recurring_carts;

        // Reset shipping
        WC()->shipping()->reset_shipping();

        // Restore shipping methods
        WC()->session->set('chosen_shipping_methods', $chosen_shipping_methods);

        // Maybe recalculate main cart shipping
        if (WC()->cart->show_shipping()) {
            WC()->cart->calculate_shipping();
        }
    }

    /**
     * Get recurring cart hash for cart item
     *
     * @access public
     * @param RP_SUB_Subscription_Product $subscription_product
     * @param array $cart_item
     * @return string
     */
    public function get_recurring_cart_hash($subscription_product, $cart_item)
    {

        // Set properties that must match for two subscription products to end up in one subscription
        $hash_data = array(

            // Subscription product settings
            $subscription_product->get_billing_cycle_length(),
            $subscription_product->get_billing_cycle_period(),
            $subscription_product->get_free_trial_length(),
            $subscription_product->get_free_trial_period(),
            $subscription_product->get_lifespan_length(),
            $subscription_product->get_lifespan_period(),

            // Trial eligibility
            RP_SUB_Customer::customer_is_eligible_for_trial($subscription_product->get_wc_product()),
        );

        // Add random string if each product must go into separate subscription
        if (!RP_SUB_Settings::is('multiple_product_checkout', 'single_subscription')) {
            $hash_data[] = RightPress_Help::get_hash();
        }

        // Hash data and return
        return RightPress_Help::get_hash(false, $hash_data);
    }

    /**
     * Get cart clone for use as recurring cart
     *
     * Does some cleanup
     *
     * @access public
     * @param object $cart
     * @return object
     */
    public function get_cart_clone($cart = null)
    {

        // Get cart
        if ($cart === null) {
            $cart = WC()->cart;
        }

        // Get cart clone
        $cart_clone = clone $cart;

        // Remove removed items
        $cart_clone->removed_cart_contents = array();

        // Return cart clone
        return $cart_clone;
    }

    /**
     * Maybe remove cart discounts
     *
     * @access public
     * @param WC_Cart $cart
     * @return void
     */
    public function maybe_remove_cart_discounts($cart)
    {

        // This should only run when totals are calculated on a recurring cart
        if (!$this->updating_recurring_carts || !isset($cart->rp_sub_recurring_cart)) {
            return;
        }

        // Not all cart discounts are recurring
        if (!RP_SUB_Settings::is('cart_discounts_are_recurring')) {

            // Get cart discount whitelist
            $whitelist = apply_filters('subscriptio_recurring_cart_discount_whitelist', array(), $cart);

            // Get applied coupons
            $applied_coupons = $cart->get_applied_coupons();

            // Iterate over cart discounts
            foreach ($applied_coupons as $applied_coupon) {

                // Cart discount is not whitelisted
                if (!in_array($applied_coupon, $whitelist, true)) {

                    // Note: We are removing coupon directly from the array (instead of calling $cart->remove_coupon())
                    // to avoid calling woocommerce_removed_coupon action which may trigger some unintended processes

                    // Locate current coupon in the applied coupons array
                    $position = array_search(wc_format_coupon_code($applied_coupon), $applied_coupons, true);

                    // Unset coupon if it was located
                    if ($position !== false) {
                        unset($cart->applied_coupons[$position]);
                    }
                }
            }
        }
    }

    /**
     * Maybe remove checkout fees
     *
     * @access public
     * @param WC_Cart $cart
     * @return void
     */
    public function maybe_remove_checkout_fees($cart)
    {

        // This should only run when totals are calculated on a recurring cart
        if (!$this->updating_recurring_carts || !isset($cart->rp_sub_recurring_cart)) {
            return;
        }

        // Not all checkout fees are recurring
        if (!RP_SUB_Settings::is('checkout_fees_are_recurring')) {

            // Get checkout fee whitelist
            $whitelist = apply_filters('subscriptio_recurring_checkout_fee_whitelist', array(), $cart);

            // Store filtered fees
            $filtered_fees = array();

            // Iterate over checkout fees
            foreach ($cart->get_fees() as $fee_id => $fee_props) {

                // Checkout fee is whitelisted
                if (in_array($fee_id, $whitelist, true)) {

                    // Add fee to filtered fees array
                    $filtered_fees[$fee_id] = $fee_props;
                }
            }

            // Set filtered fees
            $cart->fees_api()->set_fees($filtered_fees);
        }
    }

    /**
     * Maybe disable recurring cart shipping
     *
     * @access public
     * @param bool $needs_shipping
     * @return void
     */
    public function maybe_disable_recurring_cart_shipping($needs_shipping)
    {

        // System is updating recurring carts and shipping is not recurring
        if ($this->updating_recurring_carts && !RP_SUB_Settings::is('shipping_is_recurring')) {

            // Recurring cart does not need shipping
            $needs_shipping = false;
        }

        return $needs_shipping;
    }

    /**
     * Maybe set price to regular
     *
     * @access public
     * @param float $price
     * @param object $product
     * @return float
     */
    public function maybe_set_price_to_regular($price, $product)
    {

        // System is updating recurring carts and sale prices are not recurring
        if ($this->updating_recurring_carts && !RP_SUB_Settings::is('sale_price_is_recurring')) {

            // Get regular price
            $regular_price = $product->get_regular_price('edit');

            // Current price is lower than regular price
            if (RightPress_Product_Price::price_is_smaller_than($price, $regular_price)) {

                // Set price to regular price
                $price = $regular_price;
            }
        }

        return $price;
    }

    /**
     * Check if system is updating recurring carts
     *
     * @access public
     * @return bool
     */
    public static function is_updating()
    {

        return RP_SUB_Recurring_Carts::get_instance()->updating_recurring_carts;
    }

    /**
     * Get recurring cart item reference price
     *
     * @access public
     * @param string $cart_item_key
     * @return float|null
     */
    public static function get_recurring_cart_item_reference_price($cart_item_key)
    {

        // Check if recurring carts are ready
        if (!RP_SUB_Recurring_Carts::is_updating() && isset(WC()->cart->rp_sub_recurring_carts)) {

            // Iterate over recurring carts
            foreach (WC()->cart->rp_sub_recurring_carts as $recurring_cart) {

                // Check if cart item exists in current recurring cart
                if (isset($recurring_cart->cart_contents[$cart_item_key])) {

                    // Return recurring cart item reference price
                    return $recurring_cart->cart_contents[$cart_item_key]['rp_sub_recurring_price_reference'];
                }
            }
        }

        // Recurring cart item price not found
        return null;
    }





}

RP_SUB_Recurring_Carts::get_instance();
