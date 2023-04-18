<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WordPress Post Object Admin
 *
 * @class RP_SUB_WP_Post_Object_Admin
 * @package Subscriptio
 * @author RightPress
 */
abstract class RP_SUB_WP_Post_Object_Admin extends RightPress_WP_Custom_Post_Object_Admin
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
     * Get menu priority
     *
     * @access public
     * @return string
     */
    public function get_menu_priority()
    {

        return 1900;
    }





}
