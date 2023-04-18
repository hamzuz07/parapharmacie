<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Plugin specific methods related to product/subscription pricing
 *
 * @class RP_SUB_Pricing
 * @package Subscriptio
 * @author RightPress
 */

class RP_SUB_Pricing
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

        // Maybe change shop product display price
        add_filter('rightpress_product_price_display_price', array($this, 'maybe_change_shop_product_display_price'), 80, 3);

        // Maybe change cart item display price
        add_filter('rightpress_product_price_cart_item_product_display_price', array($this, 'maybe_change_cart_item_display_price'), 80, 5);
        add_filter('rightpress_product_price_cart_item_product_display_price_no_changes', array($this, 'maybe_change_cart_item_display_price_no_changes'), 80, 3);
    }

    /**
     * Maybe change shop product display price
     *
     * @access public
     * @param string $display_price
     * @param object $product
     * @param string $filter_name
     * @return string
     */
    public function maybe_change_shop_product_display_price($display_price, $product, $filter_name)
    {

        // Check request and product
        if (!RightPress_Help::is_request('frontend') || !subscriptio_is_subscription_product($product)) {
            return $display_price;
        }

        // Product does not have children, e.g. simple product
        if (!RightPress_Help::wc_product_has_children($product)) {

            // Format subscription product display price
            $display_price = RP_SUB_Pricing::format_subscription_product_display_price($display_price, subscriptio_get_subscription_product($product));
        }
        // Product is variable
        else if ($product->is_type('variable')) {

            // Get visible variation prices
            $prices = RightPress_Product_Price_Shop::get_visible_variations_prices($product);

            $min_reference_price    = null;
            $max_reference_price    = null;
            $selected_variation_id  = null;
            $selected_price         = null;
            $settings_hashes        = array();

            // Iterate over visible variations
            foreach ($product->get_visible_children() as $variation_id) {

                // Current variation is not subscription product, skip it
                if (!subscriptio_is_subscription_product($variation_id)) {
                    continue;
                }

                // Load subscription product
                $subscription_product = subscriptio_get_subscription_product($variation_id);

                // Calculate reference price per day
                $period_length      = $subscription_product->get_billing_cycle_length() . ' ' . $subscription_product->get_billing_cycle_period();
                $reference_price    = $prices['price'][$variation_id] / RP_SUB_Time::convert_period_length_to($period_length, 'days');

                // Maybe set min price
                if ($min_reference_price === null || RightPress_Product_Price::price_is_smaller_than($reference_price, $min_reference_price)) {
                    $selected_variation_id  = $variation_id;
                    $min_reference_price    = $reference_price;
                    $selected_price         = $prices['price'][$variation_id];
                }

                // Maybe set max price
                if ($max_reference_price === null || RightPress_Product_Price::price_is_bigger_than($reference_price, $max_reference_price)) {
                    $max_reference_price = $reference_price;
                }

                // Add settings hashes for comparison
                $settings_hashes[] = RightPress_Help::get_hash(false, array(
                    $subscription_product->get_billing_cycle_length(),
                    $subscription_product->get_billing_cycle_period(),
                    $subscription_product->get_free_trial_length(),
                    $subscription_product->get_free_trial_period(),
                    $subscription_product->get_lifespan_length(),
                    $subscription_product->get_lifespan_period(),
                    $subscription_product->get_signup_fee(),
                ));
            }

            // Leave unique settings hashes only
            $settings_hashes = array_unique($settings_hashes);

            // Get selected subscription product
            $subscription_product = subscriptio_get_subscription_product($selected_variation_id);

            // Prepare selected price for display
            $selected_display_price = wc_price(RightPress_Product_Price_Display::prepare_product_price_for_display($subscription_product->get_wc_product(), $selected_price, false, true));

            // Child prices differ
            if (RightPress_Product_Price::prices_differ($min_reference_price, $max_reference_price)) {

                // Format recurring amount for display
                $display_price = RP_SUB_Pricing::format_recurring_amount_for_display($selected_display_price, $subscription_product->get_billing_cycle_length(), $subscription_product->get_billing_cycle_period());

                // Apply extra formatting and set to main variable
                $display_price = sprintf(esc_html__('From %s', 'subscriptio'), $display_price);
            }
            // Child prices do not differ but other settings differ (simplified display price will be used)
            else if (count($settings_hashes) > 1) {

                // Format recurring amount for display
                $display_price = RP_SUB_Pricing::format_recurring_amount_for_display($selected_display_price, $subscription_product->get_billing_cycle_length(), $subscription_product->get_billing_cycle_period());
            }
            // Child prices and settings do not differ
            else {

                // Format subscription product display price
                $display_price = RP_SUB_Pricing::format_subscription_product_display_price($display_price, $subscription_product);
            }

            // Allow developers to override and set new display price
            $display_price = apply_filters('subscriptio_subscription_product_variable_formatted_price', $display_price, $subscription_product);
        }

        return $display_price;
    }

    /**
     * Maybe change cart item display price
     *
     * @access public
     * @param string $display_price
     * @param array $price_breakdown_entry
     * @param float $full_price
     * @param object $product
     * @param array $cart_item
     * @return string
     */
    public function maybe_change_cart_item_display_price($display_price, $price_breakdown_entry, $full_price, $product, $cart_item = null)
    {

        // Check if product is subscription product
        if (subscriptio_is_subscription_product($product)) {

            // Format cart item display price
            $display_price = RP_SUB_Pricing::format_cart_item_display_price($display_price, subscriptio_get_subscription_product($product), $cart_item);
        }

        return $display_price;
    }

    /**
     * Maybe change cart item display price
     *
     * Used when rightpress_product_price_cart_item_product_display_price is not called
     *
     * @access public
     * @param string $display_price
     * @param array $cart_item
     * @param string $cart_item_key
     * @return string
     */
    public function maybe_change_cart_item_display_price_no_changes($display_price, $cart_item, $cart_item_key)
    {

        // Check if product is subscription product
        if (subscriptio_is_subscription_product($cart_item['data'])) {

            // Format cart item display price
            $display_price = RP_SUB_Pricing::format_cart_item_display_price($display_price, subscriptio_get_subscription_product($cart_item['data']), $cart_item);
        }

        return $display_price;
    }

    /**
     * Format subscription product display price
     *
     * @access public
     * @param string $display_price
     * @param RP_SUB_Subscription_Product $subscription_product
     * @return string
     */
    public static function format_subscription_product_display_price($display_price, $subscription_product)
    {

        // Make sure subscription has recurring price so that we don't format empty prices
        if (RightPress_Help::is_empty($subscription_product->get_recurring_price())) {
            return $display_price;
        }

        // Format recurring amount
        $display_price = RP_SUB_Pricing::format_recurring_amount_for_display($display_price, $subscription_product->get_billing_cycle_length(), $subscription_product->get_billing_cycle_period());

        // Get formatted lifespan
        $formatted_lifespan = $subscription_product->get_formatted_lifespan();

        // Get formatted free trial
        $formatted_free_trial = $subscription_product->get_formatted_free_trial();

        // Get formatted signup fee
        $formatted_signup_fee = $subscription_product->get_formatted_signup_fee();

        // Lifespan, free trial and signup fee are set
        if ($formatted_lifespan && $formatted_free_trial && $formatted_signup_fee) {
            $display_price = sprintf(esc_html__('%1$s for %2$s with a free trial of %3$s and a sign-up fee of %4$s', 'subscriptio'), $display_price, $formatted_lifespan, $formatted_free_trial, $formatted_signup_fee);
        }
        // Lifespan and free trial are set
        else if ($formatted_lifespan && $formatted_free_trial) {
            $display_price = sprintf(esc_html__('%1$s for %2$s with a free trial of %3$s', 'subscriptio'), $display_price, $formatted_lifespan, $formatted_free_trial);
        }
        // Lifespan and signup fee are set
        else if ($formatted_lifespan && $formatted_signup_fee) {
            $display_price = sprintf(esc_html__('%1$s for %2$s with a sign-up fee of %3$s', 'subscriptio'), $display_price, $formatted_lifespan, $formatted_signup_fee);
        }
        // Free trial and signup fee are set
        else if ($formatted_free_trial && $formatted_signup_fee) {
            $display_price = sprintf(esc_html__('%1$s with a free trial of %2$s and a sign-up fee of %3$s', 'subscriptio'), $display_price, $formatted_free_trial, $formatted_signup_fee);
        }
        // Lifespan is set
        else if ($formatted_lifespan) {
            $display_price = sprintf(esc_html__('%1$s for %2$s', 'subscriptio'), $display_price, $formatted_lifespan);
        }
        // Free trial is set
        else if ($formatted_free_trial) {
            $display_price = sprintf(esc_html__('%1$s with a free trial of %2$s', 'subscriptio'), $display_price, $formatted_free_trial);
        }
        // Signup fee is set
        else if ($formatted_signup_fee) {
            $display_price = sprintf(esc_html__('%1$s with a sign-up fee of %2$s', 'subscriptio'), $display_price, $formatted_signup_fee);
        }

        return $display_price;
    }

    /**
     * Format cart item display price
     *
     * @access public
     * @param string $cart_item_display_price
     * @param RP_SUB_Subscription_Product $subscription_product
     * @param array $cart_item
     * @return string
     */
    public static function format_cart_item_display_price($cart_item_display_price, $subscription_product, $cart_item = null)
    {

        // Check if cart item is set
        if ($cart_item !== null) {

            // Get current price
            $current_price = $cart_item['data']->get_price();

            // Get recurring cart item price
            $recurring_price = RP_SUB_Recurring_Carts::get_recurring_cart_item_reference_price($cart_item['key']);

            // Check if recurring price differs from current price
            $prices_differ = RightPress_Product_Price::prices_differ($recurring_price, $current_price);

            // If prices differ, we need to prepare recurring price for display first
            if ($prices_differ) {
                $recurring_display_price = wc_price(RightPress_Product_Price_Display::prepare_product_price_for_display($subscription_product->get_wc_product(), $recurring_price, true));
            }
            // If prices do not differ, set recurring display price to cart item display price
            else {
                $recurring_display_price = $cart_item_display_price;
            }

            // Format recurring amount for display
            $formatted_recurring_price = RP_SUB_Pricing::format_recurring_amount_for_display($recurring_display_price, $subscription_product->get_billing_cycle_length(), $subscription_product->get_billing_cycle_period());

            // If lifespan is set, apply extra formatting to formatted recurring price
            if ($formatted_lifespan = $subscription_product->get_formatted_lifespan()) {
                $formatted_recurring_price = sprintf(esc_html__('%1$s for %2$s', 'subscriptio'), $formatted_recurring_price, $formatted_lifespan);
            }

            // If prices differ, append formatted recurring price to current price
            if ($prices_differ) {
                $cart_item_display_price = sprintf(esc_html__('%1$s now then %2$s', 'subscriptio'), $cart_item_display_price, $formatted_recurring_price);
            }
            // If prices do not differ, set display price to formatted recurring price
            else {
                $cart_item_display_price = $formatted_recurring_price;
            }
        }

        // Return cart item display price
        return $cart_item_display_price;
    }

    /**
     * Format recurring amount for display
     *
     * @access public
     * @param string $display_price
     * @param int $time_length
     * @param string $time_period
     * @return string
     */
    public static function format_recurring_amount_for_display($display_price, $time_length, $time_period)
    {

        // Get formatted billing cycle
        $formatted_billing_cycle = RP_SUB_Time::get_formatted_time_period_string($time_length, $time_period, false);

        // Format amount
        $display_price = sprintf(esc_html__('%1$s / %2$s', 'subscriptio'), $display_price, $formatted_billing_cycle);

        // Allow developers to override and return
        return apply_filters('subscriptio_formatted_recurring_amount_for_display', $display_price, $time_length, $time_period);
    }





}

RP_SUB_Pricing::get_instance();
