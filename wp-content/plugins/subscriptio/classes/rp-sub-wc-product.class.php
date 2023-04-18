<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WooCommerce Product Controller
 *
 * @class RP_SUB_WC_Product
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_WC_Product
{

    private $product_is_purchasable = array();

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

        // Maybe make product not purchasable
        add_filter('woocommerce_is_purchasable', array($this, 'maybe_make_product_not_purchasable'), 99, 2);

        // Maybe print subscription limit notice
        add_filter('woocommerce_single_product_summary', array($this, 'maybe_print_subscription_limit_notice'), 99, 2);

        // Maybe limit max quantity
        add_filter('woocommerce_quantity_input_args', array($this, 'maybe_limit_max_quantity'), 99, 2);
        add_filter('woocommerce_available_variation', array($this, 'maybe_limit_max_quantity'), 99, 2);

        // Maybe override add to cart button text
        add_filter('woocommerce_product_add_to_cart_text', array($this, 'maybe_override_add_to_cart_text'), 90, 2);
        add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'maybe_override_single_add_to_cart_text'), 90, 2);
        add_filter('woocommerce_available_variation', array($this, 'maybe_prepare_product_variations_for_add_to_cart_text_change'), 90, 3);

        // Maybe show prices for all individual variations
        add_filter('woocommerce_show_variation_price', array($this, 'maybe_show_variation_price'), 99, 3);
    }

    /**
     * Maybe make product not purchasable
     *
     * @access public
     * @param bool $is_purchasable
     * @param WC_Product $product
     * @return bool
     */
    public function maybe_make_product_not_purchasable($is_purchasable, $product)
    {

        global $wp;

        // Result not yet in cache
        if (!isset($this->product_is_purchasable[$product->get_id()])) {

            // Make sure this is a subscription product and we are not on the "Thank You" page
            if ($is_purchasable && subscriptio_is_subscription_product($product) && !is_order_received_page()) {

                // Get product id to check
                $product_id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();

                // Subscription limits are enabled
                if (!RP_SUB_Settings::is('subscription_limit', 'no_limit')) {

                    // Iterate over non-terminated customer subscriptions
                    foreach (subscriptio_get_customer_subscriptions() as $subscription) {

                        // One non-terminated subscription per customer per site
                        if (RP_SUB_Settings::is('subscription_limit', 'one_per_customer')) {

                            // Customer already has a non-terminated subscription on this site
                            $is_purchasable = false;
                        } // One non-terminated subscription per product per customer per site
                        else if (RP_SUB_Settings::is('subscription_limit', 'one_per_product')) {

                            // Iterate over subscription items
                            foreach ($subscription->get_items() as $item) {

                                // Compare product ids
                                if ($item->get_product_id() === $product_id) {

                                    // Customer already has a non-terminated subscription for this product on this site
                                    $is_purchasable = false;
                                }
                            }
                        }
                    }
                }
            }

            // Add result to cache
            $this->product_is_purchasable[$product->get_id()] = $is_purchasable;
        }

        // Return result from cache
        return $this->product_is_purchasable[$product->get_id()];
    }

    /**
     * Maybe print subscription limit notice
     *
     * @access public
     * @return string
     */
    public function maybe_print_subscription_limit_notice()
    {

        global $product;

        // Product limits are applied
        if (!$this->maybe_make_product_not_purchasable(true, $product)) {

            // One non-terminated subscription per customer per site
            if (RP_SUB_Settings::is('subscription_limit', 'one_per_customer')) {
                wc_print_notice(esc_html__('Sorry, you are not allowed to purchase this product as you already have a subscription on this site.', 'subscriptio'), 'notice');
            }
            // One non-terminated subscription per product per customer per site
            else if (RP_SUB_Settings::is('subscription_limit', 'one_per_product')) {
                wc_print_notice(esc_html__('Sorry, you are not allowed to purchase this product as you already have it in one of your subscriptions.', 'subscriptio'), 'notice');
            }
        }
    }

    /**
     * Maybe limit max quantity
     *
     * @access public
     * @param array $args
     * @param WC_Product $product
     * @return array
     */
    public function maybe_limit_max_quantity($args, $product)
    {

        // Check if subscriptions are limited
        if (!RP_SUB_Settings::is('subscription_limit', 'no_limit')) {

            // Check if product is a subscription product
            if (subscriptio_is_subscription_product($product)) {

                // Product variation hook
                if (current_filter() === 'woocommerce_available_variation') {
                    $args['max_qty'] = 1;
                }
                // Simple product hook
                else {
                    $args['max_value'] = 1;
                }
            }
        }

        return $args;
    }

    /**
     * Maybe override add to cart button text
     *
     * @access public
     * @param string $text
     * @param object $product
     * @return string
     */
    public function maybe_override_add_to_cart_text($text, $product)
    {

        // Only applicable to simple products
        if ($product->is_type('simple')) {
            $text = $this->maybe_override_single_add_to_cart_text($text, $product);
        }

        return $text;
    }

    /**
     * Maybe override single product add to cart button text
     *
     * @access public
     * @param string $text
     * @param object $product
     * @return string
     */
    public function maybe_override_single_add_to_cart_text($text, $product)
    {

        // Check if product is simple and if it can be purchased
        if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) {

            // Change button label if set in settings and product is subscription
            if (RP_SUB_Settings::get('add_to_cart_label') && subscriptio_is_subscription_product($product)) {
                $text = RP_SUB_Settings::get('add_to_cart_label');
            }
        }

        return $text;
    }

    /**
     * Maybe prepare product variations for add to cart text change
     *
     * @access public
     * @param array $data
     * @param object $variable_product
     * @param object $variation
     * @return array
     */
    public function maybe_prepare_product_variations_for_add_to_cart_text_change($data, $variable_product, $variation)
    {

        // Check if parent variable product has at least one subscription variation
        if (subscriptio_is_subscription_product($variable_product)) {

            // Add correct label to set in frontend
            $data['rp_sub_add_to_cart_text'] = subscriptio_is_subscription_product($variation) ? RP_SUB_Settings::get('add_to_cart_label') : $variation->single_add_to_cart_text();
        }

        return $data;
    }

    /**
     * Maybe show prices for all individual variations
     *
     * @access public
     * @param bool $show
     * @param object $variable_product
     * @param object $variation
     * @return bool
     */
    public function maybe_show_variation_price($show, $variable_product, $variation)
    {

        // Check if parent variable product has at least one subscription variation
        if (subscriptio_is_subscription_product($variable_product)) {

            // Set flag
            $show = true;
        }

        return $show;
    }





}

RP_SUB_WC_Product::get_instance();
