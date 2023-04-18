<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Get log entry
 *
 * @param int|RP_SUB_Log_Entry $identifier
 * @return object
 */
function subscriptio_get_log_entry($identifier)
{

    // Check if system is ready
    RP_SUB::ready_or_fail(__FUNCTION__);

    // Get log entry object
    $controller = RP_SUB_Log_Entry_Controller::get_instance();
    return $controller->get_object($identifier);
}

/**
 * Get log entries related to subscription
 *
 * @access public
 * @param RP_SUB_Subscription|int $subscription
 * @param array $args
 * @return array
 */
function subscriptio_get_log_entries_related_to_subscription($subscription, $args = array())
{

    // Check if system is ready
    RP_SUB::ready_or_fail(__FUNCTION__);

    $log_entries = array();

    // Get full list of args
    $args = wp_parse_args($args, array(
        'limit'     => -1,
        'return'    => 'objects',
    ));

    // Get all log entry ids for subscription
    $query = new WP_Query(array(
        'post_type'         => 'rp_sub_log_entry',
        'post_status'       => 'any',
        'posts_per_page'    => $args['limit'],
        'fields'        => 'ids',
        'meta_query'    => array(
            array(
                'key'       => 'subscription_id',
                'value'     => (is_a($subscription, 'RP_SUB_Subscription') ? $subscription->get_id() : absint($subscription)),
                'compare'   => '=',
            ),
        ),
    ));

    // Return query object
    if ($args['return'] === 'query') {
        return $query;
    }

    // Populate list
    foreach ($query->posts as $log_entry_id) {
        $log_entries[] = ($args['return'] === 'ids') ? $log_entry_id : subscriptio_get_log_entry($log_entry_id);
    }

    return $log_entries;
}
