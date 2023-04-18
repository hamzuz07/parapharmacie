<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WordPress Custom Post Type Based Log Entry Admin
 *
 * @class RP_SUB_WP_Log_Entry_Admin
 * @package Subscriptio
 * @author RightPress
 */
abstract class RP_SUB_WP_Log_Entry_Admin extends RightPress_WP_Log_Entry_Admin
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
     * TODO: Would be nice to remove it from here but need to solve the menu hooks in rightpress lib first
     *
     * @access public
     * @return string
     */
    public function get_menu_priority()
    {

        return 1900;
    }





}
