<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer Subscription Note Email Template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/customer-subscription-note.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @version 3.0
 */

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(esc_html__('Hi %s,', 'subscriptio'), esc_html($subscription->get_billing_first_name())); ?></p>

<p><?php esc_html_e('The following note has been added to your subscription:', 'subscriptio'); ?></p>

<blockquote><?php echo wpautop(wptexturize(make_clickable($customer_note))); ?></blockquote>

<p><?php esc_html_e('As a reminder, here are your subscription details:', 'subscriptio'); ?></p>

<?php

do_action('subscriptio_email_subscription_details', $subscription, $sent_to_admin, $plain_text, $email);

do_action('subscriptio_email_subscription_meta', $subscription, $sent_to_admin, $plain_text, $email);

do_action('subscriptio_email_customer_details', $subscription, $sent_to_admin, $plain_text, $email);

if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
