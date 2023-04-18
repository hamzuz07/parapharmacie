<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Plugin specific methods used by multiple classes
 *
 * @class RP_SUB_Help
 * @package Subscriptio
 * @author RightPress
 */

class RP_SUB_Help
{

    /**
     * Include template
     *
     * @access public
     * @param string $template
     * @param array $args
     * @return string
     */
    public static function include_template($template, $args = array())
    {

        RightPress_Help::include_template($template, RP_SUB_PLUGIN_PATH, 'subscriptio', $args);
    }





}
