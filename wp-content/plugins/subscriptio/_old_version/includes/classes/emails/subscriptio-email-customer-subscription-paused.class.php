<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customer Subscription Paused email
 *
 * @class Subscriptio_Email_Customer_Subscription_Paused
 * @package Subscriptio
 * @author RightPress
 */
if (!class_exists('Subscriptio_Email_Customer_Subscription_Paused')) {

class Subscriptio_Email_Customer_Subscription_Paused extends Subscriptio_Email
{

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->id             = 'customer_subscription_paused';
        $this->customer_email = true;
        $this->title          = esc_html__('Subscription paused', 'subscriptio');
        $this->description    = esc_html__('Subscription paused emails are sent to customers when subscriptions are paused by administrator or customers (if they are allowed to).', 'subscriptio');

        $this->heading        = esc_html__('Your subscription has been paused', 'subscriptio');
        $this->subject        = sprintf(esc_html__('Your %s subscription has been paused', 'subscriptio'), '{site_title}');

        // Call parent constructor
        parent::__construct();
    }

}
}
