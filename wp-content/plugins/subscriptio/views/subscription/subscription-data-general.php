<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription data meta box general details view
 *
 * Based on WooCommerce 3.7 order data meta box view
 *
 * @var RP_SUB_Subscription $subscription
 */

?>

<div class="order_data_column">

    <h3><?php esc_html_e('General', 'subscriptio'); ?></h3>

    <p class="form-field form-field-wide wc-customer-user">
        <!--email_off--> <!-- Disable CloudFlare email obfuscation -->

        <label for="customer_user">

            <?php esc_html_e('Customer:', 'subscriptio'); ?>

            <?php if ($customer_id): ?>
                <a href="<?php echo esc_url(add_query_arg(array('post_type' => 'rp_sub_subscription', 'customer_id' => $customer_id), admin_url('edit.php'))); ?>"><?php esc_html_e('Subscriptions &rarr;', 'subscriptio'); ?></a>
                <a href="<?php echo esc_url(add_query_arg('user_id', $customer_id, admin_url('user-edit.php'))); ?>"><?php esc_html_e('Profile &rarr;', 'subscriptio' ); ?></a>
            <?php endif; ?>

        </label>

        <select class="wc-customer-search" id="customer_user" name="customer_user" data-placeholder="<?php esc_attr_e('Guest', 'subscriptio'); ?>" data-allow_clear="true" <?php echo (!$subscription->has_status(array('draft', 'auto-draft')) ? 'disabled="disabled"' : ''); ?>>
            <option value="<?php echo esc_attr($user_id); ?>" selected="selected"><?php echo htmlspecialchars(wp_kses_post($user_string)); ?></option>
        </select>

        <!--/email_off-->
    </p>

    <p class="form-field form-field-wide rp-sub-subscription-payment-gateway">

        <label for="rp_sub_subscription_payment_gateway">

            <?php esc_html_e('Payment gateway:', 'subscriptio'); ?>

            <?php if (!$subscription->has_automatic_payments() || $subscription->has_status(array('overdue', 'suspended'))): ?>
                <?php if ($order_pending_payment = $subscription->get_order_pending_payment()): ?>
                    <a href="<?php echo esc_url($order_pending_payment->get_checkout_payment_url()); ?>"><?php esc_html_e('Customer payment page &rarr;', 'subscriptio'); ?></a>
                <?php endif; ?>
            <?php endif; ?>

        </label>

        <select id="rp_sub_subscription_payment_gateway" name="rp_sub_subscription_payment_gateway" class="wc-enhanced-select">
            <?php if ($subscription->has_automatic_payments()): ?>
                <option value="<?php echo $subscription->get_suborder()->get_payment_method(); ?>" selected="selected"><?php echo wc_get_payment_gateway_by_order($subscription->get_suborder())->get_method_title(); ?></option>
            <?php endif; ?>
            <option value="rp_sub_manual_payments" <?php echo ($subscription->has_automatic_payments() ? '' : 'selected="selected"'); ?>>Manual payments</option>
        </select>

    </p>

    <p class="form-field form-field-wide wc-order-status">

        <label for="order_status">
            <?php esc_html_e('Status:', 'subscriptio'); ?>
        </label>

        <select id="order_status" name="order_status" class="wc-enhanced-select">
            <?php foreach (RP_SUB_Subscription_Controller::get_subscription_statuses() as $status => $status_data): ?>
                <?php if ($subscription->has_status($status) || $subscription->can_have_status_changed_to($status, 'admin')): ?>
                    <option value="<?php echo esc_attr('wc-' . $status); ?>" <?php selected($status, $subscription->get_status('edit')); ?>><?php echo esc_html($status_data['label']); ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

    </p>

    <?php do_action('subscriptio_admin_subscription_data_after_subscription_details', $subscription); ?>

</div>
