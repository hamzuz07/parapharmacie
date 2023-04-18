<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * DateTime Class
 *
 * Extends PHP DateTime class via RightPress_DateTime
 *
 * @class RP_SUB_DateTime
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_DateTime extends RightPress_DateTime
{


    /**
     * =================================================================================================================
     * FORMATTING HELPERS
     * =================================================================================================================
     */

    /**
     * Get date format
     *
     * @access public
     * @return string
     */
    public function get_date_format()
    {

        // Get date format
        $date_format = parent::get_date_format();

        // Allow developers to override and return
        return apply_filters('subscriptio_date_format', $date_format);
    }

    /**
     * Get time format
     *
     * @access public
     * @return string
     */
    public function get_time_format()
    {

        // Get time format
        $time_format = parent::get_time_format();

        // Allow developers to override and return
        return apply_filters('subscriptio_time_format', $time_format);
    }

    /**
     * Get datetime format
     *
     * @access public
     * @return string
     */
    public function get_datetime_format()
    {

        // Get datetime format
        $datetime_format = parent::get_datetime_format();

        // Allow developers to override and return
        return apply_filters('subscriptio_datetime_format', $datetime_format, $this->get_date_format(), $this->get_time_format());
    }





}
