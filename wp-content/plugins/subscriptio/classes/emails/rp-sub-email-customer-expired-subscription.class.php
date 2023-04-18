<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rp-sub-subscription-email.class.php';

// We are including these files so need to check if class has not been defined yet
if (!class_exists('RP_SUB_Email_Customer_Expired_Subscription', false)) {

/**
 * Customer Expired Subscription Email
 *
 * @class RP_SUB_Email_Customer_Expired_Subscription
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Email_Customer_Expired_Subscription extends RP_SUB_Subscription_Email
{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        $this->id               = 'customer_expired_subscription';
        $this->customer_email   = true;
        $this->title            = esc_html__('Subscription expired', 'subscriptio');
        $this->description      = esc_html__('Subscription expired emails are sent to customers when subscriptions expire at the end of the predefined lifespan.', 'subscriptio');

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Get email subject
     *
     * @access public
     * @return string
     */
    public function get_default_subject()
    {

        return sprintf(esc_html__('Your %s subscription has expired', 'subscriptio'), '{site_title}');
    }

    /**
     * Get email heading
     *
     * @access public
     * @return string
     */
    public function get_default_heading()
    {

        return esc_html__('Your subscription has expired', 'subscriptio');
    }

    /**
     * Default content to show below main email content
     *
     * @access public
     * @return string
     */
    public function get_default_additional_content()
    {

        return esc_html__('Hope to see you back soon.', 'subscriptio');
    }





}
}

return new RP_SUB_Email_Customer_Expired_Subscription();
