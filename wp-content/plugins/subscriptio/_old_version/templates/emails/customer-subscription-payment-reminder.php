<?php

/**
 * Customer Subscription Payment Reminder email template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<?php do_action('woocommerce_email_header', $email_heading); ?>

<p><?php printf(esc_html__('Your recent subscription renewal order on %s is due for payment.', 'subscriptio'), get_option('blogname')); ?></p>

<?php if ($next_action_is_overdue): ?>
    <p><?php printf(esc_html__('If you do not pay it by %s, your %s will be %s and will be subject to %s.', 'subscriptio'), ('<strong>' . $next_action_datetime . '</strong>'), _n('subscription', 'subscriptions', count($order->get_items()), 'subscriptio'), ('<strong>' . $next_action . '</strong>'), $subsequent_action); ?></p>
<?php else: ?>
    <p><?php printf(esc_html__('If you do not pay it by %s, your %s will be %s.', 'subscriptio'), ('<strong>' . $next_action_datetime . '</strong>'), _n('subscription', 'subscriptions', count($order->get_items()), 'subscriptio'), ('<strong>' . $next_action . '</strong>')); ?></p>
<?php endif; ?>

<p><?php esc_html_e('To pay for this order please use the following link:', 'subscriptio'); ?></p>
<p style="padding:10px 0;"><a style="background-color:#557da1;padding:10px 15px;color:#fff;text-decoration:none;font-weight:bold;" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>"><?php esc_html_e('pay now', 'subscriptio'); ?></a></p>

<p><?php esc_html_e('Your order details are shown below for your reference:', 'subscriptio'); ?></p>

<?php do_action('woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text); ?>

<h2><?php echo esc_html__('Order:', 'subscriptio') . ' ' . $order->get_order_number(); ?></h2>
<?php Subscriptio::include_template('emails/email-order-items', array('order' => $order, 'plain_text' => false)); ?>

<?php do_action('woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text); ?>

<?php do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text); ?>

<h2><?php esc_html_e('Customer details', 'subscriptio'); ?></h2>
<?php Subscriptio::include_template('emails/email-customer-details', array('order' => $order, 'plain_text' => false)); ?>

<?php do_action('woocommerce_email_footer'); ?>
