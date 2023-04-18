<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription items
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/subscription/subscription-items.php
 *
 * Formatting and styles based on WooCommerce 3.7 order details templates for uniform appearance
 *
 * @package Subscriptio
 * @version 3.0
 */

?>

<section class="woocommerce-order-details subscriptio-account-subscription-items">

    <?php do_action('subscriptio_account_before_subscription_items', $subscription); ?>

    <h2 class="woocommerce-order-details__title"><?php esc_html_e('Subscription products', 'subscriptio'); ?></h2>

    <table class="woocommerce-table woocommerce-table--order-details shop_table order_details subscriptio-account-subscription-items-table">

        <thead>
            <tr>
                <th class="woocommerce-table__product-name product-name"><?php esc_html_e('Product', 'subscriptio'); ?></th>
                <th class="woocommerce-table__product-table product-total"><?php esc_html_e('Total', 'subscriptio'); ?></th>
            </tr>
        </thead>

        <tbody>

            <?php do_action('subscriptio_account_before_subscription_item_rows', $subscription); ?>

            <?php foreach ($items as $item_id => $item): ?>
                <?php do_action('subscriptio_account_subscription_item', $subscription, $item); ?>
            <?php endforeach; ?>

            <?php do_action('subscriptio_account_after_subscription_item_rows', $subscription); ?>

        </tbody>

        <tfoot>
            <?php foreach ($subscription->get_subscription_item_totals() as $key => $total): ?>
                <tr>
                    <th scope="row"><?php echo esc_html($total['label']); ?></th>
                    <td><?php echo ($key === 'payment_method') ? esc_html($total['value']) : wp_kses_post($total['value']); ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if ($subscription->get_customer_note()): ?>
                <tr>
                    <th><?php esc_html_e('Note:', 'subscriptio'); ?></th>
                    <td><?php echo wp_kses_post(nl2br(wptexturize($subscription->get_customer_note()))); ?></td>
                </tr>
            <?php endif; ?>
        </tfoot>

    </table>

    <?php do_action('subscriptio_account_after_subscription_items', $subscription); ?>

</section>
