<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rp-sub-subscription-email.class.php';

// We are including these files so need to check if class has not been defined yet
if (!class_exists('RP_SUB_Email_Customer_Paused_Subscription', false)) {

/**
 * Customer Paused Subscription Email
 *
 * @class RP_SUB_Email_Customer_Paused_Subscription
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Email_Customer_Paused_Subscription extends RP_SUB_Subscription_Email
{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        $this->id               = 'customer_paused_subscription';
        $this->customer_email   = true;
        $this->title            = esc_html__('Subscription paused', 'subscriptio');
        $this->description      = esc_html__('Subscription paused emails are sent to customers when subscriptions are paused by customers or shop managers.', 'subscriptio');

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

        return sprintf(esc_html__('Your %s subscription has been paused', 'subscriptio'), '{site_title}');
    }

    /**
     * Get email heading
     *
     * @access public
     * @return string
     */
    public function get_default_heading()
    {

        return esc_html__('Your subscription has been paused', 'subscriptio');
    }

    /**
     * Default content to show below main email content
     *
     * @access public
     * @return string
     */
    public function get_default_additional_content()
    {

        return esc_html__('Thank you for choosing us.', 'subscriptio');
    }





}
}

return new RP_SUB_Email_Customer_Paused_Subscription();
