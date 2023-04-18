<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Main site controller
 *
 * @class RP_SUB_Main_Site_Controller
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Main_Site_Controller extends RightPress_Main_Site_Controller
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
     * Get plugin private prefix
     *
     * @access public
     * @return string
     */
    protected function get_plugin_private_prefix()
    {

        return RP_SUB_PLUGIN_PRIVATE_PREFIX;
    }

    /**
     * Get plugin public prefix
     *
     * @access public
     * @return string
     */
    protected function get_plugin_public_prefix()
    {

        return RP_SUB_PLUGIN_PUBLIC_PREFIX;
    }

    /**
     * Print main site URL mismatch notification
     *
     * @access protected
     * @return void
     */
    protected function print_url_mismatch_notification()
    {

        include RP_SUB_PLUGIN_PATH . 'views/general/url-mismatch-notification.php';
    }





}

RP_SUB_Main_Site_Controller::get_instance();
