<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription overview
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/subscription/subscription-overview.php
 *
 * Formatting and styles based on WooCommerce 3.7 order details templates for uniform appearance
 *
 * @package Subscriptio
 * @version 3.0
 * @var array $overview_data
 */

?>

<?php do_action('subscriptio_account_before_subscription_overview', $subscription); ?>

<p class="subscriptio-account-subscription-overview">
    <?php if (!empty($overview_data)): ?>

        <table class="woocommerce-table shop_table order_details">

            <?php foreach($overview_data as $overview_row): ?>

                <tr>
                    <td><?php echo $overview_row['label']; ?></td>
                    <td><?php echo $overview_row['value']; ?></td>
                </tr>

            <?php endforeach; ?>

        </table>
    <?php endif; ?>
</p>

<?php do_action('subscriptio_account_after_subscription_overview', $subscription); ?>
