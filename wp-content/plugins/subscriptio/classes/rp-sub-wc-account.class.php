<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
RightPress_Loader::load_class_collection('wc-account');

/**
 * WooCommerce Account Controller
 *
 * @class RP_SUB_WC_Account
 * @package Subscriptio
 * @author RightPress
 */

class RP_SUB_WC_Account extends RightPress_WC_Account
{

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    // Define endpoints
    protected $endpoints = array(
        'subscriptions',
        'view-subscription',
        'edit-subscription-billing-address',
        'edit-subscription-shipping-address',
    );

    // Menu priority
    protected $menu_priority = 30;

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

        // Add shortcodes
        add_shortcode('subscriptio_customer_subscriptions', array($this, 'shortcode_customer_subscriptions'));
        add_shortcode('rp_sub_customer_subscriptions', array($this, 'shortcode_customer_subscriptions'));

        // Display related subscriptions on single order view page
        add_action(
            apply_filters('subscriptio_order_related_subscriptions_hook', 'woocommerce_order_details_after_order_table'),
            array($this, 'display_order_related_subscriptions'),
            apply_filters('subscriptio_order_related_subscriptions_position', 9)
        );

        // Print view subscription templates
        add_action('subscriptio_account_view_subscription', array($this, 'print_subscription_overview'),    apply_filters('subscriptio_account_subscription_overview_position', 20));
        add_action('subscriptio_account_view_subscription', array($this, 'print_subscription_actions'),     apply_filters('subscriptio_account_subscription_actions_position', 30));
        add_action('subscriptio_account_view_subscription', array($this, 'print_subscription_items'),       apply_filters('subscriptio_account_subscription_items_position', 40));
        add_action('subscriptio_account_view_subscription', array($this, 'print_subscription_addresses'),   apply_filters('subscriptio_account_subscription_addresses_position', 50));
        add_action('subscriptio_account_view_subscription', array($this, 'print_subscription_downloads'),   apply_filters('subscriptio_account_subscription_downloads_position', 60));
        add_action('subscriptio_account_view_subscription', array($this, 'print_subscription_orders'),      apply_filters('subscriptio_account_subscription_orders_position', 70));
        add_action('subscriptio_account_view_subscription', array($this, 'print_subscription_notes'),       apply_filters('subscriptio_account_subscription_notes_position', 80));

        // Print subscription item template
        add_action('subscriptio_account_subscription_item', array($this, 'print_subscription_item'), 10, 2);

