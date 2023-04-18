<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer Subscription Payment Failed Email Template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/plain/customer-subscription-payment-failed.php
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

echo sprintf(esc_html__('We tried to process the automatic payment for your subscription with %s. However, the payment did not go through.', 'subscriptio'), esc_html(get_bloginfo('name', 'display'))) . "\n\n";

echo esc_html__('Please contact your financial institution or log in to your account to update your billing preferences.', 'subscriptio') . "\n\n";

if (isset($next_retry_date)) {
    echo ' ' . sprintf(esc_html__('We will try to process the payment again on %s.', 'subscriptio'), $next_retry_date) . "\n\n";
}

echo wp_kses_post(
    sprintf(
        esc_html__('You can follow this link to make your payment now: %s', 'subscriptio'),
        esc_url($order->get_checkout_payment_url())
    )
) . "\n\n";

if ($next_action === 'suspend') {
    echo sprintf(esc_html__('Please resolve this issue by %s to avoid suspension of your subscription.', 'subscriptio'), $next_action_date) . "\n\n";
}
else {
    echo sprintf(esc_html__('Please resolve this issue by %s to avoid cancellation of your subscription.', 'subscriptio'), $next_action_date) . "\n\n";
}

echo esc_html__('Here are the details of the pending subscription renewal order:', 'subscriptio') . "\n\n";

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
