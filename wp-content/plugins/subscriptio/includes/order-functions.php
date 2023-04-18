<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Check if order is subscription order
 *
 * Checks for orders that have a subscription set to them, could be both initial and renewal orders
 *
 * @param WC_Order|int $order
 * @return bool
 */
function subscriptio_is_subscription_order($order)
{

    // Load order object if needed
    if (!is_a($order, 'WC_Order') && is_numeric($order)) {
        $order = wc_get_order($order);
    }

    // Check if this is WooCommerce order
    if (!is_a($order, 'WC_Order')) {
        return false;
    }

    // Check for flag in meta
    return (bool) $order->get_meta('_rp_sub:related_subscription', true);
}

/**
 * Check if order is subscription initial order
 *
 * @param WC_Order|int $order
 * @return bool
 */
function subscriptio_is_subscription_initial_order($order)
{

    // Load order object if needed
    if (!is_a($order, 'WC_Order') && is_numeric($order)) {
        $order = wc_get_order($order);
    }

    // Check if this is WooCommerce order
    if (!is_a($order, 'WC_Order')) {
        return false;
    }

    // Check for flag in meta
    return (bool) $order->get_meta('_rp_sub:initial_order', true);
}

/**
 * Check if order is subscription renewal order
 *
 * @param WC_Order|int $order
 * @return bool
 */
function subscriptio_is_subscription_renewal_order($order)
{

    // Load order object if needed
    if (!is_a($order, 'WC_Order') && is_numeric($order)) {
        $order = wc_get_order($order);
    }

    // Check if this is WooCommerce order
    if (!is_a($order, 'WC_Order')) {
        return false;
    }

    // Check for flag in meta
    return (bool) $order->get_meta('_rp_sub:renewal_order', true);
}

/**
 * Get orders related to subscription
 *
 * @access public
 * @param RP_SUB_Subscription|int $subscription
 * @param array $args                   Arguments accepted by wc_get_orders
 * @return array
 */
function subscriptio_get_orders_related_to_subscription($subscription, $args = array())
{

    // Get subscription id
    $subscription_id = is_a($subscription, 'RP_SUB_Subscription') ? $subscription->get_id() : $subscription;

    // Get orders
    return wc_get_orders(array_merge(array(
        'limit'                         => -1,
        'rp_sub_related_subscription'   => $subscription_id,
    ), $args));
}
