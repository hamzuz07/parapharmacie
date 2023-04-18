<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription data meta box shipping details view
 *
 * Based on WooCommerce 3.7 order data meta box view
 */

?>

<div class="order_data_column">

    <h3>
        <?php esc_html_e('Shipping', 'subscriptio'); ?>
        <a href="#" class="edit_address"><?php esc_html_e('Edit', 'subscriptio'); ?></a>

        <span>
            <a href="#" class="load_customer_shipping" style="display:none;"><?php esc_html_e('Load shipping address', 'subscriptio'); ?></a>
            <a href="#" class="billing-same-as-shipping" style="display:none;"><?php esc_html_e('Copy billing address', 'subscriptio'); ?></a>
        </span>
    </h3>

    <div class="address">
        <?php

        if ($order->get_formatted_shipping_address()) {
            echo '<p>' . wp_kses($order->get_formatted_shipping_address(), array('br' => array())) . '</p>';
        }
        else {
            echo '<p class="none_set"><strong>' . esc_html__('Address:', 'subscriptio') . '</strong> ' . esc_html__('No shipping address set.', 'subscriptio') . '</p>';
        }

        foreach ($address_fields['shipping'] as $field) {
            if ($field['show']) {
                echo '<p><strong>' . $field['label_for_display'] . ':</strong> ' . $field['value_for_display'] . '</p>';
            }
        }

        if (apply_filters('woocommerce_enable_order_notes_field', get_option('woocommerce_enable_order_comments', 'yes') == 'yes') && $post->post_excerpt) {
            echo '<p class="order_note"><strong>' . esc_html__('Customer provided note:', 'subscriptio') . '</strong> ' . nl2br(esc_html($post->post_excerpt)) . '</p>';
        }

        ?>
    </div>

    <div class="edit_address">

        <?php

        foreach ($address_fields['shipping'] as $field) {
            ($field['type'] === 'select') ? woocommerce_wp_select($field) : woocommerce_wp_text_input($field);
        }

        ?>

        <?php if (apply_filters('woocommerce_enable_order_notes_field', get_option('woocommerce_enable_order_comments', 'yes') == 'yes')): ?>
            <p class="form-field form-field-wide">
                <label for="excerpt"><?php esc_html_e('Customer provided note', 'subscriptio'); ?>:</label>
                <textarea rows="1" cols="40" name="excerpt" tabindex="6" id="excerpt" placeholder="<?php esc_attr_e('Customer notes about the subscription', 'subscriptio'); ?>"><?php echo wp_kses_post($post->post_excerpt); ?></textarea>
            </p>
        <?php endif; ?>
    </div>

    <?php do_action('subscriptio_admin_subscription_data_after_shipping_address', $subscription); ?>

</div>
