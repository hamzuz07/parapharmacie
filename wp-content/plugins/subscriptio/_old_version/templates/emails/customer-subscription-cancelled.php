<?php

/**
 * Customer Subscription Cancelled email template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<?php do_action('woocommerce_email_header', $email_heading); ?>

<p><?php printf(esc_html__('Your subscription on %s has been cancelled.', 'subscriptio'), get_option('blogname')); ?></p>

<p><?php esc_html_e('Details of the cancelled subscription are shown below for your reference:', 'subscriptio'); ?></p>

<?php do_action('subscriptio_email_before_subscription_table', $subscription, $sent_to_admin, $plain_text); ?>

<h2><?php echo esc_html__('Subscription:', 'subscriptio') . ' ' . $subscription->get_subscription_number(); ?></h2>
<?php Subscriptio::include_template('emails/email-subscription-items', array('subscription' => $subscription, 'plain_text' => false)); ?>

<?php do_action('subscriptio_email_after_subscription_table', $subscription, $sent_to_admin, $plain_text); ?>

<?php do_action('woocommerce_email_footer'); ?>
