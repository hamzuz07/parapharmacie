<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Email subscription details template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/plain/email-order-details.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @var RP_SUB_Subscription $subscription
 * @version 3.0
 */

do_action('subscriptio_email_before_subscription_table', $subscription, $sent_to_admin, $plain_text, $email);

echo wp_kses_post(wc_strtoupper(sprintf(esc_html__('Subscription #%s', 'subscriptio'), $subscription->get_id()))) . "\n";

echo "\n";

do_action('subscriptio_email_subscription_items', $subscription, $sent_to_admin, $plain_text, $email);

echo "==========\n\n";

$item_totals = $subscription->get_suborder()->get_order_item_totals();

if ($item_totals) {
    foreach ($item_totals as $total) {
        echo wp_kses_post($total['label'] . "\t " . $total['value']) . "\n";
    }
}

if ($subscription->get_customer_note()) {
    echo esc_html__('Note:', 'subscriptio') . "\t " . wp_kses_post(wptexturize($subscription->get_customer_note())) . "\n";
}

do_action('subscriptio_email_after_subscription_table', $subscription, $sent_to_admin, $plain_text, $email);
