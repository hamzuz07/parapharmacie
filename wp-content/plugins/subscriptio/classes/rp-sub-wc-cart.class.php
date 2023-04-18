<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WooCommerce Cart Functional Controller
 *
 * @class RP_SUB_WC_Cart
 * @package Subscriptio
 * @author RightPress
 */

class RP_SUB_WC_Cart
{

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    // Keep track of cart item trial statuses
    private $cart_item_trial_status = array();

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Enable cart item price display override
        add_filter('rightpress_product_price_cart_item_display_price_enabled', '__return_true');

        // Maybe set price to zero for free trial
        // Note: We are not using the highest possible priority so that 3rd parties can still add some one-off positive price adjustments
        add_filter('woocommerce_product_get_price', array($this, 'maybe_set_price_to_zero'), 100, 2);
        add_filter('woocommerce_product_variation_get_price', array($this, 'maybe_set_price_to_zero'), 100, 2);

        // Add sign-up fee
        add_action('woocommerce_cart_calculate_fees', array($this, 'maybe_add_signup_fees'));

        // Maybe limit product quantity in cart
        add_filter('woocommerce_add_to_cart_validation', array($this, 'maybe_limit_product_quantity_in_cart'), 99, 3);

        // Maybe print ineligible trial notice
        add_filter('woocommerce_add_to_cart_validation', array($this, 'maybe_print_ineligible_trial_notice'), 99, 3);
    }

    /**
     * Maybe set price to zero for free trial
     *
     * @access public
     * @param float $price
     * @param object $product
     * @return float
     */
    public function maybe_set_price_to_zero($price, $product)
    {

        // Check if cart has been loaded
        if (!did_action('woocommerce_cart_loaded_from_session')) {
            return $price;
        }

        // We don't want to affect recurring carts
        if (RP_SUB_Recurring_Carts::is_updating()) {
            return $price;
        }

        // Check if product is cart item product
        if (!empty($product->rightpress_in_cart)) {

            // Check if current cart item has a free trial
            if ($this->cart_item_has_free_trial($product->rightpress_in_cart, $product)) {

                // Set price to zero
                $price = 0.0;
            }
        }

        return $price;
    }

    /**
     * Check if cart item has free trial
     *
     * @access public
     * @param string $cart_item_key
     * @param object $product
     * @return bool
     */
    public function cart_item_has_free_trial($cart_item_key, $product)
    {

        // Trial status of cart item not yet resolved
        if (!isset($this->cart_item_trial_status[$cart_item_key])) {

            // Check if customer is eligible for a free trial of this product
            $this->cart_item_trial_status[$cart_item_key] = RP_SUB_Customer::customer_is_eligible_for_trial($product);
        }

        // Return cart item trial status
        return $this->cart_item_trial_status[$cart_item_key];
    }

    /**
     * Maybe add sign-up fees
     *
     * @access public
     * @return void
     */
    public function maybe_add_signup_fees()
    {

        $signup_fees = array();

        // Iterate over cart items
        foreach (RightPress_Help::get_wc_cart_items() as $cart_item_key => $cart_item) {

            // Check if product is a subscription product
            if (subscriptio_is_subscription_product($cart_item['data'])) {

                // Load subscription product object
                if ($subscription_product = subscriptio_get_subscription_product($cart_item['data'])) {

                    // Get sign-up fee
                    if ($signup_fee = $subscription_product->get_signup_fee()) {

                        // Check if current fee is taxable
                        $is_taxable = apply_filters('subscriptio_subscription_signup_fee_is_taxable', $cart_item['data']->is_taxable(), $cart_item, $subscription_product);

                        // Get fee tax class
                        if ($is_taxable) {

                            $tax_class = apply_filters('subscriptio_subscription_signup_fee_tax_class', $cart_item['data']->get_tax_class(), $cart_item, $subscription_product);
                        }
                        else {

                            $tax_class = '';
                        }

                        // Subtract tax from amount if WooCommerce is to add taxes on top of it
                        $signup_fee = RightPress_Product_Price::maybe_subtract_tax_from_amount($signup_fee, $tax_class);

                        // Maybe multiply signup fee by quantity
                        if (RP_SUB_Settings::is('signup_fees_per_item')) {
                            $signup_fee *= $cart_item['quantity'];
                        }

                        // Add to array
                        $signup_fees[$cart_item_key] = array(
                            'fee'           => $signup_fee,
                            'is_taxable'    => $is_taxable,
                            'tax_class'     => $tax_class,
                        );
                    }
                }
            }
        }

        // Count signup fees
        $count = count($signup_fees);

        // Iteration counter
        $i = 1;

        // Iterate over sign-up fees to add
        foreach ($signup_fees as $cart_item_key => $signup_fee) {

            // Format sign-up fee label
            $label = sprintf(($count > 1 ? esc_html__('Subscription sign-up fee #%s', 'subscriptio') : esc_html__('Subscription sign-up fee', 'subscriptio')), $i);

            // Allow developers to override label
            $label = apply_filters('subscriptio_subscription_signup_fee_label', $label, $cart_item, $subscription_product, $i);

            // Add sign-up fee to cart
            WC()->cart->add_fee($label, $signup_fee['fee'], $signup_fee['is_taxable'], $signup_fee['tax_class']);

            // Increment iteration counter
            $i++;
        }
    }
    /**
     * Maybe limit product quantity in cart
     *
     * @access public
     * @param bool $is_valid
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public function maybe_limit_product_quantity_in_cart($is_valid, $product_id, $quantity)
    {

        // Check if subscriptions are limited
        if (!RP_SUB_Settings::is('subscription_limit', 'no_limit')) {

            // Check if product is a subscription product
            if (subscriptio_is_subscription_product($product_id)) {

                // Cart contains provided product
                if (RightPress_Help::wc_cart_contains_product($product_id)) {

                    // Add notice
                    RightPress_Help::wc_add_notice(esc_html__('Sorry, but you can only purchase one instance of this product and you already added it to cart.', 'subscriptio'), 'notice');

                    // Prevent add to cart
                    return false;
                }
            }
        }

        return $is_valid;
    }

    /**
     * Maybe print ineligible trial notice
     *
     * @access public
     * @param bool $is_valid
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public function maybe_print_ineligible_trial_notice($is_valid, $product_id, $quantity)
    {

        // Check if product is a subscription product and is not parent product
        if (subscriptio_is_subscription_product($product_id) && !RightPress_Help::wc_product_has_children($product_id)) {

            // Load subscription product object
            if ($subscription_product = subscriptio_get_subscription_product($product_id)) {

                // Product has free trial but customer is not eligible
                if ($subscription_product->get_free_trial_length() && !RP_SUB_Customer::customer_is_eligible_for_trial($product_id)) {

                    // One trial per product per customer
                    if (RP_SUB_Settings::is('trial_limit', 'one_per_product')) {
                        $notice = esc_html__('Sorry, but you are not allowed to have a trial of this subscription product anymore.', 'subscriptio');
                    }
                    // One trial per customer
                    else {
                        $notice = esc_html__('Sorry, but you are not allowed to have a trial on this site anymore.', 'subscriptio');
                    }

                    // Append more info
                    $notice .= ' ' . esc_html__('You will be charged the full price of this subscription now.', 'subscriptio');

                    // Add notice
                    wc_add_notice($notice, 'notice');
                }
            }
        }

        // Return intact validation value
        return $is_valid;
    }





}

RP_SUB_WC_Cart::get_instance();
