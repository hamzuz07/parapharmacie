<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Get subscription
 *
 * @param int|RP_SUB_Subscription $identifier
 * @param int $customer_id
 * @return RP_SUB_Subscription|false
 */
function subscriptio_get_subscription($identifier, $customer_id = null)
{

    // Check if system is ready
    RP_SUB::ready_or_fail(__FUNCTION__);

    // Get subscription object
    $subscription = RP_SUB_Subscription_Controller::get_instance()->get_object($identifier);

    // Optionally check subscription ownership
    if (isset($customer_id) && $subscription->get_customer_id() !== $customer_id) {
        return false;
    }

    // Return subscription object
    return $subscription;
}

/**
 * Get subscriptions
 *
 * List of accepted arguments:
 *   customer               WC_Customer|int     default null    get only subscriptions belonging to specific customer
 *   order                  WC_Order|int        default null    get only subscriptions that are related to specific order
 *   statuses               array               default null    get only subscriptions that have specific statuses
 *   include_terminated     bool                default true    whether to include terminated subscriptions or not
 *
 * @param array $args
 * @return RP_SUB_Subscription[]
 */
function subscriptio_get_subscriptions($args = array())
{

    $subscriptions = array();

    // Statuses
    $statuses = !empty($args['statuses']) ? RightPress_Help::prefix((array) $args['statuses'], 'wc-') : RP_SUB_Suborder_Controller::get_wc_valid_order_statuses();

    // Format query args
    $query_args = array(
        'type'      => 'rp_sub_subscription',
        'status'    => $statuses,
        'return'    => 'ids',
        'limit'     => -1,
    );

    // Customer
    if (!empty($args['customer'])) {

        if (is_a($args['customer'], 'WC_Customer')) {
            $query_args['customer_id'] = $args['customer']->get_id();
        }
        else if (is_numeric($args['customer'])) {
            $query_args['customer_id'] = absint($args['customer']);
        }
        else {
            return $subscriptions;
        }
    }

    // Order
    if (!empty($args['order'])) {

        if (is_a($args['order'], 'WC_Order')) {
            $query_args['rp_sub_related_order'] = $args['order']->get_id();
        }
        else if (is_numeric($args['order'])) {
            $query_args['rp_sub_related_order'] = absint($args['order']);
        }
        else {
            return $subscriptions;
        }
    }

    // Get suborder ids
    $suborder_ids = wc_get_orders($query_args);

    // Load subscriptions from suborder ids
    foreach ($suborder_ids as $suborder_id) {

        // Load subscription
        $subscription = subscriptio_get_subscription($suborder_id);

        // Maybe skip terminated subscriptions
        if (isset($args['include_terminated']) && $args['include_terminated'] === false && $subscription->is_terminated()) {
            continue;
        }

        // Add subscription to subscriptions array
        $subscriptions[$suborder_id] = $subscription;
    }

    // Return subscriptions
    return $subscriptions;
}

/**
 * Get subscriptions related to order
 *
 * @access public
 * @param WC_Order|int $order
 * @return RP_SUB_Subscription[]
 */
function subscriptio_get_subscriptions_related_to_order($order)
{

    return subscriptio_get_subscriptions(array(
        'order' => $order,
    ));
}

/**
 * Get subscription related to order
 *
 * Simply gets subscriptions related to order and returns the first one in list
 * Callers expect single related order to exist when calling this method
 *
 * @access public
 * @param WC_Order|int $order
 * @return RP_SUB_Subscription|false
 */
function subscriptio_get_subscription_related_to_order($order)
{

    $subscription = false;

    if ($subscriptions = subscriptio_get_subscriptions_related_to_order($order)) {
        $subscription = array_shift($subscriptions);
    }

    return $subscription;
}

/**
 * Create new subscription programmatically
 *
 * Throws RightPress_Exception in case of an error
 *
 * @param array $args
 * @return RP_SUB_Subscription
 */
function subscriptio_create_subscription($args)
{

    // Check if system is ready
    RP_SUB::ready_or_fail(__FUNCTION__);

    // Allow developers to change $args
    $args = apply_filters('subscriptio_create_subscription_args', $args);

    // Define supported suborder args
    $suborder_args = array(

        'currency',
        'prices_include_tax',
        'status',
        'customer_id',
        'customer_note',
        'customer_ip_address',
        'customer_user_agent',
        'payment_method',
        'created_via',
    );

    // Define supported subscription args
    $subscription_args = array(

        'billing_cycle',
        'free_trial',
        'lifespan',
    );

    // Create new subscription
    $controller = RP_SUB_Subscription_Controller::get_instance();
    $subscription = $controller->create_new_object();

    // Set subscription args
    foreach ($subscription_args as $key) {
        if (isset($args[$key])) {
            $subscription->{'set_' . $key}($args[$key]);
        }
    }

    // Reference suborder object
    $suborder = $subscription->get_suborder();

    // Set suborder args
    foreach ($suborder_args as $key) {
        if (isset($args[$key])) {
            $suborder->{'set_' . $key}($args[$key]);
        }
    }

    // Save subscription
    $subscription->save();

    // Return subscription
    return $subscription;
}
