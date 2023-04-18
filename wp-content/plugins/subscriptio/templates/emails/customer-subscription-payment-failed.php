<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer Subscription Payment Failed Email Template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/customer-subscription-payment-failed.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @version 3.0
 */

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(esc_html__('Hi %s,', 'subscriptio'), esc_html($order->get_billing_first_name())); ?></p>

<p><?php printf(esc_html__('We tried to process the automatic payment for your subscription with %s. However, the payment did not go through.', 'subscriptio'), esc_html(get_bloginfo('name', 'display'))); ?></p>

<p>
    <?php esc_html_e('Please contact your financial institution or log in to your account to update your billing preferences.', 'subscriptio'); ?>

    <?php if (isset($next_retry_date)): ?>
        <?php printf(esc_html__('We will try to process the payment again on %s.', 'subscriptio'), $next_retry_date); ?>
    <?php endif; ?>
</p>

<p>
<?php printf(
    wp_kses(esc_html__('You can follow this link to make your payment now: %s', 'subscriptio'), array('a' => array('href' => array()))),
    '<a href="' . esc_url($order->get_checkout_payment_url()) . '">' . esc_html__('Pay for this renewal order', 'subscriptio') . '</a>'
); ?>
</p>

<p>
    <?php if ($next_action === 'suspend'): ?>
        <?php printf(esc_html__('Please resolve this issue by %s to avoid suspension of your subscription.', 'subscriptio'), $next_action_date); ?>
    <?php else: ?>
        <?php printf(esc_html__('Please resolve this issue by %s to avoid cancellation of your subscription.', 'subscriptio'), $next_action_date); ?>
    <?php endif; ?>
</p>

<p><?php printf(esc_html__('Here are the details of the pending subscription renewal order:', 'subscriptio')); ?></p>

<?php

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
