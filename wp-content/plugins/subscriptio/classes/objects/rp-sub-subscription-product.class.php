<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wc-product-object.class.php';

/**
 * Subscription Product
 *
 * @class RP_SUB_Subscription_Product
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Subscription_Product extends RP_SUB_WC_Product_Object
{

    protected $data = array(
        'subscription_product'  => false,
        'billing_cycle_length'  => null,
        'billing_cycle_period'  => null,
        'free_trial_length'     => null,
        'free_trial_period'     => null,
        'lifespan_length'       => null,
        'lifespan_period'       => null,
        'signup_fee'            => null,
    );

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
     * GETTERS
     * =================================================================================================================
     */

    /**
     * Get subscription product flag
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return bool
     */
    public function get_subscription_product($context = 'view', $args = array())
    {

        // Get value
        $value = $this->get_property('subscription_product', $context, $args);

        // Maybe prepare value for storage
        if ($context === 'store') {
            $value = $value ? 'yes' : 'no';
        }

        // Return value
        return $value;
    }

    /**
     * Get billing cycle length
     *
     * @access public
     * @return int|null
     */
    public function get_billing_cycle_length($context = 'view', $args = array())
    {

        return $this->get_property('billing_cycle_length', $context, $args);
    }

    /**
     * Get billing cycle period
     *
     * @access public
     * @return string|null
     */
    public function get_billing_cycle_period($context = 'view', $args = array())
    {

        return $this->get_property('billing_cycle_period', $context, $args);
    }

    /**
     * Get free trial length
     *
     * @access public
     * @return int|null
     */
    public function get_free_trial_length($context = 'view', $args = array())
    {

        return $this->get_property('free_trial_length', $context, $args);
    }

    /**
     * Get free trial period
     *
     * @access public
     * @return int|null
     */
    public function get_free_trial_period($context = 'view', $args = array())
    {

        return $this->get_property('free_trial_period', $context, $args);
    }

    /**
     * Get lifespan length
     *
     * @access public
     * @return int|null
     */
    public function get_lifespan_length($context = 'view', $args = array())
    {

        return $this->get_property('lifespan_length', $context, $args);
    }

    /**
     * Get lifespan period
     *
     * @access public
     * @return string|null
     */
    public function get_lifespan_period($context = 'view', $args = array())
    {

        return $this->get_property('lifespan_period', $context, $args);
    }

    /**
     * Get signup fee
     *
     * @access public
     * @return float|null
     */
    public function get_signup_fee($context = 'view', $args = array())
    {

        // Get signup fee
        $signup_fee = $this->get_property('signup_fee', $context, $args);

        // Return signup fee
        return $signup_fee;
    }

    /**
     * =================================================================================================================
     * SETTERS
     * =================================================================================================================
     */

    /**
     * Set subscription product flag
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param bool $value
     * @return void
     */
    public function set_subscription_product($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_subscription_product($value);

        // Set property
        $this->set_property('subscription_product', $value);
    }

    /**
     * Set billing cycle length
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return void
     */
    public function set_billing_cycle_length($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_billing_cycle_length($value);

        // Set property
        $this->set_property('billing_cycle_length', $value);
    }

    /**
     * Set billing cycle period
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function set_billing_cycle_period($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_billing_cycle_period($value);

        // Set property
        $this->set_property('billing_cycle_period', $value);
    }

    /**
     * Set free trial length
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int|null $value
     * @return void
     */
    public function set_free_trial_length($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_free_trial_length($value);

        // Set property
        $this->set_property('free_trial_length', $value);
    }

    /**
     * Set free trial period
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string|null $value
     * @return void
     */
    public function set_free_trial_period($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_free_trial_period($value);

        // Set property
        $this->set_property('free_trial_period', $value);
    }

    /**
     * Set lifespan length
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int|null $value
     * @return void
     */
    public function set_lifespan_length($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_lifespan_length($value);

        // Set property
        $this->set_property('lifespan_length', $value);
    }

    /**
     * Set lifespan period
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string|null $value
     * @return void
     */
    public function set_lifespan_period($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_lifespan_period($value);

        // Set property
        $this->set_property('lifespan_period', $value);
    }

    /**
     * Set signup fee
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param float|null $value
     * @return void
     */
    public function set_signup_fee($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_signup_fee($value);

        // Set property
        $this->set_property('signup_fee', $value);
    }

    /**
     * =================================================================================================================
     * SANITIZERS - VALIDATORS
     * =================================================================================================================
     */

    /**
     * Sanitize and validate subscription product flag
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param bool $value
     * @return bool
     */
    public function sanitize_subscription_product($value)
    {

        // Validate value
        if (!in_array($value, array(true, false, 'yes', 'no'), true)) {
            throw new RightPress_Exception('rp_sub_subscription_product_invalid_subscription_product', esc_html__('Invalid subscription product flag.', 'subscriptio'));
        }

        // Cast string value to bool
        if (is_string($value)) {
            $value = ($value === 'yes') ? true : false;
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize and validate billing cycle length
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return int
     */
    public function sanitize_billing_cycle_length($value)
    {

        // Sanitize integer
        $value = $this->sanitize_int($value, 'billing_cycle_length');

        // Validate value
        if ($value < 1) {
            throw new RightPress_Exception('rp_sub_subscription_product_invalid_billing_cycle_length', esc_html__('Invalid subscription billing cycle length.', 'subscriptio'));
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize and validate billing cycle period
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return string
     */
    public function sanitize_billing_cycle_period($value)
    {

        // Sanitize string
        $value = $this->sanitize_string($value, 'billing_cycle_period');

        // Validate value
        if (!RP_SUB_Time::validate_time_period($value)) {
            throw new RightPress_Exception('rp_sub_subscription_product_invalid_billing_cycle_period', esc_html__('Invalid subscription billing cycle length.', 'subscriptio'));
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize and validate free trial length
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return int
     */
    public function sanitize_free_trial_length($value)
    {

        // Sanitize integer
        $value = $this->sanitize_int($value, 'free_trial_length');

        // Validate value
        if ($value !== null && $value < 1) {
            throw new RightPress_Exception('rp_sub_subscription_product_invalid_free_trial_length', esc_html__('Invalid subscription free trial length.', 'subscriptio'));
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize and validate free trial period
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return string
     */
    public function sanitize_free_trial_period($value)
    {

        // Sanitize string
        $value = $this->sanitize_string($value, 'free_trial_period');

        // Validate value
        if ($value !== null && !RP_SUB_Time::validate_time_period($value)) {
            throw new RightPress_Exception('rp_sub_subscription_product_invalid_free_trial_period', esc_html__('Invalid subscription free trial length.', 'subscriptio'));
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize and validate lifespan length
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return int
     */
    public function sanitize_lifespan_length($value)
    {

        // Sanitize integer
        $value = $this->sanitize_int($value, 'lifespan_length');

        // Validate value
        if ($value !== null && $value < 1) {
            throw new RightPress_Exception('rp_sub_subscription_product_invalid_lifespan_length', esc_html__('Invalid subscription lifespan.', 'subscriptio'));
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize and validate lifespan period
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param string $value
     * @return string
     */
    public function sanitize_lifespan_period($value)
    {

        // Sanitize string
        $value = $this->sanitize_string($value, 'lifespan_period');

        // Validate value
        if ($value !== null && !RP_SUB_Time::validate_time_period($value)) {
            throw new RightPress_Exception('rp_sub_subscription_product_invalid_lifespan_period', esc_html__('Invalid subscription lifespan.', 'subscriptio'));
        }

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize and validate signup fee
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param float $value
     * @return float
     */
    public function sanitize_signup_fee($value)
    {

        // Sanitize float
        $value = $this->sanitize_float($value, 'signup_fee');

        // Handle non-null value
        if ($value !== null) {

            // Validate value
            if (!is_numeric($value) || $value <= 0) {
                throw new RightPress_Exception('rp_sub_subscription_product_invalid_signup_fee', esc_html__('Invalid subscription signup fee value.', 'subscriptio'));
            }
        }

        // Return sanitized value
        return $value;
    }

    /**
     * =================================================================================================================
     * OTHER METHODS
     * =================================================================================================================
     */

    /**
     * Check if subscriptions are enabled for this product
     *
     * Alias for get_subscription_product()
     *
     * @access public
     * @return bool
     */
    public function is_subscription_product()
    {

        return $this->get_subscription_product();
    }

    /**
     * Get recurring price
     *
     * Returns null if price is not set for a product
     *
     * @access public
     * @param float $price
     * @return float|null
     */
    public function get_recurring_price($price = null)
    {

        // Get product price
        if ($price === null) {
            $price = $this->get_price();
        }

        // Price is not set
        if (RightPress_Help::is_empty($price)) {
            return false;
        }

        // Maybe set price to regular
        if (!RP_SUB_Settings::is('sale_price_is_recurring')) {

            // Get regular price
            $regular_price = $this->get_regular_price();

            // Check if product is on sale
            if (RightPress_Product_Price::price_is_smaller_than($price, $regular_price)) {

                // Set recurring price to regular price
                $price = $regular_price;
            }
        }

        return $price;
    }

    /**
     * Get formatted price for display
     *
     * Returns null if price is not set for a product
     *
     * @access public
     * @param float $price
     * @return string|null
     */
    public function get_formatted_price($price = null)
    {

        $formatted_price = null;

        // Get recurring price
        $recurring_price = $this->get_recurring_price($price);

        // Check if recurring price is set
        if ($recurring_price !== null) {

            // Tax adjustment
            $recurring_price = RightPress_Product_Price_Display::prepare_product_price_for_display($this->get_wc_product(), $recurring_price, false, true);

            // Format recurring amount
            $formatted_price = RP_SUB_Pricing::format_recurring_amount_for_display($recurring_price, $this->get_billing_cycle_length(), $this->get_billing_cycle_period());
        }

        // Allow developers to override and return
        return apply_filters('subscriptio_subscription_product_formatted_price', $formatted_price, $recurring_price, $this);
    }

    /**
     * Get formatted signup fee
     *
     * Returns null if signup fee is not set for a product
     *
     * @access public
     * @return string|null
     */
    public function get_formatted_signup_fee()
    {

        $formatted_signup_fee = null;

        $signup_fee = $this->get_signup_fee();

        // Check if signup fee is set
        if ($signup_fee) {

            // Tax adjustment
            $signup_fee = RightPress_Product_Price_Display::prepare_product_price_for_display($this->get_wc_product(), $signup_fee, false, true);

            // Format signup fee
            $formatted_signup_fee = wc_price($signup_fee);
        }

        // Allow developers to override and return
        return apply_filters('subscriptio_subscription_product_formatted_signup_fee', $formatted_signup_fee, $signup_fee, $this);
    }

    /**
     * Get formatted free trial
     *
     * Returns null if free trial is not set for a product
     *
     * @access public
     * @return string|null
     */
    public function get_formatted_free_trial()
    {

        $formatted_free_trial = null;

        // Get free trial settings
        $time_length = $this->get_free_trial_length();
        $time_period = $this->get_free_trial_period();

        // Check if free trial is set
        if (!empty($time_length)) {

            // Format free trial
            $formatted_free_trial = RP_SUB_Time::get_formatted_time_period_string($time_length, $time_period);
        }

        // Allow developers to override and return
        return apply_filters('subscriptio_subscription_product_formatted_free_trial', $formatted_free_trial, $time_length, $time_period, $this);
    }

    /**
     * Get formatted lifespan
     *
     * Returns null if lifespan is not set for a product
     *
     * @access public
     * @return string|null
     */
    public function get_formatted_lifespan()
    {

        $formatted_lifespan = null;

        // Get lifespan settings
        $time_length = $this->get_lifespan_length();
        $time_period = $this->get_lifespan_period();

        // Check if lifespan is set
        if (!empty($time_length)) {

            // Format lifespan
            $formatted_lifespan = RP_SUB_Time::get_formatted_time_period_string($time_length, $time_period);
        }

        // Allow developers to override and return
        return apply_filters('subscriptio_subscription_product_formatted_lifespan', $formatted_lifespan, $time_length, $time_period, $this);
    }





}
