<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WooCommerce Custom Order
 *
 * @class RP_SUB_WC_Custom_Order
 * @package Subscriptio
 * @author RightPress
 */
abstract class RP_SUB_WC_Custom_Order extends RightPress_WC_Custom_Order
{

    /**
     * Constructor
     *
     * @access public
     * @param mixed $order
     * @return void
     */
    public function __construct($order = 0)
    {

        // Call parent constructor
        parent::__construct($order);
    }





}
