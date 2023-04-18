<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wc-product-object-admin.class.php';

/**
 * Subscription Product Admin
 *
 * @class RP_SUB_Subscription_Product_Admin
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Subscription_Product_Admin extends RP_SUB_WC_Product_Object_Admin
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

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Get controller class
     *
     * @access public
     * @return string
     */
    public function get_controller_class()
    {

        return 'RP_SUB_Subscription_Product_Controller';
    }

    /**
     * Get checkbox label
     *
     * @access public
     * @return string
     */
    public function get_checkbox_label()
    {

        return esc_html__('Subscription', 'subscriptio');
    }

    /**
     * Get checkbox description
     *
     * @access public
     * @return string
     */
    public function get_checkbox_description()
    {

        return esc_html__('Enable recurring billing for this product.', 'subscriptio');
    }

    /**
     * Get product list custom column value
     *
     * @access public
     * @param int $post_id
     * @return string
     */
    public function get_product_list_shared_column_value($post_id)
    {

        return '<span class="rp_sub_subscription_product-flag">Subscription</span>';
    }

    /**
     * Print product settings
     *
     * @access public
     * @return void
     */
    public function print_product_settings()
    {

        // Get product id
        global $post;
        $product_id = $post->ID;

        // Load product
        if ($product = wc_get_product($product_id)) {

            // Product must not be variable
            if (!RightPress_Help::wc_product_has_children($product)) {

                // Load subscription product object
                if ($subscription_product = $this->get_controller()->get_object($product)) {

                    // Get time periods with names
                    $time_periods = RP_SUB_Time::get_time_periods_for_display();

                    // Include view
                    include RP_SUB_PLUGIN_PATH . 'views/product/product-settings.php';
                }
            }
        }
    }

    /**
     * Print variation settings
     *
     * @access public
     * @param int $loop
     * @param array $variation_data
     * @param object $variation
     * @return void
     */
    public function print_variation_settings($loop, $variation_data, $variation)
    {

        // Load subscription product object
        if ($subscription_product = $this->get_controller()->get_object($variation->ID)) {

            // Get time periods with names
            $time_periods = RP_SUB_Time::get_time_periods_for_display();

            // Include view
            include RP_SUB_PLUGIN_PATH . 'views/product/product-variation-settings.php';
        }
    }

    /**
     * Sanitize and validate product settings
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param array $settings
     * @param object $object
     * @return array
     */
    public function sanitize_product_settings($settings, $object)
    {

        // Prepare to store errors
        $sanitized  = array();

        // List of internal properties that can't be set by user
        $internal_properties = array(
            'updated', 'plugin_version'
        );

        // Iterate over property keys of this object
        foreach ($object->get_data_keys() as $key) {

            // Skip internal properties
            if (in_array($key, $internal_properties, true)) {
                continue;
            }

            // Format sanitizer name
            $sanitizer = 'sanitize_' . $key;

            // Get value
            $value = isset($settings[$key]) ? $settings[$key] : null;

            // Sanitize and validate value
            $value = $object->{$sanitizer}($value);

            // Sanitize value and add to array
            $sanitized[$key] = $value;
        }

        // Reset time periods if time lengths are empty
        if (empty($sanitized['free_trial_length'])) {
            $sanitized['free_trial_period'] = null;
        }
        if (empty($sanitized['lifespan_length'])) {
            $sanitized['lifespan_period'] = null;
        }

        // Lifespan can't be shorter than one billing cycle
        if (!empty($sanitized['lifespan_length'])) {
            if (RP_SUB_Time::period_length_is_longer_than(($sanitized['billing_cycle_length'] . ' ' . $sanitized['billing_cycle_period']), ($sanitized['lifespan_length'] . ' ' . $sanitized['lifespan_period']))) {
                throw new RightPress_Exception('rp_sub_subscription_product_lifespan_too_short', esc_html__('Subscription lifespan must not be shorter than than its billing cycle.', 'subscriptio'));
            }
        }

        // Return sanitized setting
        return $sanitized;
    }





}

RP_SUB_Subscription_Product_Admin::get_instance();
