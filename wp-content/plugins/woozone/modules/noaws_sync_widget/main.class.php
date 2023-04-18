<?php
/*
 * Works on Amazon Products
 */
//!defined('ABSPATH') and exit;

//require_once( WOOZONE_ABSPATH . 'composer/amazon-scraper/src/ProductExtract/WidgetExtract.php' );
require_once( WOOZONE_ABSPATH . 'composer/amazon-scraper/vendor/autoload.php' );

use WooZone\AmazonScraper\ProductExtract\WidgetExtract;
//var_dump('<pre>', get_declared_classes(), '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
if (class_exists('WooZoneNoAwsSyncWidgetMain') != true) { class WooZoneNoAwsSyncWidgetMain {

	const VERSION = '1.0';

	const ONE_MILION = 1000000;

	static protected $_instance;

	private $the_plugin = null;
	private $alias = '';

	protected $amz_settings;
	protected $sync_settings = array();
	protected $sync_tables = array();

	// mysql expression to cache widget requests (used for product synchronization)
	protected $widget_response_cache_exp = 'INTERVAL 1 HOUR';

	protected $current_bulk_posts = array();



	// Required __construct() function that initalizes the AA-Team Framework
	public function __construct()
	{
		global $WooZone;

		$this->the_plugin = $WooZone;
		$this->alias = $this->the_plugin->alias;

		$this->amz_settings = $this->the_plugin->settings();

		$this->init_sync_settings();

		$this->do_debug();

		//$this->WidgetExtract = WidgetExtract::getInstance( $this->the_plugin );
	}

	// Singleton pattern
	static public function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	public function do_debug() {

		if ( isset($_GET['page']) && $_GET['page'] == $this->the_plugin->alias . "_no_aws_keys_sync_widget" ) ;
		else {
			return true;
		}

		$req = array(
			'action' => isset($_GET['sub_action']) ? $_GET['sub_action'] : '',
		);
		extract( $req );

		if ( 'add_all_bulks' === $action ) {
			var_dump('<pre>', $this->queue_build_asins_bulks() , '</pre>');
		}
		if ( 'step_execute_bulks' === $action ) {
			var_dump('<pre>', $this->step_execute_bulks() , '</pre>');
		}
		if ( 'bulk_solve' === $action ) {
			$bulksolve = $this->bulk_solve_by_id( isset($_GET['bulkid']) ? (int) $_GET['bulkid'] : 0, array(
				'stop_at_step' => isset($_GET['stopat']) ? (string) $_GET['stopat'] : null,
			));
			var_dump('<pre>', $bulksolve , '</pre>');
		}

		if ( ! empty($action) ) {
			echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		}
	}

	public function init_sync_settings() {

		$this->sync_tables = array(
			'amz_sync_widget' 		=> 'amz_sync_widget',
			'amz_sync_widget_asins' => 'amz_sync_widget_asins',
		);

		$ss = get_option($this->alias . '_noaws_sync_widget', array());
		$ss = maybe_unserialize($ss);
		$ss = $ss !== false ? $ss : array();
		$ss = array_merge(array(
			//'sync_choose_country' => 'import_country',
		), $ss);

		//:: some settings
		$delete_unavailable_products = (int) $this->the_plugin->sync_tries_till_trash; //3
		if ( isset($ss['fix_issue_sync'], $ss['fix_issue_sync']['trash_tries']) ) {
			$delete_unavailable_products = (int) $ss['fix_issue_sync']['trash_tries'];
		}
		$ss['_delete_unavailable_products'] = $delete_unavailable_products;

		$delete_variations = isset($ss['delete_variations_if_parent_notfound'])
			&& 'no' == $ss['delete_variations_if_parent_notfound'] ? false : true;
		$ss['_delete_variations'] = $delete_variations;

		$opt_sync_fields_activated = array(
			'title' => 'Product Title',
			'price' => 'Product Price',
		);
		$sync_fields_activated = isset($ss['sync_fields'])
			? (array) $ss['sync_fields'] : array_keys( $opt_sync_fields_activated );
		$sync_fields_activated = $this->the_plugin->clean_multiselect( $sync_fields_activated );
		$ss['_sync_fields_activated'] = $sync_fields_activated;

		$keep_old_price = isset($ss['keep_old_price_if_new_empty'])
			&& 'no' == $ss['keep_old_price_if_new_empty'] ? false : true;
		$ss['_keep_old_price'] = $keep_old_price;

		$this->sync_settings = $ss;

		return array(
			'sync_tables' => $this->sync_tables,
			'sync_settings' => $this->sync_settings,
		);
	}

	//====================================================================================
	// CRONJOBS

	// sync current cycle
	public function cron_full_cycle( $pms, $return='die' ) {

		$ret = array('status' => 'failed');

		$amz_verify_msg = '';

		$time_format = 'Y-m-d H:i:s';
		$current_cron_status = $pms['status']; //'new'; //
		$current_time = time(); // GMT current time
		$first_updated_date = (int) get_option('WooZone_syncwidget_first_updated_date', 0);
		$recurrence = defined('WOOZONE_SYNCWIDGET_RECURRENCE') ? WOOZONE_SYNCWIDGET_RECURRENCE : 86400; // MUST BE IN IN SECONDS / 86400 = 1 DAY
		//var_dump('<pre>', $current_time, $first_updated_date, $recurrence, $current_time >= ( $first_updated_date + $recurrence ), '</pre>'); die('debug...'); 

		// recurrence interval fulfilled
		if ( /*1 || */$current_time >= ( $first_updated_date + $recurrence ) ) {
			
			// assurance verification: reset in any case after more than 3 times the current setted recurrence interval
			$do_reset = $current_time >= ( $first_updated_date + $recurrence * 3 ) ? true : false;
			$current_cycle_done = isset($pms['verify'], $pms['verify']['syncwidget_products'])
				&& $pms['verify']['syncwidget_products'] == 'stop' ? true : false;
			
			// current cycle not yet completed and not yet reached assurance verification
			if ( ! $current_cycle_done && ! $do_reset ) {
				$ret = array_merge($ret, array(
					'status' => 'done',
					'msg' 	=> 'current sync cycle not finished yet.' . $amz_verify_msg,
				));
				return $ret;
			}
			

			$opBuildBulks = $this->queue_build_asins_bulks();

			if ( 'invalid' === $opBuildBulks['status'] ) {
				$ret = array_merge($ret, array(
					'status' => 'done',
					'msg' 	=> 'error occured trying to add bulks. ' . $opBuildBulks['msg'] . $amz_verify_msg,
				));
				return $ret;
			}

			// to know when this current sync cycle started
			update_option('WooZone_syncwidget_first_updated_date', time());

			// to measure duration for current cycle
			$cycle_stats = get_option('WooZone_syncwidget_cycle_stats', array());
			$cycle_stats = is_array($cycle_stats) ? $cycle_stats : array();
			$cycle_stats = array_merge($cycle_stats, array(
				'start_time'        => '',
				'end_time'          => '',
			));
			update_option('WooZone_syncwidget_cycle_stats', $cycle_stats);

			$ret = array_merge($ret, array(
				'status'        => 'done',
				'msg' 			=> sprintf( 'new sync cycle started at %s', get_date_from_gmt( date('Y-m-d H:i:s', $current_time), $time_format) ) . ' ' . $opBuildBulks['msg'] . $amz_verify_msg,
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
				'msg' 	=> 'current sync cycle is not finished OR sync cycle reccurence interval is not fulfilled, so we cannot start a new sync cycle.' . $amz_verify_msg,
			));
		}
		return $ret;
	}

	// sync current step non solved bulks from current cycle
	public function cron_small_bulk( $pms, $return='die' ) {

		$ret = array('status' => 'failed');

		$amz_verify_msg = '';

		$current_cron_status = $pms['status']; //'new'; //

		$cycle_stats = get_option('WooZone_syncwidget_cycle_stats', array());
		$cycle_stats = is_array($cycle_stats) ? $cycle_stats : array();
		if ( !isset($cycle_stats['start_time']) || empty($cycle_stats['start_time']) ) {
			$cycle_stats = array_merge($cycle_stats, array(
				'start_time'        => time(),
			));
			update_option('WooZone_syncwidget_cycle_stats', $cycle_stats);
		}

		$opExecuteBulks = $this->step_execute_bulks();

		if ( 'nobulks' === $opExecuteBulks['status'] ) {

			$ret = array_merge($ret, array(
				'status'        => 'stop',
				'msg' 			=> $opExecuteBulks['msg'] . $amz_verify_msg,
			));

			$cycle_stats = array_merge($cycle_stats, array(
				'end_time'          => time(),
			));
			update_option('WooZone_syncwidget_cycle_stats', $cycle_stats);

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
				'status' 	=> 'done',
				'msg' 		=> $opExecuteBulks['msg'] . $amz_verify_msg,
			));
		}
		return $ret;
	}

	// single bulk related
	public function bulk_get_by_id( $bulk_id ) {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget']}";

		$bulk_id = (int) $bulk_id;

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'bulkdata' => array(),
		);
		$bulks = array();

		$sql = trim("
			SELECT {$this->_bulk_query_part_select_fields()}
			FROM $table as b
			WHERE 1=1
				AND b.ID = '$bulk_id'
			LIMIT 1;
		");
		//var_dump('<pre>', $sql , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$res = $wpdb->get_row( $sql, OBJECT );
		if ( empty($res) ) {
			return array_replace_recursive( $ret, array(
				'msg' => 'bulk get by id : error or not found in database  error!',
			));
		}
		//var_dump('<pre>', $res , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$bulkdata = $res;
		//var_dump('<pre>', $bulkdata , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => 'ok.',
			'bulkdata' => $bulkdata,
		));
	}

	public function bulk_solve_by_id( $bulk_id, $pms=array() ) {

		$pms = array_replace_recursive( array(
			// debug - if you want to stop execution af at given step
			'stop_at_step' => null,

			// if you want to use cached widget responses
			'use_cache' => true,

			// false = no overwrite
			// true = if sync_choose_country option is 'import_country', use default amazon location country
			// string = new country location to be used for this bulk
			'overwrite_country' => true,
		), $pms);
		extract( $pms );

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
		);

		//:: get bulk data from database
		$bulkdata = $this->bulk_get_by_id( $bulk_id );
		if ( 'invalid' === $bulkdata['status'] || empty($bulkdata['bulkdata']) ) {
			return array_replace_recursive( $ret, $bulkdata );
		}

		//:: ovewrite bulk data country?
		if ( ! empty($overwrite_country) ) {

			if ( true === $overwrite_country ) {

				$sync_choose_country = isset($this->sync_settings['sync_choose_country'])
					? $this->sync_settings['sync_choose_country'] : 'import_country';
				//var_dump('<pre>',$sync_choose_country ,'</pre>');

				// ovewrite bulk country with default amazon location from amazon config module
				if ( 'import_country' !== $sync_choose_country ) {
					$def_country = $this->amz_settings['country'];
					$bulkdata['bulkdata']->country = $def_country;
				}
			}
			else {
				$bulkdata['bulkdata']->country = $overwrite_country;
			}
		}

		$bulkdata = $bulkdata['bulkdata'];

		//:: solve bulk (get remote content & parse it)
		$bulksolve = $this->bulk_solve( $bulkdata, $pms );
		if ( 'invalid' === $bulksolve['status'] || empty($bulksolve['bulkdata']) ) {
			return array_replace_recursive( $ret, $bulksolve );
		}

		return array_replace_recursive( $ret, $bulksolve );
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	//====================================================================================
	// single bulk related

	private function bulk_solve( $bulkdata, $pms=array() ) {

		$pms = array_replace_recursive( array(
			// debug - if you want to stop execution af at given step
			'stop_at_step' => null,

			// if you want to use cached widget responses
			'use_cache' => true,
		), $pms);
		extract( $pms );

		$bulkdata = (array) $bulkdata;

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'from_cache' => false,
			'cache_duration_sec' => null,
			'widget_response_date' => $bulkdata['widget_response_date'],
		);

		//:: IS SYNC ACTIVATED?
		$isSyncActivated = $this->_bulk_is_sync_activated();

		//:: update bulk status in table
		$opBulkUpdStat = $this->_bulk_update_bulk_status( $bulkdata['ID'], array_merge( $isSyncActivated, array(
			'do_widget_response' => false,
			'country' => $bulkdata['country'],
		)));

		if ( 'invalid' === $isSyncActivated['status'] ) {

			// mark each product as synced
			$this->_bulk_sync_products( $bulkdata, $isSyncActivated );

			return array_replace_recursive( $ret, $isSyncActivated );
		}


		$parseContent = null;

		//:: USE CACHE - use widget response cache if available & valid
		$bulkcache = $this->_bulk_has_cache_valid( $bulkdata );
		if ( $use_cache && 'valid' === $bulkcache['status'] ) {
			
			$parseContent = $bulkcache;

			$ret = array_replace_recursive( $ret, array(
				'from_cache' => true,
				'cache_duration_sec' => $bulkdata['cache_duration_sec'],
			));

			//:: update bulk status in table
			$opBulkUpdStat = $this->_bulk_update_bulk_status( $bulkdata['ID'], array_merge( $parseContent, array(
				'do_widget_response' => false,
				'country' => $bulkdata['country'],
			)));
		}


		//:: GET REMOTE - get widget remote content
		if ( is_null($parseContent) ) {

			//:: get widget remote content
			$getContent = $this->_bulk_get_remote_content( $bulkdata );

			if ( 'get_content' === $stop_at_step ) {
				return array_replace_recursive( $ret, $getContent );
			}

			//:: update bulk status in table
			$opBulkUpdStat = $this->_bulk_update_bulk_status( $bulkdata['ID'], array_merge( $getContent, array(
				'do_widget_response' => 'invalid' === $getContent['status'] ? false : true,
				'country' => $bulkdata['country'],
			)));

			if ( ! empty($opBulkUpdStat['upd_time']) ) {
				$ret['widget_response_date'] = $opBulkUpdStat['upd_time'];
			}

			if ( 'invalid' === $getContent['status'] || empty($getContent['content']) ) {

				// mark each product as synced
				$this->_bulk_sync_products( $bulkdata, $getContent );

				return array_replace_recursive( $ret, $getContent );
			}


			//:: parse content
			$parseContent = $this->_bulk_parse_content( $getContent['content'] );

			//:: update bulk status in table
			$opBulkUpdStat = $this->_bulk_update_bulk_status( $bulkdata['ID'], array_merge( $parseContent, array(
				'do_widget_response' => false,
				'country' => $bulkdata['country'],
			)));

			if ( 'parse_content' === $stop_at_step ) {
				return array_replace_recursive( $ret, $parseContent );
			}

			if ( 'invalid' === $parseContent['status'] || empty($parseContent['widget_prods']) ) {

				// mark each product as synced
				$this->_bulk_sync_products( $bulkdata, $parseContent );

				return array_replace_recursive( $ret, $parseContent );
			}
		}


		//:: EVERYTHING SEEMS OK
		if ( 'parse_content' === $stop_at_step ) {
			return array_replace_recursive( $ret, $parseContent );
		}

		// mark each product as synced
		$syncProducts = $this->_bulk_sync_products( $bulkdata, $parseContent );

		if ( $ret['from_cache'] ) {
			$syncProducts['msg'] = sprintf( 'From cache (cache duration = %d sec). ', $ret['cache_duration_sec'] ) . $syncProducts['msg'];
		}

		//:: update bulk status in table
		$opBulkUpdStat = $this->_bulk_update_bulk_status( $bulkdata['ID'], array_merge( $syncProducts, array(
			'do_widget_response' => false,
			'country' => $bulkdata['country'],
		)));

		return array_replace_recursive( $ret, $syncProducts );
	}

	private function _bulk_is_sync_activated() {

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'msg_full' => '',
		);

		if ( empty($this->sync_settings['_sync_fields_activated'])
			|| ! is_array($this->sync_settings['_sync_fields_activated'])
		) {
			return array_replace_recursive( $ret, array(
				'msg' => 'For sync widget to work, please make sure you select at least one product field to be updated (Price or Title)!',
			));
		}

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => 'ok. sync widget is activated for field(s) : ' . implode(', ', $this->sync_settings['_sync_fields_activated']),
		));
	}

	private function _bulk_update_bulk_status( $bulk_id, $data ) {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget']}";

		$status = $data['status'];

		//$status_msg = $data['msg'];
		$status_msg = array(
			'country_used' => $data['country'],
			'msg' => isset($data['msg']) ? $data['msg'] : '',
			'msg_full' => isset($data['msg_full']) ? $data['msg_full'] : '',
		);
		$status_msg = maybe_serialize( $status_msg );
		$status_msg = esc_sql( $status_msg );

		$do_widget_response = false;
		if ( isset($data['content']) ) {

			$do_widget_response = true;

			$content = $data['content'];
			$content = trim( $content );
			$content = esc_sql( $content );
		}
		if ( isset($data['do_widget_response']) ) {
			$do_widget_response = (bool) $data['do_widget_response'];
		}

		$time = '';

		if ( $do_widget_response ) {

			$time = current_time( 'mysql' );

			$sql = trim("
				UPDATE $table as a SET 
					a.status = '$status',
					a.status_msg = '$status_msg',
					a.widget_response = '$content',
					a.widget_response_date = '" . $time . "'
				WHERE 1=1
					AND a.ID = '$bulk_id';
			");
		}
		else {

			$sql = trim("
				UPDATE $table as a SET 
					a.status = '$status',
					a.status_msg = '$status_msg'
				WHERE 1=1
					AND a.ID = '$bulk_id';
			");
		}

		return array(
			'upd_time' => $time,
			'upd_stat' => $wpdb->query( $sql ),
		);
	}

	private function _bulk_has_cache_valid( $bulkdata ) {

		$bulkdata = (array) $bulkdata;

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
		);

		if ( $bulkdata['cache_is_expired'] ) {
			return array_replace_recursive( $ret, array(
				'msg' => 'bulk cache has expired!',
			));
		}

		$parseContent = $this->_bulk_parse_content( $bulkdata['widget_response'], true );
		$parseContent['msg'] = $parseContent['msg'];
		//if ( 'invalid' === $parseContent['status'] || empty($parseContent['widget_prods']) ) {
		//	return array_replace_recursive( $ret, $parseContent );
		//}

		return array_replace_recursive( $ret, $parseContent );
	}

	private function _bulk_get_remote_content( $bulkdata ) {

		$bulkdata = (array) $bulkdata;

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'content' => '',
		);

		$country = $bulkdata['country'];
		
		$asins = $bulkdata['bulk_asins'];
		$asins = maybe_unserialize( $asins );
		$asins = ! empty($asins) && is_array($asins) ? array_values( $asins ) : array();
		if ( empty($asins) ) {
			return array_replace_recursive( $ret, array(
				'msg' => 'bulk get remote content : empty asins or not an array!',
			));
		}

		$asins_list = implode( ',', $asins );
		//$asins_list = 'B088WG2VXT,B07XFBN7HX,B07QXV6N1B'; //DEBUG on .com

		$url = "https://www.amazon.{$country}/gp/p13n-shared/faceout-partial?reftagPrefix=homepage&widgetTemplateClass=PI::Similarities::ViewTemplates::Carousel::Desktop&faceoutTemplateClass=PI::P13N::ViewTemplates::Product::Desktop::CarouselFaceout&productDetailsTemplateClass=PI::P13N::ViewTemplates::ProductDetails::Desktop::Base&offset=0&asins={$asins_list}";
		//var_dump('<pre>', $url , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$response = wp_remote_get( $url, array(
			'user-agent' => "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:24.0) Gecko/20100101 Firefox/24.0",
			'timeout' => 30
		));
		//var_dump('<pre>', $response , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		//var_dump('<pre>', is_wp_error( $response ), wp_remote_retrieve_response_code( $response ) , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( is_wp_error( $response ) || $response_code !== 200 ) {
			$msg = 'bulk get remote content : invalid widget response!';
			$msg .= ' ' . 'response code : ' . $response_code;
			if ( is_wp_error( $response ) ) {
				$msg .= ' ' . htmlspecialchars( implode(' ; ', $response->get_error_messages()) );
			}
			return array_replace_recursive( $ret, array(
				'msg' => $msg,
			));
		}

		$body = wp_remote_retrieve_body( $response );
		//var_dump('<pre>', $body , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => 'bulk get remote content : content retrieved (not parsed yet)!',
			'content' => $body,
		));
	}

	private function _bulk_parse_content( $content, $is_cache=false ) {

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'msg_full' => '',
		);

		$msg = $is_cache ? 'content from cache. ' : '';

		try {

			$widget = WidgetExtract::getInstance( $this->the_plugin );
			$widget->set_content( $content );
			$widget_notices = $widget->get_notices();
			$vars = $widget->extract();

			$msg .= 'bulk parse content : ok.';
			$msg_full = $msg;
			$msg_full .= ! empty($widget_notices) ? ' ' : '';
			$msg_full .= implode( ' | ', $this->get_widget_notices( $widget_notices ) );

			return array_replace_recursive( $ret, array(
				'status' => 'valid',
				'msg' => $msg,
				'msg_full' => $msg_full,
				'widget_notices' => $widget_notices,
				'widget_prods' => $vars,
			));
		}
		catch (\Exception $e) {
			$msg .= "bulk parse content : \"variations\" widget content parsing exception occured! " . $e->getMessage();

			return array_replace_recursive( $ret, array(
				'msg' => $msg,
			));
		}
	}

	private function _bulk_parse_content_format( $widget_prods, $country ) {

		if ( empty($widget_prods) || ! is_array($widget_prods) ) {
			return $widget_prods;
		}

		foreach ( $widget_prods as $key => $val ) {

			if ( is_array($val) && isset($val['list_price']) && ! empty($val['list_price']) ) {

				$newprice = $val['list_price'];
				$newprice = $this->the_plugin->directimport_get_product_price_format( $newprice, $country );
				$val['list_price'] = $newprice;
				
				$widget_prods["$key"] = $val;
			}
		}
		return $widget_prods;
	}

	private function _bulk_sync_products( $bulkdata, $widget ) {

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
		);

		$country = $bulkdata['country'];

		$asins = $bulkdata['bulk_asins'];
		$asins = maybe_unserialize( $asins );
		$asins = ! empty($asins) && is_array($asins) ? $asins : array();

		if ( empty($asins) ) {
			return $ret;
		}

		$wprods = is_array($widget) && isset($widget['widget_prods']) && is_array($widget['widget_prods'])
			? $widget['widget_prods'] : array();

		if ( ! empty($wprods) ) {

			$wprods = $this->_bulk_parse_content_format( $wprods, $country );

			$this->current_bulk_posts = $this->_bulk_get_bulk_posts_data( array_keys($asins) );
			$this->current_bulk_posts = $this->current_bulk_posts['data'];
		}
		//var_dump('<pre>', $wprods, '</pre>');
		//var_dump('<pre>', $this->current_bulk_posts, '</pre>');

		$calc = array(
			'invalid' => array( 0, 'invalid' ),
			'notfound' => array( 0, 'not found on amazon' ),
			'updated' => array( 0, 'updated in database' ),
			'notupdated' => array( 0, 'not updated' ),
		);

		foreach ( $asins as $post_id => $post_asin ) {

			$post_newinfo = ! empty($wprods) && isset($wprods["$post_asin"])
				? (array) $wprods["$post_asin"] : array();

			$current_post = isset($this->current_bulk_posts["$post_id"])
				? (array) $this->current_bulk_posts["$post_id"] : array();

			//var_dump('<pre>',$post_newinfo, $current_post ,'</pre>'); continue 1;

			$status = 'valid';
			$sync_msg = '';

			// some error occured at bulk stage
			if ( isset($widget['status'], $widget['msg']) && 'invalid' === $widget['status'] ) {
				$status = 'invalid';
				$sync_msg = $widget['msg'];
			}

			$opSyncProd = $this->_bulk_sync_product( $status, array(
				'country' => $country,
				'sync_msg' => $sync_msg,
				'product_id' => $post_id,
				'asin' => $post_asin,
				'post_newinfo' => $post_newinfo,
				'current_post' => $current_post,
			));
			if ( isset($calc["{$opSyncProd['status']}"]) ) {
				$calc["{$opSyncProd['status']}"][0]++;
			}
		}

		$msg = array();
		$msg[] = 'product = simple, variable parent or variation child';
		foreach ( $calc as $kk => $vv ) {
			if ( (int) $vv[0] ) {
				$msg[] = sprintf( "products %s = %d", $vv[1], (int) $vv[0] );
			}
		}
		if ( count($msg) <= 1 ) {
			$msg = array();
		}
		$msg = implode(' ; ', $msg);

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => $msg,
		));
	}

	private function _bulk_sync_product( $status, $pms=array() ) {

		$pms = array_replace_recursive( array(
			'country' => '',
			'sync_msg' => '',
			'product_id' => 0,
			'asin' => '',
			'post_newinfo' => array(),
			'current_post' => array(),
		), $pms);
		extract( $pms );

		// asin not found in widget
		if ( 'valid' === $status && empty($post_newinfo) ) {
			$status = 'notfound';
		}

		// build message if it's not already setted
		if ( empty($sync_msg) ) {

			if ( 'valid' === $status ) {
				$sync_msg = sprintf( 'asin %s was found on amazon.%s', $asin, $country );
			}
			else if ( 'notfound' === $status ) {
				$sync_msg = sprintf( 'asin %s was not found on amazon.%s', $asin, $country );
			}
			else if ( 'invalid' === $status ) {
				$sync_msg = 'bulk sync product : unknown invalid status!';
			}
			else {
				$sync_msg = 'bulk sync product : unknown status!';
			}
		}

		$sync_msg_savedb = array(
			'status' 	=> $status,
			'msg' 		=> $sync_msg,
			'rules' 	=> array(),
			'updated' 	=> array(),
		);

		//:: PRODUCT WAS FOUND ON AMAZON => LET'S TRY TO SYNCED IT
		if ( 'valid' === $status ) {

			// try to synced it
			$updateStat = $this->_bulk_update_product_data( $post_newinfo, array(
				'rules' 	=> $this->sync_settings['_sync_fields_activated'],
				'post_id' 	=> $product_id,
				'post_asin' => $asin,
				'current_post' => $current_post,
			));

			$updateStat['msg'] = $sync_msg_savedb['msg'] . '<br />' . $updateStat['msg'];
			$sync_msg_savedb = array_replace_recursive( $sync_msg_savedb, $updateStat );

			// updated | notupdated
			$status = $updateStat['status'];
		}

		// always set bellow metas!
		update_post_meta( $product_id, "_amzaff_syncwidget_last_status", $status );
		update_post_meta( $product_id, "_amzaff_syncwidget_last_status_msg", $sync_msg_savedb );

		//:: UPDATE THE POST METAS
		// some error occured at bulk stage
		if ( 'invalid' == $status ) {
			// don't update other metas ( most important _amzaff_syncwidget_last_date ), because we don't consider this to be a real sync (either error or success), it's just to identify the product as parsed by sync process
		}
		// product not found - really not found on amazon
		else if ( 'notfound' == $status ) {

			update_post_meta( $product_id, "_amzaff_syncwidget_last_date", $this->the_plugin->last_update_date() );

			update_post_meta( $product_id, "_amzaff_syncwidget_hits",
				(int) get_post_meta($product_id, "_amzaff_syncwidget_hits", true) + 1
			);

			$this->trash_post(
				$product_id,
				$asin,
				$this->sync_settings['_delete_unavailable_products'],
				$this->sync_settings['_delete_variations']
			);
		}
		// product was found on amazon and was synced, but it's info was: updated | notupdated
		else {

			update_post_meta( $product_id, "_amzaff_syncwidget_last_date", $this->the_plugin->last_update_date() );

			update_post_meta( $product_id, "_amzaff_syncwidget_hits",
				(int) get_post_meta($product_id, "_amzaff_syncwidget_hits", true) + 1
			);

			// reset sync trash marker
			update_post_meta( $product_id, "_amzaff_syncwidget_trash_tries", 0 );

			// remove from trash tries
			$prod_trashed = array( $product_id => array() );
			$prod_trashed[ $product_id ]['asin'] = $asin;
			$this->asins_prods_marked_list( 'WooZone_syncwidget_currentlist_prod_trash_tries', $prod_trashed, 'remove' );
		}

		return $sync_msg_savedb;
	}

	private function _bulk_update_product_data( $retProd=array(), $pms=array() ) {
		$pms = array_replace_recursive(array(
			'rules' 		=> array(),
			'post_id' 		=> 0,
			'post_asin'		=> '',

			// array with ID, post_title, _price
			'current_post' 	=> false,
		), $pms);
		extract( $pms );

		//---------------------
		//:: status messages
		$ret = array(
			'status' 	=> 'notfound',
			'msg' 		=> 'update product - init',
			'rules'		=> array(),
			'updated' 	=> array(),
		);
		$stats = array();
		$msg = array();

		//---------------------
		//:: empty amazon response?
		if ( empty($retProd) || ! is_array($retProd) ) {
			$ret = array_replace_recursive( $ret, array(
				'status' => 'notfound',
				'msg' 	=> sprintf( 'update product - empty product array!', $provider ),
			));
			return $ret;
		}


		//---------------------
		//:: some inits
		$_sync_rules = array_fill_keys( array_values($rules), true );


		//---------------------
		//:: main update body
		$args_update = array();
		$args_update['ID'] = $post_id;

		//---------------------
		//:: TITLE
		if ( $_sync_rules["title"] ) {

			$new_title = isset($retProd['title']) ? (string) $retProd['title'] : null;
			$new_title = trim( $new_title );

			if ( ! empty($new_title) ) {

				$args_update['post_title'] = $new_title;

				$opGetRule = $this->_updateWooProduct_get_rule_stats(
					'title',
					$args_update['post_title'],
					isset($current_post['post_title']) ? $current_post['post_title'] : null,
					array( 'rules' => $_sync_rules )
				);
				$stats = $stats + $opGetRule;
			}
			else {
				$msg[] = 'product new title is empty, so it wasn\'t updated.';
			}
		}

		//---------------------
		//:: UPDATE POST - posts table
		//var_dump('<pre>', $args_update, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		// update the post if needed
		if ( count($args_update) > 1 ) { // because ID is allways the same!
			wp_update_post( $args_update );
		}


		//---------------------
		//:: PRICE - postmeta table
		if ( $_sync_rules["price"] ) {

			$old_meta = isset($current_post['_price']) ? $current_post['_price'] : null;

			// set the product price
			$new_price = isset($retProd['list_price'], $retProd['list_price']['_OnlyPrice'])
				? $retProd['list_price']['_OnlyPrice'] : '0.00';

			$do_update_price = true;
			if ( empty($new_price) || '0.00' === (string) $new_price ) {
				if ( $this->sync_settings['_keep_old_price'] ) {
					$do_update_price = false;
				}
			}

			if ( $do_update_price ) {

				update_post_meta($post_id, '_price_update_date', time());
				update_post_meta($post_id, '_price', $new_price);
				update_post_meta($post_id, '_regular_price', $new_price);

				delete_post_meta($post_id, '_sale_price');

				$opGetRule = $this->_updateWooProduct_get_rule_stats(
					'price',
					$new_price,
					$old_meta,
					array( 'rules' => $_sync_rules )
				);
				$stats = $stats + $opGetRule;
			}
			else {
				$msg[] = 'product new price is empty and option "Keep old price if new price is empty?" is on "yes", so it wasn\'t updated.';
			}
		}

		// any stats changed?
		$status = 'notupdated';
		$updated = array();
		foreach ( $stats as $rule => $ruleinfo ) {
			if ( 'yes' == $ruleinfo['status'] ) {
				$status = 'updated';
				//break;
				$updated[] = $rule;
			}
		}

		$msg[] = 'update product - parsing rules finished.';
		$msg = implode( ' ', $msg );

		$ret = array_replace_recursive( $ret, array(
			'status' 	=> $status,
			'msg' 		=> $msg,
			'rules' 	=> $stats,
			'updated' 	=> $updated,
		));
		return $ret;
	}

	private function _bulk_get_bulk_posts_data( $id=array() ) {

		global $wpdb;

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'data' => array(),
		);

		$id_ = $id;
		$id_ = array_map( function( $item ) {
			return "'$item'";
		}, $id_ );
		$id_ = implode(', ', $id_);

		$sql = trim("
			select p.ID, p.post_title, pm.meta_value as _price
			from {$wpdb->posts} as p
				left join {$wpdb->postmeta} as pm on pm.post_id = p.ID
			where 1=1
				and pm.meta_key = '_price' and ! isnull( pm.meta_value )
				and p.ID in ( $id_ )
			order by p.ID asc;
		");
		//var_dump('<pre>', $sql , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$res = $wpdb->get_results( $sql, OBJECT_K );
		if ( empty($res) ) {
			return array_replace_recursive( $ret, array(
				'msg' => 'couldn\'t get bulk posts data!',
			));
		}

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => 'ok.',
			'data' => $res,
		));
	}

	private function _bulk_query_part_select_fields() {

		return trim("
			b.country, b.bulk_asins, b.ID, b.status, b.status_msg, b.widget_response, b.widget_response_date,
			if ( b.widget_response_date > DATE_SUB( NOW(), $this->widget_response_cache_exp ), 0, 1 ) as cache_is_expired,
			timestampdiff( second, b.widget_response_date, NOW() ) as cache_duration_sec
		");
	}



	//====================================================================================
	// sync current step non solved bulks from current cycle

	private function step_execute_bulks() {

		$isdebug = true;

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
		);
		$msg = array();

		//:: get one bulk (not yet executed) per country
		$bulks = $this->_step_get_current_bulks();
		if ($isdebug) { var_dump('<pre>step get current bulks', $bulks , '</pre>'); }
		//'invalid' === $bulks ||
		if ( empty($bulks['bulks']) ) {
			return array_replace_recursive( $ret, $bulks, array(
				'status' => 'nobulks', //so we know there are no more bulks to be synced
			));
		}
		$bulks = $bulks['bulks'];
		//echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		//:: pause
		$pause = rand( self::ONE_MILION, self::ONE_MILION * 10 );
		if ($isdebug) {
			$_msg = sprintf( 'pause for %d micro-seconds (microsecond = one millionth of a second).', $pause );
			$msg[] = $_msg;
			var_dump('<pre>', $_msg, '</pre>');
		}
	
		if ( ! defined('WOOZONE_DEV_SERVER') || ! WOOZONE_DEV_SERVER ) {
			usleep( $pause );
		}

		//:: parse selected bulks - maximum one per country
		foreach ( $bulks as $bulkdata ) {

			// get current bulk asins
			$bulkdata = (array) $bulkdata;

			$current_bulk = $this->bulk_solve( $bulkdata, array() );
			$msg[] = 'bulk id #' . $bulkdata['ID'] . '<br />' . $current_bulk['msg'];
		}
		// end foreach

		$msg = implode('<br /><br />------------------------------------------<br /><br />', $msg);
		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => $msg,
		));
	}

	private function _step_get_current_bulks() {

		$sync_choose_country = isset($this->sync_settings['sync_choose_country'])
			? $this->sync_settings['sync_choose_country'] : 'import_country';
		//var_dump('<pre>',$sync_choose_country ,'</pre>');

		if ( 'import_country' === $sync_choose_country ) {
			return $this->_step_get_current_bulks_by_multicountry();
		}
		else {
			return $this->_step_get_current_bulks_by_defcountry();
		}
	}

	private function _step_get_current_bulks_by_defcountry() {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget']}";

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'bulks' => array(),
		);
		$bulks = array();

		$sql = trim("
			SELECT {$this->_bulk_query_part_select_fields()}
			FROM $table as b
			WHERE 1=1
				AND b.status = 'initial'
			ORDER BY b.ID ASC
			LIMIT 1;
		");
		//var_dump('<pre>', $sql , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$res = $wpdb->get_results( $sql, OBJECT_K );
		if ( empty($res) ) {
			return array_replace_recursive( $ret, array(
				'msg' => 'couldn\'t found any bulk on default amazon location country!',
			));
		}

		// ovewrite bulk country with default amazon location from amazon config module
		$def_country = $this->amz_settings['country'];
		$resnew = array( $def_country => array_shift($res) );
		$resnew["$def_country"]->country = $def_country;

		$bulks = $resnew;
		//var_dump('<pre>', $bulks , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => 'ok.',
			'bulks' => $bulks,
		));
	}

	private function _step_get_current_bulks_by_multicountry() {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget']}";

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'bulks' => array(),
		);
		$bulks = array();

		//https://stackoverflow.com/questions/11150435/mysql-order-by-min-not-matching-up-with-id
		//https://dba.stackexchange.com/questions/160216/group-by-gives-wrong-result-with-min-aggregate-function
		//, COUNT(ID) AS nb
		// !!! mysql group by related issue fix: to be sure that bulk_asins are returned for the right ID
		$sql = trim("
			SELECT {$this->_bulk_query_part_select_fields()}
			FROM (
				SELECT country, MIN(ID) AS min_id
				FROM $table
				WHERE 1=1
					AND status = 'initial'
				GROUP BY country
			) AS a
			LEFT JOIN $table AS b
				ON b.country = a.country AND b.ID = a.min_id;
		");
		//var_dump('<pre>', $sql , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$res = $wpdb->get_results( $sql, OBJECT_K );
		if ( empty($res) ) {
			return array_replace_recursive( $ret, array(
				'msg' => 'couldn\'t found any bulk on any country!',
			));
		}

		$bulks = $res;
		//var_dump('<pre>', $bulks , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => 'ok.',
			'bulks' => $bulks,
		));
	}

	//====================================================================================
	// sync current cycle

	private function queue_build_asins_bulks() {

		$isdebug = true;

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
		);

		//$sync_choose_country = isset($this->sync_settings['sync_choose_country'])
		//	? $this->sync_settings['sync_choose_country'] : 'import_country';

		//:: empty queue tables
		$opEmptyTables = $this->_queue_empty_tables();
		if ( 'invalid' === $opEmptyTables['status'] ) {
			return array_replace_recursive( $ret, array(
				'msg' => 'couldn\'t empty the queue tables!',
			));
		}
		if ($isdebug) { var_dump('<pre>empty queue tables', $opEmptyTables, '</pre>'); }

		//:: add all asins to queue table
		$add2queue = $this->_queue_add_all_asins();
		if ( empty($add2queue) ) {
			$msg = sprintf( 'queue add all asins : %s', ( false === $add2queue ? 'sql error occured!' : 'no products found!' ) );
			return array_replace_recursive( $ret, array(
				'msg' => $msg,
			));
		}
		if ($isdebug) { var_dump('<pre>queue add all asins', $add2queue, '</pre>'); }

		//:: get countries having products
		$countries = $this->_queue_get_countries_having_products();
		if ( 'invalid' === $countries || empty($countries['countries']) ) {
			return array_replace_recursive( $ret, $countries );
		}
		if ($isdebug) { var_dump('<pre>countries having products', $countries , '</pre>'); }
		$countries = $countries['countries'];

		//:: build bulks grouped by country
		if ($isdebug) { var_dump('<pre>add bulks section', '</pre>'); }

		$bulks_added = array();

		foreach ( $countries as $country_key => $country_nb ) {

			$last_id = 0;
			$prods = array();
			$nbprods = 0;

			do {
				// get current bulk asins
				$current_bulk = $this->_queue_select_bulk_asins( $country_key, $last_id, 99 );

				$last_id = $current_bulk['last_id'];
				$prods = $current_bulk['prods'];
				$nbprods += count($prods);
				if ($isdebug) { var_dump('<pre>', sprintf( 'country = %s , country total prods = %d, country current parsed prods = %d, current bulk prods = %d, current bulk last id = %d', $country_key, $country_nb, $nbprods, count($prods), $last_id ), '</pre>'); }

				// add current bulk to bulks table
				$bulk_id = $this->_queue_add_bulk( $country_key, $prods );
				if ($isdebug) { var_dump('<pre>', sprintf( 'bulk added with id = %d', $bulk_id ), '</pre>'); }

				$bulks_added[] = $bulk_id;
			}
			while( $nbprods < $country_nb || empty($last_id) || empty($prods) );
		}
		// end foreach

		if ($isdebug) { var_dump('<pre>bulks added', count($bulks_added), '</pre>'); }

		return array_replace_recursive( $ret, array(
			'status' => count($bulks_added) ? 'valid' : 'invalid',
			'msg' => count($bulks_added) ? sprintf( '%d bulks added!', count($bulks_added) ) : 'no bulk was added!',
		));
	}

	private function _queue_empty_tables() {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget_asins']}";
		$tablew = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget']}";

		$ret = array( 'status' => 'invalid' );
		$status = true;

		foreach ( array( $table, $tablew ) as $tbl ) {
			$sql = trim("
				TRUNCATE TABLE $tbl;
			");
			$ret["$tbl"] = $wpdb->query( $sql );

			$status = $status && $ret["$tbl"];
		}

		$ret['status'] = $status ? 'valid' : 'invalid';
		//var_dump('<pre>',$ret ,'</pre>');
		return $ret;
	}

	private function _queue_add_all_asins() {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget_asins']}";

		$sql = trim("
			INSERT IGNORE INTO
				$table (asin, country, post_id)
			SELECT pm.meta_value, pm2.meta_value, p.ID
			FROM {$wpdb->prefix}posts AS p
				LEFT JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
				LEFT JOIN {$wpdb->prefix}postmeta AS pm2 ON p.ID = pm2.post_id
			WHERE 1=1
				AND pm.meta_key = '_amzASIN' AND ! ISNULL(pm.meta_value)
				AND pm2.meta_key = '_amzaff_country' AND ! ISNULL(pm2.meta_value)
				AND p.post_type IN ( 'product', 'product_variation' )
				AND p.post_status = 'publish'
			ORDER BY p.ID ASC;
		");

		return $wpdb->query( $sql );
	}

	private function _queue_get_countries_having_products() {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget_asins']}";

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'countries' => array(),
			'nbprods' => 0,
		);
		$countries = array();

		// get countries having amazon products and how many has each country
		// _amzASIN, _amzaff_prodid, _amzaff_country
		$sql = trim("
			SELECT country, COUNT(post_id) AS nb
			FROM $table
			WHERE 1=1
			GROUP BY country
			ORDER BY nb ASC;
		");
		//var_dump('<pre>', $sql , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$res = $wpdb->get_results( $sql, OBJECT_K );
		if ( empty($res) ) {
			return array_replace_recursive( $ret, array(
				'msg' => 'couldn\'t found any country having products!',
			));
		}

		// use product import country as Amazon location for sync
		foreach ( $res as $kk => $vv ) {
			$res["$kk"] = $vv->nb;
		}
		$countries = $res;
		//var_dump('<pre>', $countries , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => 'ok.',
			'countries' => $countries,
			'nbprods' => (int) array_sum( $countries ),
		));
	}

	private function _queue_get_total_products() {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget_asins']}";

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'countries' => array(),
			'nbprods' => 0,
		);
		$countries = array();

		// get total number of amazon products
		$sql = trim("
			SELECT COUNT(post_id) AS nb
			FROM $table
			WHERE 1=1;
		");
		//var_dump('<pre>', $sql , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$res = $wpdb->get_var( $sql );
		if ( empty($res) ) {
			return array_replace_recursive( $ret, array(
				'msg' => 'no amazon products were found!',
			));
		}

		$countries["{$this->amz_settings['country']}"] = $res; // all products!
		//var_dump('<pre>', $countries , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => 'ok.',
			'countries' => $countries,
			'nbprods' => $res,
		));
	}

	private function _queue_select_bulk_asins( $country, $last_id=0, $how_many=99 ) {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget_asins']}";

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'prods' => array(),
			'last_id' => 0,
		);
		$countries = array();

		// get total number of amazon products
		$sql = trim("
			SELECT post_id, asin
			FROM $table
			WHERE 1=1
				AND country = '$country'
				AND post_id > '$last_id'
			ORDER BY post_id ASC
			LIMIT;
		");
		$sql = $how_many ? str_replace( 'LIMIT;', "LIMIT $how_many;", $sql ) : str_replace( 'LIMIT;', ';', $sql );
		//var_dump('<pre>', $sql , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$res = $wpdb->get_results( $sql, OBJECT_K );
		if ( empty($res) ) {
			return array_replace_recursive( $ret, array(
				'msg' => sprintf( '%s : select bulk asins - no amazon products were found!', $country ),
			));
		}

		foreach ( $res as $kk => $vv ) {
			$res["$kk"] = $vv->asin;
		}

		$last_item = array_keys($res);
		$last_item = $last_item[ count($last_item) - 1 ];

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => 'ok.',
			'prods' => $res,
			'last_id' => $last_item,
		));
	}

	private function _queue_add_bulk( $country, $prods ) {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget']}";

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'prods' => array(),
			'last_id' => 0,
		);

		if ( empty($prods) ) {
			return array_replace_recursive( $ret, array(
				'msg' => sprintf( '%s : add bulk - no amazon products were found!', $country ),
			));
		}

		$status = 'initial';
		$bulk_asins = maybe_serialize( $prods );
		$bulk_code = md5( $bulk_asins . $country );

		return $this->the_plugin->db_custom_insert(
			$table,
			array(
				'values' => array(
					'bulk_code' 	=> $bulk_code,
					'bulk_asins' 	=> $bulk_asins,
					'country' 		=> $country,
					'status' 		=> $status,
					'created_date' 	=> current_time( 'mysql' ),
				),
				'format' => array(
					'%s', '%s', '%s', '%s', '%s',
				)
			),
			true
		);
	}

	//====================================================================================
	// MISC

	private function _updateWooProduct_get_rule_stats( $rule, $new, $old=null, $pms=array() ) {

		return $this->the_plugin->_updateWooProduct_get_rule_stats( $rule, $new, $old, $pms );
	}

	// delete/trash post
	private function trash_post( $post_id, $asin, $do_trash=-1, $delete_variations=true ) {
		if ( empty($post_id) ) return true;
		global $wpdb;

		$allowed_tries = (int) $do_trash;
		$do_trash = ( -1 ==  $allowed_tries ? false : true );

		//:: don't trash product (amazon config setting is no)
		if ( !$do_trash ) {
			return false;
		}

		update_post_meta( $post_id, "_amzaff_syncwidget_trash_tries",
			(int) get_post_meta($post_id, "_amzaff_syncwidget_trash_tries", true) + 1
		);
		$sync_trash_tries = (int) get_post_meta($post_id, "_amzaff_syncwidget_trash_tries", true);

		//:: still some tries till trash product
		if ( $sync_trash_tries < $allowed_tries ) {
			//:: asins not found in db!
			$prod_trashed = array( $post_id => array() );
			$prod_trashed[ $post_id ]['asin'] = $asin;
			$this->asins_prods_marked_list( 'WooZone_syncwidget_currentlist_prod_trash_tries', $prod_trashed, 'add' );

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

		if ( $delete_variations ) {
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
		}

		//:: asins not found in db!
		$prod_trashed = array( $post_id => array() );
		$prod_trashed[ $post_id ]['asin'] = $asin;
		if ( isset($cc) && $cc ) {
			$prod_trashed[ $post_id ]['nbvars'] = $cc;
		}
		$this->asins_prods_marked_list( 'WooZone_syncwidget_currentlist_prod_trashed', $prod_trashed, 'add' );

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

		//update_option( $opt_name, $__sync_prod_notfound );
		$this->the_plugin->u->add_or_update( $opt_name, $__sync_prod_notfound );
	}

	private function get_countries_having_products() {

		global $wpdb;

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'countries' => array(),
			'nbprods' => 0,
		);
		$countries = array();

		// get countries having amazon products and how many has each country
		// _amzASIN, _amzaff_prodid, _amzaff_country
		$sql = trim("
			SELECT pm2.meta_value AS country, COUNT(p.ID) AS nb
			FROM {$wpdb->prefix}posts AS p
				LEFT JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
				LEFT JOIN {$wpdb->prefix}postmeta AS pm2 ON p.ID = pm2.post_id
			WHERE 1=1
				AND pm.meta_key = '_amzASIN' AND ! ISNULL(pm.meta_value)
				AND pm2.meta_key = '_amzaff_country' AND ! ISNULL(pm2.meta_value)
				AND p.post_type IN ( 'product', 'product_variation' )
				AND p.post_status = 'publish'
			GROUP BY country
			ORDER BY nb ASC;
		");
		//var_dump('<pre>', $sql , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$res = $wpdb->get_results( $sql, OBJECT_K );
		if ( empty($res) ) {
			//Amazon location for sync is set on "use product import country", but couldn\'t found any country having products!
			return array_replace_recursive( $ret, array(
				'msg' => 'couldn\'t found any country having products!',
			));
		}

		// use product import country as Amazon location for sync
		foreach ( $res as $kk => $vv ) {
			$res["$kk"] = $vv->nb;
		}
		$countries = $res;
		//var_dump('<pre>', $countries , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => 'ok.',
			'countries' => $countries,
			'nbprods' => (int) array_sum( $countries ),
		));
	}

	private function get_total_products() {

		global $wpdb;

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'countries' => array(),
			'nbprods' => 0,
		);
		$countries = array();

		// get total number of amazon products
		$sql = trim("
			SELECT COUNT(p.ID) AS nb
			FROM {$wpdb->prefix}posts AS p
				LEFT JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
			WHERE 1=1
				AND pm.meta_key = '_amzASIN' AND ! ISNULL(pm.meta_value)
				AND p.post_type IN ( 'product', 'product_variation' )
				AND p.post_status = 'publish';
		");
		//var_dump('<pre>', $sql , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$res = $wpdb->get_var( $sql );
		if ( empty($res) ) {
			//Amazon location for sync is set on "use currenct amazon location setting", but no amazon products were found!
			return array_replace_recursive( $ret, array(
				'msg' => 'no amazon products were found!',
			));
		}

		// use currenct amazon location setting as Amazon location for sync
		$countries["{$this->amz_settings['country']}"] = $res; // all products!
		//var_dump('<pre>', $countries , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => 'ok.',
			'countries' => $countries,
			'nbprods' => $res,
		));
	}

	private function get_widget_notices( $notices=array() ) {

		if ( empty($notices) ) {
			return array();
		}
		//return http_build_query( $notices );
		$ret = array();
		foreach ( $notices as $key => $val ) {
			$ret[] = "[$key] : $val";
		}
		return $ret;
	}

} } // end class
 
// Initialize the WooZoneNoAwsSyncWidgetMain class
$WooZoneNoAwsSyncWidgetMain = WooZoneNoAwsSyncWidgetMain::getInstance();
