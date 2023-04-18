<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer functions
 */

/**
 * Get customer subscriptions
 *
 * @param WC_Customer|int $customer
 * @param bool $include_terminated
 * @param array $statuses
 * @return RP_SUB_Subscription[]
 */
function subscriptio_get_customer_subscriptions($customer = null, $include_terminated = false, $statuses = null)
{

    // Guest have no subscriptions
    if (!is_user_logged_in()) {
        return array();
    }

    // Get customer
    $customer = ($customer !== null ? $customer : get_current_user_id());

    // Get customer subscriptions
    return subscriptio_get_subscriptions(array(
        'customer'              => $customer,
        'include_terminated'    => $include_terminated,
        'statuses'              => $statuses,
    ));
}

/**
 * Get customer enabled subscriptions
 *
 * @param WC_Customer|int $customer
 * @param bool $include_paused
 * @return RP_SUB_Subscription[]
 */
function subscriptio_get_customer_enabled_subscriptions($customer = null, $include_paused = false)
{

    // Define a list of allowed statuses
    $statuses = array('trial', 'active', 'overdue', 'set-to-cancel');

    // Maybe add paused status
    if ($include_paused) {
        $statuses[] = 'paused';
    }

    // Get customer subscriptions with specific statuses and return
    return subscriptio_get_customer_subscriptions($customer, false, $statuses);
}

/**
 * Check if customer has a subscription
 *
 * Considers subscriptions that are not cancelled/expired only, unless $include_terminated is set to true
 *
 * @param WC_Customer|int $customer
 * @param bool $include_terminated
 * @return bool
 */
function subscriptio_customer_has_subscription($customer = null, $include_terminated = false)
{

    // Check if system is ready
    RP_SUB::ready_or_fail(__FUNCTION__);

    // Customer has at least one subscription
    if (subscriptio_get_customer_subscriptions($customer, $include_terminated)) {
        return true;
    }

    // Customer has no subscriptions
    return false;
}

/**
 * Check if customer has enabled subscription
 *
 * Considers subscriptions with statuses trial, active, overdue, set-to-cancel and possibly paused
 *
 * @param WC_Customer|int $customer
 * @param bool $include_paused
 * @return bool
 */
function subscriptio_customer_has_enabled_subscription($customer = null, $include_paused = false)
{

    // Check if system is ready
    RP_SUB::ready_or_fail(__FUNCTION__);

    // Customer has at least one enabled subscription
    if (subscriptio_get_customer_enabled_subscriptions($customer, $include_paused)) {
        return true;
    }

    // Customer has no subscriptions
    return false;
}

/**
 * Check if customer has a subscription product
 *
 * Considers subscriptions that are not cancelled/expired only, unless $include_terminated is set to true
 *
 * @param int $product_id
 * @param int $customer_id
 * @param bool $include_terminated
 * @return bool
 */
function subscriptio_customer_has_subscription_product($product_id, $customer_id = null, $include_terminated = false)
{

    // Check if system is ready
    RP_SUB::ready_or_fail(__FUNCTION__);

    // Get customer's subscriptions
    $subscriptions = subscriptio_get_customer_subscriptions($customer_id, $include_terminated);

    // Iterate over subscriptions
    foreach ($subscriptions as $subscription) {

        // Iterate over subscription items
        foreach ($subscription->get_items() as $item) {

            // Customer has requested subscription product
            if ($item->get_product_id() === $product_id || $item->get_variation_id() === $product_id) {
                return true;
            }
        }
    }

    // Customer does not have requested subscription product
    return false;
}

/**
 * Get ids of customers who have subscriptions
 *
 * @return array
 */
function subscriptio_get_ids_of_customers_with_subscriptions()
{

    global $wpdb;

    return $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} AS postmeta INNER JOIN {$wpdb->posts} AS posts ON (postmeta.post_id = posts.ID) WHERE posts.post_type = 'rp_sub_subscription' AND postmeta.meta_key = '_customer_user'");
}
