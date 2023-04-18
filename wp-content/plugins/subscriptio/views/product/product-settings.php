<?php

/**
 * View for WooCommerce Product Subscription Settings
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

?>

<div class="options_group show-if-rp_sub_subscription_product-simple" style="display: none;">

    <p class="form-field rp-sub-subscription-product-billing-cycle">

        <label for="rp-sub-subscription-product-settings-billing-cycle-length"><?php esc_html_e('Billing cycle', 'subscriptio'); ?></label>

        <?php RightPress_Forms::number(array(
            'id'            => 'rp-sub-subscription-product-settings-billing-cycle-length',
            'name'          => 'rp_sub_subscription_product_settings[billing_cycle_length]',
            'class'         => 'input-text rp-sub-subscription-product-time-length',
            'placeholder'   => esc_html__('e.g. 14', 'subscriptio'),
            'required'      => 'required',
            'min'           => '1',
            'step'          => '1',
            'value'         => $subscription_product->get_billing_cycle_length(),
        )); ?>

        <?php RightPress_Forms::select(array(
            'id'        => 'rp-sub-subscription-product-settings_billing_cycle_period',
            'name'      => 'rp_sub_subscription_product_settings[billing_cycle_period]',
            'class'     => 'select rp-sub-subscription-product-time-period',
            'options'   => $time_periods,
            'value'     => $subscription_product->get_billing_cycle_period(),
        )); ?>
    </p>

    <p class="form-field rp-sub-subscription-product-free-trial">

        <label for="rp-sub-subscription-product-settings-free-trial-length"><?php esc_html_e('Free trial', 'subscriptio'); ?></label>

        <?php RightPress_Forms::number(array(
            'id'            => 'rp-sub-subscription-product-settings-free-trial-length',
            'name'          => 'rp_sub_subscription_product_settings[free_trial_length]',
            'class'         => 'input-text rp-sub-subscription-product-time-length',
            'placeholder'   => esc_html__('No free trial', 'subscriptio'),
            'min'           => '1',
            'step'          => '1',
            'value'         => $subscription_product->get_free_trial_length(),
        )); ?>

        <?php RightPress_Forms::select(array(
            'id'        => 'rp-sub-subscription-product-settings-free-trial-period',
            'name'      => 'rp_sub_subscription_product_settings[free_trial_period]',
            'class'     => 'select rp-sub-subscription-product-time-period',
            'options'   => $time_periods,
            'value'     => $subscription_product->get_free_trial_period(),
        )); ?>
    </p>

    <p class="form-field rp-sub-subscription-product-lifespan-length">

        <label for="rp-sub-subscription-product-settings-lifespan-length"><?php esc_html_e('Lifespan', 'subscriptio'); ?></label>

        <?php RightPress_Forms::number(array(
            'id'            => 'rp-sub-subscription-product-settings-lifespan-length',
            'name'          => 'rp_sub_subscription_product_settings[lifespan_length]',
            'class'         => 'input-text rp-sub-subscription-product-time-length',
            'placeholder'   => esc_html__('Infinite', 'subscriptio'),
            'min'           => '1',
            'step'          => '1',
            'value'         => $subscription_product->get_lifespan_length(),
        )); ?>

        <?php RightPress_Forms::select(array(
            'id'        => 'rp-sub-subscription-product-settings-lifespan-period',
            'name'      => 'rp_sub_subscription_product_settings[lifespan_period]',
            'class'     => 'select rp-sub-subscription-product-time-period',
            'options'   => $time_periods,
            'value'     => $subscription_product->get_lifespan_period(),
        )); ?>
    </p>

    <p class="form-field rp-sub-subscription-product-signup-fee">

        <label for="rp-sub-subscription-product-settings-signup-fee"><?php esc_html_e('Sign-up fee', 'subscriptio'); ?><?php echo ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>

        <?php RightPress_Forms::decimal(array(
            'id'            => 'rp-sub-subscription-product-settings-signup-fee',
            'name'          => 'rp_sub_subscription_product_settings[signup_fee]',
            'class'         => 'short',
            'placeholder'   => esc_html__('No sign-up fee', 'subscriptio'),
            'min'           => RightPress_Help::get_wc_smallest_price_decimal(),
            'step'          => RightPress_Help::get_wc_smallest_price_decimal(),
            'value'         => $subscription_product->get_signup_fee('edit'),
        )); ?>
    </p>

    <div style="clear: both;"></div>
</div>
