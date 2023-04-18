<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription notes
 *
 * This template can be overridden by copying it to yourtheme/subscriptio/subscription/subscription-notes.php
 *
 * Formatting and styles based on WooCommerce 3.7 order details templates for uniform appearance
 *
 * @package Subscriptio
 * @version 3.0
 */

?>

<?php do_action('subscriptio_account_before_subscription_notes', $subscription); ?>

<h2><?php esc_html_e('Subscription notes', 'subscriptio'); ?></h2>

<ol class="woocommerce-OrderUpdates commentlist notes subscriptio-account-subscription-notes">
    <?php foreach ($notes as $note): ?>
    <li class="woocommerce-OrderUpdate comment note">
        <div class="woocommerce-OrderUpdate-inner comment_container">
            <div class="woocommerce-OrderUpdate-text comment-text">
                <p class="woocommerce-OrderUpdate-meta meta"><?php echo date_i18n(esc_html__('l jS \o\f F Y, h:ia', 'subscriptio'), strtotime($note->comment_date)); /* TODO: Probably we should format this by ourselves? */ ?></p>
                <div class="woocommerce-OrderUpdate-description description">
                    <?php echo wpautop(wptexturize($note->comment_content)); ?>
                </div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>
    </li>
    <?php endforeach; ?>
</ol>

<?php do_action('subscriptio_account_after_subscription_notes', $subscription); ?>
