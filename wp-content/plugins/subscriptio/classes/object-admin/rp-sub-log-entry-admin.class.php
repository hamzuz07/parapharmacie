<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wp-log-entry-admin.class.php';

/**
 * Log Entry Admin
 *
 * @class RP_SUB_Log_Entry_Admin
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Log_Entry_Admin extends RP_SUB_WP_Log_Entry_Admin
{

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Add submenu item
        add_filter('rp_sub_menu_items', function($items) {
            return array_merge($items, array('edit.php?post_type=' . $this->get_post_type()));
        }, 100);

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Get controller class
     *
     * @access public
     * @return string
     */
    public function get_controller_class()
    {

        return 'RP_SUB_Log_Entry_Controller';
    }


    /**
     * =================================================================================================================
     * ADMIN OBJECT LIST
     * =================================================================================================================
     */

    /**
     * Register list columns
     *
     * @access public
     * @return array
     */
    public function register_list_columns()
    {

        return array(
            'created'       => esc_html__('Date', 'subscriptio'),
            'event_type'    => esc_html__('Event Type', 'subscriptio'),
            'status'        => esc_html__('Status', 'subscriptio'),
            'subscription'  => esc_html__('Subscription', 'subscriptio'),
            'order'         => esc_html__('Order', 'subscriptio'),
            'details'       => esc_html__('Details', 'subscriptio'),
        );
    }

    /**
     * Print column value
     *
     * @access public
     * @param array $column
     * @param int $post_id
     * @return void
     */
    public function print_column_value($column, $post_id)
    {

        // Get object for list table
        if ($object = $this->get_object_for_view($post_id)) {

            switch ($column) {

                // Created
                case 'created':
                    echo $object->get_created()->format_datetime() . '<br>(' . sprintf(esc_html__('%s ago', 'subscriptio'), human_time_diff($object->get_created()->format('U'))) . ')';
                    break;

                // Event Type
                case 'event_type':
                    echo '<span class="rp-sub-object-property-label"><span>' . $object->get_event_type_label() . '</span></span>';
                    break;

                // Status
                case 'status':
                    echo '<mark class="rp-sub-status-label rp-sub-status-label-' . $object->get_status() . '"><span>' . $object->get_status_label() . '</span></mark>';
                    break;

                // Subscription
                case 'subscription':

                    if ($subscription_id = $object->get_subscription_id()) {

                        // Format value
                        $value = '#' . $subscription_id;

                        // Add link to subscription if it still exists
                        if (RightPress_Help::post_is_active($subscription_id)) {
                            $value = '<a href="' . get_edit_post_link($subscription_id) . '">' . $value . '</a>';
                        }

                        // Print value
                        echo $value;
                    }

                    break;

                // Order
                case 'order':

                    if ($order_id = $object->get_order_id()) {

                        // Format value
                        $value = '#' . $order_id;

                        // Add link to order if it still exists
                        // WC31: Orders will no longer be posts
                        if (RightPress_Help::post_is_active($order_id)) {
                            $value = '<a href="' . get_edit_post_link($order_id) . '">' . $value . '</a>';
                        }

                        // Print value
                        echo $value;
                    }

                    break;

                // Details
                case 'details':

                    // Get notes
                    $notes = $object->get_notes();

                    // Add error details as last note
                    if ($error_details = $object->get_error_details()) {

                        $html = '<a href="#" class="rp-sub-log-entry-error-details-show">' . esc_html__('Show Error Details', 'subscriptio') . '</a>';
                        $html .= '<a href="#" class="rp-sub-log-entry-error-details-hide" style="display: none;">' . esc_html__('Hide Error Details', 'subscriptio') . '</a>';
                        $html .= '<div class="rp-sub-log-entry-error-details" style="display: none; overflow: scroll;"><pre>' . $error_details . '</pre></div>';
                        $notes[] = $html;
                    }

                    // Add empty note to normalize row heights
                    if (empty($notes)) {
                        $notes[] = '&nbsp;';
                    }

                    // Print list
                    echo RightPress_Help::array_to_html_list($notes);

                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Customize post list query
     *
     * @access public
     * @param object $query
     * @return void
     */
    public function customize_post_list_query(&$query)
    {

        // Call parent method
        parent::customize_post_list_query($query);

        // Reference query vars
        $query_vars = &$query->query_vars;

        // Filtering by subscription id
        if (!empty($_GET['subscription_id'])) {

            if (!isset($query_vars['meta_query']) || !is_array($query_vars['meta_query'])) {
                $query_vars['meta_query'] = array();
            }

            $query_vars['meta_query'][] = array(
                'field'     => 'subscription_id',
                'value'     => absint($_GET['subscription_id']),
                'compare'   => '=',
            );
        }
    }

    /**
     * Get post search meta fields
     *
     * @access public
     * @return array
     */
    public function get_post_search_meta_fields()
    {

        return array(
            'subscription_id',
            'order_id',
        );
    }

    /**
     * Get post search contexts
     *
     * @access public
     * @return array
     */
    public function get_post_search_contexts()
    {

        return array(
            'id'            => 'ID',
            'ID'            => 'ID',
            'subscription'  => 'subscription_id',
            'order'         => 'order_id',
        );
    }





}

RP_SUB_Log_Entry_Admin::get_instance();
