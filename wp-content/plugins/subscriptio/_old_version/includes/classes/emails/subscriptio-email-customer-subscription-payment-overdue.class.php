<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customer Subscription Overdue Payment email
 *
 * @class Subscriptio_Email_Customer_Subscription_Payment_Overdue
 * @package Subscriptio
 * @author RightPress
 */
if (!class_exists('Subscriptio_Email_Customer_Subscription_Payment_Overdue')) {

class Subscriptio_Email_Customer_Subscription_Payment_Overdue extends Subscriptio_Email
{

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->id             = 'customer_subscription_payment_overdue';
        $this->customer_email = true;
        $this->title          = esc_html__('Subscription payment overdue', 'subscriptio');
        $this->description    = esc_html__('Subscription payment overdue emails are sent to customers when they fail to pay by Payment Due date and you allow grace period.', 'subscriptio');

        $this->heading        = esc_html__('Overdue payment', 'subscriptio');
        $this->subject        = sprintf(esc_html__('Payment for renewal order %s is overdue', 'subscriptio'), '{order_number}');

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Trigger a notification
     *
     * @access public
     * @param object $subscription
     * @param array $args
     * @param bool $send_to_admin
     * @return void
     */
    public function trigger($subscription, $args = array(), $send_to_admin = false)
    {
        if (!$subscription || !isset($subscription->last_order_id)) {
            return;
        }

        $order = RightPress_Helper::wc_get_order($subscription->last_order_id);

        if (!$order) {
            return;
        }

        $this->object = $order;

        if ($send_to_admin) {
            $this->recipient = get_option('admin_email');
        }
        else {
            $this->recipient = RightPress_WC_Legacy::order_get_billing_email($this->object);
        }

        // Replace macros
        $this->find[] = '{order_number}';
        $this->replace[] = $this->object->get_order_number();

        // Check if this email type is enabled, recipient is set and we are not on a development website
        if (!$this->is_enabled() || !$this->get_recipient() || !Subscriptio::is_main_site()) {
            return;
        }

        // Get next action and next action date
        if ($subscription->calculate_suspension_time() > 0) {
            $next_action = esc_html__('suspended', 'subscriptio');
            $next_action_datetime = Subscriptio_Event_Scheduler::get_scheduled_event_datetime('suspension', $subscription->id);
        }
        else {
            $next_action = esc_html__('cancelled', 'subscriptio');
            $next_action_datetime = Subscriptio_Event_Scheduler::get_scheduled_event_datetime('cancellation', $subscription->id);
        }

        $this->template_variables = array(
            'subscription'          => $subscription,
            'order'                 => $this->object,
            'email_heading'         => $this->get_heading(),
            'sent_to_admin'         => false,
            'next_action'           => $next_action,
            'next_action_datetime'  => $next_action_datetime,
        );

        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

}
}
