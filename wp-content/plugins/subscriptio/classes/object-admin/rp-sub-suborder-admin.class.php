<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wc-custom-order-admin.class.php';

/**
 * Suborder Admin
 *
 * Note: We use two types of objects for subscriptions:
 *  - RP_SUB_Suborder which is a custom WooCommerce order type and holds most of the data in a format
 *    that is easy to copy data from/to regular WooCommerce orders and lets us reuse WooCommerce order interface
 *  - RP_SUB_Subscription which is a wrapper to add our own functionality so that we can use method/property names
 *    without prefixes and don't fear that they will clash with those in WC_Order in the future
 *
 * @class RP_SUB_Suborder_Admin
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Suborder_Admin extends RP_SUB_WC_Custom_Order_Admin
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

        // Remove default order data meta box save callback
        add_action('woocommerce_process_shop_order_meta', array($this, 'remove_order_data_meta_box_save_callback'), 0, 2);

        // Change "no longer editable" text
        add_action('woocommerce_admin_order_totals_after_refunded', array($this, 'hook_no_longer_editable_change_callback'));
        add_action('woocommerce_order_item_add_action_buttons', array($this, 'unhook_no_longer_editable_change_callback'));
    }

    /**
     * Remove default order data meta box save callback
     *
     * @access public
     * @param int $post_id
     * @param object $post
     * @return void
     */
    public function remove_order_data_meta_box_save_callback($post_id, $post)
    {

        if ($post->post_type === 'rp_sub_subscription') {
            remove_action('woocommerce_process_shop_order_meta', 'WC_Meta_Box_Order_Data::save', 40);
        }
    }

    /**
     * Hook "no longer editable" text change callback
     *
     * @access public
     * @return void
     */
    public function hook_no_longer_editable_change_callback()
    {

        global $typenow;

        if ($typenow === 'rp_sub_subscription') {
            add_filter('gettext', array($this, 'change_no_longer_editable_text'), 10, 3);
        }
    }

    /**
     * Unhook "no longer editable" text change callback
     *
     * @access public
     * @return void
     */
    public function unhook_no_longer_editable_change_callback()
    {

        global $typenow;

        if ($typenow === 'rp_sub_subscription') {
            remove_filter('gettext', array($this, 'change_no_longer_editable_text'), 10);
        }
    }

    /**
     * Change "no longer editable" text
     *
     * @access public
     * @param string $translation
     * @param string $text
     * @param string $domain
     * @return string
     */
    public static function change_no_longer_editable_text($translation, $text, $domain)
    {

        if ($text === 'This order is no longer editable.') {
            $translation = esc_html__('This subscription is no longer editable.', 'subscriptio');
        }
        else if ($text === 'To edit this order change the status back to "Pending"') {
            $translation = esc_html__('Cancelled and expired subscriptions are not editable', 'subscriptio');
        }

        return $translation;
    }





}

RP_SUB_Suborder_Admin::get_instance();
