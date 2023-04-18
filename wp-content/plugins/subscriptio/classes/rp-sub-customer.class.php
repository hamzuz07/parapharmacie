<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer controller
 *
 * @class RP_SUB_Customer
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Customer
{

    private static $is_delete_inactive_accounts_query = false;

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

        // Prevent customer account from being deleted due to no activity
        RightPress_Help::add_late_filter('woocommerce_delete_inactive_account_roles', array($this, 'flag_delete_inactive_accounts_query'));
        add_action('pre_get_users', array($this, 'modify_delete_inactive_accounts_query'));
    }

    /**
     * Flag delete inactive accounts query
     *
     * @access public
     * @param array $roles
     * @return array
     */
    public function flag_delete_inactive_accounts_query($roles)
    {

        // Set flag
        RP_SUB_Customer::$is_delete_inactive_accounts_query = true;

        return $roles;
    }

    /**
     * Modify delete inactive accounts query to prevent customer account from being deleted due to no activity
     *
     * @access public
     * @param WP_User_Query $wp_user_query
     * @return void
     */
    public function modify_delete_inactive_accounts_query($wp_user_query)
    {

        // Not our query
        if (!RP_SUB_Customer::$is_delete_inactive_accounts_query) {
            return;
        }

        // Unset flag
        RP_SUB_Customer::$is_delete_inactive_accounts_query = false;

        // Get customer ids to exclude
        $exclude = (array) $wp_user_query->get('exclude');
        $exclude = array_merge($exclude, subscriptio_get_ids_of_customers_with_subscriptions());

        // Set customer ids to exclude
        $wp_user_query->set('exclude', $exclude);
    }

    /**
     * Check if customer is eligible for trial of specific product
     *
     * @access public
     * @param WC_Product|int $product
     * @param int $customer_id
     * @param int $exclude_subscription_id
     * @return bool
     */
    public static function customer_is_eligible_for_trial($product, $customer_id = null, $exclude_subscription_id = null)
    {

        $is_eligible = true;

        // Load WooCommerce product object
        if (!is_a($product, 'WC_Product')) {
            $product = wc_get_product($product);
        }

        // Product is not subscription product or parent product provided
        if (!subscriptio_is_subscription_product($product) || RightPress_Help::wc_product_has_children($product)) {
            return false;
        }

        // Load subscription product object
        $subscription_product = subscriptio_get_subscription_product($product);

        // Subscription product does not have free trial set at all
        if (!$subscription_product->get_free_trial_length()) {
            return false;
        }

        // Get customer id
        $customer_id = $customer_id ? $customer_id : get_current_user_id();

        // Check if customer is registered
        if ($customer_id) {

            // Check if free trials are limited
            if (!RP_SUB_Settings::is('trial_limit', 'no_limit')) {

                // Iterate over customer subscriptions
                foreach (subscriptio_get_customer_subscriptions($customer_id, true) as $subscription) {

                    // Subscription did not have a free trial
                    if (!$subscription->get_free_trial()) {
                        continue;
                    }

                    // Subscription is excluded
                    if ($subscription->get_id() === $exclude_subscription_id) {
                        continue;
                    }

                    // One trial per customer per site
                    if (RP_SUB_Settings::is('trial_limit', 'one_per_customer')) {

                        // Customer already has a non-terminated subscription on this site
                        $is_eligible = false;
                        break;
                    }
                    // One trial per product per customer per site
                    else if (RP_SUB_Settings::is('trial_limit', 'one_per_product')) {

                        // Iterate over subscription items
                        foreach ($subscription->get_items() as $item) {

                            // Check both product and variation ids
                            if ($item->get_product_id() === $subscription_product->get_id() || $item->get_variation_id() === $subscription_product->get_id()) {

                                // Customer already has a non-terminated subscription for this product on this site
                                $is_eligible = false;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Allow developers to override and return
        return apply_filters('subscriptio_customer_is_eligible_for_trial', $is_eligible, $subscription_product, $customer_id);
    }





}

RP_SUB_Customer::get_instance();
