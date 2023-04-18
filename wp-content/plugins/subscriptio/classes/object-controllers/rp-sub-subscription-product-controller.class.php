<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wc-product-object-controller.class.php';

/**
 * Subscription Product Controller
 *
 * @class RP_SUB_Subscription_Product_Controller
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Subscription_Product_Controller extends RP_SUB_WC_Product_Object_Controller
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
     * Get object name
     *
     * @access public
     * @return string
     */
    public function get_object_name()
    {

        return 'subscription_product';
    }

    /**
     * Get object class
     *
     * @access public
     * @return string
     */
    public function get_object_class()
    {

        return 'RP_SUB_Subscription_Product';
    }

    /**
     * Get data store class
     *
     * @access public
     * @return string
     */
    public function get_data_store_class()
    {

        return 'RP_SUB_Subscription_Product_Data_Store';
    }

    /**
     * Check if subscriptions are enabled for product without loading object
     *
     * @access public
     * @param object|int $product
     * @return string
     */
    public function is_enabled_for_product($product)
    {

        return subscriptio_is_subscription_product($product);
    }





}

RP_SUB_Subscription_Product_Controller::get_instance();
