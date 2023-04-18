<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Download Controller
 *
 * @class RP_SUB_Download_Controller
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Download_Controller
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

        // Grant access to all files when subscription enters trial or is activated for the first time
        add_action('subscriptio_subscription_status_changed_from_pending_to_trial', array($this, 'grant_subscription_product_download_permissions'), 1);
        add_action('subscriptio_subscription_status_changed_from_pending_to_active', array($this, 'grant_subscription_product_download_permissions'), 1);

        // Revoke subscription product download permissions from order
        add_action('woocommerce_grant_product_download_permissions', array($this, 'revoke_subscription_product_download_permissions_from_order'), 1);

        // Override order download permitted status
        RightPress_Help::add_early_filter('woocommerce_order_is_download_permitted', array($this, 'order_is_download_permitted'), 2);

        // Clean up permissions when subscription is permanently deleted
        add_action('deleted_post', array($this, 'clean_up_permissions_on_subscription_removal'));
    }

    /**
     * Grant access to all files when subscription enters trial or is activated for the first time
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function grant_subscription_product_download_permissions($subscription)
    {

        // Grant access to all files
        wc_downloadable_product_permissions($subscription->get_id(), true);
    }

    /**
     * Revoke subscription product download permissions from order
     *
     * @access public
     * @param int $order_id
     * @return void
     */
    public function revoke_subscription_product_download_permissions_from_order($order_id)
    {

        // Load order object
        if ($order = wc_get_order($order_id)) {

            // Order must not be subscription suborder and must contain at least one subscription
            if ($order->get_type() !== 'rp_sub_subscription' && subscriptio_is_subscription_order($order)) {

                // Iterate over order items
                foreach ($order->get_items() as $order_item) {

                    // Get correct product id
                    $product_id = $order_item->get_variation_id() ? $order_item->get_variation_id() : $order_item->get_product_id();

                    // Check if current product is a subscription product
                    if (subscriptio_is_subscription_product($product_id)) {

                        // Revoke download permissions
                        RP_SUB_Download_Controller::revoke_downloadable_product_permissions($order_id, $product_id);
                    }
                }
            }
        }
    }

    /**
     * Override order download permitted status
     *
     * @access public
     * @param bool $permitted
     * @param WC_Order $order
     * @return bool
     */
    public function order_is_download_permitted($permitted, $order)
    {

        // Subscription suborder permissions
        if ($order->get_type() === 'rp_sub_subscription') {

            // Load subscription
            if ($subscription = subscriptio_get_subscription($order->get_id())) {

                // Check if subscription gives access
                $permitted = $subscription->gives_access();
            }
        }
        // Subscription renewal order permissions
        else if (subscriptio_is_subscription_renewal_order($order)) {

            // Downloads on renewal orders are never permitted
            $permitted = false;
        }
        // Subscription initial order permissions
        else if (subscriptio_is_subscription_initial_order($order)) {

            // Downloads are permitted only if there are non-subscription downloadable products in order
            $permitted = false;

            // Iterate over order items
            foreach ($order->get_items() as $order_item) {

                // Get product
                $product = $order_item->get_product();

                // Check if product is non-subscription downloadable product
                if ($product && $product->is_downloadable() && !subscriptio_is_subscription_product($product)) {

                    // Set flag
                    $permitted = true;

                    // Do not check further items
                    break;
                }
            }
        }

        return $permitted;
    }

    /**
     * Clean up permissions when subscription is permanently deleted
     *
     * @access public
     * @param int $post_id
     * @return void
     */
    public function clean_up_permissions_on_subscription_removal($post_id)
    {

        // Check if post is subscription suborder
        if (get_post_type($post_id) === 'rp_sub_subscription') {

            // Clean up permissions table
            RP_SUB_Download_Controller::revoke_downloadable_product_permissions($post_id);
        }
    }

    /**
     * Revoke downloadable product permissions
     *
     * @access public
     * @param int $order_id
     * @param int $product_id
     * @return void
     */
    public static function revoke_downloadable_product_permissions($order_id, $product_id = null)
    {

        global $wpdb;

        // Define arguments
        $args = array('order_id' => (int) $order_id);

        // Maybe add product id
        if ($product_id !== null) {
            $args['product_id'] = (int) $product_id;
        }

        // Delete from database
        $wpdb->delete($wpdb->prefix . 'woocommerce_downloadable_product_permissions', $args);
    }





}

RP_SUB_Download_Controller::get_instance();
