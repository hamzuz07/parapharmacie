<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load WP_List_Table if not loaded yet
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Subscription related orders list
 *
 * @class RP_SUB_Subscription_Related_Orders_List
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Subscription_Related_Orders_List extends RightPress_WP_List_Table
{

    private $singular_name  = 'order';
    private $plural_name    = 'orders';

    /**
     * Prepare table list items
     *
     * @access public
     * @return void
     */
    public function prepare_items()
    {

        $this->items = subscriptio_get_orders_related_to_subscription($this->get_related_object());
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
            'related_order'         => esc_html__('Order', 'subscriptio'),
            'related_order_date'    => esc_html__('Date', 'subscriptio'),
            'related_order_type'    => esc_html__('Type', 'subscriptio'),
            'related_order_status'  => esc_html__('Status', 'subscriptio'),
            'related_order_total'   => esc_html__('Total', 'subscriptio'),
        );

        return $columns;
    }

    /**
     * Get column value
     *
     * @access protected
     * @param object $order
     * @param string $column_name
     * @return string
     */
    protected function column_default($order, $column_name)
    {

        switch ($column_name) {

            case 'related_order':

                // Get order number and customer full name
                $value = '<strong>#' . $order->get_order_number() . ' ' . $order->get_formatted_billing_full_name() . '</strong>';

                // Add link to order if it is not trashed
                if (RightPress_Help::post_is_active($order->get_id())) {
                    $value = '<a href="' . get_edit_post_link($order->get_id()) . '">' . $value . '</a>';
                }

                break;

            case 'related_order_date':

                $datetime   = new RP_SUB_DateTime($order->get_date_created(), $order->get_date_created()->getTimeZone());
                $value      = RP_SUB_Subscription_Admin::get_formatted_list_table_datetime($datetime);

                break;

            case 'related_order_type':

                // Get order type
                $value = subscriptio_is_subscription_renewal_order($order) ? esc_html__('Renewal order', 'subscriptio') : esc_html__('Initial order', 'subscriptio');

                // Format order type
                $value = '<span class="rp-sub-object-property-label"><span>' . $value . '</span></span>';

                break;

            case 'related_order_status':

                $value = sprintf('<mark class="order-status %s"><span>%s</span></mark>', esc_attr(sanitize_html_class('status-' . $order->get_status())), esc_html(wc_get_order_status_name($order->get_status())));

                break;

            case 'related_order_total':

                $value = wp_kses_post($order->get_formatted_order_total());

                break;

            default:
                $value = '';
                break;
        }

        return $value;
    }

    /**
     * No items found text
     *
     * @access public
     * @return void
     */
    public function no_items()
    {

        esc_html_e('There are no related orders yet.', 'subscriptio');
    }





}
