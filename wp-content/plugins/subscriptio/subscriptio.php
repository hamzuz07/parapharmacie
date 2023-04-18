<?php

/**
 * Plugin Name: Subscriptio
 * Plugin URI: http://www.rightpress.net/subscriptio
 * Description: WooCommerce Subscriptions & Recurring Payments
 * Author: RightPress
 * Author URI: http://www.rightpress.net
 *
 * Text Domain: subscriptio
 * Domain Path: /languages
 *
 * Version: 3.0.7
 *
 * Requires at least: 4.9
 * Tested up to: 5.8
 *
 * WC requires at least: 3.5
 * WC tested up to: 5.7
 *
 * @package Subscriptio
 * @category Core
 * @author RightPress
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Define Constants
define('RP_SUB_PLUGIN_KEY', 'subscriptio');
define('RP_SUB_PLUGIN_PUBLIC_PREFIX', 'subscriptio_');
define('RP_SUB_PLUGIN_PRIVATE_PREFIX', 'rp_sub_');
define('RP_SUB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RP_SUB_PLUGIN_URL', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)));
define('RP_SUB_ADMIN_CAPABILITY', 'manage_subscriptio');
define('RP_SUB_SUPPORT_PHP', '5.6');
define('RP_SUB_SUPPORT_WP', '4.9');
define('RP_SUB_SUPPORT_WC', '3.5');
define('RP_SUB_VERSION', '3.0.7');

global $wpdb;

// Load old version on existing installations
if (get_option('subscriptio_options') || $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type='sub_transaction'") || ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type='subscription'") && $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key='price_time_unit'"))) {
    require_once '_old_version/subscriptio.class.php';
}
// Load current version
else {
    require_once 'rp_sub.class.php';
}

// Initialize automatic updates
require_once plugin_dir_path(__FILE__) . 'rightpress-updates/rightpress-updates.class.php';
RightPress_Updates_8754068::init(__FILE__, RP_SUB_VERSION);
