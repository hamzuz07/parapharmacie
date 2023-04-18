<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rp-sub-renewal-order-email.class.php';

// We are including these files so need to check if class has not been defined yet
if (!class_exists('RP_SUB_Email_Customer_Subscription_Payment_Reminder', false)) {

/**
 * Customer Subscription Payment Reminder Email
 *
 * @class RP_SUB_Email_Customer_Subscription_Payment_Reminder
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Email_Customer_Subscription_Payment_Reminder extends RP_SUB_Renewal_Order_Email
{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        $this->id               = 'customer_subscription_payment_reminder';
        $this->customer_email   = true;
        $this->title            = esc_html__('Subscription payment reminder', 'subscriptio');
        $this->description      = esc_html__('Subscription payment reminder emails are sent to customers according to a predefined schedule when manual subscription payment is due.', 'subscriptio');

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

        return sprintf(esc_html__('Your %s subscription payment is due', 'subscriptio'), '{site_title}');
    }

    /**
     * Get email heading
     *
     * @access public
     * @return string
     */
    public function get_default_heading()
    {

        return esc_html__('Your subscription payment is due', 'subscriptio');
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

        // Check if overdue period and suspensions are enabled
        $overdue_enabled        = (bool) RP_SUB_Scheduler::calculate_overdue_period_length($subscription);
        $suspensions_enabled    = RP_SUB_Settings::is('suspension_period');

        // Subscription is in trial or is active
        if ($subscription->has_status(array('trial', 'active'))) {
            $next_action = $overdue_enabled ? null : ($suspensions_enabled ? 'suspend' : 'cancel');
        }
        // Subscription is overdue
        else if ($subscription->has_status('overdue')) {
            $next_action = $suspensions_enabled ? 'suspend' : 'cancel';
        }
        // Subscription is suspended
        else {
            $next_action = 'cancel';
        }

        // Get next action date
        if ($next_action === 'suspend') {

            if ($expected_scheduled_subscription_suspend_datetime = RP_SUB_Scheduler::get_expected_scheduled_subscription_suspend_datetime_for_display($subscription)) {
                $next_action_date = $expected_scheduled_subscription_suspend_datetime->format_date();
            }
            else if ($expected_scheduled_renewal_payment_datetime = RP_SUB_Scheduler::get_expected_scheduled_renewal_payment_datetime_for_display($subscription)) {
                $next_action_date = $expected_scheduled_renewal_payment_datetime->format_date();
            }
            else {
                $next_action_date = '?';
            }
        }
        else if ($next_action === 'cancel') {

            if ($expected_scheduled_subscription_cancel_datetime = RP_SUB_Scheduler::get_expected_scheduled_subscription_cancel_datetime_for_display($subscription)) {
                $next_action_date = $expected_scheduled_subscription_cancel_datetime->format_date();
            }
            else if ($expected_scheduled_renewal_payment_datetime = RP_SUB_Scheduler::get_expected_scheduled_renewal_payment_datetime_for_display($subscription)) {
                $next_action_date = $expected_scheduled_renewal_payment_datetime->format_date();
            }
            else {
                $next_action_date = '?';
            }
        }
        else {
            $next_action_date = null;
        }

        // Merge with default variables and return
        return array_merge(parent::get_template_variables(), array(
            'next_action'       => $next_action,
            'next_action_date'  => $next_action_date,
        ));
    }





}
}

return new RP_SUB_Email_Customer_Subscription_Payment_Reminder();
