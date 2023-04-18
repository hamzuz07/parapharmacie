<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Legacy code support
 *
 * @class RP_SUB_Legacy
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Legacy extends RightPress_Legacy
{

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    // Legacy filters (new_filter => old_filter)
    protected $legacy_filters = array(

        // TODO: Anything here?
    );

    // Legacy actions (new_action => old_action)
    protected $legacy_actions = array(

        // TODO: Anything here?
    );





}

RP_SUB_Legacy::get_instance();
