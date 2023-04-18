<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Scripts and stylesheets
 *
 * @class RP_SUB_Assets
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Assets
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

        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', array('RP_SUB_Assets', 'enqueue_frontend_assets'));

        // Enqueue admin assets
        add_action('admin_enqueue_scripts', array('RP_SUB_Assets', 'enqueue_admin_assets'));
    }

    /**
     * Load frontend assets
     *
     * @access public
     * @return void
     */
    public static function enqueue_frontend_assets()
    {

        // Product page assets
        if (is_product()) {

            // Register scripts
            wp_enqueue_script('rp-sub-frontend-product-scripts', RP_SUB_PLUGIN_URL . '/assets/js/frontend-product.js', array('jquery'), RP_SUB_VERSION);
        }

        // Subscription page assets
        if (RP_SUB_WC_Account::is_subscription_page()) {

            // Register scripts
            wp_register_script('rp-sub-frontend-scripts', RP_SUB_PLUGIN_URL . '/assets/js/frontend.js', array('jquery'), RP_SUB_VERSION);

            // Pass variables
            wp_localize_script('rp-sub-frontend-scripts', 'rp_sub_frontend_vars', array(
                'confirm_pause'         => esc_html__('Are you sure you want to pause this subscription?', 'subscriptio'),
                'confirm_resume'        => esc_html__('Are you sure you want to resume this subscription?', 'subscriptio'),
                'confirm_set_to_cancel' => esc_html__('Are you sure you want to set this subscription to cancel at the end of the current billing cycle?', 'subscriptio'),
                'confirm_reactivate'    => esc_html__('Are you sure you want to reactivate this subscription?', 'subscriptio'),
                'confirm_cancel'        => esc_html__('Are you sure you want to cancel this subscription?', 'subscriptio'),
            ));

            // Enqueue scripts
            wp_enqueue_script('rp-sub-frontend-scripts');
        }
    }

    /**
     * Enqueue other admin assets
     *
     * @access public
     * @return void
     */
    public static function enqueue_admin_assets()
    {

        global $typenow;

        // Load assets conditionally
        if (!in_array($typenow, array('rp_sub_subscription', 'rp_sub_log_entry', 'shop_order', 'product'), true)) {
            return;
        }

        // Load admin styles
        wp_enqueue_style('rp-sub-admin-styles', RP_SUB_PLUGIN_URL . '/assets/css/admin.css', array(), RP_SUB_VERSION);

        // Register scripts
        wp_register_script('rp-sub-admin-scripts', RP_SUB_PLUGIN_URL . '/assets/js/admin.js', array('jquery'), RP_SUB_VERSION);

        // Pass variables to admin scripts
        wp_localize_script('rp-sub-admin-scripts', 'rp_sub_vars', array(
            'subscription_error_messages' => array(
                'generic_error'         => esc_html__('Please fix this element.', 'subscriptio'),
                'invalid_customer'      => esc_html__('Please select a customer.', 'subscriptio'),
                'invalid_billing_cycle' => esc_html__('Please enter a valid billing cycle length.', 'subscriptio'),
                'no_subscription_items' => esc_html__('Please add at least one subscription item.', 'subscriptio'),
            ),
            'product_settings_contexts' => array(
                'rp_sub_subscription_product' => esc_html__('Subscription', 'subscriptio'),
            ),
            'payment_gateway_change_confirmation_text' => esc_html__('Are you sure you want to change the payment method used for this subscription? Switching to manual payments will require customers to make their payments manually at the beginning of each billing cycle.', 'subscriptio'),
        ));

        // Enqueue jQuery plugins
        RightPress_Loader::load_jquery_plugin('rightpress-product-settings-control');

        // Enqueue scripts
        wp_enqueue_script('rp-sub-admin-scripts');

        // jQuery UI Tooltip
        wp_enqueue_script('jquery-ui-tooltip');
    }





}

RP_SUB_Assets::get_instance();
