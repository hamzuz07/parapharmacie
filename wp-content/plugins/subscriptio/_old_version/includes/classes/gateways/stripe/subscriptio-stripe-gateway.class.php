<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Subscriptio Stripe payment gateway class
 *
 * @class Subscriptio_Stripe_Gateway
 * @package Subscriptio
 * @author RightPress
 */
if (!class_exists('Subscriptio_Stripe_Gateway') && class_exists('WC_Payment_Gateway')) {

class Subscriptio_Stripe_Gateway extends WC_Payment_Gateway
{

    /**
     * Constructor class
     *
     * @access public
     * @param mixed $id
     * @return void
     */
    public function __construct($id = null)
    {
        global $woocommerce;

        // Gateway configuration
        $this->id                   = 'subscriptio_stripe';
        $this->icon                 = apply_filters('subscriptio_stripe_logo_url', set_url_scheme(SUBSCRIPTIO_PLUGIN_URL) . '/assets/gateways/stripe/images/subscriptio_stripe_' . ($woocommerce->countries->get_base_country() === 'US' ? 'us' : 'worldwide') . '.png');
        $this->has_fields           = true;
        $this->endpoint_url         = 'https://api.stripe.com/';
        $this->supports             = array('products', 'refunds', 'subscriptio');
        $this->method_title         = esc_html__('Stripe by Subscriptio', 'subscriptio-stripe');
        $this->method_description    = sprintf(esc_html__('Stripe requires all communications to be secured with TLS 1.2, make sure that your server %s.', 'subscriptio-paypal-ec'), ('<a href="http://url.rightpress.net/payment-gateways-tls-version-help">' . esc_html__('supports it', 'subscriptio-paypal-ec') . '</a>'));

        // Load settings fields
        $this->init_form_fields();
        $this->init_settings();

        // Define properties
        $this->enabled          = apply_filters('subscriptio_stripe_enabled', $this->get_option('enabled'));
        $this->debug            = apply_filters('subscriptio_stripe_debug', $this->get_option('debug'));
        $this->capture          = apply_filters('subscriptio_stripe_capture', $this->get_option('capture')) === 'yes' ? true : false;
        $this->title            = $this->get_option('title');
        $this->description      = $this->get_option('description');
        $this->secret_key       = $this->debug == 'yes' ? $this->get_option('test_secret_key') : $this->get_option('live_secret_key');
        $this->publishable_key  = $this->debug == 'yes' ? $this->get_option('test_publishable_key') : $this->get_option('live_publishable_key');
        $this->checkout_style   = $this->get_option('checkout_style');
        $this->checkout_image   = $this->get_option('checkout_image');

        // Save gateway settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        // Enqueue Stripe scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_on_checkout'));

        // Get admin notices and disable payment gateway if any
        $this->notices = $this->get_notices();

        // Disable payments and show admin notices if something is wrong with settings
        if ($this->enabled == 'yes' && !empty($this->notices)) {

            // Show admin notices
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['subscriptio_stripe_test_secret_key'])) {
                add_action('admin_notices', array($this, 'show_admin_notices'));
            }

            // Disable payments
            if (count($this->notices) > 1 || !isset($this->notices['ssl'])) {
                $this->enabled = 'no';
            }
        }
    }

    /**
     * Get notices for admin
     *
     * @access public
     * @param bool $return_error
     * @return array|bool
     */
    public function get_notices()
    {
        $notices = array();

        // Check for SSL support
        if (get_option('woocommerce_force_ssl_checkout') != 'yes' && !class_exists('WordPressHTTPS') && !is_ssl()) {
            $notices['ssl'] = esc_html__('Subscriptio Stripe payment gateway requires full SSL support and enforcement during Checkout. Only test mode will work until this is solved.', 'subscriptio-stripe');
        }

        // Check secret key
        if (empty($this->secret_key)) {
            $notices['secret'] = esc_html__('Subscriptio Stripe payment gateway requires Stripe Secret Key to be set.', 'subscriptio-stripe');
        }

        // Check publishable key
        if (empty($this->publishable_key)) {
            $notices['publishable'] = esc_html__('Subscriptio Stripe payment gateway requires Stripe Publishable Key to be set.', 'subscriptio-stripe');
        }

        return $notices;
    }

    /**
     * Show admin notices
     *
     * @access public
     * @return void
     */
    public function show_admin_notices()
    {
        foreach ($this->notices as $notice) {
            echo '<div class="error"><p>' . esc_html__($notice, 'subscriptio-stripe') . '</p></div>';
        }
    }

    /**
     * Check if this gateway is available for use
     *
     * @access public
     * @return bool
     */
    public function is_available()
    {
        return $this->enabled == 'yes' ? true : false;
    }

    /**
     * Process payment
     *
     * @access public
     * @param int $order_id
     * @return void
     */
    public function process_payment($order_id)
    {
        global $woocommerce;

        // Get token
        $token = isset($_POST['subscriptio_stripe_token']) ? sanitize_text_field($_POST['subscriptio_stripe_token']) : '';

        // Get selected card
        $selected_card = isset($_POST['subscriptio_stripe_card_id']) ? sanitize_text_field($_POST['subscriptio_stripe_card_id']) : '';

        // Make sure we received card id
        if (empty($selected_card)) {
            RightPress_Helper::wc_add_notice(esc_html__('Card is not selected.', 'subscriptio-stripe') . ' ' . esc_html__('We have not charged your card. Please try again.', 'subscriptio-stripe'), 'error');
            return;
        }

        // Make sure we received Stripe token if it's not an existing card
        if ($selected_card === 'none' && empty($token)) {
            RightPress_Helper::wc_add_notice(esc_html__('Authorization token not set.', 'subscriptio-stripe') . ' ' . esc_html__('We have not charged your card. Please try again.', 'subscriptio-stripe'), 'error');
            return;
        }

        // Get order object
        $order = RightPress_Helper::wc_get_order($order_id);

        if (!$order) {
            RightPress_Helper::wc_add_notice(esc_html__('Order not found.', 'subscriptio-stripe') . ' ' . esc_html__('We have not charged your card. Please try again.', 'subscriptio-stripe'), 'error');
            return;
        }

        // Data to send to Stripe
        $charge_details = array(
            'amount'        => RightPress_WC_Legacy::order_get_total($order) * Subscriptio_Stripe_Gateway::get_currency_multiplier($order),
            'currency'      => strtolower(RightPress_WC_Legacy::order_get_currency($order)),
            'description'   => apply_filters('subscriptio_stripe_payment_description', esc_html(get_bloginfo('name')) . ' - ' . esc_html__('Order', 'subscriptio-stripe') . ' ' . $order->get_order_number(), $order),
            'metadata'      => array(
                'order_id'  => RightPress_WC_Legacy::order_get_id($order),
                'email'     => RightPress_WC_Legacy::order_get_billing_email($order),
            ),
        );

        // Additional statement description
        $statement_descriptor = apply_filters('subscriptio_stripe_payment_statement_description', '', $order);

        if ($statement_descriptor !== '') {
            $charge_details['statement_descriptor'] = $statement_descriptor;
        }

        // Get user id from order
        $order_user_id = RightPress_WC_Legacy::order_get_customer_id($order);

        // Try to get user id
        if (!empty($order_user_id)) {
            $user_id = $order_user_id;
        }
        else if (is_user_logged_in()) {
            $user_id = get_current_user_id();
        }
        else {
            $user_id = 0;
        }

        // User is known - charge by sending a customer id / card id pair
        if ($user_id) {

            // Get (and possibly create) user and card details
            $user_card_details = $this->get_card($order, $user_id, $token, $selected_card);

            if (!is_array($user_card_details)) {
                RightPress_Helper::wc_add_notice($user_card_details . ' ' . esc_html__('We have not charged your card. Please try again.', 'subscriptio-stripe'), 'error');
                return;
            }

            // Handle free trial order
            if ($charge_details['amount'] == 0) {
                return $this->handle_trial($order);
            }

            // Charge card
            $charge_details['customer'] = $user_card_details['customer_id'];
            $charge_details['source'] = $user_card_details['card_id'];
            $response = $this->charge($charge_details);
        }
        // User is not known - charge guest users directly by token
        else {

            $charge_details['source'] = $token;
            $response = $this->charge($charge_details);
        }

        // Error charging card?
        if (is_string($response)) {
            RightPress_Helper::wc_add_notice($response, 'error');
            return;
        }

        // Save charge id
        RightPress_WC_Legacy::order_update_meta_data($order, '_subscriptio_stripe_charge_id', $response->id);

        // Save capture status
        RightPress_WC_Legacy::order_update_meta_data($order, '_subscriptio_stripe_charge_captured', ($response->captured ? 'yes' : 'no'));

        // Charge captured?
        if ($response->captured) {
            $order->add_order_note(sprintf(esc_html__('Stripe charge %s captured.', 'subscriptio-stripe'), $response->id));
            $order->payment_complete();
        }

        // Only authorized
        else {
            $order->add_order_note(sprintf(esc_html__('Stripe charge %s authorized and will be charged as soon as you start processing this order. Authorization will expire in 7 days.', 'subscriptio-stripe'), $response->id));
            $order->update_status('on-hold');
            RightPress_WC_Legacy::order_reduce_stock_levels($order);
        }

        // Empty cart
        $woocommerce->cart->empty_cart();

        // Redirect user
        return array(
            'result'    => 'success',
            'redirect'  => $this->get_return_url($order),
        );
    }

    /**
     * Handle order with free trial
     *
     * @access public
     * @param object $order
     * @return array
     */
    public function handle_trial($order)
    {
        global $woocommerce;

        // Complete the order
        $order->add_order_note(esc_html__('Stripe details saved for future use.', 'subscriptio-stripe'));
        $order->update_status('processing');
        RightPress_WC_Legacy::order_reduce_stock_levels($order);

        // Empty cart
        $woocommerce->cart->empty_cart();

        // Redirect user
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }

    /**
     * Get (and possibly save) card
     *
     * @access public
     * @param object $order
     * @param int $user_id
     * @param string $token
     * @param string $selected_card
     * @return array|bool
     */
    public function get_card($order, $user_id, $token, $selected_card)
    {
        // Does Stripe already know this user?
        $customer_id = RightPress_WC_Legacy::customer_get_meta($user_id, '_subscriptio_stripe_customer_id', true);

        // No customer? Create both customer and card
        if (empty($customer_id)) {

            // Send request to add a new customer
            $response = $this->send_request('customers', 'create', array(
                'source'          => $token,
                'description'   => apply_filters('subscriptio_stripe_customer_description', esc_html(get_bloginfo('name')) . ' - ' . RightPress_WC_Legacy::order_get_billing_first_name($order) . ' ' . RightPress_WC_Legacy::order_get_billing_last_name($order), $order),
                'email'         => RightPress_WC_Legacy::order_get_billing_email($order),
            ));

            // Errors?
            if (isset($response->error->message) && RightPress_Helper::string_contains_phrase($response->error->message, 'Stripe no longer supports API requests made with TLS')) {
                return esc_html__('Connection error.', 'subscriptio-stripe');
            }
            else if (!is_object($response) || !isset($response->id) || !isset($response->default_source)) {
                return esc_html__('Unable to add a new card.', 'subscriptio-stripe');
            }

            // Save user id
            RightPress_WC_Legacy::customer_update_meta_data($user_id, '_subscriptio_stripe_customer_id', $response->id);

            // Check the type of new card before adding
            if (!$this->is_funding_type_allowed($response->sources->data[0]->funding)) {
                return sprintf(esc_html__('Unable to use a card with "%s" funding type. ', 'subscriptio-stripe'), $response->sources->data[0]->funding);
            }

            // Save card details
            if (!empty($response->sources->data[0])) {
                RightPress_WC_Legacy::customer_update_meta_data($user_id, '_subscriptio_stripe_customer_cards', array(
                    $response->default_source => array(
                        'brand'     => $response->sources->data[0]->brand,
                        'funding'   => $response->sources->data[0]->funding,
                        'exp_month' => $response->sources->data[0]->exp_month,
                        'exp_year'  => $response->sources->data[0]->exp_year,
                        'last4'     => $response->sources->data[0]->last4,
                    ),
                ));
            }

            // Set card as default
            RightPress_WC_Legacy::customer_update_meta_data($user_id, '_subscriptio_stripe_customer_default_card', $response->default_source);

            // Return customer and card ids to charge
            return array(
                'customer_id'  => $response->id,
                'card_id'      => $response->default_source,
            );
        }

        // Customer exists - we need to add a new card to its profile
        else if ($selected_card == 'none') {

            // Send request to add a new card
            $response = $this->send_request('cards', 'create', array(
                'id'    => $customer_id,
                'source'  => $token,
            ));

            // Errors?
            if (isset($response->error->message) && RightPress_Helper::string_contains_phrase($response->error->message, 'Stripe no longer supports API requests made with TLS')) {
                return esc_html__('Connection error.', 'subscriptio-stripe');
            }
            else if (!is_object($response) || !isset($response->id)) {
                return esc_html__('Unable to add a new card.', 'subscriptio-stripe');
            }

            // Maybe update other cards with funding type
            $this->maybe_add_funding_type(get_current_user_id());

            // Check the type of new card before adding
            if (!$this->is_funding_type_allowed($response->funding)) {
                return sprintf(esc_html__('Unable to use a card with "%s" funding type. ', 'subscriptio-stripe'), $response->funding);
            }

            // Save card details
            $existing_cards = RightPress_WC_Legacy::customer_get_meta($user_id, '_subscriptio_stripe_customer_cards', true);
            $existing_cards = !empty($existing_cards) ? maybe_unserialize($existing_cards) : array();

            RightPress_WC_Legacy::customer_update_meta_data($user_id, '_subscriptio_stripe_customer_cards', array_merge($existing_cards, array(
                $response->id => array(
                    'brand'     => $response->brand,
                    'funding'   => $response->funding,
                    'exp_month' => $response->exp_month,
                    'exp_year'  => $response->exp_year,
                    'last4'     => $response->last4,
                ),
            )));

            // Set card as default
            RightPress_WC_Legacy::customer_update_meta_data($user_id, '_subscriptio_stripe_customer_default_card', $response->id);

            // Set card as default in Stripe
            $this->send_request('customers', 'update', array(
                'id'            => $customer_id,
                'default_source'  => $response->id,
            ));

            // Return customer and card ids to charge
            return array(
                'customer_id'  => $customer_id,
                'card_id'      => $response->id,
            );
        }

        // Existing card selected by customer
        else {

            // Maybe update cards with funding type
            $this->maybe_add_funding_type(get_current_user_id());

            // Get all customer's cards
            $cards = RightPress_WC_Legacy::customer_get_meta($user_id, '_subscriptio_stripe_customer_cards', true);

            if (empty($cards)) {
                return esc_html__('Such card does not exist.', 'subscriptio-stripe');
            }

            $cards = maybe_unserialize($cards);

            // Check if selected card exists in user's card list
            if (empty($cards) || !is_array($cards) || !isset($cards[$selected_card])) {
                return esc_html__('Such card does not exist.', 'subscriptio-stripe');
            }

            // Check the type of card
            if (!$this->is_funding_type_allowed($cards[$selected_card]['funding'])) {
                return sprintf(esc_html__('Unable to use a card with "%s" funding type. ', 'subscriptio-stripe'), $cards[$selected_card]['funding']);
            }

            // Return customer and card ids to charge
            return array(
                'customer_id'  => $customer_id,
                'card_id'      => $selected_card,
            );
        }
    }

    /**
     * Maybe update existing user cards with funding type value
     *
     * @access public
     * @params int $user_id
     * @return void
     */
    public function maybe_add_funding_type($user_id)
    {
        // Get customer id and existing cards
        $customer_id = RightPress_WC_Legacy::customer_get_meta($user_id, '_subscriptio_stripe_customer_id', true);
        $existing_cards = RightPress_WC_Legacy::customer_get_meta($user_id, '_subscriptio_stripe_customer_cards', true);
        $existing_cards = !empty($existing_cards) ? maybe_unserialize($existing_cards) : array();

        // Build new array
        $updated_cards = $existing_cards;
        $updated = false;

        // Iterate and check all cards
        foreach ($existing_cards as $card_id => $card_data) {

            // Update funding if not set
            if (!isset($card_data['funding'])) {

                // Get card details
                $response = $this->send_request('cards', 'retrieve', array(
                    'id'            => $customer_id,
                    'secondary_id'  => $card_id,
                ));

                $updated_cards = array_merge($updated_cards, array(
                    $response->id => array(
                        'brand'     => $response->brand,
                        'funding'   => $response->funding,
                        'exp_month' => $response->exp_month,
                        'exp_year'  => $response->exp_year,
                        'last4'     => $response->last4,
                    ),
                ));

                $updated = true;
            }
        }

        // Update if needed
        if ($updated === true) {
            RightPress_WC_Legacy::customer_update_meta_data($user_id, '_subscriptio_stripe_customer_cards', $updated_cards);
        }
    }

    /**
     * Check if funding type of card is allowed
     *
     * @access public
     * @params string $type
     * @return bool
     */
    public function is_funding_type_allowed($type)
    {
        // Don't check if not needed
        if ($this->get_option('restricted_funding_type') == 'none') {
            return true;
        }

        // Allow to add more types by using the filter
        $restricted_funding_types = apply_filters('subscriptio_stripe_restricted_funding_types', array($this->get_option('restricted_funding_type')));

        // Empty type allowed by default
        return !empty($type) ? !in_array($type, $restricted_funding_types) : true;
    }

    /**
     * Charge the card (or token) or ask for an authorization
     *
     * @access public
     * @params array $params
     * @return object|string
     */
    public function charge($params)
    {
        // Add defaults
        $params = array_merge(array(
            'capture' => $this->capture ? 'true' : 'false',
        ), $params);

        // Send request
        $result = $this->send_request('charges', 'create', $params);

        if (!is_object($result)) {
            return esc_html__('We were not able to connect to the payment gateway. Please try again.', 'subscriptio-stripe');
        }

        // Received error?
        if (!empty($result->error)) {

            if (RightPress_Helper::string_contains_phrase($result->error->message, 'Stripe no longer supports API requests made with TLS')) {
                return esc_html__('Connection error.', 'subscriptio-stripe');
            }

            return esc_html__('Payment gateway error:', 'subscriptio-stripe') . ' ' . $result->error->message;
        }

        return $result;
    }

    /**
     * Send request to Stripe
     *
     * @access public
     * @param string $context
     * @param string $action
     * @param array $params
     * @return object|string
     */
    public function send_request($context, $action, $params)
    {
        // Special case for customer's cards
        if ($context === 'cards') {
            $context = 'customers/' . $params['id'] . '/cards';
            unset($params['id']);
        }

        // Different actions require different endpoint URL
        switch ($action) {

            case 'update':
                $context = $context . '/' . $params['id'];
                unset($params['id']);
                break;

            case 'capture':
                $context = $context . '/' . $params['id'] . '/capture';
                unset($params['id']);
                break;

            case 'refund':
                $context = $context . '/' . $params['id'] . '/refund';
                unset($params['id']);
                break;

            case 'delete':
                $context = $context . '/' . $params['secondary_id'];
                unset($params['secondary_id']);
                break;

            case 'retrieve':
                $context = $context . '/' . $params['secondary_id'];
                unset($params['secondary_id']);
                break;

            default:
                break;
        }

        $result = wp_remote_post(
            $this->endpoint_url . 'v1/' . $context,
            array(
                'method'    => $action !== 'delete' ? 'POST' : 'DELETE',
                'headers'   => array(
                    'Authorization'     => 'Basic ' . base64_encode($this->secret_key . ':'),
                    'Stripe-Version'    => '2015-04-07',
                ),
                'body'      => $params,
                'sslverify' => Subscriptio::get_sslverify_value(),
                'timeout'   => 100,
            )
        );

        return is_wp_error($result) ? $result->get_error_message() : empty($result['body']) ? esc_html__('Response body empty.', 'subscriptio-stripe') : json_decode($result['body']);
    }

    /**
     * Enqueue scripts on Checkout
     *
     * @access public
     * @return void
     */
    public function enqueue_scripts_on_checkout()
    {
        // Scripts only needed on the Checkout page
        if (!is_page(wc_get_page_id('checkout'))) {
            return;
        }

        // Different scripts for different Checkout styles
        if ($this->checkout_style == 'inline') {

            // External Stripe script
            wp_register_script('subscriptio_stripe_js', 'https://js.stripe.com/v2/');

            // jQuery Payment Plugin
            if (!wp_script_is('jquery-payment', 'registered')) {
                wp_register_script('jquery-payment', SUBSCRIPTIO_PLUGIN_URL . '/assets/gateways/stripe/js/jquery.payment.min.js', array('jquery'), '1.1.2');
            }

            // Plugin's script
            wp_register_script('subscriptio_stripe_inline', SUBSCRIPTIO_PLUGIN_URL . '/assets/gateways/stripe/js/frontend_inline.js', array('jquery'), SUBSCRIPTIO_VERSION);

            // Pass Stripe config to frontend
            $this->add_stripe_config('subscriptio_stripe_inline');

            // Add billing details if payment is being made
            if (function_exists('is_checkout_pay_page') ? is_checkout_pay_page() : is_page(wc_get_page_id('pay'))) {
                $this->add_billing_details('subscriptio_stripe_inline');
            }

            // Enqueue scripts
            wp_enqueue_script('subscriptio_stripe_js');
            wp_enqueue_script('jquery-payment');
            wp_enqueue_script('subscriptio_stripe_inline');
        }
        else {

            // External Stripe script
            wp_register_script('subscriptio_stripe_checkout_js', 'https://checkout.stripe.com/checkout.js');

            // Plugin's script
            wp_register_script('subscriptio_stripe_modal', SUBSCRIPTIO_PLUGIN_URL . '/assets/gateways/stripe/js/frontend_modal.js', array('jquery'), SUBSCRIPTIO_VERSION);

            // Pass Stripe config to frontend
            $this->add_stripe_config('subscriptio_stripe_modal');

            // Add billing details if payment is being made
            if (function_exists('is_checkout_pay_page') ? is_checkout_pay_page() : is_page(wc_get_page_id('pay'))) {
                $this->add_billing_details('subscriptio_stripe_modal');
            }

            // Enqueue scripts
            wp_enqueue_script('subscriptio_stripe_checkout_js');
            wp_enqueue_script('subscriptio_stripe_modal');
        }
    }

    /**
     * Pass Stripe config to frontend
     *
     * @access public
     * @param string $handle
     * @return void
     */
    public function add_stripe_config($handle)
    {
        global $woocommerce;

        // Payment for existing order?
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['order_id'])) {
            $order = RightPress_Helper::wc_get_order((int) $_GET['order_id']);
        }

        wp_localize_script($handle, 'subscriptio_stripe_config', array(
            'checkout_name'                 => apply_filters('subscriptio_stripe_checkout_modal_name', get_bloginfo('name')),
            'checkout_description'          => apply_filters('subscriptio_stripe_checkout_modal_description', ''),
            'checkout_label'                => apply_filters('subscriptio_stripe_checkout_modal_button_title', esc_html__('Pay Now', 'subscriptio-stripe')),
            'checkout_image'                => apply_filters('subscriptio_stripe_checkout_modal_image_url', $this->checkout_image),
            'checkout_amount'               => $woocommerce->cart->total * Subscriptio_Stripe_Gateway::get_currency_multiplier(),
            'checkout_currency'             => strtolower(get_woocommerce_currency()),
            'checkout_email'                => isset($order) && gettype($order) == 'object' && isset($order->billing_method) ? RightPress_WC_Legacy::order_get_billing_email($order) : '',
            'publishable_key'               => $this->publishable_key,
            'error_incorrect_number'        => esc_html__('The card number is incorrect.', 'subscriptio-stripe'),
            'error_invalid_number'          => esc_html__('The card number is not a valid credit card number.', 'subscriptio-stripe'),
            'error_invalid_expiry_month'    => esc_html__("The card's expiration month is invalid.", 'subscriptio-stripe'),
            'error_invalid_expiry_year'     => esc_html__("The card's expiration year is invalid.", 'subscriptio-stripe'),
            'error_invalid_cvc'             => esc_html__("The card's security code is invalid.", 'subscriptio-stripe'),
            'error_expired_card'            => esc_html__('The card has expired.', 'subscriptio-stripe'),
            'error_incorrect_cvc'           => esc_html__("The card's security code is incorrect.", 'subscriptio-stripe'),
            'error_incorrect_zip'           => esc_html__("The card's zip code failed validation.", 'subscriptio-stripe'),
            'error_card_declined'           => esc_html__('The card was declined.', 'subscriptio-stripe'),
            'error_missing'                 => esc_html__('There is no card on a customer that is being charged.', 'subscriptio-stripe'),
            'error_processing_error'        => esc_html__('An error occurred while processing the card.', 'subscriptio-stripe'),
            'error_rate_limit'              => esc_html__("An error occurred due to requests hitting the API too quickly. Please let us know if you're consistently running into this error.", 'subscriptio-stripe'),
        ));
    }

    /**
     * Pass buyer details to Stripe scripts
     *
     * @access public
     * @param string $handle
     * @return void
     */
    public function add_billing_details($handle)
    {
        // Payment for existing order?
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['order_id'])) {
            $order = RightPress_Helper::wc_get_order((int) $_GET['order_id']);
        }

        // Check if order payment keys match
        if (isset($order) && gettype($order) === 'object' && isset($order->order_key) && $order->order_key == urldecode($_GET['order'])) {
            wp_localize_script($handle, 'subscriptio_stripe_billing_details', array(
                'billing_first_name'    => RightPress_WC_Legacy::order_get_billing_first_name($order),
                'billing_last_name'     => RightPress_WC_Legacy::order_get_billing_last_name($order),
                'billing_full_name'     => RightPress_WC_Legacy::order_get_billing_first_name($order) . ' ' . RightPress_WC_Legacy::order_get_billing_last_name($order),
                'billing_address_1'     => RightPress_WC_Legacy::order_get_billing_address_1($order),
                'billing_address_2'     => RightPress_WC_Legacy::order_get_billing_address_2($order),
                'billing_state'         => RightPress_WC_Legacy::order_get_billing_state($order),
                'billing_city'          => RightPress_WC_Legacy::order_get_billing_city($order),
                'billing_postcode'      => RightPress_WC_Legacy::order_get_billing_postcode($order),
                'billing_country'       => RightPress_WC_Legacy::order_get_billing_country($order),
            ));
        }
    }

    /**
     * Initialize form fields
     *
     * @access public
     * @return void
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => esc_html__('Enable/Disable', 'subscriptio-stripe'),
                'type'    => 'checkbox',
                'label'   => esc_html__('Enable Stripe payment gateway', 'subscriptio-stripe'),
                'default' => 'no',
            ),
            'debug' => array(
                'title'   => esc_html__('Test Mode', 'subscriptio-stripe'),
                'type'    => 'checkbox',
                'label'   => esc_html__('Enable test & debug mode', 'subscriptio-stripe'),
                'default' => 'no',
            ),
            'capture' => array(
                'title'         => esc_html__('Capture Immediately', 'subscriptio-stripe'),
                'type'          => 'checkbox',
                'label'         => esc_html__('Capture the charge immediately', 'subscriptio-stripe'),
                'description'   => esc_html__('If unchecked, an authorization is issued at the time of purchase but the charge itself is captured when you start processing the order. Uncaptured charges expire in 7 days.', 'subscriptio-stripe'),
                'default'       => 'yes',
            ),
            'title' => array(
                'title'       => esc_html__('Title', 'subscriptio-stripe'),
                'type'        => 'text',
                'description' => esc_html__('The title which the user sees during checkout.', 'subscriptio-stripe'),
                'default'     => esc_html__('Credit Card - Stripe', 'subscriptio-stripe'),
            ),
            'description' => array(
                'title'       => esc_html__('Description', 'subscriptio-stripe'),
                'type'        => 'textarea',
                'description' => esc_html__('The description which the user sees during checkout.', 'subscriptio-stripe'),
                'default'     => esc_html__('Pay Securely via Stripe', 'subscriptio-stripe'),
            ),
            'checkout_style' => array(
                'title'       => esc_html__('Checkout Style', 'subscriptio-stripe'),
                'type'        => 'select',
                'description' => esc_html__('Control how credit card details fields appear on your page.', 'subscriptio-stripe'),
                'default'     => 'inline',
                'options'     => array(
                    'inline'    => esc_html__('Inline', 'subscriptio-stripe'),
                    'modal'     => esc_html__('Modal', 'subscriptio-stripe'),
                ),
            ),
            'restricted_funding_type' => array(
                'title'       => esc_html__('Restricted type of cards', 'subscriptio-stripe'),
                'type'        => 'select',
                'description' => esc_html__('Restrict the use of some card funding type.', 'subscriptio-stripe'),
                'default'     => 'none',
                'options'     => array(
                    'none'    => esc_html__('None', 'subscriptio-stripe'),
                    'debit'    => esc_html__('Debit', 'subscriptio-stripe'),
                    'credit'     => esc_html__('Credit', 'subscriptio-stripe'),
                    'prepaid'     => esc_html__('Prepaid', 'subscriptio-stripe'),
                    'unknown'     => esc_html__('Unknown', 'subscriptio-stripe'),
                ),
            ),
            'checkout_image' => array(
                'title'       => esc_html__('Checkout Image', 'subscriptio-stripe'),
                'type'        => 'text',
                'description' => esc_html__('Stripe Checkout modal allows custom seller image to be displayed. Enter your custom 128x128 px image URL here (should be hosted on a secure location).', 'subscriptio-stripe'),
                'default'     => '',
            ),
            'test_secret_key' => array(
                'title'       => esc_html__('Test Secret Key', 'subscriptio-stripe'),
                'type'        => 'text',
                'description' => esc_html__('Test Secret Key from your Stripe Account (under Account Settings > API Keys).', 'subscriptio-stripe'),
                'default'     => '',
            ),
            'test_publishable_key' => array(
                'title'       => esc_html__('Test Publishable Key', 'subscriptio-stripe'),
                'type'        => 'text',
                'description' => esc_html__('Test Publishable Key from your Stripe Account.', 'subscriptio-stripe'),
                'default'     => '',
            ),
            'live_secret_key' => array(
                'title'       => esc_html__('Live Secret Key', 'subscriptio-stripe'),
                'type'        => 'text',
                'description' => esc_html__('Live Secret Key from your Stripe Account.', 'subscriptio-stripe'),
                'default'     => '',
            ),
            'live_publishable_key' => array(
                'title'       => esc_html__('Live Publishable Key', 'subscriptio-stripe'),
                'type'        => 'text',
                'description' => esc_html__('Live Publishable Key from your Stripe Account.', 'subscriptio-stripe'),
                'default'     => '',
            ),
        );
    }

    /**
     * Credit card details form on Checkout
     *
     * @access public
     * @return void
     */
    public function payment_fields()
    {
        global $woocommerce;

        // User logged in?
        if (is_user_logged_in()) {

            $user_id = get_current_user_id();

            // Get customer's cards
            $cards = RightPress_WC_Legacy::customer_get_meta($user_id, '_subscriptio_stripe_customer_cards', true);

            if (!empty($cards)) {
                $cards = maybe_unserialize($cards);

                // Format card names
                foreach ($cards as $card_id => $card) {
                    $brand = $card['brand'] != 'Unknown' ? $card['brand'] : esc_html__('Card', 'subscriptio-stripe');
                    $exp = Subscriptio_Stripe::format_expiration_date($card['exp_month'], $card['exp_year']);
                    $cards[$card_id] = $brand . ' ' . esc_html__('ending with', 'subscriptio-stripe') . ' ' . $card['last4'] . ' (' . esc_html__('expires', 'subscriptio-stripe') . ' ' . $exp . ')';
                }

                $cards['none'] = esc_html__('New Credit Card', 'subscriptio-stripe');
            }
            else {
                $cards = array();
            }

            // Get customer's default card
            $default_card = RightPress_WC_Legacy::customer_get_meta($user_id, '_subscriptio_stripe_customer_default_card', true);
            $default_card = !empty($default_card) ? $default_card : 'none';
        }
        else {
            $cards = array();
            $default_card = 'none';
        }

        Subscriptio::include_template('gateways/stripe/credit-card-form', array(
            'id'              => $this->id,
            'description'     => $this->description,
            'cards'           => $cards,
            'default_card'    => $default_card,
            'is_debug'        => $this->debug == 'yes' ? true : false,
            'is_inline'       => $this->checkout_style == 'inline' ? true : false,
            'checkout_amount' => $woocommerce->cart->total * Subscriptio_Stripe_Gateway::get_currency_multiplier()
        ));
    }

    /**
     * Process refund manually issued from order page
     *
     * @access public
     * @param int $order_id
     * @param float $amount
     * @param string $reason
     * @return bool
     */
    public function process_refund($order_id, $amount = null, $reason = '')
    {
        // Load order
        $order = RightPress_Helper::wc_get_order($order_id);

        if (!$order) {
            return;
        }

        // Get charge id
        $charge_id = RightPress_WC_Legacy::order_get_meta($order, '_subscriptio_stripe_charge_id', true);

        if (empty($charge_id)) {
            return;
        }

        // Send request to refund payment
        $response = $this->send_request('charges', 'refund', array(
            'id'        => $charge_id,
            'amount'    => $amount * Subscriptio_Stripe_Gateway::get_currency_multiplier($order),
        ));

        // Request failed?
        if (!is_object($response)) {
            $order->add_order_note(esc_html__('Stripe refund failed.', 'subscriptio-stripe') . ' ' . $response);
            return false;
        }

        // Received error from Stripe?
        if (!empty($response->error)) {
            $order->add_order_note(esc_html__('Stripe refund failed.', 'subscriptio-stripe') . ' ' . $response->error->message);
            return false;
        }

        // Request was successful
        $order->add_order_note(sprintf(esc_html__('%s of Stripe charge %s refunded.', 'subscriptio-stripe'), Subscriptio::get_formatted_price($amount), $response->id));
        return true;
    }

    /**
     * Get currency multiplier
     *
     * @access public
     * @param object $order
     * @return int
     */
    public static function get_currency_multiplier($order = null)
    {
        // Define currencies with no decimals
        $currencies_with_no_decimals = array('BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF');

        // Get currency
        $currency = ($order !== null ? RightPress_WC_Legacy::order_get_currency($order) : get_woocommerce_currency());

        // Get multiplier by currency
        $multiplier = in_array($currency, $currencies_with_no_decimals, true) ? 1 : 100;

        // Allow developers to override
        return absint(apply_filters('subscriptio_stripe_decimals_in_currency', $multiplier, $order));
    }

}
}
