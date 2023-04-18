<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rp-sub-renewal-order-email.class.php';

// We are including these files so need to check if class has not been defined yet
if (!class_exists('RP_SUB_Email_Customer_Subscription_Payment_Failed', false)) {

/**
 * Customer Subscription Payment Failed Email
 *
 * @class RP_SUB_Email_Customer_Subscription_Payment_Failed
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Email_Customer_Subscription_Payment_Failed extends RP_SUB_Renewal_Order_Email
{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        $this->id               = 'customer_subscription_payment_failed';
        $this->customer_email   = true;
        $this->title            = esc_html__('Subscription payment failed', 'subscriptio');
        $this->description      = esc_html__('Subscription payment failed emails are sent to customers after each failed automatic subscription payment.', 'subscriptio');

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

        return sprintf(esc_html__('Your %s subscription payment has failed', 'subscriptio'), '{site_title}');
    }

    /**
     * Get email heading
     *
     * @access public
     * @return string
     */
    public function get_default_heading()
    {

        return esc_html__('Your subscription payment has failed', 'subscriptio');
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

    /**
     * Get template variables
     *
     * @access public
     * @return array
     */
    public function get_template_variables()
    {

        // Get subscription
        $subscription = subscriptio_get_subscription_related_to_order($this->object);

        // Get next and last payment retry datetimes
        $next_retry_datetime = RP_SUB_Scheduler::get_next_payment_retry_datetime($subscription);
        $last_retry_datetime = RP_SUB_Scheduler::get_last_payment_retry_datetime($subscription);

        // Merge with default variables and return
        return array_merge(parent::get_template_variables(), array(
            'next_action'       => (RP_SUB_Settings::is('suspension_period') ? 'suspend' : 'cancel'),
            'next_action_date'  => ($last_retry_datetime ? $last_retry_datetime->format_date() : '?'),
            'next_retry_date'   => (($next_retry_datetime && $last_retry_datetime && $next_retry_datetime !== $last_retry_datetime) ? $next_retry_datetime->format_date() : null),
        ));
    }





}
}

return new RP_SUB_Email_Customer_Subscription_Payment_Failed();
