<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer Paused Subscription Email Template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/customer-paused-subscription.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @var RP_SUB_Subscription $subscription
 * @version 3.0
 */

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(esc_html__('Hi %s,', 'subscriptio'), esc_html($subscription->get_billing_first_name())); ?></p>

<p>
    <?php if ($scheduled_resumption = RP_SUB_Scheduler::get_expected_scheduled_subscription_resume_datetime_for_display($subscription)): ?>
        <?php printf(esc_html__('Your subscription with %1$s has been paused until %2$s. Here are the details of this subscription:', 'subscriptio'), esc_html(get_bloginfo('name', 'display')), $scheduled_resumption->format_date()); ?>
    <?php else: ?>
        <?php printf(esc_html__('Your subscription with %s has been paused. Here are the details of this subscription:', 'subscriptio'), esc_html(get_bloginfo('name', 'display'))); ?>
    <?php endif; ?>
</p>

<?php

do_action('subscriptio_email_subscription_details', $subscription, $sent_to_admin, $plain_text, $email);

do_action('subscriptio_email_subscription_meta', $subscription, $sent_to_admin, $plain_text, $email);

do_action('subscriptio_email_customer_details', $subscription, $sent_to_admin, $plain_text, $email);

if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
