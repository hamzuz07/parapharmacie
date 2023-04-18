<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription data meta box billing details view
 *
 * Based on WooCommerce 3.7 order data meta box view
 */

?>

<div class="order_data_column">

    <h3>
        <?php esc_html_e('Billing', 'subscriptio'); ?>
        <a href="#" class="edit_address"><?php esc_html_e('Edit', 'subscriptio'); ?></a>

        <span>
            <a href="#" class="load_customer_billing" style="display:none;"><?php esc_html_e('Load billing address', 'subscriptio'); ?></a>
        </span>
    </h3>

    <div class="address">
        <?php

        if ($order->get_formatted_billing_address()) {
            echo '<p>' . wp_kses($order->get_formatted_billing_address(), array('br' => array())) . '</p>';
        }
        else {
            echo '<p class="none_set"><strong>' . esc_html__('Address:', 'subscriptio') . '</strong> ' . esc_html__('No billing address set.', 'subscriptio') . '</p>';
        }

        foreach ($address_fields['billing'] as $field) {
            if ($field['show']) {
                echo '<p><strong>' . $field['label_for_display'] . ':</strong> ' . $field['value_for_display'] . '</p>';
            }
        }

        ?>
    </div>

    <div class="edit_address">

        <?php

        foreach ($address_fields['billing'] as $field) {
            ($field['type'] === 'select') ? woocommerce_wp_select($field) : woocommerce_wp_text_input($field);
        }

        ?>

    </div>

    <?php do_action('subscriptio_admin_subscription_data_after_billing_address', $subscription); ?>

</div>
