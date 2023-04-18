<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Controller
 *
 * @class RP_SUB_Controller
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Controller
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

    }

    /**
     * Get post type controller classes
     *
     * @access public
     * @return array
     */
    public static function get_post_type_controller_classes()
    {

        // TODO: looks like we are not using this at all?

        $hook = 'rp_sub_custom_post_type_controllers';

        // Too early
        if (!has_filter($hook)) {
            RightPress_Help::doing_it_wrong(__METHOD__, 'Method should not be called before callbacks for action hook _rp_sub_custom_post_type_controllers are set up.', '3.0');
        }

        // Allow other classes to register post type controllers
        return apply_filters($hook, array());
    }





}

RP_SUB_Controller::get_instance();
