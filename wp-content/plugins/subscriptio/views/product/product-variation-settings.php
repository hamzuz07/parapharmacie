<?php

/**
 * View for WooCommerce Product Variation Subscription Settings
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

?>

<div class="show-if-rp_sub_subscription_product-variable">

    <div>

        <p class="form-row form-row-first rp-sub-subscription-product-billing-cycle">

            <label for="rp-sub-subscription-product-settings-<?php echo $loop; ?>-billing-cycle-length"><?php esc_html_e('Billing cycle', 'subscriptio'); ?></label>

            <?php RightPress_Forms::number(array(
                'id'            => 'rp-sub-subscription-product-settings-' . $loop . '-billing-cycle-length',
                'name'          => 'rp_sub_subscription_product_settings[' . $loop . '][billing_cycle_length]',
                'class'         => 'input-text rp-sub-subscription-product-time-length',
                'placeholder'   => esc_html__('e.g. 14', 'subscriptio'),
                'required'      => 'required',
                'min'           => '1',
                'step'          => '1',
                'value'         => $subscription_product->get_billing_cycle_length(),
            )); ?>

            <?php RightPress_Forms::select(array(
                'id'        => 'rp-sub-subscription-product-settings-' . $loop . '-billing-cycle-period',
                'name'      => 'rp_sub_subscription_product_settings[' . $loop . '][billing_cycle_period]',
                'class'     => 'select rp-sub-subscription-product-time-period',
                'options'   => $time_periods,
                'value'     => $subscription_product->get_billing_cycle_period(),
            )); ?>

        </p>

        <p class="form-row form-row-last rp-sub-subscription-product-free-trial">

            <label for="rp-sub-subscription-product-settings-<?php echo $loop; ?>-free-trial-length"><?php esc_html_e('Free trial', 'subscriptio'); ?></label>

            <?php RightPress_Forms::number(array(
                'id'            => 'rp-sub-subscription-product-settings-' . $loop . '-free-trial-length',
                'name'          => 'rp_sub_subscription_product_settings[' . $loop . '][free_trial_length]',
                'class'         => 'input-text rp-sub-subscription-product-time-length',
                'placeholder'   => esc_html__('No free trial', 'subscriptio'),
                'min'           => '1',
                'step'          => '1',
                'value'         => $subscription_product->get_free_trial_length(),
            )); ?>

            <?php RightPress_Forms::select(array(
                'id'        => 'rp-sub-subscription-product-settings-' . $loop . '-free-trial-period',
                'name'      => 'rp_sub_subscription_product_settings[' . $loop . '][free_trial_period]',
                'class'     => 'select rp-sub-subscription-product-time-period',
                'options'   => $time_periods,
                'value'     => $subscription_product->get_free_trial_period(),
            )); ?>

        </p>

    </div>

    <div>

        <p class="form-row form-row-first rp-sub-subscription-product-lifespan-length">

            <label for="rp-sub-subscription-product-settings-<?php echo $loop; ?>-lifespan-length"><?php esc_html_e('Lifespan', 'subscriptio'); ?></label>

            <?php RightPress_Forms::number(array(
                'id'            => 'rp-sub-subscription-product-settings-' . $loop . '-lifespan-length',
                'name'          => 'rp_sub_subscription_product_settings[' . $loop . '][lifespan_length]',
                'class'         => 'input-text rp-sub-subscription-product-time-length',
                'placeholder'   => esc_html__('Infinite', 'subscriptio'),
                'min'           => '1',
                'step'          => '1',
                'value'         => $subscription_product->get_lifespan_length(),
            )); ?>

            <?php RightPress_Forms::select(array(
                'id'        => 'rp-sub-subscription-product-settings-' . $loop . '-lifespan-period',
                'name'      => 'rp_sub_subscription_product_settings[' . $loop . '][lifespan_period]',
                'class'     => 'select rp-sub-subscription-product-time-period',
                'options'   => $time_periods,
                'value'     => $subscription_product->get_lifespan_period(),
            )); ?>

        </p>

        <p class="form-row form-row-last rp-sub-subscription-product-signup-fee">

            <label for="rp-sub-subscription-product-settings-<?php echo $loop; ?>-signup-fee"><?php esc_html_e('Sign-up fee', 'subscriptio'); ?> <?php echo ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>

            <?php RightPress_Forms::decimal(array(
                'id'            => 'rp-sub-subscription-product-settings-' . $loop . '-signup-fee',
                'name'          => 'rp_sub_subscription_product_settings[' . $loop . '][signup_fee]',
                'class'         => 'short wc_input_decimal',
                'placeholder'   => esc_html__('No sign-up fee', 'subscriptio'),
                'min'           => RightPress_Help::get_wc_smallest_price_decimal(),
                'step'          => RightPress_Help::get_wc_smallest_price_decimal(),
                'value'         => $subscription_product->get_signup_fee(),
            )); ?>
        </p>

    </div>

    <div style="clear: both;"></div>
</div>
