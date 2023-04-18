<?php

/**
 * Plugin Name: WooCommerce PDF Invoice
 * Plugin URI: http://www.rightpress.net/woocommerce-pdf-invoice
 * Description: Generate professional PDF invoices for WooCommerce orders
 * Author: RightPress
 * Author URI: http://www.rightpress.net
 *
 * Text Domain: woo_pdf
 * Domain Path: /languages
 *
 * Version: 3.2.1
 *
 * Requires at least: 4.0
 * Tested up to: 5.4
 *
 * WC requires at least: 3.0
 * WC tested up to: 4.3
 *
 * @package WooCommerce PDF Invoice
 * @category Core
 * @author RightPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define Constants
define('WOOPDF_PLUGIN_KEY', 'woocommerce-pdf-invoice');
define('WOOPDF_PLUGIN_PUBLIC_PREFIX', 'woo_pdf_');
define('WOOPDF_PLUGIN_PRIVATE_PREFIX', 'woo_pdf_');
define('WOOPDF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WOOPDF_PLUGIN_URL', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)));
define('WOOPDF_SUPPORT_PHP', '5.3');
define('WOOPDF_SUPPORT_WP', '4.0');
define('WOOPDF_SUPPORT_WC', '3.0');
define('WOOPDF_VERSION', '3.2.1');

// Load main plugin class
require_once 'woopdf.class.php';

// Initialize automatic updates
require_once(plugin_dir_path(__FILE__) . 'rightpress-updates/rightpress-updates.class.php');
RightPress_Updates_5951088::init(__FILE__, WOOPDF_VERSION);
