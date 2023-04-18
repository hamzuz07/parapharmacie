<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription data meta box view
 *
 * Based on WooCommerce 3.7 order data meta box view
 */

?>

<style type="text/css">
    #post-body-content,
    #titlediv {
        display: none;
    }
</style>

<div class="panel-wrap woocommerce">

    <input name="post_title" type="hidden" value="<?php echo empty($post->post_title) ? esc_html__('Subscription', 'subscriptio') : esc_attr($post->post_title); ?>" />
    <input name="post_status" type="hidden" value="<?php echo esc_attr($post->post_status); ?>" />

    <div id="order_data" class="panel woocommerce-order-data">

        <h2 class="woocommerce-order-data__heading">
            <?php printf(esc_html__('Subscription %s details', 'subscriptio'), $subscription->get_subscription_number()); ?>
        </h2>

        <div class="order_data_column_container">

            <?php include_once 'subscription-data-general.php'; ?>

            <?php include_once 'subscription-data-billing.php'; ?>

            <?php include_once 'subscription-data-shipping.php'; ?>

        </div>
        <div class="clear"></div>
    </div>
</div>