        // Process action and submitted form data
        add_action('template_redirect', array($this, 'process_subscription_action'));
        add_action('template_redirect', array($this, 'save_subscription_address'));
    }

    /**
     * =================================================================================================================
     * ACCOUNT ENDPOINTS
     * =================================================================================================================
     */

    /**
     * Subscriptions endpoint
     *
     * @access public
     * @return void
     */
    public function wc_account_endpoint_subscriptions()
    {

        // User must be logged in
        if (!is_user_logged_in()) {
            return;
        }

        // Display subscription list
        echo RP_SUB_WC_Account::get_customer_subscription_list_html('subscriptions');
    }

    /**
     * View Subscription endpoint
     *
     * Note: $subscription_id is not sanitized
     *
     * @access public
     * @param string $subscription_id
     * @return void
     */
    public function wc_account_endpoint_view_subscription($subscription_id)
    {

        // Get subscription for display
        if ($subscription = $this->get_subscription_for_display($subscription_id)) {

            // Include template
            RP_SUB_Help::include_template('myaccount/view-subscription', array('subscription' => $subscription));
        }
    }

    /**
     * Edit subscription billing address endpoint
     *
     * Note: $subscription_id is not sanitized
     *
     * @access public
     * @param string $subscription_id
     * @return void
     */
    public function wc_account_endpoint_edit_subscription_billing_address($subscription_id)
    {

        $this->endpoint_edit_subscription_address($subscription_id, 'billing');
    }

    /**
     * Edit subscription shipping address endpoint
     *
     * Note: $subscription_id is not sanitized
     *
     * @access public
     * @param string $subscription_id
     * @return void
     */
    public function wc_account_endpoint_edit_subscription_shipping_address($subscription_id)
    {

        $this->endpoint_edit_subscription_address($subscription_id, 'shipping');
    }

    /**
     * Edit subscription address endpoint
     *
     * Shared method for endpoints edit-subscription-billing-address and edit-subscription-shipping-address
     *
     * @access public
     * @param string $subscription_id
     * @param string $context           Either 'billing' or 'shipping'
     * @return void
     */
    public function endpoint_edit_subscription_address($subscription_id, $context)
    {

        // Get subscription for display
        if ($subscription = $this->get_subscription_for_display($subscription_id)) {

            // Shipping address page and subscription does not need shipping
            if ($context === 'shipping' && !$subscription->needs_shipping_address()) {
                $this->redirect_to_subscription($subscription);
            }

            // Get country
            $country = wc_get_post_data_by_key("{$context}_country", $subscription->get_suborder()->{"get_{$context}_country"}('edit'));

            // Get address fields
            $address_fields = WC()->countries->get_address_fields(esc_attr($country), "{$context}_");

            // Populate with values from subscription
            foreach ($address_fields as $key => $field) {

                // Getter name
                $method = 'get_' . $key;

                // Attempt to get value
                if (is_callable(array($subscription->get_suborder(), $method))) {

                    // Set value
                    $address_fields[$key]['value'] = $subscription->get_suborder()->{$method}('edit');
                }
            }

            // Display form
            RP_SUB_Help::include_template('myaccount/form-edit-address', array(
                'subscription'      => $subscription,
                'address_fields'    => $address_fields,
                'context'           => $context,
            ));

            // Enqueue WooCommerce address-related scripts
            wp_enqueue_script('wc-country-select');
            wp_enqueue_script('wc-address-i18n');
        }
    }

    /**
     * =================================================================================================================
     * ACTION & DATA PROCESSING
     * =================================================================================================================
     */

    /**
     * Process subscription action
     *
     * This method handles customer subscription pausing, resuming, cancelling and reactivating
     *
     * @access public
     * @return void
     */
    public function process_subscription_action()
    {

        global $wp_query;

        // Not our request
        if (!RP_SUB_WC_Account::is_subscription_page() || !isset($_GET['action']) || !in_array($_GET['action'], array('pause', 'resume', 'set_to_cancel', 'cancel', 'reactivate'), 'true')) {
            return;
        }

        // Get subscription for display
        if ($subscription = $this->get_subscription_for_display($wp_query->query_vars['view-subscription'])) {

            // Reference subscription action
            $action = $_GET['action'];

            // Start logging
            $log_entry = RP_SUB_Log_Entry_Controller::create_log_entry(array(
                'event_type'        => "subscription_$action",
                'subscription_id'   => $subscription->get_id(),
                'actor_id'          => get_current_user_id(),
                'notes'             => array(
                    esc_html__('Action requested by customer via their account page.', 'subscriptio')
                ),
            ), $subscription);

            try {

                // Process action
                $subscription->{$action}('customer');

                // Get success message
                if ($action === 'pause') {
                    $message = esc_html__('Subscription has been paused.', 'subscriptio');
                }
                else if ($action === 'resume') {
                    $message = esc_html__('Subscription has been resumed.', 'subscriptio');
                }
                else if ($action === 'set_to_cancel') {
                    $message = esc_html__('Subscription has been set to cancel at the end of this billing cycle.', 'subscriptio');
                }
                else if ($action === 'cancel') {
                    $message = esc_html__('Subscription has been cancelled.', 'subscriptio');
                }
                else if ($action === 'reactivate') {
                    $message = esc_html__('Subscription has been reactivated.', 'subscriptio');
                }

                // Add note to log entry
                $log_entry->add_note($message);

                // Show notice to customer
                wc_add_notice($message, 'success');
            }
            catch (Exception $e) {

                // Get error message
                if ($action === 'pause') {
                    $message = esc_html__('Subscription could not be paused.', 'subscriptio');
                }
                else if ($action === 'resume') {
                    $message = esc_html__('Subscription could not be resumed.', 'subscriptio');
                }
                else if ($action === 'set_to_cancel') {
                    $message = esc_html__('Subscription could not be set to cancel.', 'subscriptio');
                }
                else if ($action === 'cancel') {
                    $message = esc_html__('Subscription could not be cancelled.', 'subscriptio');
                }
                else if ($action === 'reactivate') {
                    $message = esc_html__('Subscription could not be reactivated.', 'subscriptio');
                }

                // Handle caught exception
                $log_entry->handle_caught_exception($e, $message);

                // Show notice to customer
                wc_add_notice($message, 'error');
            }

            // End logging
            $log_entry->end_logging($subscription);
        }

        // Redirect to subscription page
        $this->redirect_to_subscription($subscription);
    }

    /**
     * Save submitted subscription address
     *
     * WooCommerce address field validation based on WC_Form_Handler::save_address
     *
     * @access public
     * @return void
     */
    public function save_subscription_address()
    {

        // Form submitted
        if (isset($_POST['action']) && $_POST['action'] === 'subscriptio_edit_address' && !empty($_POST['context']) && !empty($_POST['subscription_id'])) {

            // Reference context
            $context = $_POST['context'] === 'shipping' ? 'shipping' : 'billing';

            // Get subscription for display
            if ($subscription = $this->get_subscription_for_display($_POST['subscription_id'])) {

                // Verify nonce
                if (!isset($_REQUEST['subscriptio-edit-address-nonce']) || !wp_verify_nonce($_REQUEST['subscriptio-edit-address-nonce'], 'subscriptio-edit_address')) {
                    $this->redirect_to_subscription($subscription);
                }

                // Get country
                $country = wc_get_post_data_by_key("{$context}_country", $subscription->get_suborder()->{"get_{$context}_country"}('edit'));

                // Get address fields
                $address_fields = WC()->countries->get_address_fields(esc_attr($country), "{$context}_");

                // Iterate over address fields
                foreach ($address_fields as $key => $field) {

                    if (!isset($field['type'])) {
                        $field['type'] = 'text';
                    }

                    // Get value
                    if ($field['type'] === 'checkbox') {
                        $value = (int)isset($_POST[$key]);
                    } else {
                        $value = isset($_POST[$key]) ? wc_clean(wp_unslash($_POST[$key])) : '';
                    }

                    // Required field validation
                    if (!empty($field['required']) && empty($value)) {
                        wc_add_notice(sprintf(esc_html__('%s is a required field.', 'subscriptio'), $field['label']), 'error');
                    }

                    // Field value validation
                    if (!empty($value)) {

                        // Proceed depending on validation rule
                        if (!empty($field['validate']) && is_array($field['validate'])) {
                            foreach ($field['validate'] as $rule) {

                                switch ($rule) {

                                    // Postcode validation
                                    case 'postcode':

                                        $value = wc_format_postcode($value, $country);

                                        if ($value !== '' && !WC_Validation::is_postcode($value, $country)) {
                                            wc_add_notice(esc_html__('Please enter a valid postcode / ZIP.', 'subscriptio'), 'error');
                                        }

                                        break;

                                    // Phone validation
                                    case 'phone':

                                        if ($value !== '' && !WC_Validation::is_phone($value)) {
                                            wc_add_notice(sprintf(esc_html__('%s is not a valid phone number.', 'subscriptio'), '<strong>' . $field['label'] . '</strong>'), 'error');
                                        }

                                        break;

                                    // Email validation
                                    case 'email':

                                        $value = strtolower($value);

                                        if (!is_email($value)) {
                                            wc_add_notice(sprintf(esc_html__('%s is not a valid email address.', 'subscriptio'), '<strong>' . $field['label'] . '</strong>'), 'error');
                                        }

                                        break;
                                }
                            }
                        }
                    }

                    try {

                        // Set property
                        if (is_callable(array($subscription->get_suborder(), "set_$key"))) {
                            $subscription->get_suborder()->{"set_$key"}($value);
                        }
                    } catch (WC_Data_Exception $e) {
                        if ($e->getErrorCode() !== 'customer_invalid_billing_email') {
                            wc_add_notice($e->getMessage(), 'error');
                        }
                    }
                }

                // Address validated successfully
                if (!wc_notice_count('error')) {

                    // Save subscription
                    $subscription->save();

                    // Add notice
                    wc_add_notice(sprintf(esc_html__('Subscription %s address changed successfully.', 'subscriptio'), ($context === 'shipping' ? esc_html__('shipping', 'subscriptio') : esc_html__('billing', 'subscriptio'))));

                    // Redirect to subscription page
                    $this->redirect_to_subscription($subscription);
                }
            }
        }
    }


    /**
     * =================================================================================================================
     * SUBSCRIPTION PAGE TEMPLATE CALLBACKS
     * =================================================================================================================
     */

    /**
     * Print subscription overview
     *
     * @access public
     * @param object $subscription
     * @return void
     */
    public function print_subscription_overview($subscription)
    {

        // Include template
        RP_SUB_Help::include_template('subscription/subscription-overview', array(
            'subscription'  => $subscription,
            'overview_data' => RP_SUB_WC_Account::get_subscription_overview_data($subscription),
        ));
    }

    /**
     * Print subscription actions
     *
     * @access public
     * @param object $subscription
     * @return void
     */
    public function print_subscription_actions($subscription)
    {

        // Get actions
        if ($actions = RP_SUB_WC_Account::get_subscription_actions($subscription, false)) {

            // Include template
            RP_SUB_Help::include_template('subscription/subscription-actions', array(
                'subscription'  => $subscription,
                'actions'       => $actions,
            ));
        }
    }

    /**
     * Print subscription downloads
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return void
     */
    public function print_subscription_downloads($subscription)
    {

        // Check if downloads should be displayed
        if ($subscription->has_downloadable_item() && $subscription->is_download_permitted()) {

            // Get downloads
            if ($downloads = $subscription->get_downloadable_items()) {

                // Include WooCommerce template
                wc_get_template('order/order-downloads.php', array(
                    'downloads'     => $downloads,
                    'show_title'    => true,
                ));
            }
        }
    }

    /**
     * Print subscription items
     *
     * @access public
     * @param object $subscription
     * @return void
     */
    public function print_subscription_items($subscription)
    {

        // Get items
        if ($items = $subscription->get_items()) {

            // Include template
            RP_SUB_Help::include_template('subscription/subscription-items', array(
                'subscription'  => $subscription,
                'items'         => $items,
            ));
        }
    }

    /**
     * Print subscription item
     *
     * @access public
     * @param object $subscription
     * @param object $item
     * @return void
     */
    public function print_subscription_item($subscription, $item)
    {

        // Check if subscription item is visible
        if (apply_filters('subscriptio_subscription_item_visible', true, $item, $subscription)) {

            // Get product
            $product = $item->get_product();

            // Check if product is visible
            $product_is_visible = ($product && $product->is_visible());

            // Get purchase note
            $purchase_note = ($product && $subscription->has_status(apply_filters('subscriptio_purchase_note_subscription_statuses', array('trial', 'active', 'paused', 'overdue', 'suspended')))) ? $product->get_purchase_note() : '';

            // Include template
            RP_SUB_Help::include_template('subscription/subscription-item', array(
                'subscription'          => $subscription,
                'item_id'               => $item->get_id(),
                'item'                  => $item,
                'purchase_note'         => $purchase_note,
                'product'               => $product,
                'product_is_visible'    => $product_is_visible,
                'product_permalink'     => apply_filters('subscriptio_subscription_item_permalink', $product_is_visible ? $product->get_permalink($item) : '', $item, $subscription),
            ));
        }
    }

    /**
     * Print subscription addresses
     *
     * @access public
     * @param object $subscription
     * @return void
     */
    public function print_subscription_addresses($subscription)
    {

        // Check customer
        if (is_user_logged_in() && get_current_user_id() === $subscription->get_customer_id()) {

            // Include template
            RP_SUB_Help::include_template('subscription/subscription-addresses', array(
                'subscription'  => $subscription,
                'show_shipping' => !wc_ship_to_billing_address_only() && $subscription->needs_shipping_address(),
            ));
        }
    }

    /**
     * Print subscription orders
     *
     * @access public
     * @param object $subscription
     * @return void
     */
    public function print_subscription_orders($subscription)
    {

        // Get orders
        if ($orders = subscriptio_get_orders_related_to_subscription($subscription)) {

            // Include template
            RP_SUB_Help::include_template('subscription/subscription-orders', array(
                'subscription'  => $subscription,
                'orders'        => $orders,
            ));
        }
    }

    /**
     * Print subscription notes
     *
     * @access public
     * @param object $subscription
     * @return void
     */
    public function print_subscription_notes($subscription)
    {

        // Get notes
        if ($notes = $subscription->get_customer_subscription_notes()) {

            // Include template
            RP_SUB_Help::include_template('subscription/subscription-notes', array(
                'subscription'  => $subscription,
                'notes'         => $notes,
            ));
        }
    }


    /**
     * =================================================================================================================
     * OTHER METHODS
     * =================================================================================================================
     */

    /**
     * Get menu items
     *
     * @access public
     * @return array
     */
    public function get_menu_items()
    {

        $menu_items = array();

        // Subscriptions
        if (RP_SUB_WC_Account::can_display_subscription_list()) {
            $menu_items[] = array('subscriptions' => esc_html__('Subscriptions', 'subscriptio'));
        }

        return $menu_items;
    }

    /**
     * Get endpoint page title
     *
     * @access public
     * @param string $endpoint
     * @param string $var_value
     * @return string|null
     */
    public function get_endpoint_page_title($endpoint, $var_value)
    {

        switch ($endpoint) {

            // Subscriptions
            case 'subscriptions':
                return esc_html__('Subscriptions', 'subscriptio');

            // Individual Subscription
            case 'view-subscription':
            case 'edit-subscription-billing-address':
            case 'edit-subscription-shipping-address':
                if ($subscription = subscriptio_get_subscription($var_value)) {
                    return sprintf(esc_html__('Subscription %s', 'subscriptio'), $subscription->get_subscription_number());
                }

            // No title defined
            default:
                return null;
        }
    }

    /**
     * Get subscription overview data
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @return array
     */
    public function get_subscription_overview_data($subscription)
    {

        $data = array();

        // Get status label
        $status_label = $subscription->get_status_label();

        // Status
        $data['status'] = array(
            'label' => esc_html__('Status', 'subscriptio'),
            'value' => '<strong>' . $status_label . '</strong>',
        );

        // Started
        if ($first_payment = $subscription->get_first_payment()) {
            $data['first_payment'] = array(
                'label' => esc_html__('Started', 'subscriptio'),
                'value' => $first_payment->format_date(),
            );
        }

        // Status since
        if ($status_since = $subscription->get_status_since()) {
            if ($subscription->has_status(array('paused', 'overdue', 'suspended', 'cancelled', 'expired'))) {
                $data['status_since'] = array(
                    'label' => sprintf(esc_html__('%s since', 'subscriptio'), $status_label),
                    'value' => $status_since->format_date(),
                );
            }
        }

        // Scheduled resumption
        if ($subscription->has_status('paused')) {
            if ($scheduled_subscription_resume = RP_SUB_Scheduler::get_expected_scheduled_subscription_resume_datetime_for_display($subscription)) {
                $data['subscription_resume'] = array(
                    'label' => esc_html__('Resumes on', 'subscriptio'),
                    'value' => $scheduled_subscription_resume->format_date(),
                );
            }
        }

        // Scheduled renewal payment
        if ($scheduled_renewal_payment = RP_SUB_Scheduler::get_expected_scheduled_renewal_payment_datetime_for_display($subscription)) {
            $data['renewal_payment'] = array(
                'label' => esc_html__('Next payment', 'subscriptio'),
                'value' => $scheduled_renewal_payment->format_date(),
            );
        }

        // Scheduled suspension
        if ($scheduled_subscription_suspend = RP_SUB_Scheduler::get_expected_scheduled_subscription_suspend_datetime_for_display($subscription)) {
            $data['subscription_suspend'] = array(
                'label' => esc_html__('Suspension', 'subscriptio'),
                'value' => $scheduled_subscription_suspend->format_date(),
            );
        }

        // Scheduled cancellation
        if ($scheduled_subscription_cancel = RP_SUB_Scheduler::get_expected_scheduled_subscription_cancel_datetime_for_display($subscription)) {
            $data['subscription_cancel'] = array(
                'label' => esc_html__('Cancellation', 'subscriptio'),
                'value' => $scheduled_subscription_cancel->format_date(),
            );
        }

        // Scheduled expiration
        if ($scheduled_subscription_expire = RP_SUB_Scheduler::get_expected_scheduled_subscription_expire_datetime_for_display($subscription)) {
            $data['subscription_expire'] = array(
                'label' => esc_html__('Ends on', 'subscriptio'),
                'value' => $scheduled_subscription_expire->format_date(),
            );
        }

        // Allow developers to override and return
        return apply_filters('subscriptio_subscription_overview_data', $data, $subscription);
    }

    /**
     * Check if subscription list should be displayed
     *
     * @access public
     * @param string $context
     * @return bool
     */
    public static function can_display_subscription_list($context = 'myaccount')
    {

        // Customer not logged in
        if (!is_user_logged_in()) {
            return false;
        }

        // Customer has at least one subscription
        if (subscriptio_get_customer_subscriptions(null, true)) {
            return true;
        }

        // Customer has no subscriptions
        return apply_filters('subscriptio_display_empty_subscription_list', RP_SUB_Settings::is('display_empty_subscription_list'), $context);
    }

    /**
     * Display customer subscription list via shortcode
     *
     * @access public
     * @param mixed $attributes
     * @return string
     */
    public function shortcode_customer_subscriptions($attributes)
    {

        // Do not display on specific pages
        if (is_home() || is_archive() || is_search() || is_feed()) {
            return '';
        }

        // Get subscription list html and return it
        return '<div class="woocommerce">' . RP_SUB_WC_Account::get_customer_subscription_list_html('shortcode', true) . '</div>';
    }

    /**
     * Get customer subscription list html
     *
     * @access public
     * @param string $context
     * @param bool $display_empty
     * @param array $subscriptions
     * @return string
     */
    public static function get_customer_subscription_list_html($context = 'subscriptions', $display_empty = false, $subscriptions = null)
    {

        // Check if subscription list needs to be displayed
        if (!RP_SUB_WC_Account::can_display_subscription_list($context) && !$display_empty) {
            return;
        }

        // Get subscriptions
        $subscriptions = ($subscriptions !== null) ? $subscriptions : (is_user_logged_in() ? subscriptio_get_subscriptions(array('customer' => get_current_user_id())) : array());

        // Start output buffer
        ob_start();

        // Include subscription list template
        RP_SUB_Help::include_template('myaccount/subscriptions', array(
            'subscriptions' => $subscriptions,
            'columns'       => apply_filters('subscriptio_account_subscriptions_columns', array(
                'subscription-number'   => esc_html__('Subscription', 'subscriptio'),
                'subscription-status'   => esc_html__('Status', 'subscriptio'),
                'subscription-products' => esc_html__('Products', 'subscriptio'),
                'subscription-total'    => esc_html__('Recurring', 'subscriptio'),
                'subscription-actions'  => esc_html__('Actions', 'subscriptio'),
            )),
        ));

        // Get output buffer
        return ob_get_clean();
    }

    /**
     * Display related subscriptions on single order view page
     *
     * @access public
     * @param object $order
     * @return void
     */
    public function display_order_related_subscriptions($order)
    {

        // Get subscriptions by order id
        if ($subscriptions = subscriptio_get_subscriptions(array('order' => $order, 'customer' => get_current_user_id()))) {

            // Allow developers to hide related subscriptions
            if (!apply_filters('subscriptio_display_order_related_subscriptions', true)) {
                return;
            }

            // Print title
            echo '<h2 class="subscriptio-order-related-subscriptions-title">' . esc_html__('Related Subscriptions', 'subscriptio') . '</h2>';

            // Print related subscription list
            echo RP_SUB_WC_Account::get_customer_subscription_list_html('orders', false, $subscriptions);

            // Disable order again functionality for renewal orders
            if (subscriptio_is_subscription_renewal_order($order)) {
                remove_action('woocommerce_order_details_after_order_table', 'woocommerce_order_again_button');
            }
        }
    }

    /**
     * Get subscription for display from unsanitized subscription id
     *
     * @access public
     * @param int $subscription_id
     * @return RP_SUB_Subscription|bool
     */
    public function get_subscription_for_display($subscription_id)
    {

        // User must be logged in
        if (!is_user_logged_in()) {
            return false;
        }

        // Get subscription object
        $subscription = subscriptio_get_subscription((int) $subscription_id, get_current_user_id());

        // Unable to load subscription object
        if (!$subscription) {
            echo '<div class="woocommerce-error">' . esc_html__('Invalid subscription.', 'subscriptio') . ' <a href="' . get_permalink(wc_get_page_id('myaccount')).'" class="wc-forward">'. esc_html__('My Account', 'subscriptio') .'</a>' . '</div>';
            return false;
        }

        // Return subscription object
        return $subscription;
    }

    /**
     * Redirect to subscription
     *
     * @access public
     * @param object $subscription
     * @return void
     */
    public function redirect_to_subscription($subscription)
    {

        // Get subscription endpoint url
        $url = RP_SUB_WC_Account::get_subscription_endpoint_url($subscription, 'view-subscription');

        // Redirect user
        wp_redirect($url);
        exit;
    }

    /**
     * Get subscription endpoint url
     *
     * @access public
     * @param RP_SUB_Subscription|int $subscription
     * @param string $action
     * @return string
     */
    public static function get_subscription_endpoint_url($subscription, $action)
    {

        // Get subscription id
        $subscription_id = is_a($subscription, 'RP_SUB_Subscription') ? $subscription->get_id() : $subscription;

        // Return endpoint url
        return wc_get_endpoint_url($action, $subscription_id, get_permalink(wc_get_page_id('myaccount')));
    }

    /**
     * Get subscription actions
     *
     * @access public
     * @param RP_SUB_Subscription $subscription
     * @param bool $is_list
     * @return array
     */
    public static function get_subscription_actions($subscription, $is_list)
    {

        $actions = array();

        // View subscription
        if ($is_list) {
            $actions['view'] = array(
                'name'  => esc_html__('View', 'subscriptio'),
                'url'   => RP_SUB_WC_Account::get_subscription_endpoint_url($subscription, 'view-subscription'),
            );
        }

        // Check if any other actions are allowed
        if (!$is_list && !$subscription->is_terminated()) {

            // Pay now
            if ($subscription->needs_payment()) {

                $order_to_pay = null;

                // Subscription is pending initial payment
                if ($subscription->is_pending_initial_payment()) {

                    // Get initial order
                    $order = $subscription->get_initial_order();

                    // Check if order status is 'pending'
                    // Note: WooCommerce does not allow making payments to orders that are on hold (i.e. waiting for manual payment to be applied by admin)
                    if ($order->has_status('pending')) {

                        // Set order to pay
                        $order_to_pay = $order;
                    }
                }
                // Subscription is pending renewal payment and payment is not automatic or subscription
                else if (!$subscription->has_status('trial', 'active') || !$subscription->has_automatic_payments()) {

                    // Get pending renewal order
                    $order = $subscription->get_pending_renewal_order();

                    // Set order to pay
                    $order_to_pay = $order;
                }

                // Check if order to pay was set
                if ($order_to_pay) {

                    // Add action
                    $actions['pay'] = array(
                        'name'  => esc_html__('Make payment', 'subscriptio'),
                        'url'   => $order_to_pay->get_checkout_payment_url(),
                    );
                }
            }

            // Pause
            if ($subscription->can_be_paused('customer')) {
                $actions['pause'] = array(
                    'name'  => esc_html__('Pause', 'subscriptio'),
                    'url'   => add_query_arg('action', 'pause', RP_SUB_WC_Account::get_subscription_endpoint_url($subscription, 'view-subscription')),
                );
            }
            // Resume
            else if ($subscription->can_be_resumed('customer')) {
                $actions['resume'] = array(
                    'name'  => esc_html__('Resume', 'subscriptio'),
                    'url'   => add_query_arg('action', 'resume', RP_SUB_WC_Account::get_subscription_endpoint_url($subscription, 'view-subscription')),
                );
            }

            // Set to cancel at the end of current billing cycle
            if (RP_SUB_Settings::is('customer_cancelling', 'delayed') && $subscription->can_be_set_to_cancel('customer')) {
                $actions['set_to_cancel'] = array(
                    'name'  => esc_html__('Cancel', 'subscriptio'),
                    'url'   => add_query_arg('action', 'set_to_cancel', RP_SUB_WC_Account::get_subscription_endpoint_url($subscription, 'view-subscription')),
                );
            }
            // Cancel immediately
            else if ($subscription->can_be_cancelled('customer', false, true)) {
                $actions['cancel'] = array(
                    'name'  => esc_html__('Cancel', 'subscriptio'),
                    'url'   => add_query_arg('action', 'cancel', RP_SUB_WC_Account::get_subscription_endpoint_url($subscription, 'view-subscription')),
                );
            }

            // Reactivate subscription that has been set to cancel
            if ($subscription->can_be_reactivated('customer')) {
                $actions['reactivate'] = array(
                    'name'  => esc_html__('Reactivate', 'subscriptio'),
                    'url'   => add_query_arg('action', 'reactivate', RP_SUB_WC_Account::get_subscription_endpoint_url($subscription, 'view-subscription')),
                );
            }
        }

        // Allow developers to override and return
        return apply_filters('subscriptio_subscription_actions', $actions, $subscription, $is_list);
    }

    /**
     * Check if current request is for My Account subscription page
     *
     * @access public
     * @return bool
     */
    public static function is_subscription_page()
    {

        global $wp_query;

        return is_page(wc_get_page_id('myaccount')) && isset($wp_query->query_vars['view-subscription']);
    }





}

RP_SUB_WC_Account::get_instance();
