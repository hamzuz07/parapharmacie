<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription Checkup
 *
 * This class is dedicated to periodically check subscriptions against predefined list of potential issues,
 * recover automatically whenever recovery is possible and warn admin in case of critical and/or frequent issues
 *
 * @class RP_SUB_Subscription_Checkup
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Subscription_Checkup
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





}

RP_SUB_Subscription_Checkup::get_instance();
