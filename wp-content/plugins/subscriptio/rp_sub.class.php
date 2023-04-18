<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Main plugin class
 *
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB
{

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    /**
     * Class constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Load translation
        load_textdomain('subscriptio', WP_LANG_DIR . '/' . RP_SUB_PLUGIN_KEY . '/subscriptio-' . apply_filters('plugin_locale', get_locale(), 'subscriptio') . '.mo');
        load_textdomain('rightpress', WP_LANG_DIR . '/' . RP_SUB_PLUGIN_KEY . '/rightpress-' . apply_filters('plugin_locale', get_locale(), 'rightpress') . '.mo');
        load_plugin_textdomain('subscriptio', false, (RP_SUB_PLUGIN_KEY . '/languages/'));
        load_plugin_textdomain('rightpress', false, (RP_SUB_PLUGIN_KEY . '/languages/'));

        // Add plugins page links
        add_filter('plugin_action_links_' . (RP_SUB_PLUGIN_KEY . '/' . RP_SUB_PLUGIN_KEY . '.php'), array($this, 'plugins_page_links'));

        // Continue setup when all plugins are loaded
        add_action('plugins_loaded', array($this, 'on_plugins_loaded'), 1);

        // Load helper loader class
        require_once RP_SUB_PLUGIN_PATH . 'rightpress/rightpress-loader.class.php';
    }

    /**
     * Continue setup when all plugins are loaded
     *
     * @access public
     * @return void
     */
    public function on_plugins_loaded()
    {

        // Load base classes
        RightPress_Loader::load();

        // Check if environment meets requirements
        if (!self::check_environment()) {
            return;
        }

        // Load components
        RightPress_Loader::load_component(array(
            'rightpress-product-price',
            'rightpress-settings-component',
        ));

        // Load class collections
        RightPress_Loader::load_class_collection('object-control');

        // Load objects
        require_once RP_SUB_PLUGIN_PATH . 'classes/objects/rp-sub-log-entry.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/objects/rp-sub-suborder.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/objects/rp-sub-subscription.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/objects/rp-sub-subscription-product.class.php';

        // Load object data stores
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-data-stores/rp-sub-log-entry-data-store.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-data-stores/rp-sub-suborder-data-store.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-data-stores/rp-sub-subscription-data-store.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-data-stores/rp-sub-subscription-product-data-store.class.php';

        // Load object controllers
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-controllers/rp-sub-log-entry-controller.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-controllers/rp-sub-suborder-controller.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-controllers/rp-sub-subscription-controller.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-controllers/rp-sub-subscription-product-controller.class.php';

        // Load object admin classes
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-admin/rp-sub-log-entry-admin.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-admin/rp-sub-suborder-admin.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-admin/rp-sub-subscription-admin.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/object-admin/rp-sub-subscription-product-admin.class.php';

        // Load list table classes
        require_once RP_SUB_PLUGIN_PATH . 'classes/list-tables/rp-sub-order-related-subscriptions-list.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/list-tables/rp-sub-subscription-related-orders-list.class.php';

        // Load other classes
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-assets.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-controller.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-customer.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-download-controller.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-datetime.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-help.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-legacy.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-mailer.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-main-site-controller.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-payment-controller.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-pricing.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-recurring-carts.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-scheduler.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-time.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-wc-account.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-wc-cart.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-wc-checkout.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-wc-meta-box-order-data.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-wc-order.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-wc-product.class.php';

        // These classes must always be loaded after other classes are loaded
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-data-updater.class.php';
        require_once RP_SUB_PLUGIN_PATH . 'classes/rp-sub-settings.class.php';

        // Load includes
        require_once RP_SUB_PLUGIN_PATH . 'includes/customer-functions.php';
        require_once RP_SUB_PLUGIN_PATH . 'includes/log-entry-functions.php';
        require_once RP_SUB_PLUGIN_PATH . 'includes/order-functions.php';
        require_once RP_SUB_PLUGIN_PATH . 'includes/subscription-functions.php';
        require_once RP_SUB_PLUGIN_PATH . 'includes/subscription-product-functions.php';

        // Load integrations
        require_once RP_SUB_PLUGIN_PATH . 'integrations/rp-sub-integration-rp-mem.class.php';
    }

    /**
     * Checks if plugin is ready to use, throws exception if not
     *
     * @access public
     * @param string $function
     * @return bool
     */
    public static function ready_or_fail($function)
    {

        // Not ready
        if (!did_action('rightpress_init') || doing_action('rightpress_init')) {
            throw new Exception("Function $function can only be called after WordPress init action position 9.");
        }

        // Ready
        return true;
    }

    /**
     * Check if current user has administrative capability
     *
     * @access public
     * @return bool
     */
    public static function is_admin()
    {

        return current_user_can(self::get_admin_capability());
    }

    /**
     * Get administrative capability
     *
     * @access public
     * @return string
     */
    public static function get_admin_capability()
    {

        return apply_filters('subscriptio_capability', RP_SUB_ADMIN_CAPABILITY);
    }

    /**
     * Add plugins
     *
     * @access public
     * @param array $links
     * @return void
     */
    public function plugins_page_links($links)
    {

        // Support
        $link = '<a href="http://url.rightpress.net/8754068-support">' . esc_html__('Support', 'subscriptio') . '</a>';
        array_unshift($links, $link);

        // Settings
        if (self::check_environment()) {
            $link = '<a href="edit.php?post_type=rp_sub_subscription&page=rp_sub_settings">' . esc_html__('Settings', 'subscriptio') . '</a>';
            array_unshift($links, $link);
        }

        return $links;
    }

    /**
     * Check if environment meets requirements
     *
     * @access public
     * @return bool
     */
    public static function check_environment()
    {

        $is_ok = true;

        // Check PHP version
        if (!RightPress_Help::php_version_gte(RP_SUB_SUPPORT_PHP)) {
            add_action('admin_notices', array('RP_SUB', 'php_version_notice'));
            return false;
        }

        // Check WordPress version
        if (!RightPress_Help::wp_version_gte(RP_SUB_SUPPORT_WP)) {
            add_action('admin_notices', array('RP_SUB', 'wp_version_notice'));
            $is_ok = false;
        }

        // Check if WooCommerce is enabled
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array('RP_SUB', 'wc_disabled_notice'));
            $is_ok = false;
        }
        else if (!RightPress_Help::wc_version_gte(RP_SUB_SUPPORT_WC)) {
            add_action('admin_notices', array('RP_SUB', 'wc_version_notice'));
            $is_ok = false;
        }

        return $is_ok;
    }

    /**
     * Display PHP version notice
     *
     * @access public
     * @return void
     */
    public static function php_version_notice()
    {

        echo '<div class="error"><p>' . sprintf(esc_html__('%1$s requires PHP %2$s or later. Please update PHP on your server to use this plugin.', 'subscriptio'), '<strong>Subscriptio</strong>', RP_SUB_SUPPORT_PHP) . ' ' . sprintf(esc_html__('If you have any questions, please contact %s.', 'subscriptio'), ('<a href="http://url.rightpress.net/new-support-ticket">' . esc_html__('RightPress Support', 'subscriptio') . '</a>')) . '</p></div>';
    }

    /**
     * Display WP version notice
     *
     * @access public
     * @return void
     */
    public static function wp_version_notice()
    {

        echo '<div class="error"><p>' . sprintf(esc_html__('%1$s requires WordPress version %2$s or later. Please update WordPress to use this plugin.', 'subscriptio'), '<strong>Subscriptio</strong>', RP_SUB_SUPPORT_WP) . ' ' . sprintf(esc_html__('If you have any questions, please contact %s.', 'subscriptio'), ('<a href="http://url.rightpress.net/new-support-ticket">' . esc_html__('RightPress Support', 'subscriptio') . '</a>')) . '</p></div>';
    }

    /**
     * Display WC disabled notice
     *
     * @access public
     * @return void
     */
    public static function wc_disabled_notice()
    {

        echo '<div class="error"><p>' . sprintf(esc_html__('%1$s requires WooCommerce to be active. You can download WooCommerce %2$s.', 'subscriptio'), '<strong>Subscriptio</strong>', ('<a href="http://url.rightpress.net/woocommerce-download-page">' . esc_html__('here', 'subscriptio') . '</a>')) . ' ' . sprintf(esc_html__('If you have any questions, please contact %s.', 'subscriptio'), ('<a href="http://url.rightpress.net/new-support-ticket">' . esc_html__('RightPress Support', 'subscriptio') . '</a>')) . '</p></div>';
    }

    /**
     * Display WC version notice
     *
     * @access public
     * @return void
     */
    public static function wc_version_notice()
    {

        echo '<div class="error"><p>' . sprintf(esc_html__('%1$s requires WooCommerce version %2$s or later. Please update WooCommerce to use this plugin.', 'subscriptio'), '<strong>Subscriptio</strong>', RP_SUB_SUPPORT_WC) . ' ' . sprintf(esc_html__('If you have any questions, please contact %s.', 'subscriptio'), ('<a href="http://url.rightpress.net/new-support-ticket">' . esc_html__('RightPress Support', 'subscriptio') . '</a>')) . '</p></div>';
    }





}

RP_SUB::get_instance();
