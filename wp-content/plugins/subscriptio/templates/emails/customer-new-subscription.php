<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer New Subscription Email Template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/customer-new-subscription.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @version 3.0
 */

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(esc_html__('Hi %s,', 'subscriptio'), esc_html($subscription->get_billing_first_name())); ?></p>

<p><?php printf(esc_html__('A new subscription has been created on %s. Here are the details of this subscription:', 'subscriptio'), esc_html(get_bloginfo('name', 'display'))); ?></p>

<?php

do_action('subscriptio_email_subscription_details', $subscription, $sent_to_admin, $plain_text, $email);

do_action('subscriptio_email_subscription_meta', $subscription, $sent_to_admin, $plain_text, $email);

do_action('subscriptio_email_customer_details', $subscription, $sent_to_admin, $plain_text, $email);

if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
