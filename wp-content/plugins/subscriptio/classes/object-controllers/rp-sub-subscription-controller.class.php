<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wc-custom-order-object-controller.class.php';

/**
 * Subscription Controller
 *
 * Note: We use two types of objects for subscriptions:
 *  - RP_SUB_Suborder which is a custom WooCommerce order type and holds most of the data in a format
 *    that is easy to copy data from/to regular WooCommerce orders and lets us reuse WooCommerce order interface
 *  - RP_SUB_Subscription which is a wrapper to add our own functionality so that we can use method/property names
 *    without prefixes and don't fear that they will clash with those in WC_Order in the future
 *
 * @class RP_SUB_Subscription_Controller
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Subscription_Controller extends RP_SUB_WC_Custom_Order_Object_Controller
{

    // TBD: Shouldn't we have get_default_status() here?

    protected $subscription_statuses;

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

        return 'subscription';
    }

    /**
     * Get object class
     *
     * @access public
     * @return string
     */
    public function get_object_class()
    {

        return 'RP_SUB_Subscription';
    }

    /**
     * Get data store class
     *
     * @access public
     * @return string
     */
    public function get_data_store_class()
    {

        return 'RP_SUB_Subscription_Data_Store';
    }

    /**
     * Get order type
     *
     * @access public
     * @return string
     */
    public function get_order_type()
    {

        return 'rp_sub_subscription';
    }

    /**
     * Get status list
     *
     * @access public
     * @return array
     */
    public function get_status_list()
    {

        return RP_SUB_Subscription_Controller::get_subscription_statuses();
    }


    /**
     * =================================================================================================================
     * OTHER METHODS
     * =================================================================================================================
     */

    /**
     * Get subscription statuses
     *
     * Each status has the following elements:
     *  - label
     *  - label_count
     *  - system_change_to      List of statuses that subscription can be transitioned to from current status
     *  - admin_change_to       List of statuses that subscription can be manually transitioned to by admin from current status
     *  - customer_change_to    List of statuses that subscription can be manually transitioned to by customer from current status
     *  - is_admin_editable     Indicates whether admin can edit the subscription
     *  - gives_access          Indicates whether customer has access to subscriber's privileges, e.g. product downloads, member access etc.
     *
     * @access public
     * @return array
     */
    public static function get_subscription_statuses()
    {

        $instance = RP_SUB_Subscription_Controller::get_instance();

        // Define statuses
        if (!isset($instance->subscription_statuses)) {

            $instance->subscription_statuses = array(

                // Note: If making changes to 'system_change_to', 'admin_change_to' or 'customer_change_to' check this method:
                // RP_SUB_Subscription::can_have_status_changed_to(),

                // Pending payment
                'pending' => array(
                    'label'                 => esc_html_x('Pending', 'Subscription status', 'subscriptio'),
                    'label_count'           => _n_noop('Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'subscriptio'),
                    'system_change_to'      => array('trial', 'active', 'cancelled'),
                    'admin_change_to'       => array('cancelled'),
                    'customer_change_to'    => array('cancelled'), // Note: Can be disabled in plugin settings
                    'is_admin_editable'     => true,
                    'gives_access'          => false,
                ),

                // Trial
                'trial' => array(
                    'label'                 => esc_html_x('Trial', 'Subscription status', 'subscriptio'),
                    'label_count'           => _n_noop('Trial <span class="count">(%s)</span>', 'Trial <span class="count">(%s)</span>', 'subscriptio'),
                    'system_change_to'      => array('active', 'paused', 'overdue', 'suspended', 'set-to-cancel', 'cancelled', 'expired'),
                    'admin_change_to'       => array('paused', 'set-to-cancel', 'cancelled'),
                    'customer_change_to'    => array('paused', 'set-to-cancel', 'cancelled'), // Note: Can be disabled in plugin settings
                    'is_admin_editable'     => true,
                    'gives_access'          => true,
                ),

                // Active
                'active' => array(
                    'label'                 => esc_html_x('Active', 'Subscription status', 'subscriptio'),
                    'label_count'           => _n_noop('Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'subscriptio'),
                    'system_change_to'      => array('paused', 'overdue', 'suspended', 'set-to-cancel', 'cancelled', 'expired'),
                    'admin_change_to'       => array('paused', 'set-to-cancel', 'cancelled'),
                    'customer_change_to'    => array('paused', 'set-to-cancel', 'cancelled'), // Note: Can be disabled in plugin settings
                    'is_admin_editable'     => true,
                    'gives_access'          => true,
                ),

                // Paused by administrator or customer
                'paused' => array(
                    'label'                 => esc_html_x('Paused', 'Subscription status', 'subscriptio'),
                    'label_count'           => _n_noop('Paused <span class="count">(%s)</span>', 'Paused <span class="count">(%s)</span>', 'subscriptio'),
                    'system_change_to'      => array('trial', 'active', 'overdue', 'suspended', 'cancelled'), // Note: Can be changed to cancelled and one of the remaining values (overriden dynamically)
                    'admin_change_to'       => array('trial', 'active', 'overdue', 'suspended'), // Note: Can be changed to one of these values (overriden dynamically)
                    'customer_change_to'    => array('trial', 'active', 'overdue', 'suspended'), // Note: Can be changed to one of these values (overriden dynamically); can be disabled in plugin settings
                    'is_admin_editable'     => true,
                    'gives_access'          => false,
                ),

                // Overdue payment but subscription is still active before it is suspended or cancelled
                'overdue' => array(
                    'label'                 => esc_html_x('Overdue', 'Subscription status', 'subscriptio'),
                    'label_count'           => _n_noop('Overdue <span class="count">(%s)</span>', 'Overdue <span class="count">(%s)</span>', 'subscriptio'),
                    'system_change_to'      => array('active', 'paused', 'suspended', 'cancelled', 'expired'),
                    'admin_change_to'       => array('paused', 'cancelled'),
                    'customer_change_to'    => array('paused', 'cancelled'), // Note: Can be disabled in plugin settings
                    'is_admin_editable'     => true,
                    'gives_access'          => true,
                ),

                // Suspended - payment overdue
                'suspended' => array(
                    'label'                 => esc_html_x('Suspended', 'Subscription status', 'subscriptio'),
                    'label_count'           => _n_noop('Suspended <span class="count">(%s)</span>', 'Suspended <span class="count">(%s)</span>', 'subscriptio'),
                    'system_change_to'      => array('active', 'paused', 'cancelled', 'expired'),
                    'admin_change_to'       => array('paused', 'cancelled'),
                    'customer_change_to'    => array('paused', 'cancelled'), // Note: Can be disabled in plugin settings
                    'is_admin_editable'     => true,
                    'gives_access'          => false,
                ),

                // Set to cancel - subscription cancellation requested but subscription is paid (or in trial) for some time in the future and should remain non-cancelled until then
                'set-to-cancel' => array(
                    'label'                 => esc_html_x('Set to cancel', 'Subscription status', 'subscriptio'),
                    'label_count'           => _n_noop('Set to cancel <span class="count">(%s)</span>', 'Set to cancel <span class="count">(%s)</span>', 'subscriptio'),
                    'system_change_to'      => array('trial', 'active', 'cancelled', 'expired'),
                    'admin_change_to'       => array('trial', 'active', 'cancelled'),
                    'customer_change_to'    => array('trial', 'active'), // Note: Can be disabled in plugin settings
                    'is_admin_editable'     => false,
                    'gives_access'          => true,
                ),

                // Cancelled due to no payment or manually
                'cancelled' => array(
                    'label'                 => esc_html_x('Cancelled', 'Subscription status', 'subscriptio'),
                    'label_count'           => _n_noop('Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'subscriptio'),
                    'system_change_to'      => array(), // Note: System is not designed to reactivate cancelled subscriptions, do not add any values to these arrays
                    'admin_change_to'       => array(),
                    'customer_change_to'    => array(),
                    'is_admin_editable'     => false,
                    'gives_access'          => false,
                ),

                // Expired - subscription lifespan limit reached
                'expired' => array(
                    'label'                 => esc_html_x('Expired', 'Subscription status', 'subscriptio'),
                    'label_count'           => _n_noop('Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'subscriptio'),
                    'system_change_to'      => array(), // Note: System is not designed to reactivate expired subscriptions, do not add any values to these arrays
                    'admin_change_to'       => array(),
                    'customer_change_to'    => array(),
                    'is_admin_editable'     => false,
                    'gives_access'          => false,
                ),
            );
        }

        // Return statuses
        return $instance->subscription_statuses;
    }

    /**
     * Get subscription status label
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @param string $status
     * @return string
     */
    public static function get_subscription_status_label($subscription, $status = null)
    {

        // Get all statuses
        $statuses = RP_SUB_Subscription_Controller::get_subscription_statuses();

        // Get subscription status if custom status was not provided
        if ($status === null) {
            $status = $subscription->get_status();
        }

        // Get status label
        if (in_array($status, array('draft', 'auto-draft'), true)) {
            $status_label = esc_html__('Draft', 'subscriptio');
        }
        else if (isset($statuses[$status])) {
            $status_label = $statuses[$status]['label'];
        }
        else {
            $status_label = $status;
        }

        // Return status label
        return (string) apply_filters('subscriptio_subscription_status_label', $status_label, $subscription);
    }




}

RP_SUB_Subscription_Controller::get_instance();
