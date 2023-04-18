<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rp-sub-subscription-email.class.php';

// We are including these files so need to check if class has not been defined yet
if (!class_exists('RP_SUB_Email_Customer_Cancelled_Subscription', false)) {

/**
 * Customer Cancelled Subscription Email
 *
 * @class RP_SUB_Email_Customer_Cancelled_Subscription
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Email_Customer_Cancelled_Subscription extends RP_SUB_Subscription_Email
{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        $this->id               = 'customer_cancelled_subscription';
        $this->customer_email   = true;
        $this->title            = esc_html__('Subscription cancelled', 'subscriptio');
        $this->description      = esc_html__('Subscription cancelled emails are sent to customers when subscriptions are cancelled either automatically due to non-payment or manually by customers or shop managers.', 'subscriptio');

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

        return sprintf(esc_html__('Your %s subscription has been cancelled', 'subscriptio'), '{site_title}');
    }

    /**
     * Get email heading
     *
     * @access public
     * @return string
     */
    public function get_default_heading()
    {

        return esc_html__('Your subscription has been cancelled', 'subscriptio');
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

return new RP_SUB_Email_Customer_Cancelled_Subscription();
