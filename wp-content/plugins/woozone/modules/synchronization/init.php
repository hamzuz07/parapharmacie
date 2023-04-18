<?php
/**
 * Init wwcAmazonSyncronize
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		0.1
 */
if (class_exists('wwcAmazonSyncronize') != true) {
	class wwcAmazonSyncronize
	{
		const VERSION = '1.0';

		public $the_plugin = null;
		public $genericHelper = null;
		public $amzHelper = null;
		public $ebayHelper = null;

		private $cfg = array();
		private static $rules_default = array('price', 'title', 'url', 'new_variations'); // rules available till you save sync settings first time!

		private $alias = '';
		private $db = null;
		
		private static $sql_chunk_limit = 2000;
		
		private static $log_email_to = '';
		private static $log_send_mail = false;
		private static $log_save_file = false;

		private $amz_settings;

		static protected $settings = array();
		static protected $sync_options = array();

		public $sync_cronjob_type = 'product_and_variations';
		public $sync_cronjob_prods_orderby = 'id';
		public $is_cron_product_page_views = false;

		private $sql_get_products = '';


		public function __construct($WooZone)
		{
			global $wpdb;

			$this->db = $wpdb;

			$this->the_plugin = $WooZone;

			$this->genericHelper = $this->the_plugin->get_ws_object( 'generic' );
			$this->amzHelper = $this->the_plugin->get_ws_object( 'amazon' );
			$this->ebayHelper = $this->the_plugin->get_ws_object( 'ebay' );

			$this->alias = $this->the_plugin->alias;
			
			$this->amz_settings = $this->the_plugin->settings();

			$this->init_sync_settings();
			$this->init_sync_options();

			$this->cfg['available_setup'] = self::$settings;

			$this->updateSyncRules();

			$this->sync_cronjob_type = isset(self::$sync_options['sync_cronjob_type'])
				? (string) self::$sync_options['sync_cronjob_type'] : 'product_and_variations';

			$this->sync_cronjob_prods_orderby = isset(self::$sync_options['sync_cronjob_prods_orderby'])
				? (string) self::$sync_options['sync_cronjob_prods_orderby'] : 'id';

			if ( in_array($this->sync_cronjob_prods_orderby, array('product_page_views', 'product_page_views_positive')) ) {
				$this->is_cron_product_page_views = $this->sync_cronjob_prods_orderby;
			}
		}

		// setup amazon object for making request
		public function setupAmazonHelper( $params=array(), $pms=array() ) {

			$pms = array_replace_recursive( array(
				'provider' => 'amazon',
			), $pms );
			extract( $pms );

			$provider_prefix = $provider;
			if ( 'amazon' == $provider ) {
				$provider_prefix = 'amz';
			}

			$provider_4helper = $provider_prefix.'Helper';

			//:: GET SETTINGS
			//$settings = $this->the_plugin->settings();
			//$settings = $this->amz_settings;

			//:: SETUP
			$params_new = array();
			foreach ( $params as $key => $val ) {
				if ( in_array($key, array(
					'AccessKeyID', 'SecretAccessKey', 'country', 'main_aff_id', //amazon
					'ebay_DEVID', 'ebay_AppID', 'ebay_CertID', 'ebay_country', 'ebay_main_aff_id', //ebay
					'overwrite_settings'
				)) ) {
					$params_new["$key"] = $val;
				}
			}

			$this->$provider_4helper = $this->the_plugin->get_ws_object_new( $provider, 'new_helper', array(
				'the_plugin' => $this->the_plugin,
				'params_new' => $params_new,
			));

			if ( is_object($this->$provider_4helper) ) {
			}
		}

		public function get_ws_object( $provider='amazon', $what='helper' ) {
			//return $this->the_plugin->get_ws_object( $provider, $what );
			$arr = array(
				'generic'     => array(
				  'helper'        => $this->genericHelper,
				  'ws'            => null,
				),
				'amazon'        => array(
					'helper'        => $this->amzHelper,
					'ws'            => is_object($this->amzHelper) ? $this->amzHelper->aaAmazonWS : null,
				),
				//'alibaba'		=> array(
				//	'helper'		=> $this->alibabaHelper,
				//	'ws'			=> is_object($this->alibabaHelper) ? $this->alibabaHelper->aaAlibabaWS : null,
				//),
				//'envato'		=> array(
				//	'helper'		=> $this->envatoHelper,
				//	'ws'			=> is_object($this->envatoHelper) ? $this->envatoHelper->aaEnvatoWS : null,
				//),
				'ebay'        => array(
					'helper'        => $this->ebayHelper,
					'ws'            => is_object($this->ebayHelper) ? $this->ebayHelper->aaWooZoneEbayWS : null,
				),
			);
			return $arr["$provider"]["$what"];
		}

		public function init_sync_settings() {
			$ss = get_option($this->alias . '_sync', array());
			$ss = maybe_unserialize($ss);
			$ss = $ss !== false ? $ss : array();
			$ss = array_merge(array(
				'sync_products_per_request'				=> 50, // Products to sync per each cron request
				'sync_hour_start'						=> '',
				'sync_recurrence'						=> 24,
				'sync_fields'							=> array(),
			), $ss);

			self::$settings = $ss;
			return self::$settings;
		}

		public function init_sync_options() {
			$ss = get_option($this->alias . '_sync_options', array());
			$ss = maybe_unserialize($ss);
			$ss = $ss !== false ? $ss : array();
			$ss = array_merge(array(
				'interface_max_products' => 'all',
			), $ss);

			self::$sync_options = $ss;
			return self::$sync_options;
		}

		// store into cfg array, no returns
		public function updateSyncRules() {
			// Products to sync per each cron request
			if ( !isset($this->cfg['available_setup']['sync_products_per_request'])
				|| empty($this->cfg['available_setup']['sync_products_per_request']) ) {
				$this->cfg['available_setup']['sync_products_per_request'] = 50;
			}

			$sync_rules = array_keys( $this->the_plugin->get_product_sync_rules() );
			foreach ($sync_rules as $rule) {

				$this->cfg['sync_rules']["$rule"] = ! isset($this->cfg['available_setup']['sync_fields'])
					&& in_array($rule, self::$rules_default) ? true : false;

				$this->cfg['sync_rules']["$rule"] = isset($this->cfg['available_setup']['sync_fields'])
					&& in_array($rule, $this->cfg['available_setup']['sync_fields']) ? true : $this->cfg['sync_rules']["$rule"];
			}
			return $this->cfg;
		}



		//====================================================
		//== SYNC CRONJOB - GET PRODUCTS

		public function get_products() {
			$this->updateSyncRules();

			$witherror = false; // true | false

			$max_selected = (int) $this->cfg['available_setup']['sync_products_per_request'];
			//$max_selected = 1000; //DEBUG

			$sync_witherror_tries = (int) get_option('WooZone_sync_witherror_tries', 0);
			if ( $sync_witherror_tries ) {
				$witherror = true;

				$max_selected = 30;
				//$max_selected = 5; //DEBUG
			}


			//:: in case some products were deleted in the meantime
			if ( false === $this->is_cron_product_page_views ) {
		
			$currentlist_lastprod = $this->currentlist_last_product( array() );
			update_option('WooZone_sync_currentlist_last_product', $currentlist_lastprod);

			$currentlist_nbprods = $this->currentlist_last_product( array('count' => true) );
			update_option('WooZone_sync_currentlist_nb_products', $currentlist_nbprods);

			}
			//var_dump('<pre>', $currentlist_lastprod, $currentlist_nbprods, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;


			//:: get the products
			$limit = $max_selected ? (int) $max_selected * 2 : 0;

			$products = $this->select_products( array(
				'witherror' => $witherror,
				'limit' => $limit,
			));
			//var_dump('<pre>', $products, '</pre>'); die('debug...');

			$products_filtered = $this->filter_products( array(
				'max_selected' => $max_selected,
				'products' => $products,
				'witherror' => $witherror,
			));
			//var_dump('<pre>', $products_filtered, '</pre>'); die('debug...');

			return array(
				'selected' 	=> $products,
				'filtered' 	=> $products_filtered,
				'witherror' => $witherror,
			);
		}

		public function select_products( $pms=array() ) {
			global $wpdb;

			$pms = array_replace_recursive(array(
				'witherror' => false, // true | false,
				'count' => false, // true | false

				'limit' => 'all', // 'all' | an integer value
				'get_fields' => '',
				'orderby' => '',

				'filterby_currentlist_last_product' => true, // true | false
				'filterby_last_updated_product' => true, // true | false
				'is_cron_product_page_views' => $this->is_cron_product_page_views,
			), $pms);
			extract( $pms );
			//var_dump('<pre>', $pms , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

			//:: some inits
			//$prod_key = '_amzASIN';
			$prod_key = array( '_amzASIN', '_amzaff_prodid' );
			$prod_key_ = "'_amzASIN', '_amzaff_prodid'";

			$first_updated_date = (int) get_option('WooZone_sync_first_updated_date', 0);
			//$recurrence = (int) ( $this->cfg['available_setup']['sync_recurrence'] * 3600 );

			$orderby_def = 'order by p.ID asc';

			$fix_samebulk = array();
			if ( false !== $is_cron_product_page_views ) {

				if ( $witherror ) {
					$fix_samebulk = get_option('WooZone_sync_witherror_fix_samebulk', array());
				}
				else {
					$fix_samebulk = get_option('WooZone_sync_fix_samebulk', array());
				}

				$filterby_last_updated_product = false;
			}


			//:: current list / cron cycle last product to be synced
			if ( $filterby_currentlist_last_product ) {
				$currentlist_last_product = get_option('WooZone_sync_currentlist_last_product', 0);
			}


			//:: last updated/synced product
			if ( $filterby_last_updated_product ) {
				if ( ! $witherror ) {
					$last_updated_product = (int) get_option('WooZone_sync_last_updated_product', 0);
					$last_selected_product = (int) get_option('WooZone_sync_last_selected_product', 0);
					$last_updated_product = max( $last_updated_product, $last_selected_product );
				}
				else {
					$last_updated_product = (int) get_option('WooZone_sync_witherror_last_updated_product', 0);
					$last_selected_product = (int) get_option('WooZone_sync_witherror_last_selected_product', 0);
					$last_updated_product = max( $last_updated_product, $last_selected_product );
				}
			}



			//:: sql clause : last updated/synced product
			$sql_clause_last_updated_product = '';
			if ( $filterby_last_updated_product ) {
				$sql_clause_last_updated_product = "and p.ID > $last_updated_product";
			}


			//:: sql clause : current list / cron cycle last product to be synced
			$sql_clause_currentlist_last_product = '';
			if ( $filterby_currentlist_last_product && $currentlist_last_product ) {
				$sql_clause_currentlist_last_product = "and p.ID <= $currentlist_last_product";
			}


			//:: sql clause : fix same bulk
			$sql_clause_fix_samebulk = '';
			if ( ! empty($fix_samebulk) ) {
				$sql_clause_fix_samebulk = "and p.ID not in (" . implode(',', $fix_samebulk) . ")";
			}


			//:: sql clause : posttype
			// cannot sync ebay variation childs (only variation parent products can be synced)
			//$sql_clause_posttype = "and p.post_type in ('product', 'product_variation')";
			$sql_clause_posttype = "and ( p.post_type = 'product' or ( p.post_type = 'product_variation' and pm.meta_value not regexp '^eby-' ) )";
			if ( 'product_and_variations' == $this->sync_cronjob_type ) {
				$sql_clause_posttype = "and p.post_type = 'product'";
			}


			//:: sql clause : witherror
			$sql_clause_witherror = '';
			$sql_clause_witherror_where = '';

			if ( $witherror ) {
				$sql_clause_witherror = "
					left join
						$wpdb->postmeta as pm2 on p.ID = pm2.post_id and pm2.meta_key = '_amzaff_sync_last_status'
				";
				$sql_clause_witherror_where = "and ( pm2.meta_value in ('invalid', 'throttled') )";
			}


			//:: filterby_provider
			$is_sync_allowed_amazon = true;
			$is_providers_prefix = array('zzz' => 'zzz');
			$providers = $this->the_plugin->providers_is_enabled();
			foreach ( $providers as $provider ) {

				$provider_prefix = $this->the_plugin->get_ws_prefixes( $provider );
				$provider_status = $this->the_plugin->provider_action_controller( 'is_process_allowed', $provider, array() );

				if ( 'invalid' == $provider_status['status'] ) {

					if ( 'amazon' == $provider ) {
						$is_sync_allowed_amazon = false;
					}
				}
				else {
					$is_providers_prefix["$provider_prefix"] = $provider_prefix;
				}
			}

			if ( $is_sync_allowed_amazon && isset($is_providers_prefix['amz']) ) {
				unset($is_providers_prefix['amz']);
			}

			$is_providers_prefix = implode('|', $is_providers_prefix);

			$sql_filterbyprov_clause = "and ! isnull(pm.meta_value)";
			if ( ! $is_sync_allowed_amazon ) {
				$sql_filterbyprov_clause = "and ( pm.meta_key = '_amzaff_prodid' AND pm.meta_value regexp '^($is_providers_prefix)-' )";
			}
			else {
				$sql_filterbyprov_clause = "and ( ( pm.meta_key = '_amzaff_prodid' AND pm.meta_value regexp '^($is_providers_prefix)-' ) OR ( pm.meta_key = '_amzASIN' AND ! isnull(pm.meta_value) ) )";
			}


			//:: sql clause : get products (orderby) product page views
			$sql_clause_pageviews = '';
			$sql_clause_pageviews_where = '';

			if ( false !== $is_cron_product_page_views ) {

				$sql_clause_pageviews = "
					left join
						$wpdb->postmeta as pm3 on p.ID = pm3.post_id and pm3.meta_key = '_amzaff_sync_current_cycle'
					left join
						$wpdb->postmeta as pm4 on p.ID = pm4.post_id and pm4.meta_key = '_amzaff_sync_product_views'
				";

				$sql_clause_pageviews_where = "
					and ( isnull(pm3.meta_value) or pm3.meta_value != '$first_updated_date' )
				";

				if ( $witherror ) {
					$sql_clause_pageviews_where = "
						and pm3.meta_value = '$first_updated_date'
					";
				}

				if ( 'product_page_views_positive' == $is_cron_product_page_views ) {
					$sql_clause_pageviews_where .= "
						and ( ! isnull(pm4.meta_value) and pm4.meta_value > 0 )
					";
				}

				// order by page vies DESC, those null are last
				$orderby_def = "order by pm4.meta_value DESC, p.ID ASC";
			}


			//:: orderby, limit, what fields to get
			$qlimit = 'all' === $limit ? '' : 'limit ' . (int) $limit;

			if ( empty($get_fields) ) {
				$get_fields = 'p.ID, pm.meta_value as asin, p.post_parent, p.post_title, p.post_content, p.post_excerpt';
			}

			if ( empty($orderby) ) {
				$orderby = $orderby_def;
			}


			//:: when we do "count"
			if ( $count ) {
				$get_fields = 'count(p.ID)';
				$orderby = '';
				//$qlimit = '';
			}


			//DEBUG
			//$qlimit = 'limit 1';
			//$sql_clause_currentlist_last_product = '';
			//$sql_clause_last_updated_product = "and p.ID = '329733'";
			//$sql_filterbyprov_clause = "and pm.meta_key = '_amzaff_prodid' AND pm.meta_value regexp '^eby-'"; //EBAY
			//$sql_filterbyprov_clause = "and pm.meta_key = '_amzASIN' AND ! isnull(pm.meta_value)"; //AMAZON


			//:: build & execute the query
			$sql = trim("
				select
					$get_fields
				from
					$wpdb->posts as p
				left join
					$wpdb->postmeta as pm on p.ID = pm.post_id and pm.meta_key in ($prod_key_)
				$sql_clause_pageviews
				$sql_clause_witherror
				where 1=1
					$sql_clause_last_updated_product
					$sql_clause_currentlist_last_product
					$sql_clause_fix_samebulk
					$sql_clause_posttype
					and p.post_status = 'publish'
					$sql_filterbyprov_clause
					$sql_clause_pageviews_where
					$sql_clause_witherror_where
				$orderby $qlimit;
			");
			//left join
			//	$wpdb->postmeta as pm2 on p.ID = pm2.post_id and pm2.meta_key = '_amzaff_sync_last_date'
			//and ( isnull(pm2.meta_value) or ( pm2.meta_value < UNIX_TIMESTAMP( DATE_SUB(NOW(), INTERVAL $recurrence SECOND ) ) ) )

			$this->sql_get_products = $sql;
			//var_dump('<pre>', $sql, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

			if ( $count ) {
				$res = $wpdb->get_var( $sql );
				//var_dump('<pre>', $sql, $res , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
				return $res;
			}
			else {
				$res = $wpdb->get_results( $sql, OBJECT_K );
				//var_dump('<pre>', $sql, $res , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
				if ( empty($res) ) return array();
			}
			return $res;
		}

		public function filter_products( $pms=array() ) {
			$pms = array_replace_recursive(array(
				'force_sync' => false,
				'products' => array(),
				'max_selected' => 'all',
				'witherror' => false, // true | false,
			), $pms);
			//$pms['force_sync'] = true; //DEBUG
			extract( $pms );

			if ( empty($products) ) return array();

			global $wpdb;


			$first_updated_date = (int) get_option('WooZone_sync_first_updated_date', 0);

			// products IDs
			$productsId = array_keys($products);
			//var_dump('<pre>', $productsId , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

			$__meta_toget = array('_amzaff_sync_last_date', '_amzaff_country');

			$prods2meta = array();
			foreach ( (array) $__meta_toget as $meta) {
				$prods2meta["$meta"] = array();

				foreach (array_chunk($productsId, self::$sql_chunk_limit, true) as $current) {

					$currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $current));

					$sql_getmeta = "SELECT pm.post_id, pm.meta_value FROM $wpdb->postmeta as pm WHERE 1=1 AND pm.meta_key = '$meta' AND pm.post_id IN ($currentP) ORDER BY pm.post_id ASC;";
					//var_dump('<pre>',$sql_getmeta ,'</pre>');
					$res_getmeta = $wpdb->get_results( $sql_getmeta, OBJECT_K );
					$prods2meta["$meta"] = $prods2meta["$meta"] + $res_getmeta; //array_replace($prods2meta["$meta"], $res_getmeta);
				}
			}
			//var_dump('<pre>', $prods2meta , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
	 
			$selectedProducts = array();
			$contor = 0; $added = 0;
			foreach ($products as $key => $val) {

				if ( ( $max_selected != 'all' ) && ($added >= (int) $max_selected) ) break 1;

				if ( ! $witherror ) {
					update_option('WooZone_sync_last_selected_product', $key);
					//$this->set_nb_parsed( 'WooZone_sync_currentlist_nb_parsed', 1, 'add' );
				}
				else {
					update_option('WooZone_sync_witherror_last_selected_product', $key);
					//$this->set_nb_parsed( 'WooZone_sync_witherror_nb_parsed', 1, 'add' );
				}

				// only to know that the sync process tried to sync these products
				update_post_meta( $key, "_amzaff_sync_current_cycle", $first_updated_date );

				//:: do we need to synced it?
				$sync_last_date = isset($prods2meta['_amzaff_sync_last_date']["$key"])
						? $prods2meta['_amzaff_sync_last_date']["$key"]->meta_value : '';
				$is_sync_needed = $this->the_plugin->syncproduct_is_sync_needed( array(
					'recurrence' => (int) ( $this->cfg['available_setup']['sync_recurrence'] * 3600 ),
					'product_id' => $key,
					'sync_last_date' => $sync_last_date,
				));

				if ( $is_sync_needed || $force_sync ) {

					$country = isset($prods2meta['_amzaff_country']["$key"])
						? $prods2meta['_amzaff_country']["$key"]->meta_value : false;

					$pasin = $val->asin;
					$pasin = $this->the_plugin->prodid_set($pasin, $this->the_plugin->prodid_get_provider_by_asin( $pasin ), 'add');

					$selectedProducts["$key"] = array(
						//'ID' 		=> $val->ID,
						'asin' 		=> $pasin,
						'country' 	=> $country,
						'post_parent' => $val->post_parent,
						'post_title' => $val->post_title,
						'post_content' => $val->post_content,
						'post_excerpt' => $val->post_excerpt,
						'sync_last_date' => $sync_last_date,
					);
					$added++;
				}
				$contor++;
			}
			//var_dump('<pre>', $selectedProducts , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

			if ( $contor ) {
				if ( ! $witherror ) {
					//$this->set_nb_parsed( 'WooZone_sync_currentlist_nb_parsed', $contor, 'add' );
				}
				else {
					//$this->set_nb_parsed( 'WooZone_sync_witherror_nb_parsed', $contor, 'add' );
				}
			}

			//var_dump('<pre>', $selectedProducts , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			return $selectedProducts;
		}

		public function currentlist_last_product( $pms=array() ) {
			global $wpdb;

			$pms = array_replace_recursive(array(
				'witherror' => false,
				'count' => false,

				'limit' => '1',
				'get_fields' => 'p.ID',
				'orderby' => 'order by p.ID desc',

				//'filterby_currentlist_last_product' => true,
				'filterby_last_updated_product' => false,
				'is_cron_product_page_views' => $this->is_cron_product_page_views,
			), $pms);
			//extract( $pms );

			$res = $this->select_products( $pms );
			//var_dump('<pre>', $res , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

			$ret = $res;
			if ( is_array($ret) ) {
				if ( ! empty($ret) ) {
					foreach ($ret as $key => $val) {
						$ret = (int) $val->ID;
						break;
					}
				}
				else {
					$ret = 0;
				}
			}
			return $ret;
		}

		// SYNC CRONJOBS
		public function cron_full_cycle( $pms, $return='die' ) {
			$ret = array('status' => 'failed');

			$amz_verify_msg = '';

			$providers = $this->the_plugin->providers_is_enabled();
			foreach ( $providers as $provider ) {

				$provider_status = $this->the_plugin->provider_action_controller( 'is_process_allowed', $provider, array() );
				if ( 'invalid' == $provider_status['status'] ) {
					$amz_verify_msg .= $provider_status['msg'];
				}
			}

			$time_format = 'Y-m-d H:i:s';
			$current_cron_status = $pms['status']; //'new'; //
			$current_time = time(); // GMT current time
			$first_updated_date = (int) get_option('WooZone_sync_first_updated_date', 0);
			$recurrence = (int) ( $this->cfg['available_setup']['sync_recurrence'] * 3600 );
			//var_dump('<pre>', $current_time, $first_updated_date, $recurrence, $current_time >= ( $first_updated_date + $recurrence ), '</pre>'); die('debug...'); 

			//$currentlist_nbprods = $this->currentlist_last_product( array('count' => true) );
			//var_dump('<pre>', $currentlist_nbprods , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

			// recurrence interval fulfilled
			if ( /*1 || */$current_time >= ( $first_updated_date + $recurrence ) ) {
				
				// assurance verification: reset in any case after more than 4 times the current setted recurrence interval
				$do_reset = $current_time >= ( $first_updated_date + $recurrence * 4 ) ? true : false;
				$current_cycle_done = isset($pms['verify'], $pms['verify']['sync_products'])
					&& $pms['verify']['sync_products'] == 'stop' ? true : false;
				
				// current cycle not yet completed and not yet reached assurance verification
				if ( !$current_cycle_done && !$do_reset ) {
					$ret = array_merge($ret, array(
						'status' => 'done',
						'msg' 	=> 'current sync cycle not finished yet.' . $amz_verify_msg,
					));
					return $ret;
				}
				
				// here we can save WooZone_sync_cycle_stats to log before reset them bellow...
				if ( self::$log_send_mail || self::$log_save_file ) {

					$logStat = $this->save_log();
					$ret = array_merge($ret, array(
						'logStat'        => $logStat,
					));
				}


				// last product synced (succesfull or not)
				update_option('WooZone_sync_last_updated_product', 0);
				update_option('WooZone_sync_last_selected_product', 0);

				// to know when this current sync cycle started
				update_option('WooZone_sync_first_updated_date', time());

				// first we reset last product from current list of products to be synced in this cycle
				update_option('WooZone_sync_currentlist_last_product', 0);

				// last product to be synced in the current list / current cycle
				$currentlist_lastprod = $this->currentlist_last_product( array() );
				update_option('WooZone_sync_currentlist_last_product', $currentlist_lastprod);

				// number of products to be synced in the current list
				$currentlist_nbprods = $this->currentlist_last_product( array('count' => true) );
				update_option('WooZone_sync_currentlist_nb_products', $currentlist_nbprods);

				// products moved to trash or marked as to be moved - in the current list
				//update_option('WooZone_sync_currentlist_nb_parsed', array());
				update_option('WooZone_sync_currentlist_prod_trashed', array());
				update_option('WooZone_sync_currentlist_prod_trash_tries', array());

				update_option('WooZone_sync_last_bulk_code', '');
				update_option('WooZone_sync_fix_samebulk', array());

				// invalid | throttled products list - for current cycle
				update_option('WooZone_sync_witherror_last_updated_product', 0);
				update_option('WooZone_sync_witherror_last_selected_product', 0);
				update_option('WooZone_sync_witherror_nb_products', 0);
				//update_option('WooZone_sync_witherror_nb_parsed', array());
				update_option('WooZone_sync_witherror_tries', 0);
				update_option('WooZone_sync_witherror_last_bulk_code', '');
				update_option('WooZone_sync_witherror_fix_samebulk', array());

				// to measure duration for current cycle
				$cycle_stats = get_option('WooZone_sync_cycle_stats', array());
				$cycle_stats = is_array($cycle_stats) ? $cycle_stats : array();
				$cycle_stats = array_merge($cycle_stats, array(
					'start_time'        => '',
					'end_time'          => '',
				));
				update_option('WooZone_sync_cycle_stats', $cycle_stats);

				$ret = array_merge($ret, array(
					'status'        => 'done',
					'msg' 			=> sprintf( 'new sync cycle started at %s', get_date_from_gmt(date('Y-m-d H:i:s', $current_time), $time_format) ) . $amz_verify_msg,
				));

				// depedency
				if ( isset($pms['depedency'], $pms['depedency']["$current_cron_status"])
					&& !empty($pms['depedency']["$current_cron_status"]) ) {
					$ret = array_merge($ret, array(
						'depedency' => $pms['depedency']["$current_cron_status"]
					));
				}
			}
			else {
				$ret = array_merge($ret, array(
					'status' => 'done',
					'msg' 	=> 'current sync cycle not finished yet or sync cycle reccurence interval not fulfilled yet so we can start a new sync cycle.' . $amz_verify_msg,
				));
			}
			return $ret;
		}

		public function cron_small_bulk( $pms, $return='die' ) {
			$ret = array('status' => 'failed');

			$amz_verify_msg = '';

			$providers = $this->the_plugin->providers_is_enabled();
			foreach ( $providers as $provider ) {

				$provider_status = $this->the_plugin->provider_action_controller( 'is_process_allowed', $provider, array() );
				if ( 'invalid' == $provider_status['status'] ) {
					$amz_verify_msg .= $provider_status['msg'];
				}
			}

			$current_cron_status = $pms['status']; //'new'; //

			$currentlist_last_product = (int) get_option('WooZone_sync_currentlist_last_product', 0);
			$products = $this->get_products();
			$first_from_current = array_keys($products['filtered']);
			$first_from_current = (int) current($first_from_current);
			//var_dump('<pre>', $currentlist_last_product, $first_from_current, $products, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

			$cycle_stats = get_option('WooZone_sync_cycle_stats', array());
			$cycle_stats = is_array($cycle_stats) ? $cycle_stats : array();
			if ( !isset($cycle_stats['start_time']) || empty($cycle_stats['start_time']) ) {
				$cycle_stats = array_merge($cycle_stats, array(
					'start_time'        => time(),
				));
				update_option('WooZone_sync_cycle_stats', $cycle_stats);
			}

			//::FIX /2010-march-12
			// we take a bulk (name it main bulk) of let's say 30 products (asins), but they can belong to different
			// countries (both amazon or ebay), so then we sync each of these sub-bulks containing the products from one country.
			// The Problem: in the final sub-bulk, the final product we sync can have an ID which is smaller than the max ID in
			// the main bulk, so we need this code to solve this.
			$current_bulk_prods = $this->_small_bulk_get_prods( $products['selected'] );
			$current_bulk_code = md5( serialize($current_bulk_prods) );
			$current_bulk_maxid = is_array($current_bulk_prods) ? max( array_keys($current_bulk_prods) ) : false;

			if ( $products['witherror'] ) {
				$prev_bulk_code = get_option('WooZone_sync_witherror_last_bulk_code', '');
			}
			else {
				$prev_bulk_code = get_option('WooZone_sync_last_bulk_code', '');
			}

			$fix_samebulk = ! empty($prev_bulk_code) && ( $prev_bulk_code == $current_bulk_code ) ? 1 : 0;

			// current bulk of products it\'s the same as the last one (SPECIAL CASE)
			if ( $fix_samebulk ) {

				if ( $products['witherror'] ) {
					$fix_samebulk_ids = get_option('WooZone_sync_witherror_fix_samebulk', array());
					$fix_samebulk_ids = array_unique( array_merge( $fix_samebulk_ids, array_keys($current_bulk_prods) ) );
					update_option('WooZone_sync_witherror_fix_samebulk', $fix_samebulk_ids);
				}
				else {
					$fix_samebulk_ids = get_option('WooZone_sync_fix_samebulk', array());
					$fix_samebulk_ids = array_unique( array_merge( $fix_samebulk_ids, array_keys($current_bulk_prods) ) );
					update_option('WooZone_sync_fix_samebulk', $fix_samebulk_ids);
				}

				$msg = 'current bulk of products it\'s the same as the last one' . $amz_verify_msg;
				$msg .= '<br /><br />';
				$msg .= htmlspecialchars( $this->sql_get_products, ENT_NOQUOTES, "UTF-8" );

				$ret = array_merge($ret, array(
					'status'            => 'done',
					'msg' 				=> $msg,
				));

				if ( $products['witherror'] ) {
					update_option('WooZone_sync_witherror_last_updated_product', $current_bulk_maxid);
					update_option('WooZone_sync_witherror_last_selected_product', $current_bulk_maxid);
				}
				else {
					update_option('WooZone_sync_last_updated_product', $current_bulk_maxid);
					update_option('WooZone_sync_last_selected_product', $current_bulk_maxid);
				}
			}
			// no (more) products found for current cycle
			// or ( current products cycle last product ID is less then first product from current selected products list )
			// || ( $currentlist_last_product < $first_from_current )
			else if ( empty($products['selected']) ) {
				$msg = 'no (more) products found for current cycle';
				//if ( empty($products['selected']) ) {
				//	$msg = 'no (more) products found for current cycle';
				//}
				//else {
				//	$msg = sprintf(
				//		'current products cycle last product ID %s is less then first product from current selected products list with ID %s - probably new products were added after the current sync cycle started',
				//		$currentlist_last_product,
				//		$first_from_current
				//	);
				//}

				//:: the ones with error (ex. throttled)
				// we still have to do those witherror - start new cycles when none were found on the current one
				$sync_witherror_tries = (int) get_option('WooZone_sync_witherror_tries', 0);
				update_option('WooZone_sync_witherror_tries', ++$sync_witherror_tries);

				$max_retries = $this->the_plugin->ss['max_cron_sync_retries_onerror'];

				if ( $sync_witherror_tries <= $max_retries ) {

					update_option('WooZone_sync_witherror_last_updated_product', 0);
					update_option('WooZone_sync_witherror_last_selected_product', 0);

					if ( $sync_witherror_tries ) {
						$sync_witherror_nb_all_products = $this->currentlist_last_product( array(
							'witherror' => true,
							'count' => true,
						));
						update_option('WooZone_sync_witherror_nb_products', $sync_witherror_nb_all_products);
						//update_option('WooZone_sync_witherror_nb_parsed', array());

						update_option('WooZone_sync_witherror_last_bulk_code', '');
						update_option('WooZone_sync_witherror_fix_samebulk', array());
					}

					$ret = array_merge($ret, array(
						'status'            => 'done',
						'msg' 				=> "We\'ll begin the process to re-sync amazon error (ex. throttled) ones shortly - step $sync_witherror_tries of $max_retries" . $amz_verify_msg,
					));
					return $ret;
				}

				$ret = array_merge($ret, array(
					'status'        => 'stop',
					'msg' 			=> $msg . $amz_verify_msg,
				));

				$cycle_stats = array_merge($cycle_stats, array(
					'end_time'          => time(),
				));
				update_option('WooZone_sync_cycle_stats', $cycle_stats);

				// depedency
				if ( isset($pms['depedency'], $pms['depedency']["$current_cron_status"])
					&& !empty($pms['depedency']["$current_cron_status"]) ) {
					$ret = array_merge($ret, array(
						'depedency' => $pms['depedency']["$current_cron_status"]
					));
				}
			}
			// no products to be synced in this bulk
			else if ( empty($products['filtered']) ) {
				$ret = array_merge($ret, array(
					'status'            => 'done',
					'msg' 				=> 'no products to be synced in this bulk' . $amz_verify_msg,
				));
			}
			// try to SYNC products
			else {
				$products_status = $this->syncprod_multiple_bycountry( $products['filtered'], array(
					//'use_cache' => true,
					'verify_sync_date' => false,
					'verify_sync_date_vars' => false,
					'recurrence' => '',
					'from_cron' => true,
					'witherror' => $products['witherror'],
					'sync_vers_type' => $this->sync_cronjob_type,
				) );

				if ( $products['witherror'] ) {
					update_option('WooZone_sync_witherror_last_bulk_code', $current_bulk_code);
				}
				else {
					update_option('WooZone_sync_last_bulk_code', $current_bulk_code);
				}
				
				$ret = array_merge($ret, array(
					'status' 	=> 'done',
					'msg' 		=> $products_status['msg'] . $amz_verify_msg,
				));
			}
			return $ret;
		}

		public function cron_sync_gc( $pms, $return='die' ) {
			global $wpdb;

			$ret = array('status' => 'failed');

			$cache_type = 'asin';
			$table = $wpdb->prefix . 'amz_amazon_cache';
			$how_often = 'INTERVAL 1 DAY';

			$msg = array();

			// delete older amazon requests from cache
			$sql = "delete from $table where 1=1 and cache_type = %s and ( response_date <= DATE_SUB( NOW(), $how_often ) );";
			$sql = $wpdb->prepare( $sql, $cache_type );
			$res = $wpdb->query( $sql );
			//var_dump('<pre>', $sql, '</pre>'); // echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			$msg[] = sprintf( "deleted %s rows from amazon requests cache table.", (int) $res );

			$msg = implode('<br />', $msg);

			$ret = array_merge($ret, array(
				'status' 	=> 'done',
				'msg' 		=> $msg,
			));
			return $ret;
		}

		private function _small_bulk_get_prods( $products=array() ) {

			$amz_products = array();

			foreach ($products as $key => $value) {
				//$country = isset($value['country']) ? (string) $value['country'] : '--';
				//$asin = isset($value['asin']) ? (string) $value['asin'] : '';

				$pasin = $value->asin;
				$pasin = $this->the_plugin->prodid_set($pasin, $this->the_plugin->prodid_get_provider_by_asin( $pasin ), 'add');

				if ( ! empty($pasin) ) {
					$amz_products[$key] = $pasin;
				}
			}
			return $amz_products;
		}



		//====================================================
		//== UTILS
		/**
		 * UTILS
		 */
		private function trash_post( $post_id, $asin, $do_trash=-1 ) {
			if ( empty($post_id) ) return true;
			global $wpdb;

			$allowed_tries = (int) $do_trash;
			$do_trash = ( -1 ==  $allowed_tries ? false : true );

			//:: don't trash product (amazon config setting is no)
			if ( !$do_trash ) {
				return false;
			}

			update_post_meta( $post_id, "_amzaff_sync_trash_tries",
				(int) get_post_meta($post_id, "_amzaff_sync_trash_tries", true) + 1
			);
			$sync_trash_tries = (int) get_post_meta($post_id, "_amzaff_sync_trash_tries", true);

			//:: still some tries till trash product
			if ( $sync_trash_tries < $allowed_tries ) {
				//:: asins not found in db!
				$prod_trashed = array( $post_id => array() );
				$prod_trashed[ $post_id ]['asin'] = $asin;
				$this->asins_prods_marked_list( 'WooZone_sync_currentlist_prod_trash_tries', $prod_trashed, 'add' );

				return false;
			}
			
			//:: TRASH PRODUCT
			// delete the product if no longer available on Amazon
			if ( $this->the_plugin->products_force_delete ) {
				wp_delete_post( $post_id, true );
			}
			else {
				wp_trash_post( $post_id );
			}

			delete_transient( "wc_product_children_$post_id" );
			delete_transient( "wc_var_prices_$post_id" );

			// get product variations (only childs, no parents)
			$sql_childs = "SELECT p.ID, p.post_parent FROM $wpdb->posts as p WHERE 1=1 AND p.ID = '$post_id' AND p.post_status = 'publish' AND p.post_parent > 0 AND p.post_type = 'product_variation' ORDER BY p.ID ASC;";
			$res_childs = $wpdb->get_results( $sql_childs, OBJECT_K );
			//var_dump('<pre>',$res_childs,'</pre>');  

			// delete all variations of this product also
			$cc = 0;		
			foreach ( (array) $res_childs as $child_id => $child ) {

				if ( $this->the_plugin->products_force_delete ) {
					wp_delete_post( $child_id, true );
				}
				else {
					wp_trash_post( $child_id );	
				}

				$child_parent = $child->post_parent;
				delete_transient( "wc_product_children_$child_parent" );
				delete_transient( "wc_var_prices_$child_parent" );

				$cc++;
			}

			//:: asins not found in db!
			$prod_trashed = array( $post_id => array() );
			$prod_trashed[ $post_id ]['asin'] = $asin;
			if ( $cc ) {
				$prod_trashed[ $post_id ]['nbvars'] = $cc;
			}
			$this->asins_prods_marked_list( 'WooZone_sync_currentlist_prod_trashed', $prod_trashed, 'add' );

			return true;
		}

		private function asins_prods_marked_list( $opt_name, $new_not_found=array(), $operation='add' ) {

			if ( ! is_array($new_not_found) || empty($new_not_found) ) {
				return false;
			}

			$__sync_prod_notfound = get_option( $opt_name, true );
			if ( ! is_array($__sync_prod_notfound) || empty($__sync_prod_notfound) ) {
				$__sync_prod_notfound = array();
			}

			foreach ($new_not_found as $key => $val) {
				if ( 'add' == $operation ) {
					if ( isset($__sync_prod_notfound["$key"]) ) continue 1;
					$__sync_prod_notfound["$key"] = $val;
				}
				else {
					if ( isset($__sync_prod_notfound["$key"]) ) {
						unset( $__sync_prod_notfound["$key"] );
					}
				}
			}

			update_option( $opt_name, $__sync_prod_notfound );
		}

		// product data is valid
		public function is_valid_product_data( $product=array(), $from='' ) {
			if ( empty($product) || !is_array($product) ) return false;
			
			$rules = isset($product['ASIN']) && !empty($product['ASIN']);
			$rules = $rules && 1;
			return $rules ? true : false;
		}

		public function set_last_updated_product( $last_one, $witherror ) {
			if ( is_array($last_one) && ! empty($last_one) ) {
				$nb = count( array_unique( $last_one ) );

				$last_one = array_keys($last_one);
				$last_one = end($last_one);
			}
			else {
				$nb = 1;
			}

			if ( ! $witherror ) {
				update_option('WooZone_sync_last_updated_product', $last_one);
				update_option('WooZone_sync_last_selected_product', $last_one);

				//$this->set_nb_parsed( 'WooZone_sync_currentlist_nb_parsed', $nb, 'sub' );
			}
			else {
				update_option('WooZone_sync_witherror_last_updated_product', $last_one);
				update_option('WooZone_sync_witherror_last_selected_product', $last_one);

				//$this->set_nb_parsed( 'WooZone_sync_witherror_nb_parsed', $nb, 'sub' );
			}
			return true;
		}

		public function set_nb_parsed( $opt_name, $toadd, $op=false ) {
			$opt_value = get_option( $opt_name, array() );
			$nb = isset($opt_value['nb']) ? (int) $opt_value['nb'] : 0;
			$added = isset($opt_value['added']) ? (int) $opt_value['added'] : 0;

			$total = $nb + (int) $toadd;
			if ( 'add' == $op ) {
				$added = $added + $toadd;
			}
			else if ( $added && ( 'sub' == $op ) ) {
				$total = $total - $added;
				$added = 0; // reset it so we don't substract it again next time
			}
			$total = (int) $total;

			update_option( $opt_name, array(
				'nb' => $total,
				'added' => $added,
			));
		}



		//====================================================
		//== LOGS
		/**
		 * LOGS
		 */
		private function save_log() {
			global $wpdb;
			
			$ret = array();
			
			$opt_sync = $this->alias . '_sync';
			$sql = "select o.option_name, o.option_value from $wpdb->options as o where 1=1 and o.option_name regexp '^$opt_sync' order by o.option_name asc;";
			$res = $wpdb->get_results($sql, OBJECT_K);
			
			$msg = array();
			foreach ( (array) $res as $opt_name => $opt ) {
				if ( in_array($opt_name, array('WooZone_sync_prod_notfound')) ) {
					continue 1;
				}
				$opt_val = maybe_unserialize($opt->option_value);
				$msg["$opt_name"] = $opt_val;
			}
			
			if ( self::$log_send_mail ) {
				$sendMailStat = $this->log_send_mail( $msg );
			}
			if ( self::$log_save_file ) {
				$saveFileStat = $this->log_save_file( $msg );
			}
			
			return array_merge($ret, array(
				'msg'               => $msg,
				'sendMailStat'      => $sendMailStat,
				'saveFileStat'      => $saveFileStat,
			));
		}
		private function log_send_mail( $msg=array() ) {
			// send email
			add_filter('wp_mail_content_type', array($this->the_plugin, 'set_content_type'));
			//add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
			
			$current_time = time();
			$current_time = $this->the_plugin->last_update_date(true);
			$email_to = self::$log_email_to;
			$subject = sprintf(__('Products Sync - full cycle (%s)', $this->the_plugin->localizationName), $current_time);
			
			$html = $this->log_build_msg( $msg, array('sep' => '<br />', 'current_time' => $current_time) );
			//$html = '<p>The <em>HTML</em> message</p>';
			
			$sendStat = wp_mail( $email_to, $subject, $html );

			// reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
			remove_filter('wp_mail_content_type', array($this->the_plugin, 'set_content_type'));

			return array(
				'mailStat'          => $sendStat,
				'mailFields'        => compact( 'email_to', 'subject' ), //compact( 'email_to', 'subject', 'html' ),
			);
		}
		private function log_save_file( $msg=array() ) {
			$logFolder = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'log/';

			$html = $this->log_build_msg( $msg, array('sep' => PHP_EOL) );
	 
			$saveStat = file_put_contents( $logFolder . 'log-sync.txt', $html, FILE_APPEND );
			return array(
				'saveStat'          => $saveStat,
				'saveFields'        => '', //compact( 'html' ),
			);
		}
		private function log_build_msg( $msg=array(), $pms ) {
			extract($pms);

			if ( empty($current_time) ) {
				$current_time = time();
				$current_time = $this->the_plugin->last_update_date(true);
			}
			
			$subject = sprintf(__('Products Sync - full cycle (%s)', $this->the_plugin->localizationName), $current_time);

			$html = array();
			$html[] = '###########################################################';
			$html[] = '## ' . $subject . $sep;
			ob_start();
			
			var_dump('<pre>',$msg,'</pre>'); 
			
			$html[] = ob_get_contents();
			ob_end_clean();
			
			$html[] = $sep.$sep;
			
			$html = implode($sep, $html);
			return $html;
		}



		//====================================================
		//== NEW SYNC - march 2018
		/**
		 * NEW SYNC - march 2018
		 */

		// $status = valid | invalid | throttled | notfound
		// _amzaff_sync_hits = keep the total number of successfull synced for a product
		// _amzaff_sync_hits_prev = keep the number of synced for a product, but from the last report for report module
		public function syncprod_update_product_meta( $status, $pms=array() ) {
			$pms = array_replace_recursive(array(
				'DEBUG' 				=> false,
				'from_cron' 			=> false,
				'parent_id' 			=> false,
				'product_id' 			=> 0,
				'asin' 					=> '',

				// array with post_title, post_content, post_excerpt or get_post( POSTID, ARRAY_A )
				'current_post' 			=> false,

				'first_updated_date' 	=> '',
				'sync_msg'				=> '',

				// $retProd must be formated through method 'build_product_data' from amz.helper.class.php
				'retProd' 				=> array(),

				// array with post_title, post_content, post_excerpt or get_post( POSTID, ARRAY_A )
				'parent_post' 			=> false,

				// the return of method 'product_find_new_variations'
				'product_vars' 			=> array(),
			), $pms);
			extract( $pms );
			//var_dump('<pre>', $status, $pms ,'</pre>'); return;

			if ( ! empty($asin) ) {
				$asin = $this->the_plugin->prodid_set($asin, $this->the_plugin->prodid_get_provider_by_asin( $asin ), 'add');
			}
			$provider = $this->the_plugin->prodid_get_provider_by_asin( $asin );

			$is_variable = ! empty($product_vars) && is_array($product_vars)
				&& isset($product_vars['product_type']) && 'variable' == $product_vars['product_type'] ? true : false;
			$is_variation_child = (int) $parent_id > 0 ? true : false;

			$delete_unavailable_products = (int) $this->the_plugin->sync_tries_till_trash;
			if ( isset($this->amz_settings['fix_issue_sync'], $this->amz_settings['fix_issue_sync']['trash_tries']) ) {
				$delete_unavailable_products = (int) $this->amz_settings['fix_issue_sync']['trash_tries'];
			}

			$sync_msg_savedb = array(
				'status' 	=> $status,
				'msg' 		=> $sync_msg,
				'rules' 	=> array(),
				'updated' 	=> array(),
			);

			//:: PRODUCT WAS FOUND ON AMAZON => LET'S TRY TO SYNCED IT
			if ( 'valid' == $status ) {

				// try to synced it
				$updateStat = $this->the_plugin->updateWooProduct( $retProd, array(
					'provider' 	=> $provider,
					'rules' 	=> $this->cfg['sync_rules'],
					'post_id' 	=> $product_id,
					'post_asin' => $asin,
					'current_post' => $current_post,
					'parent_id' => $parent_id,
					'parent_post' => $parent_post,
				));

				$updateStat['msg'] = $sync_msg_savedb['msg'] . '<br />' . $updateStat['msg'];
				$sync_msg_savedb = array_replace_recursive( $sync_msg_savedb, $updateStat );

				// notfound = found on amazon, but probably offerlistingid don't exist
				// notfound | updated | notupdated
				$status = $updateStat['status'];
			}


			//:: IS VARIATION CHILD
			if ( $is_variation_child ) {

				if ( in_array($status, array('notfound', 'updated')) ) {

					$sync_msg_db_parent = get_post_meta( $parent_id, '_amzaff_sync_last_status_msg', true );
					$sync_msg_db_parent = is_array($sync_msg_db_parent) && isset($sync_msg_db_parent['status'])
						? $sync_msg_db_parent : array( 'msg' => $sync_msg_db_parent );

					// temporary (till parent is synced) mark the parent that something changed on this variation child
					if ( ! isset($sync_msg_db_parent["_variations_$status"]) ) {
						$sync_msg_db_parent["_variations_$status"] = array(
							'code' => '',
							'status' => 'yes',
							'vars' => array(),
						);
					}

					$sync_msg_db_parent["_variations_$status"]['vars']["$product_id"] = $asin;

					update_post_meta( $parent_id, "_amzaff_sync_last_status_msg", $sync_msg_db_parent );
				}
			}
			//:: IS VARIABLE PARENT PRODUCT
			else if ( $is_variable ) {

				$sync_msg_db_parent = get_post_meta( $product_id, '_amzaff_sync_last_status_msg', true );
				$sync_msg_db_parent = is_array($sync_msg_db_parent) && isset($sync_msg_db_parent['status'])
					? $sync_msg_db_parent : array( 'msg' => $sync_msg_db_parent );

				// some variations childs were changed => parent is considered update too
				foreach ( array('notfound', 'updated') as $val ) {

					if ( isset($sync_msg_db_parent["_variations_$val"])
						&& ! empty($sync_msg_db_parent["_variations_$val"])
					) {
						$sync_msg_savedb['rules']["_variations_$val"] = $sync_msg_db_parent["_variations_$val"];

						if ( ! in_array("_variations_$val", $sync_msg_savedb['updated']) ) {
							$sync_msg_savedb['updated'][] = "_variations_$val";
						}

						$sync_msg_savedb['status'] = 'updated';
						if ( 'notupdated' == $status ) {
							$status = 'updated';
						}
					}
				}
				// end foreach
			}


			if ( $DEBUG ) {
				unset( $pms['retProd'], $pms['product_vars'] );
				var_dump('<pre>', "product id: $product_id | asin: $asin /syncprod_update_product_meta", '</pre>');
				var_dump('<pre>', 'pms', $status, $pms, '</pre>');
				var_dump('<pre>', 'sync_msg_savedb', $sync_msg_savedb, '</pre>');
				return $sync_msg_savedb;
			}


			// only to know that the sync process tried to sync these products
			//if ( $from_cron ) {
			//	update_post_meta( $product_id, "_amzaff_sync_current_cycle", $first_updated_date );
			//}

			update_post_meta( $product_id, "_amzaff_sync_last_status", $status );
			update_post_meta( $product_id, "_amzaff_sync_last_status_msg", $sync_msg_savedb );


			//:: UPDATE THE POST METAS
			// throttled | invalid = some other error than "product not found"
			if ( in_array($status, array('invalid', 'throttled')) ) {

				// don't update other metas ( most important _amzaff_sync_last_date ), because we don't consider this to be a real sync (either error or success), it's just to identify the product as parsed by sync process
			}
			// product not found - really not found on amazon
			else if ( 'notfound' == $status ) {

				delete_post_meta( $product_id, "_amzaff_sync_product_views" );

				if ( empty($sync_msg_savedb['msg']) ) {
					$sync_msg_savedb['msg'] = sprintf( 'asin %s was not found on %s', $asin, $provider );
				}

				update_post_meta( $product_id, "_amzaff_sync_last_date", $this->the_plugin->last_update_date() );
				
				update_post_meta( $product_id, "_amzaff_sync_hits_prev",
					(int) get_post_meta($product_id, "_amzaff_sync_hits_prev", true) + 1
				);

				$this->trash_post( $product_id, $asin, $delete_unavailable_products );
			}
			// product was found on amazon and was synced, but it's info was: updated | notupdated
			//else if ( in_array($status, array('updated', 'notupdated')) ) {
			//else if ( 'notfound' != $status ) {
			else {

				delete_post_meta( $product_id, "_amzaff_sync_product_views" );

				update_post_meta( $product_id, "_amzaff_sync_last_date", $this->the_plugin->last_update_date() );
				
				update_post_meta( $product_id, "_amzaff_sync_hits_prev",
					(int) get_post_meta($product_id, "_amzaff_sync_hits_prev", true) + 1
				);

	 			// successfull sync
				update_post_meta( $product_id, "_amzaff_sync_hits",
					(int) get_post_meta($product_id, "_amzaff_sync_hits", true) + 1
				);

				// reset sync trash marker
				update_post_meta( $product_id, "_amzaff_sync_trash_tries", 0 );

				// remove from trash tries
				$prod_trashed = array( $product_id => array() );
				$prod_trashed[ $product_id ]['asin'] = $asin;
				$this->asins_prods_marked_list( 'WooZone_sync_currentlist_prod_trash_tries', $prod_trashed, 'remove' );
			}

			return $sync_msg_savedb;
		}

		// update the products selected cron_small_bulk
		// $products = array of pairs ( product_id => array( asin, country... ) )
		public function syncprod_multiple_bycountry( $products=array(), $pms=array() ) {
			$pms = array_replace_recursive(array(
				'DEBUG' 				=> false,

				// true = cache amazon response
				'use_cache' 			=> true,

				// do we need to verify last sync date for each product from $products (even if it's just a variation child)
				// we sync the product only if the condition (based on last sync date and recurrence) is met
				'verify_sync_date' 		=> false,

				// do we need to verify last sync date for all variations of each product from $products (if it's a variable product)
				// we sync the product only if the condition (based on last sync date and recurrence) is met
				'verify_sync_date_vars' => false,

				// only if you want to use some custom recurrence
				'recurrence' 			=> '',

				// true = call is made by cron
				'from_cron' 			=> false,

				// we're at step when getting those with amazon eror: throttled or some other error
				'witherror' 			=> false,

				'chunk_size' 			=> 10,

	 			// default | product_and_variations
				'sync_vers_type' 		=> 'product_and_variations',
			), $pms);
			extract( $pms );

			//:: some inits
			$this->updateSyncRules();

			$ss = self::$sync_options;
			$sync_choose_country = isset($ss['sync_choose_country']) ? $ss['sync_choose_country'] : 'import_country';

			$updStats = array();

			//:: validation
			if ( empty($products) ) {
				$ret = array(
					'status' => 'invalid',
					'msg' => 'no products selected - maybe all products are already synced!',
				);
				return $ret;
			}

			//:: group products by provider and country
			$amz_products = array();
			foreach ($products as $key => $value) {
				$country = isset($value['country']) ? (string) $value['country'] : '--';
				$asin = isset($value['asin']) ? (string) $value['asin'] : '';

				if ( empty($asin) ) {
					continue 1;
				}

				$provider = $this->the_plugin->prodid_get_provider_by_asin( $asin );

				if ( ! isset($amz_products["$provider"]) ) {
					$amz_products["$provider"] = array();
				}
				if ( ! isset($amz_products["$provider"]["$country"]) ) {
					$amz_products["$provider"]["$country"] = array();
				}
				$amz_products["$provider"]["$country"]["$key"] = $asin;
			}

			//:: loop through products which are grouped by country
			// loop 1
			foreach ($amz_products as $group_provider => $group_countries) {

				// loop 2
				foreach ($group_countries as $group_country => $group_prods) {

					$group_country_ = '--' !== $group_country ? $group_country : '';

					$syncProdPms = array(
						'provider' => $group_provider,
						'from_cron' => $from_cron,
						'witherror' => $witherror,
						'prods_info' => $products,
					);

					$country = '';
					if ( 'import_country' == $sync_choose_country ) {
						if ( ! empty($group_country_) && is_string($group_country_) ) {
							$country = (string) $group_country_;
						}
					}
					else {
						$prefix = 'amazon' != $group_provider ? $group_provider.'_' : '';
						$country = $this->the_plugin->amz_settings[$prefix.'country'];
					}

					if ( 'product_and_variations' == $sync_vers_type ) {
						$syncProdPms = array_replace_recursive( $syncProdPms, array(
							'DEBUG' => $DEBUG,
							'use_cache' => $use_cache,
							'verify_sync_date' => $verify_sync_date,
							'verify_sync_date_vars' => $verify_sync_date_vars,
							'recurrence' => $recurrence,
							'chunk_size' => $chunk_size,
						));
						$syncStat = $this->syncprod_multiple( $group_prods, $country, $syncProdPms );
						$updStats[] = $syncStat['msg'];
					}
					else {
						// sync chunks of products till we finish all in the current bulk
						foreach (array_chunk($group_prods, $chunk_size, true) as $chunk_products) {
							$syncStat = $this->syncprod_multiple_oldvers( $chunk_products, $country, $syncProdPms );
							$updStats[] = $syncStat['msg'];
						}
					}
				}
				// end loop 2
			}
			// end loop 1
		
			$msg = implode( '<br /><br />------------------------------------------<br /><br />', $updStats );

			$ret = array(
				'status' => 'valid',
				'msg' => $msg,
			);
			return $ret;
		}

		//
		// OLD version
		//
		// $asins = array of pairs ( product_id => asin )
		public function syncprod_multiple_oldvers( $asins=array(), $country='', $pms=array() ) {
			$pms = array_replace_recursive(array(
				'provider' => 'amazon',

				// true = call is made by cron
				'from_cron' => false,

				// we're at step when getting those with amazon eror: throttled or some other error
				'witherror' => false,

				// extra info per each product
				// array of pairs ( product_id => array( asin, country... ) )
				'prods_info' => array(),
			), $pms);
			extract( $pms );

			//var_dump('<pre>', $asins, $country, $pms , '</pre>'); //echo __FILE__ . ":" . __LINE__;die . PHP_EOL;


			//---------------------
			//:: SOME INIT
			$sep = PHP_EOL;
			$asins_notfound = array();
			$asins_updated = array();
			$asins_details = array();

			$ret = array(
				'status' 			=> 'invalid',
				'msg' 				=> '',
				'asins' 			=> $asins,
				'asins_notfound' 	=> $asins_notfound,
				'asins_updated' 	=> $asins_updated,
				'asins_details' 	=> $asins_details,
			);
			
			$msg = array();


			//---------------------
			//:: NO ASINS
			//var_dump('<pre>', $asins, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			if ( empty($asins) ) {

				//:: RETURN
				$ret = array_merge($ret, array(
					'status' 	=> 'invalid',
					'msg' 		=> "{$provider} : No ASINs provided!",
				));
				return $ret;
				//:: END RETURN
			}


			//---------------------
			//:: SOME INIT
			$req_func = "{$provider}_request_make_lookup";

			$countries = $this->get_ws_object( $provider )->get_countries('country');
			$country_name = isset($countries["$country"]) ? $countries["$country"] : '';

			$this->updateSyncRules();

			$first_updated_date = (int) get_option('WooZone_sync_first_updated_date', 0);


			//---------------------
			//:: SETUP AMAZON & MAKE REQUEST
			// make request to amazon api
			$opMakeRequest = $this->the_plugin->$req_func( array_values($asins), $country );
			//$this->amzHelper = $this->the_plugin->get_ws_object( 'amazon' );
			//var_dump('<pre>', $opMakeRequest , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			$msg_ = $opMakeRequest['msg'];


			//---------------------
			//:: ERROR RESPONSE FROM AMAZON
	 		if ( ! in_array( $opMakeRequest['status'], array('valid', 'semivalid') ) ) {

				$asins_notfound = $asins;
				if ( count($asins_notfound) ) {
					
					foreach ($asins_notfound as $localID => $asin) {

						$prodinfo = isset($prods_info["$localID"]) ? $prods_info["$localID"] : array();

						// status: invalid | throttled | notfound
						$this->syncprod_update_product_meta( $opMakeRequest['status'], array(
							'from_cron' 			=> $from_cron,
							'parent_id' 			=> isset($prodinfo['post_parent']) ? $prodinfo['post_parent'] : false,
							'product_id' 			=> $localID,
							'asin' 					=> $asin,
							'current_post' 			=> $prodinfo,
							'first_updated_date' 	=> $first_updated_date,
							'sync_msg'				=> $msg_,
							'retProd' 				=> array(),
							'parent_post' 			=> false,
						));
					}
				}

				// update WooZone_remaining_at, take this as marker for next sync bulk
				if ( $from_cron ) {
					$this->set_last_updated_product( $asins, $witherror );
				}

				//:: RETURN
				$msg[] = $msg_;
				$msg[] = sprintf( 'Products (ID, ASIN) pairs on [country] %s requested :', $country_name );
				$msg[] = implode(', ', array_map(array($this->the_plugin, 'prepareForPairView'), $asins, array_keys($asins)));
				$msg = implode( '<br />', $msg );

				$ret = array_merge($ret, array(
					'status' => 'invalid',
					'msg' => $msg
				));
				return $ret;
			}


			//---------------------
			//:: SUCCESSFUL RESPONSE FROM AMAZON
			//:: products loop - the ones found on amazon
			$contor = 0;
			foreach ($opMakeRequest['amz_response'] as $thisProd) {

				$localID = 0;

				if ( ! $this->is_valid_product_data($thisProd) ) {
					continue 1;
				}

				$retProd = $this->get_ws_object( $provider )->build_product_data( $thisProd );
				//var_dump('<pre>', $retProd , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

				$pasin = $retProd['ASIN'];
				$pasin = $this->the_plugin->prodid_set($pasin, $provider, 'add');
				//var_dump('<pre>', $pasin , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

				foreach ($asins as $code_key => $code_value) {

					if( $pasin == $code_value){
						$localID = $code_key;
						$asins_updated[$localID] = $pasin;
						$asins_details[$localID]['Title'] = $retProd['Title'];
						break;
					}
				}

				if ( $localID <= 0 ) {
					continue 1;
				}

				// pause before the next product is updated
				if ( $contor ) {
					usleep( 10000 ); //0.01 second
				}

				$prodinfo = isset($prods_info["$localID"]) ? $prods_info["$localID"] : array();

				$this->syncprod_update_product_meta( 'valid', array(
					'from_cron' 			=> $from_cron,
					'parent_id' 			=> isset($prodinfo['post_parent']) ? $prodinfo['post_parent'] : false,
					'product_id' 			=> $localID,
					'asin' 					=> $pasin,
					'current_post' 			=> $prodinfo,
					'first_updated_date' 	=> $first_updated_date,
					'sync_msg'				=> sprintf( 'asin %s was successfully found on %s', $pasin, $provider ),
					'retProd' 				=> $retProd,
					'parent_post' 			=> false,
				));

				// update WooZone_remaining_at, take this as marker for next sync bulk
				if ( $from_cron ) {
					$this->set_last_updated_product( $localID, $witherror );
				}

				$contor++;
			}
			//:: end products loop

			//:: remove products not found on amazon
			$asins_notfound = array_diff($asins, $asins_updated);
			if ( count($asins_notfound) ) {

				foreach ($asins_notfound as $localID => $asin) {

					$prodinfo = isset($prods_info["$localID"]) ? $prods_info["$localID"] : array();

					$this->syncprod_update_product_meta( 'notfound', array(
						'from_cron' 			=> $from_cron,
						'parent_id' 			=> isset($prodinfo['post_parent']) ? $prodinfo['post_parent'] : false,
						'product_id' 			=> $localID,
						'asin' 					=> $asin,
						'current_post' 			=> $prodinfo,
						'first_updated_date' 	=> $first_updated_date,
						'sync_msg'				=> $msg_,
						'retProd' 				=> array(),
						'parent_post' 			=> false,
					));
				}

				// update WooZone_remaining_at, take this as marker for next sync bulk
				if ( $from_cron ) {
					$this->set_last_updated_product( $asins, $witherror );
				}
			}

			//var_dump('<pre>', $asins, $asins_updated, $asins_notfound , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;


			//---------------------
			//:: RETURN
			$ret = array_merge($ret, array(
				'status' 			=> count($asins) == count($asins_notfound) ? 'invalid' : 'valid',
				'asins' 			=> $asins,
				'asins_notfound' 	=> $asins_notfound,
				'asins_updated' 	=> $asins_updated,
				'asins_details' 	=> $asins_details,
			));

			if ( count($asins) != count($asins_updated) ) {
				$msg[] = 'Sync Status - not all products were found :';
				$msg[] = $msg_;
			}
			if ( ! empty($asins_updated) ) {
				$msg[] = sprintf( 'Products (ID, ASIN) pairs on country [%s] FOUND and synced successfully :', $country_name );
				$msg[] = implode(', ', array_map(array($this->the_plugin, 'prepareForPairView'), $asins_updated, array_keys($asins_updated)));
			}
			if ( ! empty($asins_notfound) ) {
				$msg[] = sprintf( 'Products (ID, ASIN) pairs on country [%s] NOT FOUND and marked for deletion :', $country_name );
				$msg[] = implode(', ', array_map(array($this->the_plugin, 'prepareForPairView'), $asins_notfound, array_keys($asins_notfound)));
			}
			$msg = implode( '<br />', $msg );

			$ret = array_merge($ret, array(
				'msg' => $msg
			));
			return $ret;
		}

		// try to sync a list of products (all from the same country)
		// $products = array of pairs ( product_id => asin )
		public function syncprod_multiple( $products, $country='', $pms=array() ) {
			global $wpdb;

			$pms = array_replace_recursive( array(
				'provider' 				=> 'amazon',

				'DEBUG' 				=> false,

				// true = cache amazon response
				'use_cache' 			=> true,

				// do we need to verify last sync date for each product from $products (even if it's just a variation child)
				// we sync the product only if the condition (based on last sync date and recurrence) is met
				'verify_sync_date' 		=> false,

				// do we need to verify last sync date for all variations of each product from $products (if it's a variable product)
				// we sync the product only if the condition (based on last sync date and recurrence) is met
				'verify_sync_date_vars' => false,

				// only if you want to use some custom recurrence
				'recurrence' 			=> '',

				// true = call is made by cron
				'from_cron' 			=> false,

				// we're at step when getting those with amazon eror: throttled or some other error
				'witherror' 			=> false,

				// extra info per each product
				// array of pairs ( product_id => array( asin, country... ) )
				'prods_info' 			=> array(),

				'chunk_size' 			=> 10,
			), $pms);
			extract( $pms );


			//:: init
			$ret = array(
				'status' 		=> 'invalid',
				'msg' 			=> '',
				'is_sync_needed' => array(),
			);
	 		$msg = array();


			//:: validation
			if ( empty($products) || ! is_array($products) ) {
				$ret = array_replace_recursive($ret, array(
					'msg' 		=> "no products provided!",
				));
				return $ret;
			}
			//var_dump('<pre>', $products, $country, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;


			//:: some init
			$req_func = "{$provider}_request_make_lookup";

			$countries = $this->get_ws_object( $provider )->get_countries('country');
			$country_name = isset($countries["$country"]) ? $countries["$country"] : '';

			$this->updateSyncRules();

			$first_updated_date = (int) get_option('WooZone_sync_first_updated_date', 0);

			if ( empty($recurrence) ) {
				$recurrence = (int) ( $this->cfg['available_setup']['sync_recurrence'] * 3600 );
			}


			//:: main loop /through products till we finish all
			$step = 0;
			foreach ( array_chunk($products, $chunk_size, true) as $products2 ) {

				if ( $step ) {
					$msg[] = '<br /><br />------------------------------------------<br /><br />';
				}

				//=======================================
				//== current chunk of products
				$cache_type = 'asin';

				$prodsamz = array();
				$opGetAsin = array();

				$msg_ = 'unknown error occuring...';

				//:: USE CACHE
				$cache_name = array();
				foreach ($products2 as $kk => $vv) {
					$cache_name[] = strtoupper($vv) . '-' . $country;
				}

				$do_req_amz = true;

				if ( $use_cache ) {

					$do_req_amz = false;

					$opGetAsin = $this->the_plugin->amazon_request_get_cache( $cache_name, $cache_type );

					//:: it's in cache (all asins must be cached for this cache to be considered ok)
					if ( is_array($opGetAsin) && ! empty($opGetAsin) && ( count($opGetAsin) == count($cache_name) ) ) {
						$msg[] = implode( ', ', $cache_name ) . ' : get from cache';

						foreach ($opGetAsin as $kk => $vv) {
							$_asin = str_replace( '-' . $country, '', $kk );
							$_asin = strtoupper($_asin);
							$prodsamz["$_asin"] = $vv;
						}
					}
					//:: it's NOT in cache
					else {
						$do_req_amz = true;
					}
				}

				//:: MAKE REQUEST TO AMAZON
				if ( $do_req_amz ) {
					$asins_list = array_values( $products2 );

					// make request to amazon api
					$opMakeRequest = $this->the_plugin->$req_func( $asins_list, $country );

					$msg_ = $opMakeRequest['msg'];
					//$msg[] = implode( ', ', $cache_name ) . ' : ' . $opMakeRequest['msg'];
					$msg[] = $opMakeRequest['msg'];

					// is it a valid amazon response?
					$opstatus = in_array( $opMakeRequest['status'], array('valid', 'semivalid', 'notfound') ) ? 'ok' : 'error';
					foreach ($products2 as $kk => $vv) {
						$_asin = strtoupper($vv);

						// do we have the asin?
						if ( isset($opMakeRequest['amz_response']["$vv"]) ) {
							$prodsamz["$_asin"] = $opMakeRequest['amz_response']["$vv"];
						}
						else {
							if ( 'ok' == $opstatus ) {
								$prodsamz["$_asin"] = array(
									'status' 	=> 'notfound',
									'error' 	=> sprintf( 'asin %s was not found on %s', $vv, $provider ),
								);
							}
							else {
								$prodsamz["$_asin"] = array(
									'status' 	=> $opMakeRequest['status'],
									'error' 	=> $opMakeRequest['msg'],
								);
							}
						}

						// save in cache
						if ( $use_cache && 'ok' == $opstatus ) {
							$cache_name_ = strtoupper($vv) . '-' . $country;
							$this->the_plugin->amazon_request_save_cache( $cache_name_, $cache_type, array(
								'country' 	=> $country,
								'response' 	=> $prodsamz["$_asin"],
								'provider' 	=> $provider,
							));
						}
					} // end foreach
				}

				//DEBUG
				//var_dump('<pre>-------------', "step: $step" ,'</pre>'); var_dump('<pre>', $msg, array_keys($prodsamz) ,'</pre>');
				$step++;


				//=======================================
				//== loop through found products on amazon from this current chunk

				//:: amazon loop
				$cc = 0;
				foreach ( $products2 as $product_id => $asin ) {
					$_asin = strtoupper($asin); 

					$asin_info = isset($prodsamz["$_asin"]) ? $prodsamz["$_asin"] : array();
					$prodinfo = isset($prods_info["$product_id"]) ? $prods_info["$product_id"] : array();

					$msg[] = '<br />';

					$msg[] = '########';
					$msg[] = sprintf( 'product (id = %s, asin = %s) on country = %s', $product_id, $asin, $country_name );

					//DEBUG
					if ( $DEBUG ) {
						$asin_info_dbg = isset($asin_info['ASIN']) ? array( 'asin' => $asin_info['ASIN'] ) : $asin_info;
						var_dump('<pre>=============================================================' ,'</pre>');
						var_dump('<pre>', "product id: $product_id | asin: $asin | country: $country", 'asin_info', $asin_info_dbg ,'</pre>');
					}


					//:: do we need to synced it?
					if ( $verify_sync_date ) {
						$is_sync_needed = $this->the_plugin->syncproduct_is_sync_needed( array(
							'recurrence' => $recurrence,
							'product_id' => $product_id,
							'sync_last_date' => isset($prodinfo['sync_last_date']) ? $prodinfo['sync_last_date'] : false,
						));

						$ret['is_sync_needed']["$product_id"] = $is_sync_needed;

						if ( ! $is_sync_needed ) {
							// update WooZone_remaining_at, take this as marker for next sync bulk
							if ( $from_cron ) {
								$this->set_last_updated_product( $product_id, $witherror );
							}

							$msg[] = sprintf( 'product doesn\'t need to be synced.' );

							//DEBUG
							if ( $DEBUG ) {
								var_dump('<pre>', 'PRODUCT DOESN\'T NEED TO BE SYNCED.' ,'</pre>');
							}
							$cc++;
							continue 1;
						}
					}


					//:: NOT VALID - amazon response
					if ( ! isset($asin_info['ASIN']) ) {

						//DEBUG
						if ( $DEBUG ) {
							var_dump('<pre>', sprintf( 'PRODUCT NOT FOUND OR (%s ERROR: INVALID | THROTTLED)', $provider ) ,'</pre>');
						}

						$status = isset($asin_info['status']) ? $asin_info['status'] : 'invalid';
						$sync_msg = isset($asin_info['error']) ? $asin_info['error'] : $msg_;

						$_prod_stats = array(
							'DEBUG' 				=> $DEBUG,
							'from_cron' 			=> $from_cron,
							'parent_id' 			=> isset($prodinfo['post_parent']) ? $prodinfo['post_parent'] : false,
							'product_id' 			=> $product_id,
							'asin' 					=> $asin,
							'current_post' 			=> $prodinfo,
							'first_updated_date' 	=> $first_updated_date,
							'sync_msg'				=> $sync_msg,
							'retProd' 				=> array(),
							'parent_post' 			=> false,
						);
						$_prod_opstat = $this->syncprod_update_product_meta( $status, $_prod_stats );
						$msg[] = $_prod_opstat['msg'];
						$msg[] = sprintf( 'product status = %s', $_prod_opstat['status'] );

						// update WooZone_remaining_at, take this as marker for next sync bulk
						if ( $from_cron ) {
							$this->set_last_updated_product( $product_id, $witherror );
						}

						$cc++;
						continue 1;
					}

					$asin_info_formated = $this->get_ws_object( $provider )->build_product_data( $asin_info );

					$opProductType = $this->the_plugin->get_product_type_by_apiresponse( $asin_info_formated, $provider );
					extract( $opProductType ); //is_variable, is_variation_child, nb_variations, product_type
					// $product_type = $product_vars['product_type'];

					$can_sync_variations = $this->the_plugin->can_sync_variations( $asin_info_formated, $provider );

					//:: VALID - try to find all variations for this product
					$product_vars = $this->the_plugin->product_find_new_variations( $asin_info_formated, array(
						'only_new' 		=> false,
						'product_id' 	=> $product_id,
						'provider' 		=> $provider,
					));

					//DEBUG
					if ( $DEBUG ) {
						$product_vars_dbg = $this->the_plugin->product_find_new_variations( $asin_info_formated, array(
							'DEBUG'			=> $DEBUG,
							'only_new' 		=> false,
							'product_id' 	=> $product_id,
							'provider' 		=> $provider,
						));
						if ( isset($product_vars_dbg['current_post'], $product_vars_dbg['current_post']['post_content']) ) {
							unset( $product_vars_dbg['current_post']['post_content'] );
						}
						var_dump('<pre>', 'GET PRODUCT VARIATIONS', $product_vars_dbg ,'</pre>');
					}


					//DEBUG
					if ( $DEBUG ) {
						var_dump('<pre>', sprintf( 'PRODUCT IS <u>%s</u> (from %s) /can_sync_variations = %s', strtoupper($product_type), $provider, (int) $can_sync_variations ) ,'</pre>');
					}
					$msg[] = sprintf( 'product type (on %s) = %s', $provider, strtolower($product_type) );
					$msg[] = sprintf( 'can_sync_variations = %s', (int) $can_sync_variations );

					//:: SIMPLE PRODUCT OR JUST A VARIATION CHILD - UPDATE
					if ( 'variable' != $product_type || ! $can_sync_variations ) {
						$_prod_stats = array(
							'DEBUG' 				=> $DEBUG,
							'from_cron' 			=> $from_cron,
							'parent_id' 			=> isset($prodinfo['post_parent']) ? $prodinfo['post_parent'] : false,
							'product_id' 			=> $product_id,
							'asin' 					=> $asin,
							'current_post' 			=> $prodinfo,
							'first_updated_date' 	=> $first_updated_date,
							'sync_msg'				=> sprintf( 'asin %s was successfully found on %s', $asin, $provider ),
							'retProd' 				=> $asin_info_formated,
							'parent_post' 			=> false,
						);
						$_prod_opstat = $this->syncprod_update_product_meta( 'valid', $_prod_stats );
						$msg[] = $_prod_opstat['msg'];
						$msg[] = sprintf( 'product status = %s', $_prod_opstat['status'] );

						// update WooZone_remaining_at, take this as marker for next sync bulk
						if ( $from_cron ) {
							$this->set_last_updated_product( $product_id, $witherror );
						}

						$cc++;
						continue 1;
					}


					//:: VARIABLE PRODUCT - get variations from database, which don't exist on amazon anymore - DELETE THEM
					//DEBUG
					if ( $DEBUG ) {
						var_dump('<pre>', sprintf( 'VARIABLE PRODUCT - variations_notfound - get variations from database, which don\'t exist on %s anymore - DELETE : %s', $provider, count($product_vars['variations_notfound']) ) ,'</pre>');
					}
					if ( ! empty($product_vars['variations_notfound']) ) {

						foreach ( $product_vars['variations_notfound'] as $kk => $vv) {

							//if ( $DEBUG ) { break; }

							//:: do we need to synced it?
							if ( $verify_sync_date_vars ) {
								$is_sync_needed = $this->the_plugin->syncproduct_is_sync_needed( array(
									'recurrence' => $recurrence,
									'product_id' => $vv['current_post']['post_id'],
									'sync_last_date' => false,
								));

								if ( ! $is_sync_needed ) {
									//DEBUG
									if ( $DEBUG ) {
										var_dump('<pre>', sprintf( "PRODUCT ( %s, %s ) DOESN'T NEED TO BE SYNCED.", $vv['current_post']['post_id'], $kk ) ,'</pre>');
									}
									continue 1;
								}
							}

							$_prod_stats = array(
								'DEBUG' 				=> $DEBUG,
								'from_cron' 			=> $from_cron,
								'parent_id' 			=> $vv['current_post']['post_parent'], //$product_id
								'product_id' 			=> $vv['current_post']['post_id'],
								'asin' 					=> $kk,
								'current_post' 			=> $vv['current_post'],
								'first_updated_date' 	=> $first_updated_date,
								'sync_msg'				=> sprintf( 'asin %s was not found on %s', $kk, $provider ),
								'retProd' 				=> array(),
								'parent_post' 			=> $product_vars['current_post'],
							);
							$this->syncprod_update_product_meta( 'notfound', $_prod_stats );
						}
					}


					//:: VARIABLE PRODUCT - get variations from amazon, which exist in database - UPDATE THEM
					//DEBUG
					if ( $DEBUG ) {
						var_dump('<pre>', sprintf( 'VARIABLE PRODUCT - variations_exist - get variations from amazon, which exist in database - UPDATE : %s', count($product_vars['variations_exist']) ) ,'</pre>');
					}
					if ( ! empty($product_vars['variations_exist']) ) {

						foreach ( $product_vars['variations_exist'] as $kk => $vv) {

							//DEBUG
							//if ( $DEBUG ) { break; }
							if ( isset($vv['current_post'], $vv['current_post']['post_content']) ) {
								unset( $vv['current_post']['post_content'] );
							}
							//var_dump('<pre>',$kk, $vv ,'</pre>');
							//continue 1;
							//end DEBUG

							//:: do we need to synced it?
							if ( $verify_sync_date_vars ) {
								$is_sync_needed = $this->the_plugin->syncproduct_is_sync_needed( array(
									'recurrence' => $recurrence,
									'product_id' => $vv['current_post']['post_id'],
									'sync_last_date' => false,
								));

								if ( ! $is_sync_needed ) {
									//DEBUG
									if ( $DEBUG ) {
										var_dump('<pre>', sprintf( "PRODUCT ( %s, %s ) DOESN'T NEED TO BE SYNCED.", $vv['current_post']['post_id'], $kk ) ,'</pre>');
									}
									continue 1;
								}
							}

							$variation_item = $vv['variation_item'];
							$variation_item_formated = $this->get_ws_object( $provider )->build_product_data( $variation_item );
							//var_dump('<pre>', $variation_item, $variation_item_formated ,'</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

							$_prod_stats = array(
								'DEBUG' 				=> $DEBUG,
								'from_cron' 			=> $from_cron,
								'parent_id' 			=> $vv['current_post']['post_parent'], //$product_id
								'product_id' 			=> $vv['current_post']['post_id'],
								'asin' 					=> $kk,
								'current_post' 			=> $vv['current_post'],
								'first_updated_date' 	=> $first_updated_date,
								'sync_msg'				=> sprintf( 'asin %s was successfully found on %s', $kk, $provider ),
								'retProd' 				=> $variation_item_formated,
								'parent_post' 			=> $product_vars['current_post'],
							);
							$this->syncprod_update_product_meta( 'valid', $_prod_stats );
						}
					}


					//:: VARIABLE PRODUCT - get new variations from amazon, which don't exist in database - INSERT THEM
					//DEBUG
					if ( $DEBUG ) {
						var_dump('<pre>', sprintf( 'VARIABLE PRODUCT - variations_new - get new variations from amazon, which don\'t exist in database - INSERT : %s', $product_vars['total_new'] ), 'ADDING NEW VARIATIONS IS MADE BELLOW IN \'VARIABLE PRODUCT - MAIN PARENT PRODUCT TO UPDATE\' BY PARAMETER product_vars' ,'</pre>');
					}
					//$this->the_plugin->product_add_new_variations( $product_id, $product_vars );
					// ADDING NEW VARIATIONS IS MADE BELLOW IN 'VARIABLE MAIN PARENT PRODUCT - UPDATE' BY PARAMETER product_vars


					//:: VARIABLE MAIN PARENT PRODUCT - UPDATE
					//DEBUG
					if ( $DEBUG ) {
						var_dump('<pre>', 'VARIABLE PRODUCT - MAIN PARENT PRODUCT TO UPDATE' ,'</pre>');
					}
					$_prod_stats = array(
						'DEBUG' 				=> $DEBUG,
						'from_cron' 			=> $from_cron,
						'parent_id' 			=> 0,
						'product_id' 			=> $product_id,
						'asin' 					=> $asin,
						'current_post' 			=> $product_vars['current_post'],
						'first_updated_date' 	=> $first_updated_date,
						'sync_msg'				=> sprintf( 'asin %s was successfully found on %s', $asin, $provider ),
						'retProd' 				=> $asin_info_formated,
						'parent_post' 			=> false,
						'product_vars' 			=> $product_vars,
					);
					$_prod_opstat = $this->syncprod_update_product_meta( 'valid', $_prod_stats );
					$msg[] = $_prod_opstat['msg'];
					$msg[] = sprintf( 'product status = %s', $_prod_opstat['status'] );

					// update WooZone_remaining_at, take this as marker for next sync bulk
					if ( $from_cron ) {
						$this->set_last_updated_product( $product_id, $witherror );
					}

					$cc++;
				}
				// end amazon loop
			}
			// end main loop

			$msg = implode('<br />', $msg);
			$ret = array_replace_recursive($ret, array(
				'status' 		=> 'valid',
				'msg' 			=> $msg,
			));
			//var_dump('<pre>', $ret , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			return $ret;
		}
	}
}