<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wp-log-entry-data-store.class.php';

/**
 * Log Entry Data Store
 *
 * @class RP_SUB_Log_Entry_Data_Store
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Log_Entry_Data_Store extends RP_SUB_WP_Log_Entry_Data_Store
{

    /**
     * Get post type
     *
     * @access public
     * @return string
     */
    public function get_post_type()
    {

        return 'rp_sub_log_entry';
    }

    /**
     * Get capability type
     *
     * @access public
     * @return string
     */
    public function get_capability_type()
    {

        return array('rp_sub_log_entry', 'rp_sub_log_entries');
    }





}
