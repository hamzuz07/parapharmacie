<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wp-log-entry-controller.class.php';

/**
 * Log Entry Controller
 *
 * @class RP_SUB_Log_Entry_Controller
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Log_Entry_Controller extends RP_SUB_WP_Log_Entry_Controller
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

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Get object name
     *
     * @access public
     * @return string
     */
    public function get_object_name()
    {

        return 'log_entry';
    }

    /**
     * Get object class
     *
     * @access public
     * @return string
     */
    public function get_object_class()
    {

        return 'RP_SUB_Log_Entry';
    }

    /**
     * Get data store class
     *
     * @access public
     * @return string
     */
    public function get_data_store_class()
    {

        return 'RP_SUB_Log_Entry_Data_Store';
    }

    /**
     * Define custom taxonomies with terms
     *
     * @access public
     * @return array
     */
    public function define_taxonomies_with_terms()
    {

        /**
         * IMPORTANT:
         * If event type is related to scheduled action, then event type and scheduled action names must match
         * See RP_SUB_Scheduler::register_actions()
         */

        return array(

            // Event type
            'event_type' => array(

                // Taxonomy settings
                'singular'  => esc_html__('Event type', 'subscriptio'),
                'plural'    => esc_html__('Event types', 'subscriptio'),
                'all'       => esc_html__('All event types', 'subscriptio'),

                // Grouped terms
                'grouped_terms' => array(

                    'subscription' => array(
                        'label' => esc_html__('Subscription', 'subscriptio'),
                        'terms' => array(

                            // Other subscription changes
                            'new_subscription' => array(
                                'label' => esc_html__('New subscription', 'subscriptio'),
                            ),
                            'schedule_revision' => array(
                                'label' => esc_html__('Schedule revision', 'subscriptio'),
                            ),
                            'subscription_edit' => array(
                                'label' => esc_html__('Subscription edited', 'subscriptio'),
                            ),
                            'subscription_delete' => array(
                                'label' => esc_html__('Subscription deleted', 'subscriptio'),
                            ),

                            // Subscription status changes
                            'subscription_pause' => array(
                                'label' => esc_html__('Subscription pausing', 'subscriptio'),
                            ),
                            'subscription_resume' => array(
                                'label' => esc_html__('Subscription resuming', 'subscriptio'),
                            ),
                            'subscription_suspend' => array(
                                'label' => esc_html__('Subscription suspending', 'subscriptio'),
                            ),
                            'subscription_set_to_cancel' => array(
                                'label' => esc_html__('Set to cancel', 'subscriptio'),
                            ),
                            'subscription_cancel' => array(
                                'label' => esc_html__('Subscription cancelling', 'subscriptio'),
                            ),
                            'subscription_reactivate' => array(
                                'label' => esc_html__('Subscription reactivating', 'subscriptio'),
                            ),
                            'subscription_expire' => array(
                                'label' => esc_html__('Subscription expiring', 'subscriptio'),
                            ),
                        ),
                    ),

                    'payment' => array(
                        'label' => esc_html__('Payment', 'subscriptio'),
                        'terms' => array(

                            'renewal_payment' => array(
                                'label' => esc_html__('Payment due', 'subscriptio'),
                            ),
                            'payment_retry' => array(
                                'label' => esc_html__('Payment retry', 'subscriptio'),
                            ),
                            'payment_received' => array(
                                'label' => esc_html__('Payment received', 'subscriptio'),
                            ),
                        ),
                    ),

                    'order' => array(
                        'label' => esc_html__('Order', 'subscriptio'),
                        'terms' => array(

                            'initial_order' => array(
                                'label' => esc_html__('New initial order', 'subscriptio'),
                            ),
                            'renewal_order' => array(
                                'label' => esc_html__('New renewal order', 'subscriptio'),
                            ),
                            'order_cancel' => array(
                                'label' => esc_html__('Order cancelled', 'subscriptio'),
                            ),
                            'order_refund' => array(
                                'label' => esc_html__('Order refunded', 'subscriptio'),
                            ),
                            'order_delete' => array(
                                'label' => esc_html__('Order deleted', 'subscriptio'),
                            ),
                        ),
                    ),

                    'notification' => array(
                        'label' => esc_html__('Notifications', 'subscriptio'),
                        'terms' => array(

                            'payment_reminder' => array(
                                'label' => esc_html__('Payment reminder', 'subscriptio'),
                            ),
                        ),
                    ),

                    'errors' => array(
                        'label' => esc_html__('Other errors', 'subscriptio'),
                        'terms' => array(

                            'unexpected_error' => array(
                                'label' => esc_html__('Unexpected error', 'subscriptio'),
                            ),
                        ),
                    ),

                    'other' => array(
                        'label' => esc_html__('Other', 'subscriptio'),
                        'terms' => array(

                            'settings_update' => array(
                                'label' => esc_html__('Settings updated', 'subscriptio'),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }





}

RP_SUB_Log_Entry_Controller::get_instance();
