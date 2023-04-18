<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer Processing Renewal Order Email Template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/customer-processing-renewal-order.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @version 3.0
 */

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(esc_html__('Hi %s,', 'subscriptio'), esc_html($order->get_billing_first_name())); ?></p>

<p><?php printf(esc_html__('Just to let you know &mdash; your subscription renewal order #%s is now being processed:', 'subscriptio'), esc_html($order->get_order_number())); ?></p>

<?php

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
