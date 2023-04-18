<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WordPress Custom Post Object Class
 *
 * @class RP_SUB_WP_Custom_Post_Object
 * @package Subscriptio
 * @author RightPress
 */
abstract class RP_SUB_WP_Custom_Post_Object extends RightPress_WP_Custom_Post_Object
{

    // DateTime class to use
    protected $datetime_class = 'RP_SUB_DateTime';

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
     * Reset plugin version
     *
     * @access public
     * @return void
     */
    public function reset_plugin_version()
    {

        $this->set_plugin_version(RP_SUB_VERSION);
    }





}
