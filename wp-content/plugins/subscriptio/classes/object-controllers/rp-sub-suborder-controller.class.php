<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wc-custom-order-controller.class.php';

/**
 * Suborder Controller
 *
 * Note: We use two types of objects for subscriptions:
 *  - RP_SUB_Suborder which is a custom WooCommerce order type and holds most of the data in a format
 *    that is easy to copy data from/to regular WooCommerce orders and lets us reuse WooCommerce order interface
 *  - RP_SUB_Subscription which is a wrapper to add our own functionality so that we can use method/property names
 *    without prefixes and don't fear that they will clash with those in WC_Order in the future
 *
 * @class RP_SUB_Suborder_Controller
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Suborder_Controller extends RP_SUB_WC_Custom_Order_Controller
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
     * Get post type
     *
     * @access public
     * @return string
     */
    public function get_post_type()
    {

        return 'rp_sub_subscription';
    }

    /**
     * Get menu position
     *
     * @access public
     * @return int
     */
    public function get_menu_position()
    {

        return 56;
    }

    /**
     * Get menu icon
     *
     * @access public
     * @return string
     */
    public function get_menu_icon()
    {

        return 'dashicons-update';
    }

    /**
     * Get object class
     *
     * @access public
     * @return string
     */
    public function get_object_class()
    {

        return 'RP_SUB_Suborder';
    }

    /**
     * Get data store class
     *
     * @access public
     * @return string
     */
    public function get_data_store_class()
    {

        return 'RP_SUB_Suborder_Data_Store';
    }

    /**
     * Get post labels
     *
     * @access public
     * @return array
     */
    public function get_post_labels()
    {

        return array(
            'name'                  => esc_html__('Subscriptions', 'subscriptio'),
            'singular_name'         => esc_html__('Subscription', 'subscriptio'),
            'add_new'               => esc_html__('Add subscription', 'subscriptio'),
            'add_new_item'          => esc_html__('Add new subscription', 'subscriptio'),
            'edit'                  => esc_html__('Edit', 'subscriptio'),
            'edit_item'             => esc_html__('Edit subscription', 'subscriptio'),
            'new_item'              => esc_html__('New subscription', 'subscriptio'),
            'view_item'             => esc_html__('View subscription', 'subscriptio'),
            'search_items'          => esc_html__('Search subscriptions', 'subscriptio'),
            'not_found'             => esc_html__('No subscriptions found', 'subscriptio'),
            'not_found_in_trash'    => esc_html__('No subscriptions found in trash', 'subscriptio'),
            'parent'                => esc_html__('Parent subscriptions', 'subscriptio'),
            'menu_name'             => esc_html__('Subscriptions', 'subscriptio'),
            'filter_items_list'     => esc_html__('Filter subscriptions', 'subscriptio'),
            'items_list_navigation' => esc_html__('Subscriptions navigation', 'subscriptio'),
            'items_list'            => esc_html__('Subscriptions list', 'subscriptio'),
        );
    }

    /**
     * Get custom order statuses
     *
     * @access public
     * @return array
     */
    public function get_custom_order_statuses()
    {

        return RP_SUB_Subscription_Controller::get_subscription_statuses();
    }





}

RP_SUB_Suborder_Controller::get_instance();
