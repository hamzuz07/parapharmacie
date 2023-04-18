<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WooCommerce Order Functional Controller
 *
 * The following meta keys are set to orders:
 *  _rp_sub:renewal_order           Flag indicating that WooCommerce order is a subscription renewal order
 *  _rp_sub:related_subscription    Subscription id linking initial order and renewal orders to a specific subscription
 *
 * @class RP_SUB_WC_Order
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_WC_Order
{

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    private $related_subscriptions_list = null;

    private $cancelling_subscription_related_orders = false;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Maybe handle subscription payment
        add_action('woocommerce_order_status_changed', array($this, 'maybe_handle_subscription_payment'), 10, 4);

        // WooCommerce order cancelled, refunded or deleted - maybe cancel subscription(s)
        add_action('woocommerce_order_status_cancelled', array($this, 'order_cancelled_deleted_or_fully_refunded'));
        add_action('woocommerce_order_status_refunded', array($this, 'order_cancelled_deleted_or_fully_refunded'));
        add_action('delete_post', array($this, 'order_cancelled_deleted_or_fully_refunded'));

        // Prevent WooCommerce from cancelling unpaid subscription renewal orders prematurely
        add_filter('woocommerce_cancel_unpaid_order', array($this, 'cancel_unpaid_order'), 10, 2);

        // Prevent WooCommerce from trashing subscription related orders due to privacy settings
        add_filter('woocommerce_trash_pending_orders_query_args', array($this, 'prevent_privacy_trashing_subscription_orders'));
        add_filter('woocommerce_trash_failed_orders_query_args', array($this, 'prevent_privacy_trashing_subscription_orders'));
        add_filter('woocommerce_trash_cancelled_orders_query_args', array($this, 'prevent_privacy_trashing_subscription_orders'));

        // Prevent WooCommerce from anonymizing subscription related orders due to privacy settings
        add_filter('woocommerce_anonymize_completed_orders_query_args', array($this, 'prevent_anonymizing_subscription_orders'));

        // Cancel unpaid related orders when subscription is terminated
        add_filter('subscriptio_subscription_status_changing_to_cancelled', array($this, 'cancel_unpaid_subscription_orders'));
        add_filter('subscriptio_subscription_status_changing_to_expired', array($this, 'cancel_unpaid_subscription_orders'));

        // Maybe set up related subscriptions list
        add_action('load-post.php', array($this, 'maybe_set_up_related_subscriptions_list'));

        // Maybe add related subscriptions meta box
        add_action('add_meta_boxes', array($this, 'maybe_add_related_subscriptions_meta_box'), 99, 2);

        // Set up custom order query vars
        add_filter('woocommerce_order_data_store_cpt_get_orders_query', array($this, 'set_up_custom_order_query_vars'), 10, 2);

        // Maybe trigger created renewal order notification
        add_action('subscriptio_created_renewal_order', array($this, 'maybe_trigger_created_renewal_order_notification'), 10, 2);

        // Maybe prevent stock reduction in renewal orders
        add_filter('woocommerce_can_reduce_order_stock', array($this, 'maybe_prevent_stock_reduction_in_renewal_orders'), 99, 2);
    }

    /**
     * Set up custom order query vars
     *
     * @access public
     * @param array $query_args
     * @param array $query_vars
     * @return array
     */
    public function set_up_custom_order_query_vars($query_args, $query_vars)
    {

        // Related order
        if (isset($query_vars['rp_sub_related_order'])) {

            // Set up query
            $query_args['meta_query'][] = array(
                'key'   => '_rp_sub:meta:related_order',
                'value' => esc_attr($query_vars['rp_sub_related_order']),
            );
        }

        // Related subscription
        if (isset($query_vars['rp_sub_related_subscription'])) {

            // Find orders with no related subscriptions
            if ($query_vars['rp_sub_related_subscription'] === false) {

                // Set up query
                $query_args['meta_query'][] = array(
                    'key'       => '_rp_sub:related_subscription',
                    'compare'   => 'NOT EXISTS',
                );
            }
            // Find orders related to subscription
            else {

                // Set up query
                $query_args['meta_query'][] = array(
                    'key'   => '_rp_sub:related_subscription',
                    'value' => esc_attr($query_vars['rp_sub_related_subscription']),
                );
            }
        }

        return $query_args;
    }


    /**
     * =================================================================================================================
     * PAYMENT HANDLING
     * =================================================================================================================
     */

    /**
     * Maybe handle subscription payment if status is changed to one of the order paid statuses and order contains
     * subscription products or is subscription renewal order
     *
     * @access public
     * @param int $order_id
     * @param string $status_from
     * @param string $status_to
     * @param object $order
     * @return void
     */
    public function maybe_handle_subscription_payment($order_id, $status_from, $status_to, $order)
    {

        // Check if order became paid
        if ($order->is_paid() && !in_array($status_from, wc_get_is_paid_statuses(), true)) {

            // Get subscriptions related to this order
            $subscriptions = subscriptio_get_subscriptions(array(
                'order' => $order_id,
            ));

            // Check if any subscriptions are related to this order
            if (!empty($subscriptions)) {

                // Iterate over related subscriptions
                foreach ($subscriptions as $subscription) {

                    // Start logging
                    $log_entry = RP_SUB_Log_Entry_Controller::create_log_entry(array(
                        'event_type'        => 'payment_received',
                        'subscription_id'   => $subscription->get_id(),
                        'order_id'          => $order_id,
                        'notes'             => array(
                            esc_html__('Order was marked paid, updating subscription.', 'subscriptio'),
                        ),
                    ), $subscription);

                    try {

                        // Subscription needs payment
                        if ($subscription->needs_payment(true) && ($subscription->get_pending_renewal_order_id() === $order_id || $subscription->get_initial_order_id() === $order_id)) {

                            // Apply subscription payment
                            $subscription->apply_payment($order);
                        }
                        // Subscription does not need payment
                        else {

                            // Add not to log entry
                            $log_entry->add_note(esc_html__('This subscription is already paid for, nothing to do.', 'subscriptio'));
                        }
                    }
                    catch (Exception $e) {

                        // Handle caught exception
                        $log_entry->handle_caught_exception($e);
                    }

                    // End logging
                    $log_entry->end_logging($subscription);
                }
            }
        }
    }


    /**
     * =================================================================================================================
     * CANCELLATIONS AND REFUNDS
     * =================================================================================================================
     */

    /**
     * WooCommerce order cancelled, deleted or fully refunded - maybe cancel subscription(s)
     *
     * @access public
     * @param int $order_id
     * @return void
     */
    public function order_cancelled_deleted_or_fully_refunded($order_id)
    {

        // TODO: We should check rp_sub_cart_item_reference and not delete anything that admin added manually or so

        // Subscription is being cancelled, we can ignore this call
        if ($this->cancelling_subscription_related_orders) {
            return;
        }

        // Action is delete_post and post is not shop_order
        if (get_post_type($order_id) !== 'shop_order') {
            return;
        }

        // Check if order was cancelled, refunded or deleted
        $was_cancelled  = current_action() === 'woocommerce_order_status_cancelled';
        $was_refunded   = current_action() === 'woocommerce_order_status_refunded';
        $was_deleted    = current_action() === 'delete_post';

        // Load order
        $order = wc_get_order($order_id);

        // Check if order is subscription initial or renewal order
        $is_initial_order = subscriptio_is_subscription_initial_order($order);
        $is_renewal_order = subscriptio_is_subscription_renewal_order($order);

        // Only handle this event if order is subscription initial or renewal order
        if ($is_initial_order || $is_renewal_order) {

            // Get related subscriptions
            $subscriptions = subscriptio_get_subscriptions_related_to_order($order);

            // Iterate over related subscriptions
            foreach ($subscriptions as $subscription) {

                // Initial order deleted - subscription has not started
                if ($is_initial_order && $was_deleted && $subscription->is_pending_initial_payment()) {
                    $event_type = 'order_delete';
                    $note       = esc_html__('Unpaid subscription initial order deleted, cancelling subscription.', 'subscriptio');
                }
                // Initial order cancelled - no renewal orders generated
                else if ($is_initial_order && $was_cancelled && !$subscription->get_last_renewal_order_id()) {
                    $event_type = 'order_cancel';
                    $note       = esc_html__('Subscription initial order cancelled, cancelling subscription.', 'subscriptio');
                }
                // Initial order refunded - no renewal orders generated
                else if ($is_initial_order && $was_refunded && !$subscription->get_last_renewal_order_id() && RP_SUB_Settings::is('cancel_on_refunded_payment')) {
                    $event_type = 'order_refund';
                    $note       = esc_html__('Subscription initial order refunded, cancelling subscription.', 'subscriptio');
                }
                // Renewal order cancelled - subscription has/had this order as the last renewal order
                else if ($is_renewal_order && $was_cancelled && $subscription->get_last_renewal_order_id() === $order->get_id()) {
                    $event_type = 'order_cancel';
                    $note       = esc_html__('Subscription renewal order cancelled, cancelling subscription.', 'subscriptio');
                }
                // Renewal order refunded - subscription has/had this order as the last renewal order
                else if ($is_renewal_order && $was_refunded && $subscription->get_last_renewal_order_id() === $order->get_id() && RP_SUB_Settings::is('cancel_on_refunded_payment')) {
                    $event_type = 'order_refund';
                    $note       = esc_html__('Subscription renewal order refunded, cancelling subscription.', 'subscriptio');
                }
                // Nothing to do, skip to another subscription
                else {
                    continue;
                }

                // Start logging
                $log_entry = RP_SUB_Log_Entry_Controller::create_log_entry(array(
                    'event_type'        => $event_type,
                    'subscription_id'   => $subscription->get_id(),
                    'order_id'          => $order->get_id(),
                    'notes'             => array($note),
                ), $subscription);

                try {

                    // Set flag to allow cancellation
                    $subscription->last_payment_just_voided = true;

                    // Cancel subscription
                    $subscription->cancel();
                }
                catch (Exception $e) {

                    // Handle caught exception
                    $log_entry->handle_caught_exception($e);
                }

                // End logging
                $log_entry->end_logging($subscription);
            }
        }
    }

    /**
     * Prevent WooCommerce from cancelling unpaid subscription renewal orders prematurely
     *
     * @access public
     * @param bool $cancel
     * @param object $order
     * @return bool
     */
    public function cancel_unpaid_order($cancel, $order)
    {

        // Do not cancel subscription renewal orders
        if (subscriptio_is_subscription_renewal_order($order)) {
            return false;
        }

        return $cancel;
    }

    /**
     * Prevent WooCommerce from trashing subscription related orders due to privacy settings
     *
     * @access public
     * @param array $query_args
     * @return array
     */
    public function prevent_privacy_trashing_subscription_orders($query_args)
    {

        // Set arg to exclude orders related to subscriptions and return
        return array_merge($query_args, array('rp_sub_related_subscription' => false));
    }

    /**
     * Prevent WooCommerce from anonymizing subscription related orders due to privacy settings
     *
     * @access public
     * @param array $query_args
     * @return array
     */
    public function prevent_anonymizing_subscription_orders($query_args)
    {

        // Set arg to exclude orders related to subscriptions and return
        return array_merge($query_args, array('rp_sub_related_subscription' => false));
    }

    /**
     * Cancel unpaid related orders when subscription is terminated
     *
     * @access protected
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function cancel_unpaid_subscription_orders($subscription)
    {

        // Set flag
        $this->cancelling_subscription_related_orders = true;

        // Subscription has not started
        if ($subscription->has_status('pending')) {

            // Get initial order
            if ($initial_order = $subscription->get_initial_order()) {

                // Cancel unpaid initial order
                if (!$initial_order->is_paid()) {

                    // TODO: Would be good to cancel this order, provided it only has this subscription in it (need a check for that); if it has other stuff in it, would it be possible to amend the order?
                }
            }
        }
        // Subscription has started
        else {

            // Get pending renewal order
            if ($renewal_order = $subscription->get_pending_renewal_order()) {

                // Cancel pending renewal order
                $renewal_order->update_status('cancelled', esc_html__('Unpaid subscription renewal order cancelled - subscription terminated.', 'subscriptio'));

                // Unset pending renewal order id
                $subscription->set_pending_renewal_order_id(null);

                // Add note to log entry
                $subscription->add_log_entry_note(sprintf(esc_html__('Unpaid subscription renewal order #%d cancelled.', 'subscriptio'), $renewal_order->get_id()));
            }
        }

        // Unset flag
        $this->cancelling_subscription_related_orders = true;
    }


    /**
     * =================================================================================================================
     * SUBSCRIPTIONS IN ORDER VIEW
     * =================================================================================================================
     */

    /**
     * Maybe set up related subscriptions list
     *
     * @access public
     * @return void
     */
    public function maybe_set_up_related_subscriptions_list()
    {

        global $typenow;
        global $post;

        // Not our post type
        if ($typenow !== 'shop_order') {
            return;
        }

        // Initialize list
        $this->related_subscriptions_list = new RP_SUB_Order_Related_Subscriptions_List();
    }

    /**
     * Maybe add related subscriptions meta box
     *
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function maybe_add_related_subscriptions_meta_box($post_type, $post)
    {

        // Check if post type is shop order
        if ($post_type === 'shop_order') {

            // Check if order has related subscriptions
            if (subscriptio_get_subscriptions_related_to_order($post->ID)) {

                // Add meta box
                add_meta_box(
                    'rp-sub-order-related-subscriptions',
                    esc_html__('Related subscriptions', 'subscriptio'),
                    array($this, ('print_meta_box_related_subscriptions')),
                    'shop_order',
                    'normal',
                    'high'
                );
            }
        }
    }

    /**
     * Print meta box related subscriptions
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function print_meta_box_related_subscriptions($post)
    {

        // Load order
        if ($order = wc_get_order($post->ID)) {

            // Check if list is initialized
            if ($this->related_subscriptions_list !== null) {

                // Set subscription
                $this->related_subscriptions_list->set_related_object($order);

                // Prepare items
                $this->related_subscriptions_list->prepare_items();

                // Display list
                $this->related_subscriptions_list->display();
            }
        }
    }


    /**
     * =================================================================================================================
     * INITIAL & RENEWAL ORDER METHODS
     * =================================================================================================================
     */

    /**
     * Create initial order
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return WC_Order|null
     */
    public static function create_initial_order($subscription)
    {

        // Subscription has started, this is unexpected
        if (!$subscription->has_status(array('pending', 'draft', 'auto-draft'))) {
            throw new RightPress_Exception('rp_sub_create_initial_order_subscription_has_started', esc_html__('Unable to create initial order - subscription has started.', 'subscriptio'));
        }

        // Initial order already exists
        if ($order = $subscription->get_initial_order()) {
            return $order;
        }

        // Create initial order
        $order = RP_SUB_WC_Order::create_order_from_subscription($subscription, 'initial');

        // Add note to order
        $order->add_order_note(sprintf(esc_html__('Initial order for subscription #%d.', 'subscriptio'), $subscription->get_id()));

        // Set initial order id to subscription
        $subscription->set_initial_order_id($order->get_id());

        // Save subscription
        $subscription->save();

        // Trigger action
        do_action("subscriptio_created_initial_order", $order, $subscription);

        // Complete payment on order if it does not need one
        if (!$order->needs_payment()) {
            $order->payment_complete();
        }

        // Return initial order
        return $order;
    }

    /**
     * Create renewal order
     *
     * Throws RightPress_Exception in case of an error
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @return WC_Order|null
     */
    public static function create_renewal_order($subscription)
    {

        // Subscription has not started, this is unexpected
        if ($subscription->has_status(array('pending', 'draft', 'auto-draft'))) {
            throw new RightPress_Exception('rp_sub_create_renewal_order_subscription_has_not_started', esc_html__('Unable to create renewal order - subscription has not started.', 'subscriptio'));
        }

        // Pending renewal order already exists
        if ($order = $subscription->get_pending_renewal_order()) {
            return $order;
        }

        // Create renewal order
        $order = RP_SUB_WC_Order::create_order_from_subscription($subscription, 'renewal');

        // Add note to order
        $order->add_order_note(sprintf(esc_html__('Renewal order for subscription #%d.', 'subscriptio'), $subscription->get_id()));

        // Set pending renewal order id to subscription
        $subscription->set_pending_renewal_order_id($order->get_id());

        // Save subscription
        $subscription->save();

        // Trigger action
        do_action("subscriptio_created_renewal_order", $order, $subscription);

        // Complete payment on order if it does not need one
        if (!$order->needs_payment()) {
            $order->payment_complete();
        }

        // Return initial order
        return $order;
    }

    /**
     * Create order from subscription
     *
     * Throws RightPress_Exception in case of an error
     *
     * Supported types:
     *  - initial
     *  - renewal
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param string $type
     * @return WC_Order|null
     */
    private static function create_order_from_subscription($subscription, $type)
    {

        global $wpdb;

        $order = null;

        // Start transaction, if supported
        wc_transaction_query('start');

        try {

            // Load subscription if id was passed
            if (!is_a($subscription, 'RP_SUB_Subscription')) {
                $subscription = subscriptio_get_subscription($subscription);
            }

            // Check subscription object
            if (!is_a($subscription, 'RP_SUB_Subscription')) {
                throw new RightPress_Exception('rp_sub_create_order_from_subscription_invalid_subscription_object', esc_html__('Invalid subscription object.', 'subscriptio'));
            }

            // Subscription is terminated
            if ($subscription->is_terminated()) {
                throw new RightPress_Exception('rp_sub_create_order_from_subscription_subscription_terminated', esc_html__('Unable to create order from subscription - subscription is terminated.', 'subscriptio'));
            }

            // Reference suborder
            $suborder = $subscription->get_suborder();

            // Create new order
            $order = wc_create_order(array(
                'customer_id'   => $suborder->get_customer_id('edit'),
                'created_via'   => 'subscription',
            ));

            // Add order id to log entry
            $subscription->add_log_entry_property('order_id', $order->get_id());

            // Set subscription order flag
            $order->add_meta_data("_rp_sub:{$type}_order", 'yes');

            // Get order props to set
            $order_props = array(

                // Totals
                'shipping_total'        => $suborder->get_shipping_total('edit'),
                'shipping_tax'          => $suborder->get_shipping_tax('edit'),
                'discount_total'        => $suborder->get_discount_total('edit'),
                'discount_tax'          => $suborder->get_discount_tax('edit'),
                'cart_tax'              => $suborder->get_cart_tax('edit'),
                'total'                 => $suborder->get_total('edit'),

                // Other props
                'currency'              => $suborder->get_currency('edit')              ? $suborder->get_currency('edit')           : get_woocommerce_currency(),
                'prices_include_tax'    => $suborder->get_prices_include_tax('edit')    ? $suborder->get_prices_include_tax('edit') : (get_option('woocommerce_prices_include_tax') === 'yes'),
                'customer_note'         => $suborder->get_customer_note('edit')                 ? $suborder->get_customer_note('edit')              : '',
                'payment_method'        => $suborder->get_payment_method('edit')                ? $suborder->get_payment_method('edit')             : '',
            );

            // Allow developers to override
            $order_props = apply_filters("subscriptio_subscription_{$type}_order_props", $order_props, $subscription);

            // Set order props
            $order->set_props($order_props);

            // Set VAT exempt flag
            $order->add_meta_data('is_vat_exempt', ($suborder->get_meta('is_vat_exempt', true, 'edit') === 'yes' ? 'yes' : 'no'));

            // Copy addresses
            RP_SUB_WC_Order::copy_addresses_between_orders($suborder, $order);

            // Copy meta data (only copies explicitly whitelisted meta entries)
            RP_SUB_WC_Order::copy_meta_data_between_orders($suborder, $order);

            // Copy items
            RP_SUB_WC_Order::copy_items_between_orders($suborder, $order);

            // Trigger action
            do_action("subscriptio_creating_order_from_subscription", $order, $subscription, $type);

            // Save order
            $order->save();

            // Commit transaction, if supported
            wc_transaction_query('commit');

            /**
             * OLD CODE FROM OLD METHOD:
            // If renewal order's total is zero or the site is demo - change status to processing
            if (RightPress_WC_Legacy::order_get_total($order) == 0 || RightPress_Helper::is_demo()) {
            $order->update_status('processing');
            }
             */

            // Trigger action
            do_action("subscriptio_created_order_from_subscription", $order, $subscription, $type);
        }
        catch (Exception $e) {

            // Rollback transaction, if supported
            wc_transaction_query('rollback');

            // Propagate exception
            throw $e;
        }

        // Return order
        return $order;
    }

    /**
     * Copy addresses between orders
     *
     * Supported address types: billing, shipping
     *
     * @access public
     * @param object $source_order
     * @param object $target_order
     * @param array|string $address_types
     * @return void
     */
    public static function copy_addresses_between_orders(&$source_order, &$target_order, $address_types = array())
    {

        // Address type not specified, will copy both
        if (empty($address_types)) {
            $address_types = array('billing', 'shipping');
        }

        // Ensure address types is array
        $address_types = (array) $address_types;

        // Format list of shipping field keys
        $field_keys = array(
            'shipping' => array(
                'first_name',
                'last_name',
                'company',
                'address_1',
                'address_2',
                'city',
                'state',
                'postcode',
                'country',
            ),
        );

        // Format list of billing field keys
        $field_keys['billing'] = array_merge($field_keys['shipping'], array(
            'email',
            'phone',
        ));

        // Iterate over address types
        foreach ($address_types as $address_type) {

            // Iterate over field keys
            foreach ($field_keys[$address_type] as $field_key) {

                // Format getter and setter method names
                $getter = 'get_' . $address_type . '_' . $field_key;
                $setter = 'set_' . $address_type . '_' . $field_key;

                // Copy address field value
                $target_order->{$setter}($source_order->{$getter}('edit'));
            }
        }
    }

    /**
     * Copy meta data between orders
     *
     * Only copies explicitly whitelisted meta entries
     *
     * @access public
     * @param object $source_order
     * @param object $target_order
     * @param array $whitelist
     * @return void
     */
    public static function copy_meta_data_between_orders(&$source_order, &$target_order, $whitelist = array())
    {

        // Allow developers to whitelist custom meta keys
        $whitelist = apply_filters('subscriptio_order_meta_data_whitelist', $whitelist, $source_order, $target_order);

        // Check if any meta keys are whitelisted
        if (!empty($whitelist)) {

            // Get meta data from source
            $meta_data = $source_order->get_meta_data();

            // Iterate over whitelisted keys
            foreach ($whitelist as $meta_key) {

                // Get array keys of meta values matching current meta key
                $array_keys = array_keys(wp_list_pluck($meta_data, 'key'), $meta_key);

                // Check if meta values for current key were found
                if (!empty($array_keys)) {

                    // Get values
                    $values = array_intersect_key($meta_data, array_flip($array_keys));

                    // Iterate over values
                    foreach ($values as $value) {

                        // Add current value to target
                        $target_order->add_meta_data($meta_key, $value->value);
                    }
                }
            }
        }
    }

    /**
     * Copy items between orders
     *
     * @access public
     * @param object $source_order
     * @param object $target_order
     * @param array $item_types
     * @return void
     */
    public static function copy_items_between_orders(&$source_order, &$target_order, $item_types = null)
    {

        // Define item types with props
        $item_types_with_props = array(
            'line_item' => array('product_id' => null, 'variation_id' => null, 'quantity' => null, 'tax_class' => null, 'subtotal' => null, 'total' => null, 'taxes' => null),
            'fee'       => array('amount' => null, 'tax_class' => null, 'tax_status' => null, 'total' => null, 'taxes' => null),
            'shipping'  => array('method_id' => null, 'instance_id' => null, 'total' => null, 'taxes' => null),
            'tax'       => array('rate_id' => null, 'label' => null, 'compound' => null, 'tax_total' => null, 'shipping_tax_total' => null, 'rate_percent' => null),
            'coupon'    => array('discount' => null, 'discount_tax' => null),
        );

        // Iterate over item types
        foreach ($item_types_with_props as $item_type => $props) {

            // Check if item type is allowed
            if ($item_types === null || in_array($item_type, $item_types, true)) {

                // Get source items
                $source_items = $source_order->get_items($item_type);

                // Iterate over source items
                foreach ($source_items as $source_item) {

                    // Add item to target order
                    $target_item_id = wc_add_order_item($target_order->get_id(), array(
                        'order_item_name'   => $source_item['name'],
                        'order_item_type'   => $source_item['type'],
                    ));

                    // Get target item
                    $target_item = $target_order->get_item($target_item_id);

                    // Copy props
                    $target_item_props = $props;

                    array_walk($target_item_props, function(&$value, $key, $source_item) {
                        $value = $source_item->{'get_' . $key}('edit');
                    }, $source_item);

                    // Set props
                    $target_item->set_props($target_item_props);

                    // Copy meta data
                    foreach ($source_item->get_meta_data() as $meta_entry) {
                        $target_item->update_meta_data($meta_entry->key, $meta_entry->value);
                    }

                    // Save target item
                    $target_item->save();
                }
            }
        }
    }


    /**
     * =================================================================================================================
     * OTHER METHODS
     * =================================================================================================================
     */

    /**
     * Maybe trigger created renewal order notification
     *
     * @access public
     * @param WC_Order $renewal_order
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function maybe_trigger_created_renewal_order_notification($renewal_order, $subscription)
    {

        // We don't want to send notification for subscriptions that have automatic payments
        if (!$subscription->has_automatic_payments()) {

            // Trigger notification action
            do_action('subscriptio_created_renewal_order_notification', $renewal_order);
        }
    }

    /**
     * Maybe prevent stock reduction in renewal orders
     *
     * @access public
     * @param bool $reduce_stock
     * @param WC_Order $order
     * @return bool
     */
    public function maybe_prevent_stock_reduction_in_renewal_orders($reduce_stock, $order)
    {

        // Check if order is subscription renewal order
        if (subscriptio_is_subscription_renewal_order($order)) {

            // Check if stock reduction should be prevented
            if (!apply_filters('subscriptio_can_reduce_renewal_order_stock', true, $order)) {

                // Set flag
                $reduce_stock = false;
            }
        }

        return $reduce_stock;
    }





}

RP_SUB_WC_Order::get_instance();
