<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer Suspended Subscription Email Template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/customer-suspended-subscription.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @version 3.0
 */

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(esc_html__('Hi %s,', 'subscriptio'), esc_html($subscription->get_billing_first_name())); ?></p>

<p><?php printf(esc_html__('Your subscription with %1$s has been suspended and is set to be cancelled on %2$s in case of no payment. Here are the details of this subscription:', 'subscriptio'), esc_html(get_bloginfo('name', 'display')), $next_action_date); ?></p>

<?php

do_action('subscriptio_email_subscription_details', $subscription, $sent_to_admin, $plain_text, $email);

do_action('subscriptio_email_subscription_meta', $subscription, $sent_to_admin, $plain_text, $email);

do_action('subscriptio_email_customer_details', $subscription, $sent_to_admin, $plain_text, $email);

if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
