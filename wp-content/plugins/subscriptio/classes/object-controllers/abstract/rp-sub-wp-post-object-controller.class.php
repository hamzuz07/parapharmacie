<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WordPress Post Object Controller
 *
 * @class RP_SUB_WP_Post_Object_Controller
 * @package Subscriptio
 * @author RightPress
 */
abstract class RP_SUB_WP_Post_Object_Controller extends RightPress_WP_Custom_Post_Object_Controller
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

        // Register post type controller class
        add_filter('rp_sub_custom_post_type_controllers', array($this, 'register_post_type_controller_class'));
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

    /**
     * Get main post type
     *
     * @access public
     * @return string
     */
    public function get_main_post_type()
    {

        return 'rp_sub_subscription';
    }





}
