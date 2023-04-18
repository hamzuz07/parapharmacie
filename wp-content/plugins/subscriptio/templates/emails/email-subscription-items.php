<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Email subscription details template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/email-order-items.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @var RP_SUB_Subscription $subscription
 * @version 3.0
 */

$text_align  = is_rtl() ? 'right' : 'left';
$margin_side = is_rtl() ? 'left' : 'right';

?>

<?php foreach ($items as $item_id => $item): ?>

    <?php

    $product        = $item->get_product();
    $sku            = '';
    $purchase_note  = '';
    $image          = '';

    if (!apply_filters('subscriptio_subscription_item_visible', true, $item, $subscription)) {
        continue;
    }

    if (is_object($product)) {
        $sku            = $product->get_sku();
        $purchase_note  = $product->get_purchase_note();
        $image          = $product->get_image($image_size);
    }

    ?>

    <tr class="order_item">
        <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">

            <?php

            // Show image
            if ($show_image) {
                echo wp_kses_post(apply_filters('subscriptio_subscription_item_thumbnail', $image, $item));
            }

            // Product name
            echo wp_kses_post(apply_filters('subscriptio_subscription_item_name', $item->get_name(), $item, false));

            // SKU
            if ($show_sku && $sku) {
                echo wp_kses_post(' (#' . $sku . ')');
            }

            // Allow other plugins to add additional product information here
            do_action('subscriptio_subscription_item_meta_start', $item_id, $item, $subscription, $plain_text);

            wc_display_item_meta(
                $item,
                array(
                    'label_before' => '<strong class="wc-item-meta-label" style="float: ' . esc_attr($text_align) . '; margin-' . esc_attr($margin_side) . ': .25em; clear: both">',
                )
            );

            // Allow other plugins to add additional product information here
            do_action('subscriptio_subscription_item_meta_end', $item_id, $item, $subscription, $plain_text);

            ?>

        </td>

        <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php echo wp_kses_post(apply_filters('subscriptio_email_subscription_item_quantity', esc_html($item->get_quantity()), $item)); ?>
        </td>

        <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php echo wp_kses_post($subscription->get_formatted_line_subtotal($item)); ?>
        </td>

    </tr>

    <?php if ($show_purchase_note && $purchase_note): ?>
        <tr>
            <td colspan="3" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                <?php echo wp_kses_post(wpautop(do_shortcode($purchase_note))); ?>
            </td>
        </tr>
    <?php endif; ?>

<?php endforeach; ?>
