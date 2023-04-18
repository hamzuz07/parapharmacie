<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Email subscription details template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/email-order-details.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @var RP_SUB_Subscription $subscription
 * @version 3.0
 */

$text_align = is_rtl() ? 'right' : 'left';

do_action('subscriptio_email_before_subscription_table', $subscription, $sent_to_admin, $plain_text, $email); ?>

<h2>
    <?php echo wp_kses_post(sprintf(esc_html__('Subscription #%s', 'subscriptio'), $subscription->get_id())); ?>
</h2>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
            <tr>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Product', 'subscriptio'); ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Quantity', 'subscriptio'); ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Price', 'subscriptio'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php do_action('subscriptio_email_subscription_items', $subscription, $sent_to_admin, $plain_text, $email); ?>
        </tbody>
        <tfoot>
            <?php

            $item_totals = $subscription->get_suborder()->get_order_item_totals();

            if ($item_totals) {
                $i = 0;
                foreach ($item_totals as $total) {
                    $i++;
                    ?>
                    <tr>
                        <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr($text_align); ?>; <?php echo ($i === 1) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post($total['label']); ?></th>
                        <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; <?php echo ($i === 1) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post($total['value']); ?></td>
                    </tr>
                    <?php
                }
            }

            ?>

            <?php if ($subscription->get_customer_note()): ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Note:', 'subscriptio'); ?></th>
                    <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php echo wp_kses_post(nl2br(wptexturize($subscription->get_customer_note()))); ?></td>
                </tr>
            <?php endif; ?>

        </tfoot>
    </table>
</div>

<?php do_action('subscriptio_email_after_subscription_table', $subscription, $sent_to_admin, $plain_text, $email); ?>
