<?php

/**
 * Customer Subscription Payment Overdue email template (plain text)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

echo $email_heading . "\n\n";

echo sprintf(esc_html__('Your recent subscription renewal order on %s is late for payment.', 'subscriptio'), get_option('blogname')) . "\n\n";

echo sprintf(esc_html__('If you do not pay it by %s, your %s will be %s.', 'subscriptio'), ('<strong>' . $next_action_datetime . '</strong>'), _n('subscription', 'subscriptions', count($order->get_items()), 'subscriptio'), ('<strong>' . $next_action . '</strong>')) . "\n\n";

echo sprintf(esc_html__('To pay for this order please use the following link: %s', 'subscriptio'), esc_url($order->get_checkout_payment_url())) . "\n\n";

echo esc_html__('Your order details are shown below for your reference:', 'subscriptio') . "\n\n";

echo "****************************************************\n\n";

do_action('woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text);

echo esc_html__('Order:', 'subscriptio') . ' ' . $order->get_order_number() . "\n";

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text);

Subscriptio::include_template('emails/plain/email-order-items', array('order' => $order, 'plain_text' => true));

echo "\n****************************************************\n\n";

do_action('woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text);

echo esc_html__('Customer details', 'subscriptio') . "\n";

Subscriptio::include_template('emails/plain/email-customer-details', array('order' => $order, 'plain_text' => true));

echo "\n****************************************************\n\n";

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'));
