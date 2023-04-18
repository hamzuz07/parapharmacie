<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customer Subscription Completed Order email
 *
 * @class Subscriptio_Email_Customer_Subscription_Completed_Order
 * @package Subscriptio
 * @author RightPress
 */
if (!class_exists('Subscriptio_Email_Customer_Subscription_Completed_Order')) {

class Subscriptio_Email_Customer_Subscription_Completed_Order extends Subscriptio_Email
{

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->id             = 'customer_subscription_completed_order';
        $this->customer_email = true;
        $this->title          = esc_html__('Subscription completed order', 'subscriptio');
        $this->description    = esc_html__('Subscription completed order emails are sent to the customer when subscription renewal order is marked complete.', 'subscriptio');

        $this->heading        = esc_html__('Your subscription renewal order is complete', 'subscriptio');
        $this->subject        = sprintf(esc_html__('Your %1$s subscription renewal order from %2$s is complete', 'subscriptio'), '{site_title}', '{order_date}');

        $this->heading_downloadable = $this->get_option('heading_downloadable', esc_html__('Your subscription renewal order is complete - download your files', 'subscriptio'));
        $this->subject_downloadable = $this->get_option('subject_downloadable', sprintf(esc_html__('Your %s subscription renewal order is complete - download your files', 'subscriptio'), '{site_title}'));

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
        $this->find[] = '{order_date}';
        $this->replace[] = RightPress_WC_Legacy::order_get_formatted_date_created($this->object);

        // Check if this email type is enabled, recipient is set and we are not on a development website
        if (!$this->is_enabled() || !$this->get_recipient() || !Subscriptio::is_main_site()) {
            return;
        }

        // Get subscription
        $subscription = Subscriptio_Order_Handler::get_subscriptions_from_order_id(RightPress_WC_Legacy::order_get_id($order));
        $subscription = reset($subscription);

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

    /**
     * Get subject
     *
     * @access public
     * @return string
     */
    public function get_subject()
    {
        if (!empty($this->object) && $this->object->has_downloadable_item()) {
            return apply_filters('subscriptio_email_subject_' . $this->id, $this->format_string($this->subject_downloadable), $this->object);
        }
        else {
            return apply_filters('subscriptio_email_subject_' . $this->id, $this->format_string($this->subject), $this->object);
        }
    }

    /**
     * Get heading
     *
     * @access public
     * @return string
     */
    public function get_heading()
    {
        if (!empty($this->object) && $this->object->has_downloadable_item()) {
            return apply_filters('subscriptio_email_heading_' . $this->id, $this->format_string($this->heading_downloadable), $this->object);
        }
        else {
            return apply_filters('subscriptio_email_heading_' . $this->id, $this->format_string($this->heading), $this->object);
        }
    }

    /**
     * Initialise settings form fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'     => esc_html__('Enable/Disable', 'subscriptio'),
                'type'      => 'checkbox',
                'label'     => esc_html__('Enable this email notification', 'subscriptio'),
                'default'   => 'yes',
            ),
            'send_to_admin' => array(
                'title'     => esc_html__('Send to admin?', 'subscriptio'),
                'type'      => 'checkbox',
                'label'     => esc_html__('Send copy of this email to admin', 'subscriptio'),
                'default'   => 'no',
            ),
            'subject' => array(
                'title'         => esc_html__('Subject', 'subscriptio'),
                'type'          => 'text',
                'description'   => sprintf(esc_html__('Defaults to %s', 'subscriptio'), ('<code>' . $this->subject . '</code>')),
                'placeholder'   => '',
                'default'       => '',
            ),
            'heading' => array(
                'title'         => esc_html__('Email Heading', 'subscriptio'),
                'type'          => 'text',
                'description'   => sprintf(esc_html__('Defaults to %s', 'subscriptio'), ('<code>' . $this->heading . '</code>')),
                'placeholder'   => '',
                'default'       => '',
            ),
            'subject_downloadable' => array(
                'title'         => esc_html__('Subject (downloadable)', 'subscriptio'),
                'type'          => 'text',
                'description'   => sprintf(esc_html__('Defaults to %s', 'subscriptio'), ('<code>' . $this->subject_downloadable . '</code>')),
                'placeholder'   => '',
                'default'       => '',
            ),
            'heading_downloadable' => array(
                'title'         => esc_html__('Email Heading (downloadable)', 'subscriptio'),
                'type'          => 'text',
                'description'   => sprintf(esc_html__('Defaults to %s', 'subscriptio'), ('<code>' . $this->heading_downloadable . '</code>')),
                'placeholder'   => '',
                'default'       => '',
            ),
            'email_type' => array(
                'title'         => esc_html__('Email type', 'subscriptio'),
                'type'          => 'select',
                'description'   => esc_html__('Choose which format of email to send.', 'subscriptio'),
                'default'       => 'html',
                'class'         => 'email_type',
                'options'       => array(
                    'plain'         => esc_html__('Plain text', 'subscriptio'),
                    'html'          => esc_html__('HTML', 'subscriptio'),
                    'multipart'     => esc_html__('Multipart', 'subscriptio'),
                ),
            ),
        );
    }

}
}
