<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription orders details
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/subscription/subscription-orders.php
 *
 * Formatting and styles based on WooCommerce 3.7 order details templates for uniform appearance
 *
 * @package Subscriptio
 * @version 3.0
 */

?>

<section class="subscriptio-account-subscription-orders">

    <?php do_action('subscriptio_account_before_subscription_orders', $subscription, $orders); ?>

    <h2><?php esc_html_e('Related Orders', 'subscriptio'); ?></h2>

    <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
        <thead>
            <tr>
                <?php foreach (wc_get_account_orders_columns() as $column_id => $column_name): ?>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr($column_id); ?>"><span class="nobr"><?php echo esc_html($column_name); ?></span></th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>
            <?php
            foreach ($orders as $order) {

                $item_count = $order->get_item_count() - $order->get_item_count_refunded();

                ?>

                <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
                    <?php foreach (wc_get_account_orders_columns() as $column_id => $column_name): ?>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr($column_id); ?>" data-title="<?php echo esc_attr($column_name); ?>">

                            <?php if (has_action('woocommerce_my_account_my_orders_column_' . $column_id)) : ?>
                                <?php do_action('woocommerce_my_account_my_orders_column_' . $column_id, $order); ?>

                            <?php elseif ($column_id === 'order-number'): ?>
                                <a href="<?php echo esc_url($order->get_view_order_url()); ?>">
                                    <?php echo esc_html(esc_html_x('#', 'hash before order number', 'subscriptio') . $order->get_order_number()); ?>
                                </a>

                            <?php elseif ($column_id === 'order-date'): ?>
                                <time datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></time>

                            <?php elseif ($column_id === 'order-status'): ?>
                                <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>

                            <?php elseif ($column_id === 'order-total'): ?>
                                <?php echo wp_kses_post(sprintf(_n('%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'subscriptio'), $order->get_formatted_order_total(), $item_count)); ?>

                            <?php elseif ($column_id === 'order-actions'): ?>
                                <?php

                                $actions = wc_get_account_orders_actions($order);

                                if (!empty($actions)) {
                                    foreach ($actions as $key => $action) {
                                        echo '<a href="' . esc_url($action['url']) . '" class="woocommerce-button button ' . sanitize_html_class($key) . '">' . esc_html($action['name']) . '</a>';
                                    }
                                }
                                ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>

    <?php do_action('subscriptio_account_after_subscription_orders', $subscription, $orders); ?>

</section>
