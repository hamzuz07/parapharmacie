<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
RightPress_Loader::load_class_collection('data-updater');

/**
 * Data Updater
 *
 * @class RP_SUB_Data_Updater
 * @package Subscriptio
 * @author RightPress
 */

class RP_SUB_Data_Updater extends RightPress_Data_Updater
{

    // TODO: $this->is_installation will be false during upgrade since rp_sub_version was already set in 2.3.9
    // TODO: migrate from old settings stored under 'subscriptio_options'
    // TODO: Migration - most probably all subscriptions should have their items set to existing products BEFORE migration takes place and we should provide a tool to link items to products on site
    // TODO: possibly change from post_type="subscription" to post_type="rp_sub_subscription" and write a separate old data retrieval method
    // TODO: possibly change from post_type="sub_transaction" to post_type="rp_sub_log_entry" and write a separate old data retrieval method
    // TODO: Ensure plugin settings and object data are not overriden if old settings or object page is submitted after the update (opened before update)
    // TODO: Shouldn't we ensure that only one updater is running and not a few of them accidentally? Also, need a way to safely run migration of bigger data sets that may cause timeout/memory issues (e.g. Stripe migration problems).
    // TODO: When migrating, check all date fields for this issue https://github.com/RightPress/subscriptio/issues/319
    // TODO: Copies of settings, objects, data as they were before migration https://github.com/RightPress/subscriptio/issues/393
    // TODO: Must fill last_renewal_order_id for all subscriptions with renewal orders when migrating to 3.0
    // TODO: Previously we allowed manual scheduled event date changes - need to account for that when migrating
    // TODO: Migration script should check if everything is ready, e.g. Stripe extension installed or so.
    // TODO: Incorporate Stripe migration (if not migrated yet) into the main migration process, separate migration script is not in the new version (include source/customer retrieval from all possible locations)
    // TODO: Need to import scheduled actions from old schedulers (we had one current and one legacy)
    // TODO: Migrate main site url (old subscriptio_main_site_url, new rp_sub_main_site_url; old subscriptio_ignore_url_mismatch, new rp_sub_ignored_mismatch_url)
    // TODO: Migrated email enabled state and email settings
    // TODO: Migrate product settings, including "Subscription" checkbox key in database (was '_subscriptio', now '_rp_sub:subscription_product')
    // TODO: Before migrating, check if new version has never run on the same site (e.g. check for presence of settings or subscription records in database) as this could become messed up
    // TODO: Need to consider data under _subscriptio_pause_limit in user meta for pause_limit_reached()
    // TODO: If we used to grant file access to subscribers in a different way, need to fix this during migration
    // TODO: Need to check old _subscriptio_trial_product_ids during subscriptio_customer_is_eligible_for_trial

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

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Get plugin version
     *
     * @access public
     * @return string
     */
    public function get_plugin_version()
    {

        return RP_SUB_VERSION;
    }

    /**
     * Get plugin private prefix
     *
     * @access public
     * @return string
     */
    public function get_plugin_private_prefix()
    {

        return RP_SUB_PLUGIN_PRIVATE_PREFIX;
    }

    /**
     * Get custom terms
     *
     * @access public
     * @return array
     */
    public function get_custom_terms()
    {

        $terms = array();

        // Log entry
        foreach (RP_SUB_Log_Entry_Controller::get_instance()->define_taxonomies_with_terms() as $taxonomy => $taxonomy_data) {
            foreach ($taxonomy_data['grouped_terms'] as $term_group => $term_group_data) {
                foreach ($term_group_data['terms'] as $term => $term_data) {

                    // Add to main array
                    $terms['rp_sub_log_entry_' . $taxonomy][$term] = array(
                        'title' => $term_data['label'],
                    );
                }
            }
        }

        return $terms;
    }

    /**
     * Execute custom update procedure
     *
     * @access public
     * @return string
     */
    public function execute_custom()
    {

    }

    /**
     * Get custom capabilities
     *
     * @access public
     * @return string
     */
    public function get_custom_capabilities()
    {

        return array(
            'core' => array(
                RP_SUB_ADMIN_CAPABILITY
            ),
        );
    }

    /**
     * Get capability types
     *
     * @access public
     * @return array
     */
    public function get_capability_types()
    {

        return array(
            array('rp_sub_subscription', 'rp_sub_subscriptions'),
            array('rp_sub_log_entry', 'rp_sub_log_entries'),
        );
    }

    /**
     * Get custom tables sql
     *
     * @access public
     * @param string $table_prefix
     * @param string $collate
     * @return string
     */
    public function get_custom_tables_sql($table_prefix, $collate)
    {

        return "";
    }

    /**
     * Migrate settings
     *
     * @access public
     * @param array $stored
     * @param string $to_settings_version
     * @return array
     */
    public static function migrate_settings($stored, $to_settings_version)
    {

        return $stored;
    }





}

RP_SUB_Data_Updater::get_instance();
