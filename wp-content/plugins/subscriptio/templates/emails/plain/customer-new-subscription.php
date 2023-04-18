<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer New Subscription Email Template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/plain/customer-new-subscription.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @version 3.0
 */

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html(wp_strip_all_tags($email_heading));
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo sprintf(esc_html__('Hi %s,', 'subscriptio'), esc_html($subscription->get_billing_first_name())) . "\n\n";

echo sprintf(esc_html__('A new subscription has been created on %s. Here are the details of this subscription:', 'subscriptio'), esc_html(get_bloginfo('name', 'display'))) . "\n\n";

do_action('subscriptio_email_subscription_details', $subscription, $sent_to_admin, $plain_text, $email);

echo "\n----------------------------------------\n\n";

do_action('subscriptio_email_subscription_meta', $subscription, $sent_to_admin, $plain_text, $email);

do_action('subscriptio_email_customer_details', $subscription, $sent_to_admin, $plain_text, $email);

echo "\n\n----------------------------------------\n\n";

if ($additional_content) {
    echo esc_html(wp_strip_all_tags(wptexturize($additional_content)));
    echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post(apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')));
