<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customer Subscription Resumed email
 *
 * @class Subscriptio_Email_Customer_Subscription_Resumed
 * @package Subscriptio
 * @author RightPress
 */
if (!class_exists('Subscriptio_Email_Customer_Subscription_Resumed')) {

class Subscriptio_Email_Customer_Subscription_Resumed extends Subscriptio_Email
{

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->id             = 'customer_subscription_resumed';
        $this->customer_email = true;
        $this->title          = esc_html__('Subscription resumed', 'subscriptio');
        $this->description    = esc_html__('Subscription resumed emails are sent to customers when subscriptions are resumed (unpaused) by administrator or customers (if they are allowed to).', 'subscriptio');

        $this->heading        = esc_html__('Your subscription has been resumed', 'subscriptio');
        $this->subject        = sprintf(esc_html__('Your %s subscription has been resumed', 'subscriptio'), '{site_title}');

        // Call parent constructor
        parent::__construct();
    }

}
}
