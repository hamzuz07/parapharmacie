<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customer Subscription New Order email
 *
 * @class Subscriptio_Email_Customer_Subscription_New_Order
 * @package Subscriptio
 * @author RightPress
 */
if (!class_exists('Subscriptio_Email_Customer_Subscription_New_Order')) {

class Subscriptio_Email_Customer_Subscription_New_Order extends Subscriptio_Email
{

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->id             = 'customer_subscription_new_order';
        $this->customer_email = true;
        $this->title          = esc_html__('Subscription new order', 'subscriptio');
        $this->description    = esc_html__('Subscription new order emails are sent to the customer when a new renewal order is generated.', 'subscriptio');

        $this->heading        = sprintf(esc_html__('New subscription renewal order %s', 'subscriptio'), '{order_number}');
        $this->subject        = sprintf(esc_html__('New subscription renewal order %s', 'subscriptio'), '{order_number}');

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Trigger a notification
     *
     * @access public
     * @param object $order
     * @param array $args
     * @param bool $send_to_admin
     * @return void
     */
    public function trigger($order, $args = array(), $send_to_admin = false)
    {
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

        // Get subscription
        $subscriptions = Subscriptio_Order_Handler::get_subscriptions_from_order_id(RightPress_WC_Legacy::order_get_id($order));
        $subscription = reset($subscriptions);

        if (!$subscription) {
            return;
        }

        $this->template_variables = array(
            'subscription'  => $subscription,
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
        );

        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

}
}