<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wc-custom-order-data-store.class.php';

/**
 * Suborder Data Store
 *
 * Note: We use two types of objects for subscriptions:
 *  - RP_SUB_Suborder which is a custom WooCommerce order type and holds most of the data in a format
 *    that is easy to copy data from/to regular WooCommerce orders and lets us reuse WooCommerce order interface
 *  - RP_SUB_Subscription which is a wrapper to add our own functionality so that we can use method/property names
 *    without prefixes and don't fear that they will clash with those in WC_Order in the future
 *
 * @class RP_SUB_Suborder_Data_Store
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Suborder_Data_Store extends RP_SUB_WC_Custom_Order_Data_Store
{

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
     * Get capability type
     *
     * @access public
     * @return string
     */
    public function get_capability_type()
    {

        return array('rp_sub_subscription', 'rp_sub_subscriptions');
    }





}
