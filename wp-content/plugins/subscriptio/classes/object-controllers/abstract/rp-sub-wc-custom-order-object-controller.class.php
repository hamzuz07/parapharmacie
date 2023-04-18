<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WooCommerce Custom Order Object Controller
 *
 * @class RP_SUB_WC_Custom_Order_Object_Controller
 * @package Subscriptio
 * @author RightPress
 */
abstract class RP_SUB_WC_Custom_Order_Object_Controller extends RightPress_WC_Custom_Order_Object_Controller
{

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
     * Get plugin public prefix
     *
     * @access public
     * @return string
     */
    public function get_plugin_public_prefix()
    {

        return RP_SUB_PLUGIN_PUBLIC_PREFIX;
    }

    /**
     * Get plugin private prefix
     *
     * @access public
     * @return string
     */
    public function get_plugin_private_prefix()
    {

        return RP_SUB_PLUGIN_PRIVATE_PREFIX;
    }





}
