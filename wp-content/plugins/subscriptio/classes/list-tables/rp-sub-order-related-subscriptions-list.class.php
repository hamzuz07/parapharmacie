<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Order related subscriptions list
 *
 * @class RP_SUB_Order_Related_Subscriptions_List
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Order_Related_Subscriptions_List extends RightPress_WP_List_Table
{

    private $singular_name  = 'subscription';
    private $plural_name    = 'subscriptions';

    /**
     * Prepare table list items
     *
     * @access public
     * @return void
     */
    public function prepare_items()
    {

        $this->items = subscriptio_get_subscriptions_related_to_order($this->get_related_object());
    }

    /**
     * Get columns
     *
     * @access public
     * @return array
     */
    public function get_columns()
    {

        $columns = array(
            'related_subscription_subscription'     => esc_html__('Subscription', 'subscriptio'),
            'related_subscription_first_payment'    => esc_html__('Started', 'subscriptio'),
            'related_subscription_status'           => esc_html__('Status', 'subscriptio'),
            'related_subscription_recurring_total'  => esc_html__('Recurring', 'subscriptio'),
        );

        return $columns;
    }

    /**
     * Get column value
     *
     * @access protected
     * @param object $subscription
     * @param string $column_name
     * @return string
     */
    protected function column_default($subscription, $column_name)
    {

        // Format method name
        $method = 'get_subscription_list_column_value_' . str_replace('related_subscription_', '', $column_name);

        // Return column value
        return RP_SUB_Subscription_Admin::$method($subscription);
    }





}
