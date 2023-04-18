<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription item
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/subscription/subscription-item.php
 *
 * Formatting and styles based on WooCommerce 3.7 order details templates for uniform appearance
 *
 * @package Subscriptio
 * @version 3.0
 */

?>

<tr class="<?php echo esc_attr(apply_filters('subscriptio_subscription_item_class', 'woocommerce-table__line-item order_item subscriptio-account-subscription-item', $item, $subscription)); ?>">

    <td class="woocommerce-table__product-name product-name">

        <?php echo apply_filters('subscriptio_subscription_item_name', $product_permalink ? sprintf('<a href="%s">%s</a>', $product_permalink, $item->get_name()) : $item->get_name(), $item, $product_is_visible); ?>

        <?php echo apply_filters('subscriptio_subscription_item_quantity_html', ' <strong class="product-quantity">' . sprintf('&times; %s', esc_html($item->get_quantity())) . '</strong>', $item); ?>

        <?php do_action('subscriptio_account_before_subscription_item_meta', $item_id, $item, $subscription, false); ?>

        <?php wc_display_item_meta($item); ?>

        <?php do_action('subscriptio_account_after_subscription_item_meta', $item_id, $item, $subscription, false); ?>

    </td>

    <td class="woocommerce-table__product-total product-total">
        <?php echo $subscription->get_formatted_line_subtotal($item); ?>
    </td>

</tr>

<?php if ($purchase_note): ?>

<tr class="woocommerce-table__product-purchase-note product-purchase-note">
    <td colspan="2"><?php echo wpautop(do_shortcode(wp_kses_post($purchase_note))); ?></td>
</tr>

<?php endif; ?>
