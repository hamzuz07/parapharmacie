<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rp-sub-email.class.php';

/**
 * Renewal Order Email
 *
 * @class RP_SUB_Renewal_Order_Email
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Renewal_Order_Email extends RP_SUB_Email
{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Define placeholders
        $this->placeholders = array(
            '{order_date}'          => '',
            '{order_number}'        => '',
            '{subscription_number}' => '',
        );

        // Add site title placeholder
        if (!RightPress_Help::wc_version_gte('3.7')) {
            $this->placeholders['{site_title}'] = $this->get_blogname();
        }

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Trigger renewal order email
     *
     * @access public
     * @param object $renewal_order
     * @return void
     */
    public function trigger($renewal_order)
    {

        // Order is not subscription renewal order
        // Note: We use WooCommerce action hooks in some cases therefore we need to differentiate
        // between renewal orders and regular orders here
        if (!subscriptio_is_subscription_renewal_order($renewal_order)) {
            return;
        }

        // Get order
        $renewal_order = wc_get_order($renewal_order);

        // Invalid renewal order
        if (!is_a($renewal_order, 'WC_Order')) {
            return;
        }

        // Get related subscription
        $subscription = subscriptio_get_subscription_related_to_order($renewal_order);

        // Invalid subscription
        if (!is_a($subscription, 'RP_SUB_Subscription')) {
            return;
        }

        // Set object
        $this->object = $renewal_order;

        // Set recipient
        $this->recipient = $renewal_order->get_billing_email();

        // Set placeholder values
        $this->placeholders['{order_date}']             = wc_format_datetime($renewal_order->get_date_created());
        $this->placeholders['{order_number}']           = esc_html_x('#', 'hash before subscription number', 'subscriptio') . $renewal_order->get_order_number();
        $this->placeholders['{subscription_number}']    = $subscription->get_subscription_number();

        // Set template variables
        $this->template_variables = array_merge($this->get_template_variables(), array(
            'order'         => $renewal_order,
            'subscription'  => $subscription,
        ));

        // Call parent method
        parent::trigger($renewal_order);
    }





}
