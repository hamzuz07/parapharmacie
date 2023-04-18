<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription actions
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/subscription/subscription-actions.php
 *
 * Formatting and styles based on WooCommerce 3.7 order details templates for uniform appearance
 *
 * @package Subscriptio
 * @version 3.0
 */

?>

<?php do_action('subscriptio_account_before_subscription_actions', $subscription); ?>

<p class="subscriptio-account-subscription-actions" style="text-align: right;">

    <?php foreach ($actions as $key => $action): ?>
        <a href="<?php echo esc_url($action['url']); ?>" class="button subscriptio-subscription-action-<?php echo sanitize_html_class($key); ?>"><?php echo esc_html($action['name']); ?></a>
    <?php endforeach; ?>

</p>

<?php do_action('subscriptio_account_after_subscription_actions', $subscription); ?>
