<?php

/**
 * Customer Subscription Edit Shipping Address
 *
 * Based on WooCommerce 3.7 edit address form template
 *
 * @var string $context
 * @var array $address_fields
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

?>

<?php do_action('subscriptio_before_subscription_address_edit'); ?>

<form method="post">

    <h3><?php echo ($context === 'shipping' ? esc_html__('Shipping address', 'subscriptio') : esc_html__('Billing address', 'subscriptio')); ?></h3>

    <div class="woocommerce-address-fields">

        <div class="woocommerce-address-fields__field-wrapper">
            <?php
            foreach ($address_fields as $key => $field) {
                woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, $field['value']));
            }
            ?>
        </div>

        <p>
            <button type="submit" class="button" name="save_address" value="<?php esc_attr_e('Save address', 'subscriptio'); ?>"><?php esc_html_e('Save address', 'subscriptio'); ?></button>
            <?php wp_nonce_field('subscriptio-edit_address', 'subscriptio-edit-address-nonce'); ?>
            <input type="hidden" name="action" value="subscriptio_edit_address" />
            <input type="hidden" name="context" value="<?php echo $context; ?>" />
            <input type="hidden" name="subscription_id" value="<?php echo $subscription->get_id(); ?>" />
        </p>
    </div>

</form>

<?php do_action('subscriptio_after_subscription_address_edit'); ?>
