<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WooPDF')) {

/**
 * Main plugin class
 *
 * @class WooPDF
 * @package WooCommerce_PDF_Invoice
 * @author RightPress
 */
class WooPDF
{

    // Singleton instance
    private static $instance = false;

    /**
     * Singleton control
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->date_from = null;
        $this->date_to = null;
        $this->temp_order_id = null;

        // Load translation
        load_textdomain('woo_pdf', WP_LANG_DIR . '/woocommerce-pdf-invoice/woo_pdf-' . apply_filters('plugin_locale', get_locale(), 'woo_pdf') . '.mo');
        load_textdomain('rightpress', WP_LANG_DIR . '/' . WOOPDF_PLUGIN_KEY . '/rightpress-' . apply_filters('plugin_locale', get_locale(), 'rightpress') . '.mo');
        load_plugin_textdomain('woo_pdf', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        load_plugin_textdomain('rightpress', false, WOOPDF_PLUGIN_KEY . '/languages/');

        // Additional plugin page links
        add_filter('plugin_action_links_' . (WOOPDF_PLUGIN_KEY . '/' . WOOPDF_PLUGIN_KEY . '.php'), array($this, 'plugin_settings_link'));

        // Include RightPress library loaded class
        require_once WOOPDF_PLUGIN_PATH . 'rightpress/rightpress-loader.class.php';

        // Execute other code when all plugins are loaded
        add_action('plugins_loaded', array($this, 'on_plugins_loaded'), 1);
    }

    /**
     * Code executed when all plugins are loaded
     *
     * @access public
     * @return void
     */
    public function on_plugins_loaded()
    {
        // Load helper classes
        RightPress_Loader::load();

        // Check environment
        if (!self::check_environment()) {
            return;
        }

        // Load includes
        require_once WOOPDF_PLUGIN_PATH . 'includes/woo-pdf-plugin-structure.inc.php';
        require_once WOOPDF_PLUGIN_PATH . 'includes/woo-pdf-functions.inc.php';

        // Load plugin configuration
        $this->get_config();

        // Load options
        $this->opt = $this->get_options();

        // Add settings page
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'admin_construct'));
        }

        // Load resources conditionally
        if (preg_match('/page=woo-pdf/i', $_SERVER['REQUEST_URI'])) {
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        }

        // Hook into WooCommerce / WordPress
        if ($this->opt['woo_pdf_enabled'] || $this->opt['woo_pdf_proforma_enabled']) {

            // Generate regular invoice
            $order_is_paid_statuses = array_unique(apply_filters('woocommerce_order_is_paid_statuses', array('processing', 'completed')));

            if (!in_array('completed', $order_is_paid_statuses)) {
                $order_is_paid_statuses[] = 'completed';
            }

            foreach ($order_is_paid_statuses as $order_is_paid_status) {
                add_action('woocommerce_order_status_' . $order_is_paid_status, array($this, 'maybe_generate_regular_invoice'), 9);
            }

            // Email invoices
            add_filter('woocommerce_email_attachments', array($this, 'send_by_email'), 10, 3);
            add_action('woocommerce_checkout_order_processed', array($this, 'new_order'), 10, 2);

            // Other hooks
            add_action('template_redirect', array($this, 'hide_attachment_pages'), 1);
            add_action('woocommerce_admin_order_actions', array($this, 'admin_invoice_link'), 10, 2);
            add_action('woocommerce_order_details_after_order_table', array($this, 'user_invoice_link'));
            add_filter('woocommerce_my_account_my_orders_actions', array($this, 'orders_actions'), 10, 2);
            add_action('add_meta_boxes', array($this, 'add_woo_pdf_metabox'));
        }

        // Intercept download calls
        if (isset($_GET['wpd_invoice'])) {
            add_action('init', array($this, 'push_invoice'));
        }
        if (isset($_GET['wpd_proforma'])) {
            add_action('init', array($this, 'push_proforma'));
        }
        if (isset($_GET['wpd_delete_invoice'])) {
            add_action('init', array($this, 'delete_invoice_request'));
        }
        if (isset($_GET['wpd_generate_invoice'])) {
            add_action('init', array($this, 'generate_invoice'));
        }
        if (isset($_GET['woo_pdf_download_from']) && isset($_GET['woo_pdf_download_to'])) {
            add_action('init', array($this, 'batch_download'));
        }

        // Allow other authorized users to save settings (by default works for admin only)
        add_filter('option_page_capability_woo_pdf_opt_group_general_settings', array($this, 'get_admin_capability'));
        add_filter('option_page_capability_woo_pdf_opt_group_advanced_settings', array($this, 'get_admin_capability'));
        add_filter('option_page_capability_woo_pdf_opt_group_seller_details', array($this, 'get_admin_capability'));
        add_filter('option_page_capability_woo_pdf_opt_group_content_blocks', array($this, 'get_admin_capability'));
        add_filter('option_page_capability_woo_pdf_opt_group_translation', array($this, 'get_admin_capability'));
        add_filter('option_page_capability_woo_pdf_opt_group_batch_download', array($this, 'get_admin_capability'));

        // Optionally remove regular invoices when personal order data is erased
        add_action('woocommerce_privacy_remove_order_personal_data', array($this, 'maybe_remove_invoices_for_privacy'));

        // Add invoice meta to order personal data export
        add_filter('woocommerce_privacy_export_order_personal_data_meta', array($this, 'add_invoice_meta_to_personal_data_export'));
        add_filter('woocommerce_privacy_export_order_personal_data_meta_value', array($this, 'get_invoice_meta_value_for_personal_data_export'), 10, 3);

        // Allow custom vars in WooCommerce orders query
        add_filter('woocommerce_order_data_store_cpt_get_orders_query', array($this, 'add_custom_orders_query_vars'), 10, 2);
    }

    /**
     * Loads/sets configuration values from structure file and database
     *
     * @access public
     * @return void
     */
    public function get_config()
    {
        // Settings tree
        $this->settings = woo_pdf_plugin_settings();

        // Load some data from config
        $this->hints = $this->options('hint');
        $this->validation = $this->options('validation', true);
        $this->titles = $this->options('title');
        $this->options = $this->options('values');
        $this->section_info = $this->get_section_info();
    }

    /**
     * Get settings options: default, hint, validation, values
     *
     * @access public
     * @param string $name
     * @param bool $split_by_page
     * @return array
     */
    public function options($name, $split_by_page = false)
    {
        $results = array();

        // Iterate over settings array and extract values
        foreach ($this->settings as $page => $page_value) {
            $page_options = array();

            foreach ($page_value['children'] as $section => $section_value) {
                foreach ($section_value['children'] as $field => $field_value) {
                    if (isset($field_value[$name])) {
                        $page_options['woo_pdf_' . $field] = $field_value[$name];
                    }
                }
            }

            $results[preg_replace('/_/', '-', $page)] = $page_options;
        }

        $final_results = array();

        if (!$split_by_page) {
            foreach ($results as $value) {
                $final_results = array_merge($final_results, $value);
            }
        }
        else {
            $final_results = $results;
        }

        return $final_results;
    }

    /**
     * Get single plugin option statically
     *
     * @access public
     * @param string $key
     * @return mixed
     */
    public static function opt($key)
    {
        $instance = WooPDF::get_instance();
        return isset($instance->opt[$key]) ? $instance->opt[$key] : null;
    }

    /**
     * Get admin capability
     *
     * @access public
     * @return string
     */
    public static function get_admin_capability()
    {
        return apply_filters('woo_pdf_admin_capability', 'edit_posts');
    }

    /**
     * Get admin user roles
     *
     * @access public
     * @return string
     */
    public static function get_admin_roles()
    {
        return apply_filters('woo_pdf_admin_roles', array('administrator', 'shop_manager'));
    }

    /**
     * Get array of section info strings
     *
     * @access public
     * @return array
     */
    public function get_section_info()
    {
        $results = array();

        // Iterate over settings array and extract values
        foreach ($this->settings as $page_value) {
            foreach ($page_value['children'] as $section => $section_value) {
                if (isset($section_value['info'])) {
                    $results[$section] = $section_value['info'];
                }
            }
        }

        return $results;
    }

    /*
     * Get plugin options set by user
     *
     * @access public
     * @return array
     */
    public function get_options()
    {
        $saved_options = get_option('woo_pdf_options', $this->options('default'));

        if (is_array($saved_options)) {
            return array_merge($this->options('default'), $saved_options);
        }
        else {
            return $this->options('default');
        }
    }

    /*
     * Update options
     *
     * @access public
     * @return bool
     */
    public function update_options($args = array())
    {
        return update_option('woo_pdf_options', array_merge($this->get_options(), $args));
    }

    /**
     * Add link to admin page under Woocommerce menu
     *
     * @access public
     * @return void
     */
    public function add_admin_menu()
    {
        $user_roles = RightPress_Help::current_user_roles();
        $user_role = array_shift($user_roles);

        if (!in_array($user_role, self::get_admin_roles())) {
            return;
        }

        global $submenu;

        if (isset($submenu['woocommerce'])) {
            add_submenu_page(
                'woocommerce',
                __('WooCommerce PDF Invoices', 'woo_pdf'),
                __('PDF Invoices', 'woo_pdf'),
                self::get_admin_capability(),
                'woo-pdf',
                array($this, 'set_up_admin_page')
            );
        }
    }

    /*
     * Set up admin page
     *
     * @access public
     * @return void
     */
    public function set_up_admin_page()
    {
        // Get current tab
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general_settings';
        $current_tab = isset($this->settings[$current_tab]) ? $current_tab : 'general_settings';

        // Open container
        echo '<div class="wrap woocommerce">';

        // Open form
        if ($current_tab === 'batch_download') {
            echo '<form class="woo_pdf_batch_download">';
        }
        else {
            echo '<form method="post" action="options.php" enctype="multipart/form-data">';
        }

        // Check for general warnings
        if (!$this->image_library_exists()) {
            add_settings_error(
                'woo_pdf',
                'general',
                __('Image processing library not found on your server.<br>You must have either GD or Imagick extension enabled on your server for this module to work correctly.', 'woo_pdf')
            );
        }

        // Print notices
        settings_errors('woo_pdf');

        // Print page tabs
        $this->render_tabs($current_tab);

        // Print page content
        $this->render_page($current_tab);

        // Close container
        echo '</form></div>';
    }

    /**
     * Admin interface constructor
     *
     * @access public
     * @return void
     */
    public function admin_construct()
    {
        $user_roles = RightPress_Help::current_user_roles();
        $user_role = array_shift($user_roles);

        if (!in_array($user_role, self::get_admin_roles())) {
            return;
        }

        // Iterate pages
        foreach ($this->settings as $page => $page_value) {

            register_setting(
                'woo_pdf_opt_group_' . $page,               // Option group
                'woo_pdf_options',                          // Option name
                array($this, 'options_validate')            // Sanitize
            );

            // Iterate sections
            foreach ($page_value['children'] as $section => $section_value) {

                add_settings_section(
                    $section,
                    $section_value['title'],
                    array($this, 'render_section_info'),
                    'woo-pdf-admin-' . str_replace('_', '-', $page)
                );

                // Iterate fields
                foreach ($section_value['children'] as $field => $field_value) {

                    add_settings_field(
                        'woo_pdf_' . $field,                                     // ID
                        $field_value['title'],                                      // Title
                        array($this, 'render_options_' . $field_value['type']),     // Callback
                        'woo-pdf-admin-' . str_replace('_', '-', $page),            // Page
                        $section,                                                   // Section
                        array(                                                      // Arguments
                            'name' => 'woo_pdf_' . $field,
                            'options' => $this->opt,
                        )
                    );

                }
            }
        }
    }

    /**
     * Render admin page navigation tabs
     *
     * @access public
     * @param string $current_tab
     * @return void
     */
    public function render_tabs($current_tab = 'general-settings')
    {
        $current_tab = preg_replace('/-/', '_', $current_tab);

        // Fix for WordPress notices jumping in between header and settings area
        echo '<h2 style="padding: 0; margin: 0; height: 0;"></h2>';

        // Output admin page tab navigation
        echo '<h2 class="woo_pdf_tabs_container nav-tab-wrapper">';
        echo '<div id="icon-woo-pdf" class="icon32 icon32-woo-pdf"><br></div>';
        foreach ($this->settings as $page => $page_value) {
            $class = ($page == $current_tab) ? ' nav-tab-active' : '';
            echo '<a class="nav-tab'.$class.'" href="?page=woo-pdf&tab='.$page.'">'.((isset($page_value['icon']) && !empty($page_value['icon'])) ? $page_value['icon'] . '&nbsp;' : '').$page_value['title'].'</a>';
        }
        echo '</h2>';
    }

    /**
     * Render settings page
     *
     * @access public
     * @param string $page
     * @return void
     */
    public function render_page($page){

        $page_name = preg_replace('/_/', '-', $page);

        // Is this a batch download page?
        if ($page === 'batch_download') {
            ?>
                <div class="wrap woocommerce woo-pdf">
                <div class="woo_pdf_container">
                    <h3><?php _e('Batch Invoice Download', 'woo_pdf'); ?></h3>

                    <table class="form-table">
                        <tbody>
                            <tr valign="tr">
                                <th scope="row"><?php _e('Date from', 'woo_pdf'); ?></th>
                                <td>
                                    <input type="text" id="woo_pdf_download_from" name="woo_pdf_download_from" value="">
                                </td>
                            </tr>
                            <tr valign="tr">
                                <th scope="row"><?php _e('Date to', 'woo_pdf'); ?></th>
                                <td>
                                    <input type="text" id="woo_pdf_download_to" name="woo_pdf_download_to" value="">
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="woo_pdf_section_info"><?php _e('Please note that only regular invoices are available for batch download.', 'woo_pdf'); ?></div>

                    <p class="submit">
                        <button type="button" name="submit" id="woo_pdf_batch_download" class="button button-primary"><?php _e('Download Invoices', 'woo_pdf'); ?></button>
                    </p>

                </div>
                </div>
            <?php
        }
        // Is this a standard settings page?
        else {
            ?>

                <div class="woo_pdf_container">
                    <input type="hidden" name="current_tab" value="<?php echo $page_name; ?>" />

                    <?php if ($page == 'content_blocks'): ?>
                        <div class="woo_pdf_content_tab_description">
                            <h3><?php _e('Macros', 'woo_pdf'); ?></h3>
                            <p class="woo_pdf_about"><?php _e('Footer and all custom blocks support the following macros in both title and content:', 'woo_pdf'); ?></p>
                                <div>
                                    <div style="float: left;">
                                        <ul class="woo_pdf_macros">
                                            <li><strong>{{invoice_number}}</strong></li>
                                            <li><strong>{{invoice_date}}</strong></li>
                                            <li><strong>{{order_id}}</strong></li>
                                            <li><strong>{{order_date}}</strong></li>
                                            <li><strong>{{order_amount}}</strong></li>
                                            <li><strong>{{order_currency}}</strong></li>
                                        </ul>
                                    </div>
                                    <div style="float: left;">
                                        <ul class="woo_pdf_macros">
                                            <li><strong>{{customer_id}}</strong></li>
                                            <li><strong>{{customer_note}}</strong></li>
                                            <li><strong>{{billing_email}}</strong></li>
                                            <li><strong>{{billing_phone}}</strong></li>
                                            <li><strong>{{payment_method}}</strong></li>
                                            <li><strong>{{shipping_method}}</strong></li>
                                        </ul>
                                    </div>
                                    <div style="float: left;">
                                        <ul class="woo_pdf_macros">
                                            <li><strong>{{shipping_first_name}}</strong></li>
                                            <li><strong>{{shipping_last_name}}</strong></li>
                                            <li><strong>{{shipping_company}}</strong></li>
                                            <li><strong>{{shipping_address_1}}</strong></li>
                                            <li><strong>{{shipping_address_2}}</strong></li>
                                            <li><strong>{{shipping_city}}</strong></li>
                                        </ul>
                                    </div>
                                    <div style="float: left;">
                                        <ul class="woo_pdf_macros">
                                            <li><strong>{{shipping_postcode}}</strong></li>
                                            <li><strong>{{shipping_country}}</strong></li>
                                            <li><strong>{{shipping_state}}</strong></li>
                                            <li><strong>{{invoice_title}}</strong></li>
                                            <li><strong>{{invoice_date_title}}</strong></li>
                                            <li><strong>{{order_amount_title}}</strong></li>
                                        </ul>
                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                            <p class="woo_pdf_about_inverse"><?php _e('You can insert any other order field (including custom fields) in the same way, e.g.', 'woo_pdf'); ?> <strong>{{my_custom_field_key}}</strong>.</p>

                        </div>
                    <?php endif; ?>

                    <?php if ($page == 'general_settings'): ?>
                        <input type="hidden" name="woo_pdf_options[woo_pdf_next_invoice_number_original_value]" value="<?php echo get_option('woo_pdf_next_invoice_number', '1'); ?>">
                    <?php endif; ?>

                    <?php
                        settings_fields('woo_pdf_opt_group_' . $page);
                        do_settings_sections('woo-pdf-admin-' . $page_name);

                        echo '<div></div>';

                        submit_button();
                    ?>
                </div>
            <?php
        }

        // Get uploads url and path
        $uploads_dir = wp_upload_dir();

        // Pass variables to JavaScript
        ?>
            <script language="JavaScript">
                var woo_pdf_hints = <?php echo json_encode($this->hints); ?>;
                var woo_pdf_home_url = '<?php echo home_url(); ?>';
                var woo_pdf_uploads_url = '<?php echo $uploads_dir['baseurl']; ?>';
                var woo_pdf_uploads_path = '<?php echo $uploads_dir['basedir']; ?>';
            </script>
        <?php
    }

    /**
     * Render section info
     *
     * @access public
     * @param array $section
     * @return void
     */
    public function render_section_info($section)
    {
        if (isset($this->section_info[$section['id']])) {
            echo $this->section_info[$section['id']];
        }
    }

    /*
     * Render a text field
     *
     * @access public
     * @param array $args
     * @return void
     */
    public function render_options_text($args = array())
    {
        $value = $args['options'][$args['name']];

        if ($args['name'] == 'woo_pdf_next_invoice_number') {
            $options_value = get_option('woo_pdf_next_invoice_number');
            $value = ($options_value === false) ? $value : $options_value;
        }

        printf(
            '<input type="text" id="%s" name="woo_pdf_options[%s]" value="%s" class="woo-pdf-field-width" />',
            $args['name'],
            $args['name'],
            $value
        );
    }

    /*
     * Render a text area
     *
     * @access public
     * @param array $args
     * @return void
     */
    public function render_options_textarea($args = array())
    {
        printf(
            '<textarea id="%s" name="woo_pdf_options[%s]" class="woo_pdf_textarea">%s</textarea>',
            $args['name'],
            $args['name'],
            $args['options'][$args['name']]
        );
    }

    /*
     * Render a checkbox
     *
     * @access public
     * @param array $args
     * @return void
     */
    public function render_options_checkbox($args = array())
    {
        printf(
            '<input type="checkbox" id="%s" name="woo_pdf_options[%s]" value="1" %s />',
            $args['name'],
            $args['name'],
            checked($args['options'][$args['name']], true, false)
        );
    }

    /*
     * Render a dropdown
     *
     * @access public
     * @param array $args
     * @return void
     */
    public function render_options_dropdown($args = array())
    {
        printf(
            '<select id="%s" name="woo_pdf_options[%s]" class="woo-pdf-field-width">',
            $args['name'],
            $args['name']
        );
        foreach ($this->options[$args['name']] as $key => $name) {
            printf(
                '<option value="%s" %s>%s</option>',
                $key,
                selected($key, $args['options'][$args['name']], false),
                $name
            );
        }
        echo '</select>';
    }

    /**
     * Render select from media library field
     *
     * @access public
     * @param array $args
     * @return void
     */
    public function render_options_media($args = array())
    {
        // Render text input field
        printf(
            '<input id="%s" type="text" name="woo_pdf_options[%s]" value="%s" class="woo-pdf-field-width" />',
            $args['name'],
            $args['name'],
            $args['options'][$args['name']]
        );

        // Render "Open Library" button
        printf(
            '<input id="%s_upload_button" type="button" value="%s" />',
            $args['name'],
            __('Open Library', 'woo_pdf')
        );
    }

    /**
     * Validate admin form input
     *
     * @access public
     * @param array $input
     * @return array
     */
    public function options_validate($input)
    {
        $current_tab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'general-settings';
        $output = $this->get_options();

        $errors = array();

        // Avoid accidental next invoice number overwrite


        // Iterate over fields and validate/sanitize input
        foreach ($this->validation[$current_tab] as $field => $rule) {

            // Different routines for different field types
            switch($rule['rule']) {

                // Validate numbers
                case 'number':

                    // Exception - make sure we do not accidentally overwrite next invoice number
                    $next_invoice_number_error = false;

                    if ($field == 'woo_pdf_next_invoice_number') {
                        if ($input['woo_pdf_numbering_method'] == '0' && isset($input['woo_pdf_next_invoice_number_original_value']) && ($input['woo_pdf_next_invoice_number_original_value'] != get_option('woo_pdf_next_invoice_number', '1'))) {
                            array_push($errors, array('setting' => $field, 'code' => 'number'));
                            $next_invoice_number_error = true;
                        }
                    }

                    if (!$next_invoice_number_error) {
                        if (is_numeric($input[$field]) || ($input[$field] == '' && $rule['empty'] == true)) {

                            // Save next invoice number separately
                            if ($field == 'woo_pdf_next_invoice_number') {
                                update_option('woo_pdf_next_invoice_number', $input[$field]);
                            }
                            else {
                                $output[$field] = $input[$field];
                            }
                        }
                        else {
                            array_push($errors, array('setting' => $field, 'code' => 'number'));
                        }
                    }

                    break;

                // Validate boolean values (actually 1 and 0)
                case 'bool':
                    $input[$field] = (!isset($input[$field]) || $input[$field] == '') ? '0' : $input[$field];
                    if (in_array($input[$field], array('0', '1')) || ($input[$field] == '' && $rule['empty'] == true)) {
                        $output[$field] = $input[$field];
                    }
                    else {
                        array_push($errors, array('setting' => $field, 'code' => 'bool'));
                    }
                    break;

                // Validate predefined options
                case 'option':
                    if (isset($input[$field]) && (isset($this->options[$field][$input[$field]]) || ($input[$field] == '' && $rule['empty'] == true))) {
                        $output[$field] = $input[$field];
                    }
                    else if (!isset($input[$field])) {
                        $output[$field] = '';
                    }
                    else {
                        array_push($errors, array('setting' => $field, 'code' => 'option'));
                    }
                    break;

                // Validate emails
                case 'email':
                    if (isset($input[$field]) && (filter_var(trim($field), FILTER_VALIDATE_EMAIL) || ($input[$field] == '' && $rule['empty'] == true))) {
                        $output[$field] = esc_attr(trim($input[$field]));
                    }
                    else if (!isset($input[$field])) {
                        $output[$field] = '';
                    }
                    else {
                        array_push($errors, array('setting' => $field, 'code' => 'email'));
                    }
                    break;

                // Validate URLs
                case 'url':
                    // FILTER_VALIDATE_URL for filter_var() does not work as expected
                    if (isset($input[$field]) && ($input[$field] == '' && $rule['empty'] != true)) {
                        array_push($errors, array('setting' => $field, 'code' => 'url'));
                    }
                    else if (!isset($input[$field])) {
                        $output[$field] = '';
                    }
                    else {
                        $output[$field] = esc_attr(trim($input[$field]));
                    }
                    break;

                // Default validation rule (text fields etc)
                default:
                    if (isset($input[$field]) && ($input[$field] == '' && $rule['empty'] != true)) {
                        array_push($errors, array('setting' => $field, 'code' => 'string'));
                    }
                    else if (!isset($input[$field])) {
                        $output[$field] = '';
                    }
                    else {
                        $output[$field] = esc_attr(trim($input[$field]));
                    }
                    break;
            }
        }

        // Display settings updated message
        add_settings_error(
            'woo_pdf',
            'woo_pdf_' . 'settings_updated',
            __('Your settings have been saved.', 'woo_pdf'),
            'updated'
        );

        // Display errors
        foreach ($errors as $error) {
            $reverted = __('Reverted to a previous value.', 'woo_pdf');

            $messages = array(
                'number' => __('must be numeric', 'woo_pdf') . '. ' . $reverted,
                'bool' => __('must be either 0 or 1', 'woo_pdf') . '. ' . $reverted,
                'option' => __('is not allowed', 'woo_pdf') . '. ' . $reverted,
                'email' => __('is not a valid email address', 'woo_pdf') . '. ' . $reverted,
                'url' => __('is not a valid URL', 'woo_pdf') . '. ' . $reverted,
                'string' => __('is not a valid text string', 'woo_pdf') . '. ' . $reverted,
            );

            add_settings_error(
                'woo_pdf',
                $error['code'],
                __('Value of', 'woo_pdf') . ' "' . $this->titles[$error['setting']] . '" ' . $messages[$error['code']]
            );
        }

        return $output;
    }

    /**
     * Generate regular invoice
     *
     * @access public
     * @param object $order
     * @return void
     */
    public function make_invoice($order)
    {
        // Is invoicing enabled?
        if (!$this->opt['woo_pdf_enabled']) {
            return;
        }

        // Is image processing extension enabled? (required by tcpdf)
        if (!$this->image_library_exists()) {
            return;
        }

        // Load PDF class
        if (!class_exists('TCPDF')) {
            require WOOPDF_PLUGIN_PATH.'libraries/tcpdf/tcpdf.php';
        }
        if (!class_exists('WooPdfInvoice')) {
            require WOOPDF_PLUGIN_PATH.'classes/woo-pdf-invoice.class.php';
        }

        // Get invoice number
        if ($this->opt['woo_pdf_numbering_method'] == 0) {
            $next_invoice_number = $this->get_next_invoice_number();
        }
        else if ($this->opt['woo_pdf_numbering_method'] == 1) {
            $next_invoice_number = $order->get_order_number();
            $next_invoice_number = preg_replace('/[^0-9.]+/', '', $next_invoice_number);
        }
        else {
            $next_invoice_number = $order->get_order_number();
        }

        // Get random code for file name
        $random_name = substr(md5(time()), 0, 5).substr($next_invoice_number, -3, 3);
        $file_name = $random_name.'.pdf';

        // Get prefix and suffix
        if ($this->opt['woo_pdf_numbering_method'] == 2) {
            $invoice_number_prefix = '';
            $invoice_number_suffix = '';
        }
        else {
            $invoice_number_prefix = $this->replace_prefix_suffix_macros($this->opt['woo_pdf_number_prefix'], $order, 'prefix');
            $invoice_number_suffix = $this->replace_prefix_suffix_macros($this->opt['woo_pdf_number_suffix'], $order, 'suffix');
        }

        // Initialize tcpdf
        $info = array(
            'id' => $next_invoice_number,
            'code' => $random_name,
            'prefix' => $invoice_number_prefix,
            'suffix' => $invoice_number_suffix,
        );
        $pdf = new WooPdfInvoice(array('order' => $order, 'options' => $this->opt, 'info' => $info, 'type' => 'invoice'), 'P', 'pt', 'A4');
        $pdf->CreateInvoice();

        // Set up file directory
        $upload_dir = wp_upload_dir();
        $location = $upload_dir['basedir'] . '/' . 'woocommerce_pdf_invoices';
        if (!file_exists($location)) {
            mkdir($location, 0755, true);
        }

        // Protect invoices directory from listing
        if (!file_exists($location . '/index.php')) {
            touch($location . '/index.php');
        }

        // Save file to selected directory
        $pdf->Output($location . '/' . $file_name, 'F');

        // From here on we don't need hash tag or other special characters before invoice number
        $next_invoice_number = preg_replace('/[^0-9.]+/', '', $next_invoice_number);

        // Push invoice number and random name to order meta
        RightPress_WC::order_add_meta_data($order, '_woo_pdf_invoice_id', $next_invoice_number, true);
        RightPress_WC::order_add_meta_data($order, '_woo_pdf_invoice_prefix', $invoice_number_prefix, true);
        RightPress_WC::order_add_meta_data($order, '_woo_pdf_invoice_suffix', $invoice_number_suffix, true);
        RightPress_WC::order_add_meta_data($order, '_woo_pdf_invoice_code', $random_name, true);
        RightPress_WC::order_add_meta_data($order, '_woo_pdf_invoice_date', WooPDF::get_invoice_date_timestamp($order), true);
        $order->save();
    }

    /**
     * Get invoice prefix, suffix, ID and code name
     *
     * @access public
     * @param string $order_id
     * @return array
     */
    public static function get_invoice($order_id)
    {
        // Get invoice data from post meta
        $id = self::get_invoice_meta($order_id, 'woo_pdf_invoice_id');
        $prefix = self::get_invoice_meta($order_id, 'woo_pdf_invoice_prefix');
        $suffix = self::get_invoice_meta($order_id, 'woo_pdf_invoice_suffix');
        $code = self::get_invoice_meta($order_id, 'woo_pdf_invoice_code');
        $date = self::get_invoice_meta($order_id, 'woo_pdf_invoice_date');

        // Return false if no invoice data found
        if (empty($id) || empty($code)) {
            return false;
        }

        // Otherwise, return invoice data
        return array(
            'id' => $id,
            'prefix' => $prefix,
            'suffix' => $suffix,
            'code' => $code,
            'date' => $date
        );
    }

    /**
     * Check if invoice is already generated for this order
     *
     * @access public
     * @param string $order_id
     * @return void
     */
    public static function is_invoice_generated($order_id)
    {
        // Load order
        $order = wc_get_order($order_id);

        // No order
        if (!$order) {
            return;
        }

        // Woocommerce Subscriptions check for renewal order
        $original_order = $order->get_meta('_original_order', true, 'edit');

        // Maybe remove meta copied from original order
        if (!empty($original_order)) {

            // Double-check before deleting
            $original_order_invoice_id = self::get_invoice_meta($original_order, 'woo_pdf_invoice_id');
            $current_order_invoice_id = self::get_invoice_meta($order_id, 'woo_pdf_invoice_id');

            // Actually delete meta if it's the same invoice used, and return false to generate new one
            if ($original_order_invoice_id == $current_order_invoice_id) {
                self::delete_invoice_meta($order_id, 'woo_pdf_invoice_id');
                self::delete_invoice_meta($order_id, 'woo_pdf_invoice_prefix');
                self::delete_invoice_meta($order_id, 'woo_pdf_invoice_suffix');
                self::delete_invoice_meta($order_id, 'woo_pdf_invoice_code');
                self::delete_invoice_meta($order_id, 'woo_pdf_invoice_date');
                return false;
            }
        }

        // Regular orders check
        $invoice_id = self::get_invoice_meta($order_id, 'woo_pdf_invoice_id');
        if (!empty($invoice_id)) {
            return true;
        }

        return false;
    }

    /**
     * WooCommerce order status changed - check if regular invoice needs to be generated automatically
     *
     * @access public
     * @param int $order_id
     * @return void
     */
    public function maybe_generate_regular_invoice($order_id)
    {
        // Is invoicing enabled?
        if (!$this->opt['woo_pdf_enabled']) {
            return;
        }

        // Load order
        $order = wc_get_order($order_id);

        // Check if order was loaded
        if (!$order) {
            return;
        }

        // Order must be paid but is not
        if ($this->opt['woo_pdf_generate_on'] === 'paid' && !$order->is_paid()) {
            return;
        }

        // Order must be completed but is not
        if ($this->opt['woo_pdf_generate_on'] === 'completed' && !$order->has_status('completed')) {
            return;
        }

        // Check maybe we already have invoice for this order
        if (self::is_invoice_generated($order_id)) {
            return;
        }

        // Allow developers to abort generating invoices
        if (!apply_filters('woo_pdf_generate_regular_invoice', true, $order)) {
            return;
        }

        // If not - create a new one
        $this->make_invoice($order);
    }

    /**
     * Send proforma invoice when order is created with status pending
     *
     * @access public
     * @param int $order_id
     * @param array $posted
     * @return void
     */
    public function new_order($order_id, $posted)
    {
        if ($this->opt['woo_pdf_proforma_enabled'] && $this->opt['woo_pdf_send_customer_invoice'] && class_exists('WC_Emails')) {

            $order = wc_get_order($order_id);
            $wc_emails = WC_Emails::instance();

            if (is_object($order) && is_object($wc_emails) && method_exists($wc_emails, 'customer_invoice')) {
                $wc_emails->customer_invoice($order);
            }
        }
    }

    /**
     * Send invoice by email
     *
     * @access public
     * @param string/array $attachments
     * @return string/array
     */
    public function send_by_email($attachments, $email_type = null, $order = null)
    {
        // Check if required properties were passed from WooCommerce
        if (!isset($email_type) || !is_object($order) || !is_a($order, 'WC_Order')) {
            return $attachments;
        }

        // Get order id
        $order_id = $order->get_id();

        // Allow developers to cancel attaching invoices (e.g. to only send invoices with certain payment methods)
        if (!apply_filters('woo_pdf_send_by_email', true, $order, $email_type, $attachments)) {
            return $attachments;
        }

        $send_regular   = false;
        $send_proforma  = false;

        // Send invoice to customer along with Customer Invoice email
        if ($email_type === 'customer_invoice') {

            // Check if regular invoices are enabled and invoice is available for this order
            if ($this->opt['woo_pdf_enabled'] && WooPDF::get_invoice($order_id)) {
                $send_regular = true;
            }
            // Check if proforma invoices are enabled
            else if ($this->opt['woo_pdf_proforma_enabled']) {
                $send_proforma = true;
            }
        }
        // Send proforma invoice to customer along with Order On-hold email
        else if ($email_type === 'customer_on_hold_order') {

            // Check if proforma invoice needs to be sent
            if ($this->opt['woo_pdf_proforma_enabled'] && $this->opt['woo_pdf_send_proforma_email']) {
                $send_proforma = true;
            }
        }
        // Send proforma or regular invoice to customer along with Processing Order email
        else if ($email_type === 'customer_processing_order') {

            // Check if regular invoice needs to be sent and is available for this order
            if ($this->opt['woo_pdf_enabled'] && $this->opt['woo_pdf_send_email'] && WooPDF::get_invoice($order_id)) {
                $send_regular = true;
            }
            // Check if proforma invoice needs to be sent
            else if ($this->opt['woo_pdf_proforma_enabled'] && $this->opt['woo_pdf_send_proforma_email']) {
                $send_proforma = true;
            }
        }
        // Send regular invoice to customer along with Completed Order email
        else if ($email_type === 'customer_completed_order') {

            // Check if regular invoice needs to be sent and is available for this order
            if ($this->opt['woo_pdf_enabled'] && $this->opt['woo_pdf_send_email'] && WooPDF::get_invoice($order_id)) {
                $send_regular = true;
            }
        }
        // Send proforma invoice to admin along with New Order email
        else if ($email_type === 'new_order') {

            // Check if proforma invoice needs to be sent
            if ($this->opt['woo_pdf_proforma_enabled'] && $this->opt['woo_pdf_attach_to_new_order']) {
                $send_proforma = true;
            }
        }

        // Allow developers to override
        $send_regular = apply_filters('woo_pdf_email_send_regular_invoice', $send_regular, $order_id, $email_type);
        $send_proforma = apply_filters('woo_pdf_email_send_proforma_invoice', $send_proforma, $order_id, $email_type);

        // Send regular invoice
        if ($send_regular) {

            // Get invoice details
            $invoice = WooPDF::get_invoice($order_id);

            // Get invoice path
            $upload_dir = wp_upload_dir();
            $location = $upload_dir['basedir'] . '/' . 'woocommerce_pdf_invoices';
            $invoice_path = $location . '/' . $invoice['code'] . '.pdf';

            $original_file = file_get_contents($invoice_path);

            // Use our own /tmp directory to store a copy (to avoid open_basedir / safe_mode errors)
            $temp_location = $location . '/' . 'tmp';

            if (!file_exists($temp_location)) {
                mkdir($temp_location, 0755, true);
            }

            // Protect /tmp directory from listing
            if (!file_exists($temp_location . '/index.php')) {
                touch($temp_location . '/index.php');
            }

            // Create temporary file with human-readable file name
            $file_name = _x($this->opt['woo_pdf_title_filename_prefix'], 'file name prefix', 'woo_pdf') . ($invoice['prefix'] != '' ? $invoice['prefix'] . '_' : '') . $invoice['id'] . ($invoice['suffix'] != '' ? '_' . $invoice['suffix'] : '') . '.pdf';
            $file_name = $this->sanitize_file_name($file_name);

            // Format full path
            $temp_file = $temp_location . '/' . $file_name;

            // Store temporary file
            if (file_put_contents($temp_file, $original_file)) {

                // Add invoice to attachments
                $attachments = WooPDF::add_file_to_attachments($attachments, $temp_file);
            }

            // Make sure to delete temporary file
            register_shutdown_function(array('WooPDF', 'delete_email_file'), $temp_file);
        }
        // Send proforma invoice
        else if ($send_proforma) {

            // Get (temporary) proforma invoice path
            $proforma_path = $this->get_proforma($order_id);

            // Add invoice to attachments
            $attachments = WooPDF::add_file_to_attachments($attachments, $proforma_path);

            // Make sure to delete temporary file
            register_shutdown_function(array('WooPDF', 'delete_email_file'), $proforma_path);
        }

        return $attachments;
    }

    /**
     * Add file path to email attachments variable
     *
     * @access public
     * @param mixed $attachments
     * @param string $file_path
     * @return mixed
     */
    public static function add_file_to_attachments($attachments, $file_path)
    {
        // Check if path was provided
        if (empty($file_path)) {
            return $attachments;
        }

        // Attachments variable is empty string
        if (is_string($attachments) && empty($attachments)) {
            $attachments = $file_path;
        }

        // Attachments variable is non-empty string
        if (is_string($attachments) && !empty($attachments)) {
            $attachments .= PHP_EOL . $file_path;
        }

        // Attachments variable is array
        if (is_array($attachments)) {
            array_push($attachments, $file_path);
        }

        return $attachments;
    }

    /**
     * Get next invoice number
     *
     * @access public
     * @return int
     */
    public function get_next_invoice_number()
    {
        // Get next invoice number from options
        $current_invoice_number = get_option('woo_pdf_next_invoice_number');

        // Don't have invoice number yet?
        if ($current_invoice_number === false) {

            // Do we have it in the main options array (upgraded from pre-2.1.5 version)
            $current_invoice_number = isset($this->opt['woo_pdf_next_invoice_number']) ? $this->opt['woo_pdf_next_invoice_number'] : 1;
        }

        // Check if user added preceding zeros to the number
        preg_match('/^0*/', $current_invoice_number, $zeros);
        $zero_count = $zeros[0] !== '' ? strlen($zeros[0]) : 0;

        // Get current invoice number length
        $current_number_length = strlen($current_invoice_number) - $zero_count;

        // We may need to reset counter each year
        if ($this->opt['woo_pdf_reset_each_year']) {
            if ($last_invoice_year = get_option('woo_pdf_last_invoice_year')) {
                if ((int) $last_invoice_year < (int) date('Y')) {
                    $current_invoice_number = 1;
                }
            }
        }

        // Save next invoice number
        $next_invoice_number = $current_invoice_number + 1;

        // Maybe add zeros to new number
        if (!empty($zero_count)) {

            // Get zero count in case number length has changed
            $zero_count = $zero_count - (strlen($next_invoice_number) - $current_number_length);

            // Prepend zeros, if any
            $next_invoice_number = str_repeat('0', $zero_count) . $next_invoice_number;
        }

        update_option('woo_pdf_next_invoice_number', $next_invoice_number);

        // Save current year so we can reset sequence every year
        update_option('woo_pdf_last_invoice_year', date('Y'));

        return $current_invoice_number;
    }

    /**
     * Render admin invoice download link
     *
     * @access public
     * @param string $content
     * @param object $order
     * @return string
     */
    public function admin_invoice_link($content, $order)
    {
        // Get order id
        $order_id = $order->get_id();

        // Get invoice details
        $invoice = WooPDF::get_invoice($order_id);

        // WooCommerce styling fix
        if (RightPress_Help::wc_version_gte('3.3')) {
            $button_style = 'style="text-indent:-9999px;position:relative;padding:6px 4px!important;height:2em!important;width:2em;"';
        }
        else {
            $button_style = 'style="display:block;text-indent:-9999px;position:relative;padding:6px 4px;height:2em!important;width:2em;"';
        }

        // Show regular invoice link
        if ($url = WooPDF::get_regular_invoice_url($order, true)) {

            echo '<a id="" class="button tips" ' . $button_style . ' href="' . $url . '" data-tip="' . __('Invoice', 'woo_pdf') . '">' .
                 '<img src="' . WOOPDF_PLUGIN_URL . '/assets/images/download.png' . '" alt="' . __('Invoice', 'woo_pdf') . '" width="14">' .
                 '</a>';
        }
        // Show proforma invoice link
        else if ($url = WooPDF::get_proforma_invoice_url($order, true)) {

            echo '<a id="" class="button tips" ' . $button_style . ' href="' . $url . '" data-tip="' . __('Proforma', 'woo_pdf') . '">' .
                 '<img src="' . WOOPDF_PLUGIN_URL . '/assets/images/download.png' . '" alt="' . __('Proforma', 'woo_pdf') . '" width="14">' .
                 '</a>';
        }

        return $content;
    }

    /**
     * Get invoice url
     *
     * @access public
     * @param object $order
     * @param bool $admin
     * @return string|bool
     */
    public static function get_regular_invoice_url($order, $admin = false)
    {

        $instance = WooPDF::get_instance();

        // Get invoice details
        $invoice = WooPDF::get_invoice($order->get_id());

        // Check if invoice exists and invoicing is enabled
        if (is_array($invoice) && !empty($invoice) && $instance->opt['woo_pdf_enabled']) {

            // Format download code
            $data           = $invoice['id'] . '|' . $instance->strip_pipe_character($invoice['prefix']) . '|' . $invoice['code'] . '|' . $instance->strip_pipe_character($invoice['suffix']);
            $download_code  = base64_encode($data);

            // Format query string
            $query_string = '/?wpd_invoice=' . $download_code . ($admin ? '' : '&order_key=' . $order->get_order_key());

            // Format full url and return
            return home_url($query_string);
        }

        // Unable to get URL
        return false;
    }

    /**
     * Get proforma url
     *
     * @access public
     * @param object $order
     * @param bool $admin
     * @return string|bool
     */
    public static function get_proforma_invoice_url($order, $admin = false)
    {

        $instance = WooPDF::get_instance();

        // Get order id
        $order_id = $order->get_id();

        // Get regular invoice details (we want to make sure regular invoice does not exist yet)
        $invoice = WooPDF::get_invoice($order_id);

        // Check if proforma invoice can be used
        if (!is_array($invoice) && $instance->opt['woo_pdf_proforma_enabled'] && !$order->is_paid()) {

            // Format query string
            $query_string = '/?wpd_proforma=' . $order_id . ($admin ? '' : '&order_key=' . $order->get_order_key());

            // Format full url and return
            return home_url($query_string);
        }

        // Unable to get URL
        return false;
    }

    /**
     * Render user invoice download link
     *
     * @access public
     * @param object $order
     * @return string
     */
    public function user_invoice_link($order)
    {

        // Show regular invoice link
        if ($url = WooPDF::get_regular_invoice_url($order)) {

            // Check if regular invoice link can be displayed
            if ($this->opt['woo_pdf_allow_download'] && apply_filters('woo_pdf_allow_regular_invoice_download', true, $order, 'single')) {

                echo '<p class="woo_pdf_download_link" style="padding: 15px 0;"><a id="woo_pdf_invoice_download_link" href="' . $url . '" data-tip="Invoice" style="text-decoration: none; box-shadow: none;">' .
                     '<img style="vertical-align: middle; display: inline-block;" src="' . WOOPDF_PLUGIN_URL . '/assets/images/pdf.png'.'" alt="Invoice" width="20" height="20">' .
                     '<span style="padding-left: 10px;">' . $this->opt['woo_pdf_title_download_invoice'] . '</span>' .
                     '</a></p>';
            }
        }
        // Show proforma invoice link
        else if ($url = WooPDF::get_proforma_invoice_url($order)) {

            // Check if proforma invoice link can be displayed
            if ($this->opt['woo_pdf_allow_proforma_download'] && apply_filters('woo_pdf_allow_proforma_invoice_download', true, $order, 'single')) {

                echo '<p class="woo_pdf_download_link" style="padding: 15px 0;"><a id="woo_pdf_proforma_download_link" href="' . $url . '" data-tip="Invoice" style="text-decoration: none; box-shadow: none;">' .
                     '<img style="vertical-align: middle; display: inline-block;" src="' . WOOPDF_PLUGIN_URL . '/assets/images/pdf.png'.'" alt="Invoice" width="20" height="20">' .
                     '<span style="padding-left: 10px;">' . $this->opt['woo_pdf_title_download_proforma'] . '</span>' .
                     '</a></p>';
            }
        }
    }

    /**
     * Get invoice meta field
     *
     * @access public
     * @param int $order_id
     * @param string $field
     * @return string|bool
     */
    public static function get_invoice_meta($order_id, $field)
    {
        // Load order
        $order = wc_get_order($order_id);

        // No order
        if (!$order) {
            return false;
        }

        // Try to get field without underscore
        $value = $order->get_meta($field, true, 'edit');

        if (!empty($value)) {
            return $value;
        }

        // Then try to get field with underscore
        $value = $order->get_meta(('_' . $field), true, 'edit');

        if (!empty($value)) {
            return $value;
        }

        return false;
    }

    /**
     * Delete invoice meta field of both formats
     *
     * @access public
     * @return void
     */
    public static function delete_invoice_meta($order_id, $field)
    {
        RightPress_WC::order_delete_meta_data($order_id, $field);
        RightPress_WC::order_delete_meta_data($order_id, ('_' . $field));
    }

    /**
     * Batch download invoices
     *
     * @access public
     * @return void
     */
    public function batch_download()
    {
        // Check if zip extension is present
        if (!extension_loaded('zip')) {
            exit();
        }

        // Check if user has rights to batch download invoices
        if (!$this->is_user_ok()) {
            return;
        }

        // Get dates
        $this->date_from = $_GET['woo_pdf_download_from'];
        $this->date_to = $_GET['woo_pdf_download_to'];

        // Load ids of orders with invoices
        $order_ids = wc_get_orders(array(
            '_woo_pdf_invoice_date__from'   => strtotime($this->date_from),
            '_woo_pdf_invoice_date__to'     => strtotime('tomorrow', strtotime($this->date_to)) - 1,
            'limit'                         => -1,
            'return'                        => 'ids',
        ));

        // Legacy - invoices were stored as order post attachments prior to version 3.0
        $attachment_files = array();

        // Load attachment invoices
        $attachments = query_posts(array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_mime_type' => 'application/pdf',
            'post_status' => 'inherit',
            'date_query' => array(
                'after'     => date('Y-m-d H:i:s', strtotime($this->date_from)),
                'before'    => date('Y-m-d H:i:s', (strtotime('tomorrow', strtotime($this->date_to)) - 1)),
                'inclusive' => true,
            ),
        ));

        // Add attachments (for old orders support)
        if (!empty($attachments)) {

            foreach ($attachments as $attachment) {

                $order_post = get_post($attachment->post_parent);

                if (!$order_post || $order_post->post_type != 'shop_order') {
                    continue;
                }

                if (!in_array($order_post->ID, $order_ids)) {
                    $order_ids[] = $order_post->ID;
                    $attachment_files[$order_post->ID] = get_attached_file($attachment->ID);
                }
            }
        }

        // Set up file directory
        $upload_dir = wp_upload_dir();
        $location = $upload_dir['basedir'] . '/' . 'woocommerce_pdf_invoices/tmp';
        if (!file_exists($location)) {
            mkdir($location, 0755, true);
        }

        // Protect /tmp directory from listing
        if (!file_exists($location . '/index.php')) {
            touch($location . '/index.php');
        }

        // Generate zip file
        $file = tempnam($location, 'woo_pdf');
        $zip = new ZipArchive();
        $zip->open($file, ZipArchive::OVERWRITE);

        $file_added = false;

        // Add files to zip
        foreach ($order_ids as $order_id) {

            // Get invoice meta
            $id = self::get_invoice_meta($order_id, 'woo_pdf_invoice_id');
            $code = self::get_invoice_meta($order_id, 'woo_pdf_invoice_code');
            $prefix = self::get_invoice_meta($order_id, 'woo_pdf_invoice_prefix');
            $suffix = self::get_invoice_meta($order_id, 'woo_pdf_invoice_suffix');

            if ($id == '') {
                continue;
            }

            // Format file path and file name
            $file_path = isset($attachment_files[$order_id]) ? $attachment_files[$order_id] : $upload_dir['basedir'] . '/' . 'woocommerce_pdf_invoices' . '/' . $code . '.pdf';
            $file_name = _x($this->opt['woo_pdf_title_filename_prefix'], 'file name prefix', 'woo_pdf') . (!empty($prefix) ? $prefix . '_' : '') . $id . (!empty($suffix) ? '_' . $suffix : '') . '.pdf';

            // Sanitize file name (issue #192)
            $file_name = $this->sanitize_file_name($file_name);

            // Add file
            $zip->addFile($file_path, $file_name);

            $file_added = true;
        }

        // Add dummy data if no files were added
        if (!$file_added) {
            $zip->addFromString('no invoices for selected period', '');
        }

        // Close and output
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: attachment; filename="file.zip"');

        // Clear any open output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        readfile($file);
        @unlink($file);
        exit;
    }

    /**
     * Pushes invoice file to the browser
     *
     * @access public
     * @return void
     */
    public function push_invoice()
    {
        $this->maybe_force_user_login();

        $invoice = explode('|', base64_decode($_GET['wpd_invoice']));

        if (count($invoice) != 4) {
            exit;
        }

        // Load order
        $order = WooPDF::get_order_by_meta('_woo_pdf_invoice_code', $invoice[2]);

        // Unable to load order
        if (!$order) {
            exit;
        }

        // Get order key
        $order_key = RightPress_Help::wc_version_gte('3.0') ? $order->get_order_key() : $order->order_key;

        // Check if user has a right to get this document
        if (!$this->is_user_ok() && (get_current_user_id() != $order->get_customer_id() || !is_user_logged_in()) && (empty($_REQUEST['order_key']) || $_REQUEST['order_key'] !== $order_key)) {
            exit;
        }

        // Get file path
        $file_path = WooPDF::get_regular_invoice_full_path($invoice[2]);

        // Get sanitized file name
        $file_name = _x($this->opt['woo_pdf_title_filename_prefix'], 'file name prefix', 'woo_pdf') . (!empty($invoice[1]) ? $invoice[1] . '_' : '') . $invoice[0] . (!empty($invoice[3]) ? '_' . $invoice[3] : '') . '.pdf';
        $file_name = $this->sanitize_file_name($file_name);

        // Clear any open output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Push file to browser
        if ($fp = fopen($file_path, 'rb')) {
            header('Content-Type: application/pdf');
            header('Content-Length: ' . filesize($file_path));
            header('Content-disposition: attachment; filename="' . $file_name . '"');
            fpassthru($fp);
        }

        exit;
    }

    /**
     * Generates and pushes proforma invoice to the browser
     *
     * @access public
     * @return void
     */
    public function push_proforma()
    {
        if (!$this->opt['woo_pdf_proforma_enabled']) {
            return;
        }

        $order_id = $_GET['wpd_proforma'];

        if (!class_exists('WC_Order')) {
            exit();
        }

        // Load order
        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        // Check if user has a right to get this document
        if (!$this->is_user_ok() && (get_current_user_id() != $order->get_customer_id() || !is_user_logged_in()) && (empty($_REQUEST['order_key']) || $_REQUEST['order_key'] !== $order->get_order_key())) {
            return;
        }

        // Load PDF class
        if (!class_exists('TCPDF')) {
            require WOOPDF_PLUGIN_PATH.'libraries/tcpdf/tcpdf.php';
        }
        if (!class_exists('WooPdfInvoice')) {
            require WOOPDF_PLUGIN_PATH.'classes/woo-pdf-invoice.class.php';
        }

        $display_order_id = $order->get_order_number();

        $info = array(
            'id' => $display_order_id,
            'prefix' => '',
            'suffix' => '',
            'code' => ''
        );

        // We don't need hash tag before invoice name for file name
        $display_order_id = preg_replace('/[^0-9.]+/', '', $display_order_id);

        // Clear any open output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Generate proforma and push it directly to browser
        $pdf = new WooPdfInvoice(array('order' => $order, 'options' => $this->get_options(), 'info' => $info, 'type' => 'proforma'), 'P', 'pt', 'A4');
        $pdf->CreateInvoice();
        $pdf->Output($display_order_id.'.pdf', 'D');
        exit();
    }

    /**
     * Delete regular invoice request for specified order
     *
     * @access public
     * @return false
     */
    public function delete_invoice_request()
    {
        // Check if user has rights to delete invoices
        if (!$this->is_user_ok()) {
            return;
        }

        // Extract request data
        $invoice = explode('|', base64_decode($_GET['wpd_delete_invoice']));

        if (count($invoice) != 4) {
            return;
        }

        // Delete invoice
        $this->delete_invoice($_GET['order_id']);

        // Redirect back to order page
        wp_redirect(admin_url('/post.php?post='.$_GET['order_id'].'&action=edit'));
        exit;
    }

    /**
     * Delete invoice and its meta by order id
     *
     * @access public
     * @param int $order_id
     * @return void
     */
    public function delete_invoice($order_id)
    {
        // Delete invoice post attachment (legacy)
        $this->delete_invoice_post_attachment($order_id);

        // Get invoice access code
        if ($code = self::get_invoice_meta($order_id, 'woo_pdf_invoice_code')) {

            // Get file path
            $upload_dir = wp_upload_dir();
            $location = $upload_dir['basedir'] . '/' . 'woocommerce_pdf_invoices';
            $file_path = $location . '/' . $code . '.pdf';

            // Delete invoice
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Remove post meta from order post
        self::delete_invoice_meta($order_id, 'woo_pdf_invoice_id');
        self::delete_invoice_meta($order_id, 'woo_pdf_invoice_prefix');
        self::delete_invoice_meta($order_id, 'woo_pdf_invoice_suffix');
        self::delete_invoice_meta($order_id, 'woo_pdf_invoice_code');
        self::delete_invoice_meta($order_id, 'woo_pdf_invoice_date');
    }

    /**
     * Delete invoice post attachment
     *
     * Legacy - prior to version 3.0 invoices were added as attachments to order posts
     *
     * @access public
     * @param int $order_id
     * @return void
     */
    public function delete_invoice_post_attachment($order_id)
    {
        // Get all post attachments (to find the one that needs to be removed - support for old orders)
        $attachments = get_children($order_id);

        // Find and delete post attachment that represents invoice
        if (is_array($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                if ($attachment->post_mime_type == 'application/pdf' && preg_match('/^invoice\-.+/', $attachment->post_name)) {
                    wp_delete_attachment($attachment->ID);
                    break;
                }
            }
        }
    }

    /**
     * Generate regular invoice manually on specified order
     *
     * @access public
     * @return false
     */
    public function generate_invoice()
    {
        if (!class_exists('WC_Order')) {
            return;
        }

        // Load order
        $order = wc_get_order($_GET['wpd_generate_invoice']);

        if (!$order) {
            return;
        }

        // Check if user has rights to generate invoices
        if (!$this->is_user_ok()) {
            return;
        }

        // Check maybe we already have invoice for this order
        if (self::is_invoice_generated($order->get_id())) {
            wp_redirect(admin_url('/post.php?post='.$_GET['wpd_generate_invoice'].'&action=edit'));
            exit;
        }

        // If not - create a new one
        $this->make_invoice($order);

        // Redirect back to order page
        wp_redirect(admin_url('/post.php?post='.$_GET['wpd_generate_invoice'].'&action=edit'));
        exit;
    }

    /**
     * Generate proforma invoice and store it temporary
     *
     * @access public
     * @param string $order_id
     * @return string
     */
    public function get_proforma($order_id)
    {
        if (!$this->opt['woo_pdf_proforma_enabled']) {
            return;
        }

        if (!class_exists('WC_Order')) {
            exit();
        }

        // Load order
        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        // Load PDF class
        if (!class_exists('TCPDF')) {
            require WOOPDF_PLUGIN_PATH.'libraries/tcpdf/tcpdf.php';
        }
        if (!class_exists('WooPdfInvoice')) {
            require WOOPDF_PLUGIN_PATH.'classes/woo-pdf-invoice.class.php';
        }

        $info = array(
            'id' => $order->get_order_number(),
            'prefix' => '',
            'suffix' => '',
            'code' => ''
        );

        // Create temporary file
        $upload_dir = wp_upload_dir();
        $temp_location = $upload_dir['basedir'] . '/' . 'woocommerce_pdf_invoices' . '/' . 'tmp';
        if (!file_exists($temp_location)) {
            mkdir($temp_location, 0755, true);
        }

        // Protect /tmp directory from listing
        if (!file_exists($temp_location . '/index.php')) {
            touch($temp_location . '/index.php');
        }

        $temp_file = $temp_location . '/' . $order->get_order_number() . '.pdf';

        // Generate proforma and save it to disk temporary
        $pdf = new WooPdfInvoice(array('order' => $order, 'options' => $this->get_options(), 'info' => $info, 'type' => 'proforma'), 'P', 'pt', 'A4');
        $pdf->CreateInvoice();
        $pdf->Output($temp_file, 'F');

        return $temp_file;
    }

    /**
     * Maybe force user to login
     *
     * @access public
     * @return void
     */
    public function maybe_force_user_login() {

        if ($this->opt['woo_pdf_force_login'] && !is_user_logged_in()) {
            auth_redirect();
        }
    }

    /**
     * Check user has rights for some actions
     *
     * @access public
     * @return void
     */
    public function is_user_ok() {

        // Check capability
        if (!current_user_can(self::get_admin_capability())) {
            return false;
        }

        // Check roles
        if (array_intersect(self::get_admin_roles(), RightPress_Help::current_user_roles())) {
            return true;
        }

        return false;
    }

    /**
     * Load scripts required for admin
     *
     * @access public
     * @return void
     */
    public function enqueue_scripts() {
        // Scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script('media-upload');
        wp_enqueue_media();
        wp_enqueue_script('thickbox');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-tooltip');

        // Our own
        wp_register_script('woo-pdf-js', WOOPDF_PLUGIN_URL . '/assets/js/woo-pdf.js', array('jquery'), WOOPDF_VERSION);
        wp_enqueue_script('woo-pdf-js');

        // Styles
        wp_register_style('woo-pdf-css', WOOPDF_PLUGIN_URL . '/assets/css/style.css', array(), WOOPDF_VERSION);
        wp_enqueue_style('woo-pdf-css');
        wp_register_style('woo-pdf-jquery-ui', WOOPDF_PLUGIN_URL . '/assets/css/jquery-ui.css', array(), '1.10.3');
        wp_enqueue_style('woo-pdf-jquery-ui');
        wp_register_style('woo-pdf-font-awesome', WOOPDF_PLUGIN_URL . '/assets/css/font-awesome/css/font-awesome.min.css', array(), '4.0.3');
        wp_enqueue_style('woo-pdf-font-awesome');
        wp_enqueue_style('thickbox');
    }

    /**
     * Check if PHP image processing extension is installed
     *
     * @access public
     * @return bool
     */
    public function image_library_exists()
    {
        if (extension_loaded('imagick') || (extension_loaded('gd') && function_exists('gd_info'))) {
            return true;
        }

        return false;
    }

    /**
     * Hide attachment pages
     *
     * Legacy - prior to version 3.0 invoices were added as attachments to order posts
     *
     * @access public
     * @return void
     */
    public function hide_attachment_pages()
    {
        global $post;

        if (is_attachment() && isset($post->post_parent) && is_numeric($post->post_parent) && ($post->post_parent != 0)) {

            $parent = get_post($post->post_parent);

            if ($post->post_mime_type == 'application/pdf' && $parent->post_type == 'shop_order') {
                wp_redirect(home_url(), 301);
            }
        }
    }

    /**
     * Add settings link on plugins page
     *
     * @access public
     * @return void
     */
    public function plugin_settings_link($links)
    {
        // Support
        $settings_link = '<a href="http://url.rightpress.net/support-site" target="_blank">' . __('Support', 'woo_pdf') . '</a>';
        array_unshift($links, $settings_link);

        // Settings
        if (self::check_environment()) {
            $settings_link = '<a href="admin.php?page=woo-pdf">' . __('Settings', 'woo_pdf') . '</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    /**
     * Maybe show invoice download button on the orders page
     *
     * @access public
     * @param array $actions
     * @return array
     */
    public function orders_actions($actions, $order)
    {
        if ($this->opt['woo_pdf_display_orders_page_button']) {

            $invoice = WooPDF::get_invoice($order->get_id());

            // Show invoice link
            if (is_array($invoice) && !empty($invoice) && $this->opt['woo_pdf_enabled'] && $this->opt['woo_pdf_allow_download'] && apply_filters('woo_pdf_allow_regular_invoice_download', true, $order, 'list')) {
                $data = $invoice['id'] . '|' . $this->strip_pipe_character($invoice['prefix']) . '|' . $invoice['code'] . '|' . $this->strip_pipe_character($invoice['suffix']);
                $download_code = base64_encode($data);
                $download_url = home_url('/?wpd_invoice='.$download_code);
                $title = $this->opt['woo_pdf_document_name'];
            }
            // Show proforma link
            else if (!is_array($invoice) && $this->opt['woo_pdf_proforma_enabled'] && $this->opt['woo_pdf_allow_proforma_download'] && !$order->is_paid() && apply_filters('woo_pdf_allow_proforma_invoice_download', true, $order, 'list')) {
                $download_url = home_url('/?wpd_proforma=' . $order->get_id());
                $title = $this->opt['woo_pdf_proforma_name'];
            }

            if (isset($download_url) && isset($title)) {
                $actions['invoice'] = array(
                    'url' => $download_url,
                    'name' => $title,
                );
            }
        }

        return $actions;
    }

    /**
     * Add admin meta box with actions
     *
     * WC31: As of WC 3.4 this is still done treating orders as posts
     *
     * @access public
     * @return void
     */
    public function add_woo_pdf_metabox()
    {
        global $post;

        if (!$post) {
            return;
        }

        // Get order
        $order = wc_get_order($post->ID);

        if (!$order || $order->get_status() === 'auto-draft') {
            return;
        }

        $invoice = WooPDF::get_invoice($post->ID);

        if ((is_array($invoice) && !empty($invoice) && $this->opt['woo_pdf_enabled']) || (!is_array($invoice) && $this->opt['woo_pdf_proforma_enabled'] && (!$order->is_paid() || $this->opt['woo_pdf_enabled'])) || ($this->opt['woo_pdf_enabled'])) {
            add_meta_box('woo_pdf_metabox', __('PDF Invoices', 'woo_pdf'), array($this, 'woo_pdf_metabox_content'), 'shop_order', 'side', 'default');
        }
    }

    /**
     * Add admin meta box content
     *
     * WC31: As of WC 3.4 this is still done treating orders as posts
     *
     * @access public
     * @return void
     */
    public function woo_pdf_metabox_content()
    {
        global $post;

        if (!$post) {
            return;
        }

        // Get order
        $order = wc_get_order($post->ID);

        if (!$order) {
            return;
        }

        $invoice = WooPDF::get_invoice($post->ID);

        echo '<table class="form-table">';

        if (is_array($invoice) && !empty($invoice) && $this->opt['woo_pdf_enabled']) {

            $data = $invoice['id'] . '|' . $this->strip_pipe_character($invoice['prefix']) . '|' . $invoice['code'] . '|' . $this->strip_pipe_character($invoice['suffix']);
            $download_code = base64_encode($data);
            $download_url = home_url('/?wpd_invoice='.$download_code);
            $delete_url = home_url('/?wpd_delete_invoice='.$download_code.'&order_id='.$post->ID);

            ?>
                <tr>
                    <td>
                        <a class="button tips" href="<?php echo $download_url; ?>" data-tip="<?php _e('Download regular invoice', 'woo_pdf'); ?>"><?php _e('Invoice', 'woo_pdf'); ?></a>
                        <?php if ($this->opt['woo_pdf_allow_delete']): ?>
                            <a class="button tips" href="<?php echo $delete_url; ?>" data-tip="<?php _e('Delete invoice so you can regenerate it if needed', 'woo_pdf'); ?>"><?php _e('Delete Invoice', 'woo_pdf'); ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php
        }
        else if (!is_array($invoice) && $this->opt['woo_pdf_proforma_enabled'] && (!$order->is_paid() || $this->opt['woo_pdf_enabled'])) {

            $download_url = home_url('/?wpd_proforma='.$post->ID);
            $generate_url = home_url('/?wpd_generate_invoice='.$post->ID);

            ?>
                <tr>
                    <td>
                        <?php if ($order->get_status() != 'completed'): ?>
                            <a class="button tips" href="<?php echo $download_url; ?>" data-tip="<?php _e('Download proforma invoice', 'woo_pdf'); ?>"><?php _e('Proforma', 'woo_pdf'); ?></a>
                        <?php endif; ?>
                        <?php if ($this->opt['woo_pdf_enabled']): ?>
                            <a class="button tips" href="<?php echo $generate_url; ?>" data-tip="<?php _e('Manually generate regular invoice', 'woo_pdf'); ?>"><?php _e('Generate Invoice', 'woo_pdf'); ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php
        }
        else if ($this->opt['woo_pdf_enabled']) {

            $generate_url = home_url('/?wpd_generate_invoice='.$post->ID);

            ?>
                <tr>
                    <td>
                        <a class="button tips" href="<?php echo $generate_url; ?>" data-tip="<?php _e('Manually generate regular invoice', 'woo_pdf'); ?>"><?php _e('Generate Invoice', 'woo_pdf'); ?></a>
                    </td>
                </tr>
            <?php
        }

        echo '</table>';
    }

    /**
     * Delete temporary file (shutdown function)
     *
     * @param string $file
     * @return void
     */
    public static function delete_email_file($file)
    {
        @unlink($file);
    }

    /**
     * Replace prefix/suffix macros
     *
     * @access public
     * @param string $string
     * @param object $order
     * @param string $position
     * @return string
     */
    public function replace_prefix_suffix_macros($string, $order, $position = 'prefix')
    {
        // Define macros
        $macros = array(
            '{{year}}'  => (in_array($this->opt['woo_pdf_date_format'], array('0', '2', '4')) ? date('y') : date('Y')),
            '{{month}}' => (in_array($this->opt['woo_pdf_date_format'], array('0', '1', '2', '3')) ? date('n') : date(($this->opt['woo_pdf_date_format'] == '6') ? ('F') : ('m'))),
            '{{day}}'   => (in_array($this->opt['woo_pdf_date_format'], array('4', '5', '7', '8')) ? date('d') : date('j')),
        );

        // Allow developers to add their own macros
        $macros = apply_filters('woo_pdf_prefix_suffix_macros', $macros, $order, $position);

        foreach ($macros as $key => $value) {
            $string = preg_replace('/' . preg_quote($key) . '/i', $value, $string);
        }

        return $string;
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
        if (!version_compare(PHP_VERSION, WOOPDF_SUPPORT_PHP, '>=')) {

            // Add notice
            add_action('admin_notices', array('WooPDF', 'php_version_notice'));

            // Do not proceed as RightPress Helper requires PHP 5.3
            return false;
        }

        // Check WordPress version
        if (!RightPress_Help::wp_version_gte(WOOPDF_SUPPORT_WP)) {
            add_action('admin_notices', array('WooPDF', 'wp_version_notice'));
            $is_ok = false;
        }

        // Check if WooCommerce is enabled
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array('WooPDF', 'wc_disabled_notice'));
            $is_ok = false;
        }
        else if (!RightPress_Help::wc_version_gte(WOOPDF_SUPPORT_WC)) {
            add_action('admin_notices', array('WooPDF', 'wc_version_notice'));
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
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce PDF Invoice</strong> requires PHP %s or later. Please update PHP on your server to use this plugin.', 'woo_pdf'), WOOPDF_SUPPORT_PHP) . ' ' . sprintf(__('If you have any questions, please contact %s.', 'woo_pdf'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'woo_pdf') . '</a>') . '</p></div>';
    }

    /**
     * Display WP version notice
     *
     * @access public
     * @return void
     */
    public static function wp_version_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce PDF Invoice</strong> requires WordPress version %s or later. Please update WordPress to use this plugin.', 'woo_pdf'), WOOPDF_SUPPORT_WP) . ' ' . sprintf(__('If you have any questions, please contact %s.', 'woo_pdf'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'woo_pdf') . '</a>') . '</p></div>';
    }

    /**
     * Display WC disabled notice
     *
     * @access public
     * @return void
     */
    public static function wc_disabled_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce PDF Invoice</strong> requires WooCommerce to be active. You can download WooCommerce %s.', 'woo_pdf'), '<a href="http://url.rightpress.net/woocommerce-download-page">' . __('here', 'woo_pdf') . '</a>') . ' ' . sprintf(__('If you have any questions, please contact %s.', 'woo_pdf'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'woo_pdf') . '</a>') . '</p></div>';
    }

    /**
     * Display WC version notice
     *
     * @access public
     * @return void
     */
    public static function wc_version_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce PDF Invoice</strong> requires WooCommerce version %s or later. Please update WooCommerce to use this plugin.', 'woo_pdf'), WOOPDF_SUPPORT_WC) . ' ' . sprintf(__('If you have any questions, please contact %s.', 'woo_pdf'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'woo_pdf') . '</a>') . '</p></div>';
    }

    /**
     * Get invoice path
     *
     * Used for integration with other plugins
     * Returns path to file with its original name
     * Files are stored temporarily for the duration of the request only
     *
     * TBD: code from this method is used in other methods, needs some refactoring
     *
     * @access public
     * @param mixed $order
     * @return mixed
     */
    public static function get_order_invoice_path($order)
    {

        $instance = WooPDF::get_instance();

        // Load order
        if (!is_object($order)) {
            $order = wc_get_order($order);
        }

        // Check if order was loaded
        if (!$order) {
            return false;
        }

        // Attempt to get regular invoice details
        $invoice = WooPDF::get_invoice($order->get_id());

        // Get regular invoice path
        if (!empty($invoice)) {

            // Get invoice path
            $upload_dir = wp_upload_dir();
            $location = $upload_dir['basedir'] . '/' . 'woocommerce_pdf_invoices';
            $invoice_path = $location . '/' . $invoice['code'] . '.pdf';

            $original_file = file_get_contents($invoice_path);

            // Use our own /tmp directory to store a copy (to avoid open_basedir / safe_mode errors)
            $temp_location = $location . '/' . 'tmp';

            if (!file_exists($temp_location)) {
                mkdir($temp_location, 0755, true);
            }

            // Protect /tmp directory from listing
            if (!file_exists($temp_location . '/index.php')) {
                touch($temp_location . '/index.php');
            }

            // Create temporary file with human-readable file name
            $file_name = _x(WooPDF::opt('woo_pdf_title_filename_prefix'), 'file name prefix', 'woo_pdf') . ($invoice['prefix'] != '' ? $invoice['prefix'] . '_' : '') . $invoice['id'] . ($invoice['suffix'] != '' ? '_' . $invoice['suffix'] : '') . '.pdf';
            $file_name = $instance->sanitize_file_name($file_name);

            // Format full path
            $temp_file = $temp_location . '/' . $file_name;

            // Attempt to store file temporarily
            if (!file_put_contents($temp_file, $original_file)) {
                return false;
            }

            // Make sure to delete temporary file by the end of this request
            register_shutdown_function(array('WooPDF', 'delete_email_file'), $temp_file);

            // Return regular invoice path
            return $temp_file;
        }
        // Get proforma invoice path
        else if (WooPDF::opt('woo_pdf_proforma_enabled')) {

            // Get order statuses for which proforma invoices are available
            // TBD: we should make this check general and use in other methods too
            $proforma_invoice_order_statuses = apply_filters('woo_pdf_proforma_invoice_order_statuses', array('pending', 'on-hold'));

            if (!in_array($order->get_status(), $proforma_invoice_order_statuses)) {
                return false;
            }

            // Get (temporary) proforma invoice path
            $proforma_path = $instance->get_proforma($order->get_id());

            // Make sure to delete temporary file by the end of this request
            register_shutdown_function(array('WooPDF', 'delete_email_file'), $proforma_path);

            // Return proforma invoice path
            return $proforma_path;
        }

        return false;
    }

    /**
     * Get order by meta
     *
     * @access public
     * @param string $meta_key
     * @param string $meta_value
     * @return object|bool
     */
    public static function get_order_by_meta($meta_key, $meta_value)
    {
        // Whitelisted meta keys (see self::add_custom_orders_query_vars())
        if (!in_array($meta_key, array('_woo_pdf_invoice_code', '_woo_pdf_invoice_date__from', '_woo_pdf_invoice_date__to'), true)) {
            throw new Exception('WooCommerce PDF Invoice: Trying to get orders by meta without adding custom query vars first.');
        }

        // Get orders
        $orders = wc_get_orders(array(
            'limit'     => 1,
            $meta_key   => $meta_value
        ));

        // Return order
        return !empty($orders) ? array_pop($orders) : false;
    }

    /**
     * Optionally remove regular invoices when personal order data is erased
     *
     * @access public
     * @param object $order
     * @return void
     */
    public function maybe_remove_invoices_for_privacy($order)
    {
        // Delete invoice if it needs to be deleted
        if (WooPDF::opt('woo_pdf_remove_invoices_for_privacy')) {
            $this->delete_invoice($order->get_id());
        }
    }

    /**
     * Add invoice meta to order personal data export
     *
     * @access public
     * @param array $meta_to_export
     * @return array
     */
    public function add_invoice_meta_to_personal_data_export($meta_to_export)
    {
        // Add invoice related meta keys
        $meta_to_export['woo_pdf_invoice_id']   = __('Invoice number', 'woo_pdf');
        $meta_to_export['woo_pdf_invoice_date'] = __('Invoice date', 'woo_pdf');

        // Return meta keys
        return $meta_to_export;
    }

    /**
     * Get invoice meta value for order personal data export
     *
     * @access public
     * @param string $value
     * @param string $meta_key
     * @param object $order
     * @return string
     */
    public function get_invoice_meta_value_for_personal_data_export($value, $meta_key, $order)
    {
        // Get order id
        $order_id = $order->get_id();

        // Invoice number
        if ($meta_key === 'woo_pdf_invoice_id') {

            // Get invoice details
            if ($invoice = WooPDF::get_invoice($order_id)) {

                // Return formatter invoice number
                return WooPDF::get_formatted_invoice_number($invoice['id'], $invoice['prefix'], $invoice['suffix']);
            }
        }

        // Invoice date
        if ($meta_key === 'woo_pdf_invoice_date') {

            // Get invoice details
            if ($invoice = WooPDF::get_invoice($order_id)) {

                // Format and return date
                return date(WooPDF::get_date_format(), $invoice['date']);
            }
        }

        return $value;
    }

    /**
     * Get date format
     *
     * @access public
     * @return string
     */
    public static function get_date_format()
    {
        // Define formats
        $formats = array(
            '0' => 'n/j/y',
            '1' => 'n/j/Y',
            '2' => 'j/n/y',
            '3' => 'j/n/Y',
            '4' => 'y-m-d',
            '5' => 'Y-m-d',
            '6' => 'F j, Y',
            '7' => 'd.m.Y',
            '8' => 'd-m-Y',
        );

        // Get setting
        $setting = WooPDF::opt('woo_pdf_date_format');

        // Return format string
        return apply_filters('woo_pdf_date_format', $formats[$setting]);
    }

    /**
     * Get formatted invoice number
     *
     * @access public
     * @param string $id
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public static function get_formatted_invoice_number($id, $prefix, $suffix)
    {
        // Get invoice number separator
        $invoice_number_separator = WooPDF::get_invoice_number_separator();

        // Format and return
        return (!empty($prefix) ? WooPDF::decode($prefix) . $invoice_number_separator : '') . $id . (!empty($suffix) ? $invoice_number_separator . WooPDF::decode($suffix) : '');
    }

    /**
     * Decode HTML special entities back to characters
     *
     * @access public
     * @param string $string
     * @return string
     */
    public static function decode($string)
    {
        return html_entity_decode(htmlspecialchars_decode($string, ENT_QUOTES), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get invoice number separator
     *
     * @access public
     * @return string
     */
    public static function get_invoice_number_separator()
    {
        return apply_filters('woo_pdf_invoice_number_separator', ' ');
    }

    /**
     * Allow custom vars in WooCommerce orders query
     *
     * @access public
     * @param array $query
     * @param array $query_vars
     * @return array
     */
    public function add_custom_orders_query_vars($query, $query_vars)
    {
        // Invoice code
        if (!empty($query_vars['_woo_pdf_invoice_code'])) {
            $query['meta_query'][] = array(
                'key'   => '_woo_pdf_invoice_code',
                'value' => esc_attr($query_vars['_woo_pdf_invoice_code']),
            );
        }

        // Invoice date from
        if (!empty($query_vars['_woo_pdf_invoice_date__from'])) {
            $query['meta_query'][] = array(
                'key'       => '_woo_pdf_invoice_date',
                'value'     => esc_attr($query_vars['_woo_pdf_invoice_date__from']),
                'compare'   => '>=',
            );
        }

        // Invoice date to
        if (!empty($query_vars['_woo_pdf_invoice_date__to'])) {
            $query['meta_query'][] = array(
                'key'       => '_woo_pdf_invoice_date',
                'value'     => esc_attr($query_vars['_woo_pdf_invoice_date__to']),
                'compare'   => '<=',
            );
        }

	return $query;
    }

    /**
     * Sanitize file name
     *
     * @access public
     * @param string $file_name
     * @return string
     */
    public function sanitize_file_name($file_name)
    {

        // Replace non-allowed characters with underscore
        $file_name = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '_', $file_name);

        // Remove runs of underscores
        $file_name = mb_ereg_replace("([_]{2,})", '', $file_name);

        // Remove runs of periods
        $file_name = mb_ereg_replace("([\.]{2,})", '', $file_name);

        return $file_name;
    }

    /**
     * Strip pipe character
     *
     * @access public
     * @param string $string
     * @return string
     */
    public function strip_pipe_character($string)
    {

        return str_replace('|', '', $string);
    }

    /**
     * Get invoice date timestamp
     *
     * @access public
     * @param object $order
     * @param int $custom_timestamp
     * @return int
     */
    public static function get_invoice_date_timestamp($order, $custom_timestamp = null)
    {

        // Get timestamp
        $timestamp = $custom_timestamp !== null ? $custom_timestamp : time();

        // Allow developers to override and return
        return apply_filters('woo_pdf_invoice_date_timestamp', $timestamp, $order);
    }

    /**
     * Get regular invoice full path
     *
     * @access public
     * @param string $file_name
     * @retirn string
     */
    public static function get_regular_invoice_full_path($file_name)
    {

        // Get uploads directory path
        $upload_dir = wp_upload_dir();

        // Format and return full path
        return $upload_dir['basedir'] . '/woocommerce_pdf_invoices/' . $file_name . '.pdf';
    }





}

WooPDF::get_instance();

}
