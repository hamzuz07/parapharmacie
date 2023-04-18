<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wp-log-entry.class.php';

/**
 * Log Entry
 *
 * @class RP_SUB_Log_Entry
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Log_Entry extends RP_SUB_WP_Log_Entry
{

    // Define properties
    protected $data = array(
        'subscription_id'   => null,
        'order_id'          => null,
        'actor_id'          => null,        // ID of user who performed the action; value is null in case action was performed by system
        'event_type'        => null,
        'notes'             => array(),
        'error_details'     => null,
    );

    /**
     * Constructor
     *
     * @access public
     * @param mixed $object
     * @param object $data_store
     * @param object $controller
     * @return void
     */
    public function __construct($object, $data_store, $controller)
    {

        // Call parent constructor
        parent::__construct($object, $data_store, $controller);
    }


    /**
     * =================================================================================================================
     * GETTERS
     * =================================================================================================================
     */

    /**
     * Get subscription id
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return int
     */
    public function get_subscription_id($context = 'view', $args = array())
    {

        return $this->get_property('subscription_id', $context, $args);
    }

    /**
     * Get order id
     *
     * @access public
     * @param string $context
     * @param array $args
     * @return int
     */
    public function get_order_id($context = 'view', $args = array())
    {

        return $this->get_property('order_id', $context, $args);
    }

    /**
     * =================================================================================================================
     * SETTERS
     * =================================================================================================================
     */

    /**
     * Set subscription id
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return void
     */
    public function set_subscription_id($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_subscription_id($value);

        // Set property
        $this->set_property('subscription_id', $value);
    }

    /**
     * Set order id
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return void
     */
    public function set_order_id($value)
    {

        // Sanitize and validate value
        $value = $this->sanitize_order_id($value);

        // Set property
        $this->set_property('order_id', $value);
    }


    /**
     * =================================================================================================================
     * SANITIZERS - VALIDATORS
     * =================================================================================================================
     */

    /**
     * Sanitize and validate subscription id
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return string
     */
    public function sanitize_subscription_id($value)
    {

        // Sanitize integer
        $value = $this->sanitize_int($value, 'subscription_id');

        // Return sanitized value
        return $value;
    }

    /**
     * Sanitize and validate order id
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param int $value
     * @return string
     */
    public function sanitize_order_id($value)
    {

        // Sanitize integer
        $value = $this->sanitize_int($value, 'order_id');

        // Return sanitized value
        return $value;
    }


    /**
     * =================================================================================================================
     * LOGGING METHODS
     * =================================================================================================================
     */

    /**
     * Add subscription id
     *
     * Sets subscription id and saves object
     *
     * @ccess public
     * @param int $subscription_id
     * @return void
     */
    public function add_subscription_id($subscription_id)
    {

        $this->set_subscription_id($subscription_id);
        $this->save();
    }

    /**
     * Add order id
     *
     * Sets order id and saves object
     *
     * @ccess public
     * @param int $order_id
     * @return void
     */
    public function add_order_id($order_id)
    {

        $this->set_order_id($order_id);
        $this->save();
    }


    /**
     * =================================================================================================================
     * OTHER METHODS
     * =================================================================================================================
     */





}
