<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'abstract/rp-sub-wc-custom-order-object-admin.class.php';

/**
 * Subscription Admin
 *
 * Note: We use two types of objects for subscriptions:
 *  - RP_SUB_Suborder which is a custom WooCommerce order type and holds most of the data in a format
 *    that is easy to copy data from/to regular WooCommerce orders and lets us reuse WooCommerce order interface
 *  - RP_SUB_Subscription which is a wrapper to add our own functionality so that we can use method/property names
 *    without prefixes and don't fear that they will clash with those in WC_Order in the future
 *
 * @class RP_SUB_Subscription_Admin
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Subscription_Admin extends RP_SUB_WC_Custom_Order_Object_Admin
{

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    private $related_orders_list = null;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Call parent constructor
        parent::__construct();

        // Maybe make subscription editable
        RightPress_Help::add_late_filter('wc_order_is_editable', array($this, 'maybe_make_subscription_editable'), 2);

        // Add order actions
        add_filter('woocommerce_order_actions', array($this, 'add_order_actions'));

        // Prevent delete of non-terminated subscriptions
        add_filter('user_has_cap', array($this, 'maybe_prevent_subscription_delete'), 10, 4);

        // Maybe set up related orders list
        add_action('load-post.php', array($this, 'maybe_set_up_related_orders_list'));
        add_action('load-post-new.php', array($this, 'maybe_set_up_related_orders_list'));
    }

    /**
     * Get controller class
     *
     * @access public
     * @return string
     */
    public function get_controller_class()
    {

        return 'RP_SUB_Subscription_Controller';
    }

    /**
     * Get post type
     *
     * @access public
     * @return string
     */
    public function get_post_type()
    {

        return 'rp_sub_subscription';
    }


    /**
     * =================================================================================================================
     * ADMIN OBJECT LIST
     * =================================================================================================================
     */

    /**
     * Customize post list query
     *
     * @access public
     * @param object $query
     * @return void
     */
    public function customize_post_list_query(&$query)
    {

        // Call parent method for base customizations
        parent::customize_post_list_query($query);

        // Reference query vars
        $query_vars = &$query->query_vars;

        // Filter orders by customer
        if (!empty($_GET['customer_id'])) {
            $query_vars['meta_query'] = array(
                array(
                    'key'       => '_customer_user',
                    'value'     => (int) $_GET['customer_id'],
                    'compare'   => '=',
                ),
            );
        }
    }

    /**
     * Register list columns
     *
     * @access public
     * @return array
     */
    public function register_list_columns()
    {

        return apply_filters('subscriptio_admin_subscription_list_table_columns', array(
            'subscription'                      => esc_html__('Subscription', 'subscriptio'),
            'products'                          => esc_html__('Products', 'subscriptio'),
            'last_order'                        => esc_html__('Last Order', 'subscriptio'),
            'first_payment'                     => esc_html__('Started', 'subscriptio'),
            'scheduled_renewal_payment'         => esc_html__('Payment Due', 'subscriptio'),
            'status'                            => esc_html__('Status', 'subscriptio'),
            'recurring_total'                   => esc_html__('Recurring', 'subscriptio'),
        ));
    }

    /**
     * Print column value
     *
     * @access public
     * @param string $column_name
     * @param int $post_id
     * @return void
     */
    public function print_column_value($column_name, $post_id)
    {

        // Get subscription for view
        if ($subscription = $this->get_object_for_view($post_id)) {

            // Format method name
            $method = 'get_subscription_list_column_value_' . $column_name;

            // Get value
            $value = RP_SUB_Subscription_Admin::$method($subscription);

            // Allow developers to modify value and display it
            echo apply_filters('subscriptio_admin_subscription_list_table_column_value', $value, $column_name, $subscription);
        }
    }

    /**
     * Get subscription list subscription column value
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return string
     */
    public static function get_subscription_list_column_value_subscription($subscription)
    {

        // Get subscription number and customer full name
        $value = '<strong>' . $subscription->get_subscription_number() . ' ' . $subscription->get_formatted_billing_full_name() . '</strong>';

        // Add link to subscription if it is not trashed
        if (RightPress_Help::post_is_active($subscription->get_id())) {
            $value = '<a href="' . get_edit_post_link($subscription->get_id()) . '">' . $value . '</a>';
        }

        return $value;
    }

    /**
     * Get subscription list products column value
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return string
     */
    public static function get_subscription_list_column_value_products($subscription)
    {

        // Default value
        $value = '—';

        // Get subscription items
        if ($subscription_items = $subscription->get_items()) {

            $products = array();

            // Iterate over subscription items
            foreach ($subscription_items as $item) {

                // Get product name
                $current_product = $item->get_name();

                // Add link to product edit page if it still exists
                // WC31: Products will no longer be posts
                if (RightPress_Help::post_is_active($item->get_product_id())) {
                    $current_product = '<a href="' . get_edit_post_link($item->get_product_id()) . '">' . $current_product . '</a>';
                }

                // Maybe append quantity
                if ($item->get_quantity() > 1) {
                    $current_product .= (' &times; ' . $item->get_quantity());
                }

                // Add current product to main array
                $products[] = $current_product;
            }

            // Apply limit
            $limit      = apply_filters('subscriptio_admin_subscription_list_table_product_display_limit', 3);
            $count      = count($products);
            $remainder  = $count - $limit;

            if ($remainder > 0) {
                $products   = array_slice($products, 0, $limit);
                $products[] = sprintf(_n('and %d other product', 'and %d other products', $remainder, 'subscriptio'), $remainder);
            }

            $value = implode('<br>', $products);
        }

        return $value;
    }

    /**
     * Get subscription list last order column value
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return string
     */
    public static function get_subscription_list_column_value_last_order($subscription)
    {

        // Default value
        $value = '—';

        // Get related order ids
        if ($order_ids = subscriptio_get_orders_related_to_subscription($subscription, array('return' => 'ids'))) {

            // Get last order id
            $order_id = max($order_ids);

            // Format value
            $value = '#' . $order_id;

            // Add link to order if it still exists
            // WC31: Orders will no longer be posts
            if (RightPress_Help::post_is_active($order_id)) {
                $value = '<a href="' . get_edit_post_link($order_id) . '">' . $value . '</a>';
            }
        }

        return $value;
    }

    /**
     * Get subscription list first payment column value
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return string
     */
    public static function get_subscription_list_column_value_first_payment($subscription)
    {

        // Default value
        $value = '—';

        // Get first payment datetime
        if ($datetime = $subscription->get_first_payment()) {
            $value = RP_SUB_Subscription_Admin::get_formatted_list_table_datetime($datetime);
        }

        return $value;
    }

    /**
     * Get subscription list scheduled renewal payment column value
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return string
     */
    public static function get_subscription_list_column_value_scheduled_renewal_payment($subscription)
    {

        // Default value
        $value = '—';

        // Get scheduled renewal payment datetime
        if ($datetime = $subscription->get_scheduled_renewal_payment()) {
            $value = RP_SUB_Subscription_Admin::get_formatted_list_table_datetime($datetime);
        }

        return $value;
    }

    /**
     * Get subscription list status column value
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return string
     */
    public static function get_subscription_list_column_value_status($subscription)
    {

        return '<mark class="rp-sub-status-label rp-sub-status-label-' . $subscription->get_status() . '"><span>' . $subscription->get_status_label() . '</span></mark>';
    }

    /**
     * Get subscription list recurring total column value
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return string
     */
    public static function get_subscription_list_column_value_recurring_total($subscription)
    {

        $value = wp_kses_post($subscription->get_formatted_recurring_total());

        // Subscription uses automatic payments
        if ($subscription->has_automatic_payments()) {

            // Load payment gateway
            if ($payment_gateway = wc_get_payment_gateway_by_order($subscription->get_suborder())) {

                $value .= '<br>' . sprintf(esc_html__('via %s', 'subscriptio'), ('<strong>' . $payment_gateway->get_method_title() . '</strong>'));
            }
        }
        // Subscription uses manual payments
        else {

            $value .= '<br>' . sprintf(esc_html__('via %s', 'subscriptio'), ('<strong>' . esc_html__('manual payments', 'subscriptio') . '</strong>'));
        }

        return $value;
    }

    /**
     * Get formatted datetime for list table
     *
     * @access public
     * @param object $datetime
     * @return string
     */
    public static function get_formatted_list_table_datetime($datetime)
    {

        return '<time datetime="' . (string) $datetime . '" title="' . $datetime->format_datetime() . '">' . $datetime->format_human_time_diff(24*60*60) . '</time>';
    }

    /**
     * Get post search meta fields
     *
     * @access public
     * @return array
     */
    public function get_post_search_meta_fields()
    {

        return array(
            '_billing_address_index',
            '_rp_sub:meta:related_order',
        );
    }

    /**
     * Get post search contexts
     *
     * @access public
     * @return array
     */
    public function get_post_search_contexts()
    {

        return array(
            'id'    => 'ID',
            'ID'    => 'ID',
            'order' => '_rp_sub:meta:related_order',
        );
    }


    /**
     * =================================================================================================================
     * ADMIN OBJECT EDITING
     * =================================================================================================================
     */

    /**
     * Maybe make subscription editable
     *
     * @access public
     * @param bool $is_editable
     * @param WC_Order $order
     * @return bool
     */
    public function maybe_make_subscription_editable($is_editable, $order)
    {

        // Order is suborder
        if ($order->get_type() === 'rp_sub_subscription') {

            // Get current suborder status
            $status = $order->get_status();

            // Get subscription statuses
            $statuses = RP_SUB_Subscription_Controller::get_subscription_statuses();

            // Override flag
            $is_editable = in_array($status, array('auto-draft', 'draft'), true) || (isset($statuses[$status]) && $statuses[$status]['is_admin_editable']);
        }

        return $is_editable;
    }

    /**
     * Add order actions
     *
     * @access public
     * @param array $actions
     * @return array
     */
    public function add_order_actions($actions)
    {

        global $theorder;

        // Order is subscription suborder
        if ($theorder->get_type() === 'rp_sub_subscription') {

            // Override with subscription actions
            $actions = $this->get_post_actions();
        }

        return $actions;
    }

    /**
     * Register post actions
     *
     * @access public
     * @param object $object
     * @return array
     */
    public function register_post_actions($object = null)
    {

        // TODO:  add_action('woocommerce_order_action_{action_key}', array($this, 'process_action_{handler}'));

        // TODO: Create renewal order in advance?
        // TODO: Process renewal payment in advance?
        // TODO: Send payment reminder?

        return array();
    }

    /**
     * Register meta boxes
     *
     * @access public
     * @return array
     */
    public function register_meta_boxes()
    {

        return array(

            'subscription_data' => array(
                'title'     => esc_html__('Subscription data', 'subscriptio'),
                'context'   => 'normal',
                'priority'  => 'high',
            ),

            'related_orders' => array(
                'title'     => esc_html__('Related orders', 'subscriptio'),
                'context'   => 'normal',
                'priority'  => 'default',
            ),

            'schedule' => array(
                'title'     => esc_html__('Subscription schedule', 'subscriptio'),
                'context'   => 'side',
                'priority'  => 'default',
            ),

            'activity_log' => array(
                'title'     => esc_html__('Activity log', 'subscriptio'),
                'context'   => 'side',
                'priority'  => 'low',
            ),
        );
    }

    /**
     * Register meta boxes whitelist
     *
     * @access public
     * @return array
     */
    public function register_meta_boxes_whitelist()
    {

        return apply_filters('subscriptio_subscription_admin_meta_box_whitelist', array(
            'submitdiv',
            'woocommerce-order-items',
            'woocommerce-order-actions',
            'woocommerce-order-notes',
            'woocommerce-order-downloads',
        ));
    }

    /**
     * Print subscription data meta box
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function print_meta_box_subscription_data($post)
    {

        global $theorder;

        // Get suborder object
        if (!is_object($theorder)) {
            $theorder = wc_get_order($post->ID);
        }

        $order = $theorder;

        // Get subscription object
        if ($subscription = subscriptio_get_subscription($order->get_id())) {

            // Initialize address fields
            RP_SUB_WC_Meta_Box_Order_Data::init_address_fields();

            // Get some properties
            $customer_id = $subscription->get_customer_id('edit');

            // Prepare user id and user string for display
            if ($customer_id) {

                $user_id        = $customer_id;
                $user           = get_user_by('id', $user_id);
                $user_string    = sprintf(esc_html__('%1$s (#%2$s &ndash; %3$s)', 'subscriptio'), $user->display_name, absint($user->ID), $user->user_email);
            }
            else {
                $user_id        = '';
                $user_string    = '';
            }

            // Prepare address fields for display
            $address_fields = array(
                'billing'   => array(),
                'shipping'  => array(),
            );

            // Iterate over address types
            foreach ($address_fields as $address_type => $values) {

                // Iterate over fields of current type
                foreach (RP_SUB_WC_Meta_Box_Order_Data::{'get_' . $address_type . '_fields'}() as $key => $field) {

                    // Format field name
                    $field_name = $address_type . '_' . $key;

                    // Set default fielt type if no type is set
                    if (!isset($field['type'])) {
                        $field['type'] = 'text';
                    }

                    // Set field id if no id is set
                    if (!isset($field['id'])) {
                        $field['id'] = '_' . $address_type . '_' . $key;
                    }

                    // Set show property if no such property is set
                    if (!isset($field['show'])) {
                        $field['show'] = true;
                    }

                    // Set field value if no value is set
                    if (!isset($field['value'])) {

                        if (is_callable(array($order, 'get_' . $field_name))) {
                            $field['value'] = $order->{"get_$field_name"}('edit');
                        }
                        else {
                            $field['value'] = $order->get_meta('_' . $field_name);
                        }
                    }

                    // Format label for display
                    $field['label_for_display'] = esc_html($field['label']);

                    // Format value for display
                    if ($field_name === 'billing_phone') {
                        $field['value_for_display'] = wp_kses_post(wc_make_phone_clickable($field['value']));
                    }
                    else {
                        $field['value_for_display'] = wp_kses_post(make_clickable(esc_html($field['value'])));
                    }

                    // Add to main array
                    $address_fields[$address_type][] = $field;
                }
            }

            // Get time periods
            $time_periods = RP_SUB_Time::get_time_periods_for_display();

            // Print nonce
            wp_nonce_field('rightpress_save_admin_submitted_data', 'rightpress_post_nonce');

            // Include view
            include_once RP_SUB_PLUGIN_PATH . 'views/subscription/subscription-data.php';
        }
    }

    /**
     * Print related orders meta box
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function print_meta_box_related_orders($post)
    {

        // Get subscription for view
        if ($subscription = $this->get_object_for_view($post->ID)) {

            // Check if list is initialized
            if ($this->related_orders_list !== null) {

                // Set subscription
                $this->related_orders_list->set_related_object($subscription);

                // Prepare items
                $this->related_orders_list->prepare_items();

                // Display list
                $this->related_orders_list->display();
            }
        }
    }

    /**
     * Print schedule meta box
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function print_meta_box_schedule($post)
    {

        // Get subscription for view
        if ($subscription = $this->get_object_for_view($post->ID)) {    /* @var $subscription RP_SUB_Subscription */

            // Get time periods with names
            $time_periods = RP_SUB_Time::get_time_periods_for_display();

            // Get all actions
            $all_actions = RP_SUB_Scheduler::get_instance()->get_actions();

            // Get scheduled actions
            $scheduled_actions = array_filter(array(
                'schedule_revision'         => $subscription->get_scheduled_schedule_revision(),
                'renewal_order'             => $subscription->get_scheduled_renewal_order(),
                'payment_reminder'          => $subscription->get_scheduled_payment_reminder(),
                'renewal_payment'           => $subscription->get_scheduled_renewal_payment(),
                'payment_retry'             => $subscription->get_scheduled_payment_retry(),
                'subscription_resume'       => $subscription->get_scheduled_subscription_resume(),
                'subscription_suspend'      => $subscription->get_scheduled_subscription_suspend(),
                'subscription_cancel'       => $subscription->get_scheduled_subscription_cancel(),
                'subscription_expire'       => $subscription->get_scheduled_subscription_expire(),
            ));

            // Prepare period lengths
            $billing_cycle_length = RP_SUB_Time::get_length_from_period_length($subscription->get_billing_cycle());
            $billing_cycle_period = RP_SUB_Time::get_period_from_period_length($subscription->get_billing_cycle());
            $lifespan_length = RP_SUB_Time::get_length_from_period_length($subscription->get_lifespan());
            $lifespan_period = RP_SUB_Time::get_period_from_period_length($subscription->get_lifespan());

            // Include view
            include_once RP_SUB_PLUGIN_PATH . 'views/subscription/schedule.php';
        }
    }

    /**
     * Print activity log meta box
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function print_meta_box_activity_log($post)
    {

        // Get subscription for view
        if ($subscription = $this->get_object_for_view($post->ID)) {

            // Get number of entries to show
            $limit = apply_filters('subscriptio_admin_subscription_related_log_entry_limit', 5);

            // Get log entries
            $log_entries = subscriptio_get_log_entries_related_to_subscription($post->ID, array('limit' => $limit));

            // Include view
            include_once RP_SUB_PLUGIN_PATH . 'views/subscription/activity-log.php';
        }
    }

    /**
     * Maybe set up related orders list
     *
     * @access public
     * @return void
     */
    public function maybe_set_up_related_orders_list()
    {

        global $typenow;
        global $post;

        // Not our post type
        if ($typenow !== 'rp_sub_subscription') {
            return;
        }

        // Initialize list
        $this->related_orders_list = new RP_SUB_Subscription_Related_Orders_List();
    }

    /**
     * Save submitted subscription data
     *
     * @access public
     * @param int $object_id
     * @param array $data
     * @param array $posted
     * @return void
     */
    public function handle_action_save($object_id, $data, $posted)
    {

        // TODO: This method may be restructured a little

        // Load subscription
        if ($subscription = subscriptio_get_subscription($object_id)) {

            // Subscription is no longer editable
            if ($subscription->is_terminated()) {
                throw new RightPress_Exception('rp_sub_subscription_modifying_terminated', esc_html__('Warning! Cancelled or expired subscriptions cannot be modified.', 'subscriptio'));
            }

            try {

                // Check if this is a new manually created subscription
                $is_new = $subscription->has_status('draft', 'auto-draft');

                // Create log entry
                $log_entry = RP_SUB_Log_Entry_Controller::create_log_entry(array(
                    'event_type'        => ($is_new ? 'new_subscription' : 'subscription_edit'),
                    'subscription_id'   => $subscription->get_id(),
                    'actor_id'          => get_current_user_id(),
                    'notes'             => array(
                        ($is_new ? esc_html__('New subscription has been created manually.', 'subscriptio') : esc_html__('Saving changes made by shop manager.', 'subscriptio')),
                    ),
                ), $subscription);

                // Save suborder properties
                $suborder = $subscription->get_suborder();
                $this->save_suborder_properties($suborder, $data, $posted);

                // Get subscription properties to set
                $properties = (!empty($_POST['rp_sub_subscription_settings']) && is_array($_POST['rp_sub_subscription_settings'])) ? $_POST['rp_sub_subscription_settings'] : array();

                // Billing cycle
                if (!empty($_POST['rp_sub_subscription_settings_billing_cycle_length'])) {

                    // Get billing cycle to set
                    $properties['billing_cycle'] = $_POST['rp_sub_subscription_settings_billing_cycle_length'] . ' ' . $_POST['rp_sub_subscription_settings_billing_cycle_period'];

                    // Check if billing cycle has been changed
                    if (!$is_new && $subscription->get_last_payment() && $properties['billing_cycle'] !== $subscription->get_billing_cycle('edit')) {

                        // Add log entry note
                        $log_entry->add_note(esc_html__('Billing cycle length changed. This will take effect at the beginning of the next billing cycle.', 'subscriptio'));

                        // Add admin notice
                        add_settings_error(
                            'rp_sub_subscription',
                            'post_updated',
                            esc_html__('Heads up! Changes to billing cycle length will take effect at the beginning of the next billing cycle. Current billing cycle is not affected.', 'subscriptio'),
                            'updated'
                        );
                    }
                }

                // Lifespan
                if (isset($_POST['rp_sub_subscription_settings_lifespan_length'])) {

                    // Lifespan was cleared
                    if ($_POST['rp_sub_subscription_settings_lifespan_length'] === '') {
                        $properties['lifespan'] = null;
                    }
                    // Lifespan was not cleared
                    else {
                        $properties['lifespan'] = $_POST['rp_sub_subscription_settings_lifespan_length'] . ' ' . $_POST['rp_sub_subscription_settings_lifespan_period'];
                    }
                }

                // Set subscription properties
                $subscription->set_properties($properties);

                // Set submitted status
                $subscription->set_status(RightPress_Help::clean_wc_status(wc_clean(wp_unslash($_POST['order_status']))), 'admin');

                // Set payment gateway
                if (isset($_POST['rp_sub_subscription_payment_gateway'])) {

                    // Currently only change from automatic to manual payments is supported
                    if ($subscription->has_automatic_payments() && $_POST['rp_sub_subscription_payment_gateway'] === 'rp_sub_manual_payments') {

                        // Clear payment gateway from suborder data
                        $subscription->get_suborder()->set_payment_method('');

                        // Clear payment gateway options from subscription data
                        $subscription->set_payment_gateway_options(array());

                        // Add log entry note
                        $log_entry->add_note(esc_html__('Payment method changed to manual payments.', 'subscriptio'));
                    }
                }

                // Subscription requires initial order
                if ($subscription->has_status('pending') && !$subscription->get_initial_order()) {

                    // Add log entry note
                    $log_entry->add_note(esc_html__('Creating subscription initial order.', 'subscriptio'));

                    // Create initial order
                    if ($initial_order = RP_SUB_WC_Order::create_initial_order($subscription)) {

                        // Set initial order id
                        $subscription->set_initial_order_id($initial_order->get_id());
                    }
                }

                // Check required subscription properties
                // Note: This should never happen as we have validation in the frontend
                if ($is_new && (!$subscription->get_suborder()->get_customer_id() || !$subscription->get_suborder()->get_items() || !$subscription->get_billing_cycle())) {

                    // Delete post
                    wp_delete_post($subscription->get_id());

                    // Add log entry note and set status to error
                    $log_entry->add_note(esc_html__('Required properties missing, invalid subscription deleted.', 'subscriptio'));
                    $log_entry->set_status('error');

                    // End logging
                    $log_entry->end_logging($subscription);

                    // Redirect to subscriptions list
                    wp_redirect(admin_url('edit.php?post_type=rp_sub_log_entry'));
                    exit;
                }

                // Save subscription
                $subscription->save();

                // Extra stuff for new subscription
                if ($is_new) {

                    // Trigger actions
                    do_action('subscriptio_subscription_created', $subscription);
                    do_action('subscriptio_subscription_created_via_admin', $subscription, $posted);
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

    /**
     * Save suborder properties
     *
     * @access public
     * @param object $suborder
     * @param array $data
     * @param array $posted
     * @return void
     */
    public function save_suborder_properties(&$suborder, $data, $posted)
    {

        // Initialize address fields
        RP_SUB_WC_Meta_Box_Order_Data::init_address_fields();

        // Store properties to set
        $properties = array();

        // Create order key
        if (!$suborder->get_order_key()) {
            $properties['order_key'] = RightPress_Help::wc_version_gte('3.5.4') ? wc_generate_order_key() : ('wc_' . apply_filters('woocommerce_generate_order_key', 'order_' . wp_generate_password(13, false)));
        }

        // Update customer
        $customer_id = isset($_POST['customer_user']) ? absint($_POST['customer_user']) : null;

        if ($customer_id !== null && RightPress_Help::wp_user_exists($customer_id)) {
            if ($customer_id !== $suborder->get_customer_id()) {
                $properties['customer_id'] = $customer_id;
            }
        }

        // Update addresses fields
        foreach (array('billing', 'shipping') as $address_type) {

            // Iterate over fields of current type
            foreach (RP_SUB_WC_Meta_Box_Order_Data::{'get_' . $address_type . '_fields'}() as $key => $field) {

                if (!isset($field['id'])) {
                    $field['id'] = '_' . $address_type . '_' . $key;
                }

                if (!isset($_POST[$field['id']])) {
                    continue;
                }

                if (is_callable(array($suborder, ('set_' . $address_type . '_' . $key)))) {
                    $properties[$address_type . '_' . $key] = wc_clean(wp_unslash($_POST[$field['id']]));
                }
                else {
                    $suborder->update_meta_data($field['id'], wc_clean(wp_unslash($_POST[$field['id']])));
                }
            }
        }

        // Set created via prop if new post
        if (isset($_POST['original_post_status']) && $_POST['original_post_status'] === 'auto-draft') {
            $properties['created_via'] = 'admin';
        }

        // Save suborder data
        $suborder->set_props($properties);
    }


    /**
     * =================================================================================================================
     * OTHER METHODS
     * =================================================================================================================
     */

    /**
     * Prevent delete of non-terminated subscriptions
     *
     * Based on user_has_cap filter
     *
     * @access public
     * @param array $allcaps
     * @param array $caps
     * @param array $args
     * @param object $user
     * @return array
     */
    public function maybe_prevent_subscription_delete($allcaps, $caps, $args, $user)
    {

        // Check if delete_shop_orders capability was requested
        if (in_array('delete_shop_orders', $caps, true)) {

            // Check if post id is defined and is of our type
            if (!empty($args[2]) && is_numeric($args[2]) && get_post_type($args[2]) === 'rp_sub_subscription') {

                // Attempt to load subscription
                if ($subscription = subscriptio_get_subscription($args[2])) {

                    // Subscription is not terminated
                    if (!$subscription->is_terminated()) {

                        // Set capability to false
                        $allcaps['delete_shop_orders'] = false;
                    }
                }
            }
        }

        return $allcaps;
    }





}

RP_SUB_Subscription_Admin::get_instance();
