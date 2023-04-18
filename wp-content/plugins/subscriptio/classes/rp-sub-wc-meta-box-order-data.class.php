<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Just a proxy to access protected $billing_fields and $shipping_fields properties
 *
 * @class RP_SUB_WC_Meta_Box_Order_Data
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_WC_Meta_Box_Order_Data extends WC_Meta_Box_Order_Data
{


    /**
     * Get billing fields
     *
     * @access public
     * @return array
     */
    public static function get_billing_fields()
    {

        return self::$billing_fields;
    }

    /**
     * Get shipping fields
     *
     * @access public
     * @return array
     */
    public static function get_shipping_fields()
    {

        return self::$shipping_fields;
    }





}
