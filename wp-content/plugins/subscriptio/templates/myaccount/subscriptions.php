<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscriptions
 *
 * Shows subscriptions on the account page
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/myaccount/subscriptions.php
 *
 * Formatting and styles based on WooCommerce 3.7 order list template for uniform appearance
 *
 * @package Subscriptio
 * @version 3.0
 */

?>

<?php do_action('subscriptio_account_before_subscriptions', $subscriptions); ?>

<?php if (!empty($subscriptions)): ?>

    <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table subscriptio-account-subscriptions-table">

        <thead>
            <tr>
                <?php foreach ($columns as $column_id => $column_name): ?>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr($column_id); ?>"><span class="nobr"><?php echo esc_html($column_name); ?></span></th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($subscriptions as $subscription): ?>

                <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($subscription->get_status()); ?> order">

                    <?php foreach ($columns as $column_id => $column_name): ?>

                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr($column_id); ?>" data-title="<?php echo esc_attr($column_name); ?>">

                            <?php if (has_action('subscriptio_account_subscriptions_column_' . $column_id)): ?>
                                <?php do_action('subscriptio_account_subscriptions_column_' . $column_id, $subscription); ?>

                            <?php elseif ($column_id === 'subscription-number'): ?>
                                <a href="<?php echo esc_url(RP_SUB_WC_Account::get_subscription_endpoint_url($subscription, 'view-subscription')); ?>">
                                    <?php echo esc_html($subscription->get_subscription_number()); ?>
                                </a>

                            <?php elseif ($column_id === 'subscription-status') : ?>
                                <?php echo esc_html($subscription->get_status_label()); ?>

                            <?php elseif ($column_id === 'subscription-products') : ?>
                                <?php echo $subscription->get_formatted_product_name(); ?>

                            <?php elseif ($column_id === 'subscription-total') : ?>
                                <?php echo $subscription->get_formatted_recurring_total(); ?>

                            <?php elseif ($column_id === 'subscription-actions') : ?>
                                <?php foreach (RP_SUB_WC_Account::get_subscription_actions($subscription, true) as $key => $action): ?>
                                    <a href="<?php echo esc_url($action['url']); ?>" class="woocommerce-button button <?php echo sanitize_html_class($key); ?>"><?php echo esc_html($action['name']); ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php else: ?>

    <div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info subscriptio-account-no-subscriptions">
        <a class="woocommerce-Button button" href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
            <?php esc_html_e('Go to the shop', 'subscriptio'); ?>
        </a>
        <?php esc_html_e( 'You have no subscriptions.', 'subscriptio' ); ?>
    </div>

<?php endif; ?>

<?php do_action('subscriptio_account_after_subscriptions', $subscriptions); ?>
