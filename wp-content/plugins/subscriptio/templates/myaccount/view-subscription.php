<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * View Subscription
 *
 * Shows the details of a particular subscription on the account page
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/myaccount/view-subscription.php
 *
 * Formatting and styles based on WooCommerce 3.7 view order template for uniform appearance
 *
 * @package Subscriptio
 * @version 3.0
 */

?>

<?php do_action('subscriptio_account_before_view_subscription', $subscription); ?>

<?php do_action('subscriptio_account_view_subscription', $subscription); ?>

<?php do_action('subscriptio_account_after_view_subscription', $subscription); ?>
