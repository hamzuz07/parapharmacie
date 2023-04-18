<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Email subscription details template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/plain/email-order-items.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @var RP_SUB_Subscription $subscription
 * @version 3.0
 */

foreach ($items as $item_id => $item) {

    if (apply_filters('subscriptio_subscription_item_visible', true, $item)) {

        $product        = $item->get_product();
        $sku            = '';
        $purchase_note  = '';

        if (is_object($product)) {
            $sku            = $product->get_sku();
            $purchase_note  = $product->get_purchase_note();
        }

        echo apply_filters('subscriptio_subscription_item_name', $item->get_name(), $item, false);

        if ($show_sku && $sku) {
            echo ' (#' . $sku . ')';
        }

        echo ' X ' . apply_filters('subscriptio_email_subscription_item_quantity', $item->get_quantity(), $item);
        echo ' = ' . $subscription->get_formatted_line_subtotal($item) . "\n";

        // Allow other plugins to add additional product information here
        do_action('subscriptio_subscription_item_meta_start', $item_id, $item, $subscription, $plain_text);

        echo strip_tags( wc_display_item_meta($item, array(
            'before'    => "\n- ",
            'separator' => "\n- ",
            'after'     => "",
            'echo'      => false,
            'autop'     => false,
        )));

        // Allow other plugins to add additional product information here
        do_action('subscriptio_subscription_item_meta_end', $item_id, $item, $subscription, $plain_text);
    }

    // Note
    if ($show_purchase_note && $purchase_note) {
        echo "\n" . do_shortcode(wp_kses_post($purchase_note));
    }

    echo "\n\n";

}
