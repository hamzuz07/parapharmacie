<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription addresses
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/subscription/subscription-addresses.php
 *
 * Formatting and styles based on WooCommerce 3.7 order details templates for uniform appearance
 *
 * @package Subscriptio
 * @var $subscription RP_SUB_Subscription
 * @version 3.0
 */

?>

<section class="woocommerce-customer-details subscriptio-account-subscription-addresses">

    <?php do_action('subscriptio_account_before_subscription_addresses', $subscription); ?>

    <section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
        <div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">

            <h2 class="woocommerce-column__title"><?php esc_html_e('Billing address', 'subscriptio'); ?></h2>

            <address>

                <?php echo wp_kses_post($subscription->get_formatted_billing_address(esc_html__('N/A', 'subscriptio'))); ?>

                <?php if ($subscription->get_billing_phone()): ?>
                    <br><?php echo esc_html($subscription->get_billing_phone()); ?>
                <?php endif; ?>

                <?php if ($subscription->get_billing_email()): ?>
                    <br><?php echo esc_html($subscription->get_billing_email()); ?>
                <?php endif; ?>
            </address>

            <a href="<?php echo esc_url(wc_get_endpoint_url('edit-subscription-billing-address', $subscription->get_id())); ?>" class="edit"><?php echo esc_html__('Edit', 'subscriptio'); ?></a>

        </div>

        <?php if ($show_shipping): ?>

            <div class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">

                <h2 class="woocommerce-column__title"><?php esc_html_e('Shipping address', 'subscriptio'); ?></h2>

                <address>
                    <?php echo wp_kses_post($subscription->get_formatted_shipping_address(esc_html__('N/A', 'subscriptio'))); ?>
                </address>

                <a href="<?php echo esc_url(wc_get_endpoint_url('edit-subscription-shipping-address', $subscription->get_id())); ?>" class="edit"><?php echo esc_html__('Edit', 'subscriptio'); ?></a>

            </div>

        <?php endif; ?>

    </section>

    <?php do_action('subscriptio_account_after_subscription_addresses', $subscription); ?>

</section>
