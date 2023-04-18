<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * PayPal Express Checkout payment gateway extension loader
 *
 * @class RP_SUB_PayPal_EC_Loader
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_PayPal_EC_Loader
{

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Load payment gateway
        add_action('init', array($this, 'load_payment_gateway'));

        // Add payment gateway
        add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateway'));

        // Intercept PayPal response
        add_action('parse_request', array($this, 'paypal_express_return_page'), 10);
    }

    /**
     * Load payment gateway
     *
     * @access public
     * @return void
     */
    public function load_payment_gateway()
    {

        // Check if gateway is enabled in settings
        if (RP_SUB_Settings::is('paypal_ec_enabled')) {

            // Load payment gateway classes
            require_once RP_SUB_PLUGIN_PATH . 'gateways/paypal-ec/subscriptio-paypal-ec-gateway.class.php';
            require_once RP_SUB_PLUGIN_PATH . 'gateways/paypal-ec/subscriptio-paypal-ec-nvp.class.php';
            require_once RP_SUB_PLUGIN_PATH . 'gateways/paypal-ec/subscriptio-paypal-ec-payment-request.class.php';
            require_once RP_SUB_PLUGIN_PATH . 'gateways/paypal-ec/subscriptio-paypal-ec-subscriptions.class.php';

            // Define support for automatic payments
            add_filter('subscriptio_subscriptio_paypal_ec_automatic_payments_ready', '__return_true');
        }
    }

    /**
     * Add payment gateway
     *
     * @access public
     * @param array $gateways
     * @return void
     */
    public function add_payment_gateway($gateways)
    {

        // Check if gateway is enabled in settings
        if (RP_SUB_Settings::is('paypal_ec_enabled')) {

            // Add gateway class
            $gateways[] = 'Subscriptio_PayPal_EC_Gateway';

            // Load text domain
            load_textdomain('subscriptio-paypal-ec', WP_LANG_DIR . '/' . RP_SUB_PLUGIN_KEY . '/subscriptio-paypal-ec-' . apply_filters('plugin_locale', get_locale(), 'subscriptio') . '.mo');
            load_plugin_textdomain('subscriptio-paypal-ec', false, (RP_SUB_PLUGIN_KEY . '/languages/'));
        }

        return $gateways;
    }

    /**
     * Intercept PayPal response and process the payment
     *
     * @access public
     * @return void
     */
    public function paypal_express_return_page()
    {

        if (!empty($_GET['subscriptio_ppec_action']) && ($_GET['subscriptio_ppec_action'] === 'do_payment')) {
            $gateway = new Subscriptio_PayPal_EC_Gateway();
            $gateway->do_express_checkout_payment();
        }
    }





}

RP_SUB_PayPal_EC_Loader::get_instance();
