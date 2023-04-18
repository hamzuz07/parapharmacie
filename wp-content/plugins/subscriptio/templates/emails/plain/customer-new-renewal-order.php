<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer New Renewal Order Email Template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/plain/customer-new-renewal-order.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @version 3.0
 */

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html(wp_strip_all_tags($email_heading));
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo sprintf(esc_html__('Hi %s,', 'subscriptio'), esc_html($order->get_billing_first_name())) . "\n\n";

if ($order->has_status('pending')) {

    echo wp_kses_post(
        sprintf(
            esc_html__('A new subscription renewal order has been created for you on %1$s. Please follow this link to make a payment when you’re ready: %2$s', 'subscriptio'),
            esc_html(get_bloginfo('name', 'display')),
            esc_url($order->get_checkout_payment_url())
        )
    ) . "\n\n";
}
else {

    echo esc_html__('Here are the details of your subscription renewal order:', 'subscriptio') . "\n\n";
}

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

echo "\n----------------------------------------\n\n";

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

echo "\n\n----------------------------------------\n\n";

if ($additional_content) {
    echo esc_html(wp_strip_all_tags(wptexturize($additional_content)));
    echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post(apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')));
