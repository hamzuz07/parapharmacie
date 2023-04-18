<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wc-custom-order.class.php';

/**
 * Suborder
 *
 * Note: We use two types of objects for subscriptions:
 *  - RP_SUB_Suborder which is a custom WooCommerce order type and holds most of the data in a format
 *    that is easy to copy data from/to regular WooCommerce orders and lets us reuse WooCommerce order interface
 *  - RP_SUB_Subscription which is a wrapper to add our own functionality so that we can use method/property names
 *    without prefixes and don't fear that they will clash with those in WC_Order in the future
 *
 * @class RP_SUB_Suborder
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Suborder extends RP_SUB_WC_Custom_Order
{

    // Define custom data store
    protected $data_store_name = 'rp-sub-subscription';

    // Define custom object type
    protected $object_type = 'rp_sub_subscription';

    // Define custom properties
    protected $extra_data = array();

    /**
     * Constructor
     *
     * @access public
     * @param mixed $suborder
     * @return void
     */
    public function __construct($suborder = 0)
    {

        // Call parent constructor
        parent::__construct($suborder);
    }

    /**
     * Get internal type
     *
     * @access public
     * @return string
     */
    public function get_type()
    {

        return 'rp_sub_subscription';
    }

    /**
     * Override valid statuses
     *
     * @access public
     * @return array
     */
    public function get_valid_statuses()
    {

        return RP_SUB_Suborder_Controller::get_wc_valid_order_statuses();
    }

    /**
     * Handle the status transition
     *
     * @access protected
     * @return void
     */
    protected function status_transition()
    {

        $this->status_transition = false;
    }


    /**
     * =================================================================================================================
     * OVERRIDING REFUNDS FUNCTIONALITY
     * =================================================================================================================
     */

    /**
     * Get order refunds
     *
     * @access public
     * @return array
     */
    public function get_refunds()
    {

        return array();
    }

    /**
     * Get amount already refunded
     *
     * @access public
     * @return string
     */
    public function get_total_refunded()
    {

        return 0;
    }

    /**
     * Get the total tax refunded
     *
     * @access public
     * @return float
     */
    public function get_total_tax_refunded()
    {

        return 0;
    }

    /**
     * Get the total shipping refunded
     *
     * @access public
     * @return float
     */
    public function get_total_shipping_refunded()
    {

        return 0;
    }

    /**
     * Gets the count of order items of a certain type that have been refunded
     *
     * @access public
     * @param string $item_type
     * @return string
     */
    public function get_item_count_refunded($item_type = '')
    {

        return 0;
    }

    /**
     * Get the total number of items refunded
     *
     * @access public
     * @param string $item_type
     * @return int
     */
    public function get_total_qty_refunded($item_type = 'line_item')
    {

        return 0;
    }

    /**
     * Get the refunded amount for a line item
     *
     * @access public
     * @param int $item_id
     * @param string $item_type
     * @return int
     */
    public function get_qty_refunded_for_item($item_id, $item_type = 'line_item')
    {

        return 0;
    }

    /**
     * Get the refunded amount for a line item
     *
     * @access public
     * @param int $item_id
     * @param string $item_type
     * @return int
     */
    public function get_total_refunded_for_item($item_id, $item_type = 'line_item')
    {

        return 0;
    }

    /**
     * Get the refunded tax amount for a line item
     *
     * @access public
     * @param int $item_id
     * @param int $tax_id
     * @param string $item_type
     * @return int
     */
    public function get_tax_refunded_for_item($item_id, $tax_id, $item_type = 'line_item')
    {

        return 0;
    }

    /**
     * Get total tax refunded by rate ID
     *
     * @access public
     * @param int $rate_id
     * @return int
     */
    public function get_total_tax_refunded_by_rate_id($rate_id)
    {

        return 0;
    }





}
