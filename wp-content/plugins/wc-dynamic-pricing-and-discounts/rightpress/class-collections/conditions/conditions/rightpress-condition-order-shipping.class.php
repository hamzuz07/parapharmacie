<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rightpress-condition.class.php';

/**
 * Condition Group: Order Shipping
 *
 * @class RightPress_Condition_Order_Shipping
 * @package RightPress
 * @author RightPress
 */
abstract class RightPress_Condition_Order_Shipping extends RightPress_Condition
{

    protected $group_key        = 'order_shipping';
    protected $group_position   = 430;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        parent::__construct();

        $this->hook_group();
    }

    /**
     * Get group label
     *
     * @access public
     * @return string
     */
    public function get_group_label()
    {

        return esc_html__('Shipping', 'rightpress');
    }





}