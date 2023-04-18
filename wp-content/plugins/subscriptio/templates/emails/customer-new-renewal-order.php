<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer New Renewal Order Email Template
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/emails/customer-new-renewal-order.php
 *
 * Based on WooCommerce 3.7 email templates
 *
 * @package Subscriptio
 * @version 3.0
 */

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(esc_html__('Hi %s,', 'subscriptio'), esc_html($order->get_billing_first_name())); ?></p>

<p>
<?php

    if ($order->has_status('pending')) {

        printf(
            wp_kses(esc_html__('A new subscription renewal order has been created for you on %1$s. Please follow this link to make a payment when you’re ready: %2$s', 'subscriptio'), array('a' => array('href' => array()))),
            esc_html(get_bloginfo('name', 'display')),
            '<a href="' . esc_url($order->get_checkout_payment_url()) . '">' . esc_html__('Pay for this renewal order', 'subscriptio') . '</a>'
        );
    }
    else {

        printf(esc_html__('Here are the details of your subscription renewal order:', 'subscriptio'));
    }

?>
</p>

<?php

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
