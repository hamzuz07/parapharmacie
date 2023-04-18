<?php
/*
* Define class WooZoneNoAwsSyncWidget
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;

if (class_exists('WooZoneNoAwsSyncWidget') != true) { class WooZoneNoAwsSyncWidget {

	const VERSION = '1.0';

	static protected $_instance;

	private $the_plugin = null;
	private $alias = '';

	private $module_folder = '';
	private $module = '';

	protected $amz_settings;
	protected $sync_settings = array();
	protected $sync_tables = array();

	private $syncObj = null;



	// Required __construct() function that initalizes the AA-Team Framework
	public function __construct()
	{
		global $WooZone;

		$this->the_plugin = $WooZone;
		$this->alias = $this->the_plugin->alias;

		$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/noaws_sync_widget/';
		$this->module = $this->the_plugin->cfg['modules']['noaws_sync_widget'];

		$this->amz_settings = $this->the_plugin->settings();

		if ( $this->the_plugin->is_admin ) {
			add_action('admin_menu', array( $this, 'adminMenu' ));
		}

		add_action( 'wp_ajax_WooZoneNoAWS_SyncWidget', array( $this, 'ajax_request' ) );

		require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/noaws_sync_widget/main.class.php' );
		//$this->syncObj = new WooZoneNoAwsSyncWidgetMain();
		$this->syncObj = WooZoneNoAwsSyncWidgetMain::getInstance();

		//$this->init_sync_settings();
		$__ = $this->syncObj->init_sync_settings();
		$this->sync_settings = $__['sync_settings'];
		$this->sync_tables = $__['sync_tables'];
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
	//== AJAX REQUEST
	//====================================================================================

	public function ajax_request() {

		$requestData = array(
			'action' 	=> isset($_REQUEST['sub_action']) ? (string) $_REQUEST['sub_action'] : '',
			'bulk_id' 	=> isset($_REQUEST['bulk_id']) ? (int) $_REQUEST['bulk_id'] : 0,
		);
		extract($requestData);
		//var_dump('<pre>', $requestData , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$ret = array(
			'status' => 'invalid',
			'msg' => 'Invalid action!',
		);

		if ( empty($action) || !in_array($action, array(
			'default',
			'sync_bulk',
			'reset_sync_stats',
			'cronjob_stats_mainstats',
		)) ) {
			die(json_encode($ret));
		}

		//:: actions
		switch ( $action ) {

			case 'default':
				$ret = array_replace_recursive( $ret, array(
					'status' => 'valid',
					'msg' => 'this is a default action!',
				));
				break;

			case 'sync_bulk':

				$opSyncBulk = $this->sync_bulk( $bulk_id );
				$ret = array_replace_recursive( $ret, $opSyncBulk );
				break;

			case 'reset_sync_stats':

				$what = isset($_REQUEST['what']) ? $_REQUEST['what'] : '';

				$opResetSyncStats = $this->reset_sync_stats( $what );
				$ret = array_replace_recursive( $ret, $opResetSyncStats );
				break;

			case 'cronjob_stats_mainstats':

				$opStatus = $this->sync_stats( false );
				$ret = array_merge($ret, array(
					'status'		=> 'valid',
					'html'			=> $opStatus,
				));
				break;

		}

		die( json_encode( $ret ) );
	}



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	//====================================================================================
	// HTML Interface

	// Hooks
	public function adminMenu()
	{
	   self::getInstance()
			->_registerAdminPages();

		if( isset($_GET['page']) && $_GET['page'] == $this->the_plugin->alias . "_no_aws_keys_sync_widget" ) {

			/*wp_enqueue_script(
				'WooZone/noaws_sync_widget',
				$this->module_folder . 'assets/app.build.js',
				array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-dom-ready' ),
				'1.0',
				true
			);*/

			/*wp_localize_script( 'WooZone/noaws_sync_widget', 'WooZoneNoAwsKeysImport', array(
				'assets_url' => $this->module_folder,
				'import_in_category' => $this->get_importin_category(),
				'imported_products' => WooZoneDirectImport()->get_imported_products(),
				'validation' => array(
					'ipc' => get_option( 'WooZone_register_key' ),
					'email' => get_option( 'WooZone_register_email' ) ? get_option( 'WooZone_register_email' ) : get_option( 'admin_email' ),
					'buyer' => get_option( 'WooZone_register_buyer' ),
					'licence' => get_option( 'WooZone_register_licence' ),
					'when' => get_option( 'WooZone_register_timestamp' ),
					'ipc' => get_option( 'WooZone_register_key' ),
					'home_url' => home_url('/'),
					'plugin_alias' => $this->the_plugin->alias
				)
			) );*/

			wp_enqueue_style(
				'WooZone/noaws_sync_widget',
				$this->module_folder . 'assets/app.css'
			);
		}
	}

	// Register plug-in module admin pages and menus
	protected function _registerAdminPages()
	{
		add_submenu_page(
			$this->the_plugin->alias,
			$this->the_plugin->alias . " " . __('No AWS Keys Sync Widget', 'woozone'),
			__('No AWS Keys Sync Widget', 'woozone'),
			'manage_options',
			$this->the_plugin->alias . "_no_aws_keys_sync_widget",
			array($this, 'printBaseInterface')
		);

		require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/noaws_sync_widget/list.table.php' );
		$this->wp_list_table = new WooZoneNoAwsSyncWidgetList( $this->the_plugin, $this );

		return $this;
	}

	public function printBaseInterface()
	{
		global $wpdb;

		$req = array(
			'bulk_country' => isset($_GET['bulk_country']) ? $_GET['bulk_country'] : '',
			'bulk_status' => isset($_GET['bulk_status']) ? $_GET['bulk_status'] : '',
		);
		extract( $req );

		echo WooZone_asset_path( 'js', $this->module_folder . 'app.syncwidget.js', false );
		//echo WooZone_asset_path( 'css', $this->module_folder . 'syncwidget.css', false );
?>

	<div id="<?php echo WooZone()->alias?>" class="WooZone-direct-import">

		<div id="WooZone-sync-log" class="<?php echo WooZone()->alias?>-content" style="margin: 0px;">

			<?php
			// show the top menu
			WooZoneAdminMenu::getInstance()->make_active('import|noaws_sync_widget')->show_menu();
			?>

			<!-- Content -->
			<section class="WooZone-main">

				<?php
				echo WooZone()->print_section_header(
					$this->module['noaws_sync_widget']['menu']['title'],
					$this->module['noaws_sync_widget']['description'],
					$this->module['noaws_sync_widget']['help']['url']
				);
				?>
				<div id="<?php echo WooZone()->alias?>-SyncWidget-wrapper">

					<form id="events-noaws_sync_widget" method="get">

    					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    					<input type="hidden" name="bulk_country" value="<?php echo $bulk_country; ?>" />
    					<input type="hidden" name="bulk_status" value="<?php echo $bulk_status; ?>" />

						<?php
							$this->wp_list_table->prepare_items();
							$this->wp_list_table->display();
						?>

					</form>
				</div>
			</section>
		</div>
	</div>

<?php
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	private function init_sync_settings() {
	}

	private function sync_bulk( $bulk_id ) {

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
		);

		$opSync = $this->syncObj->bulk_solve_by_id( $bulk_id, array() );

		//:: status column
		$html = array();
		$html[] = '<div class="' . ( WooZone()->alias ) . '-bulk-status ' .$opSync['status'] . '">';
		$html[] = 	$opSync['status'];
		$html[] = '</div>';
		$status = implode(PHP_EOL, $html);

		//:: status details column
		$data = $opSync;
		$html = array();
		$html[] = '<div class="' . ( WooZone()->alias ) . '-view-all-statusmsg">';
		$html[] = 	'<div>';
		$html[] = 		isset($data['msg_full']) && ! empty($data['msg_full']) ? $data['msg_full'] : $data['msg'];
		$html[] = 	'</div>';
		$html[] = 	isset($data['msg_full']) && ! empty($data['msg_full']) ? '<a href="#">view all details</a>' : '';
		$html[] = '</div>';
		$status_msg = implode(PHP_EOL, $html);

		//: response date column
		$date = $opSync['widget_response_date'];
		if ( ! empty($date) ) {
			$date = WooZone()->last_update_date('true', strtotime($date));
		}

		$ret = array_replace_recursive( $ret, $opSync, array(
			'columns' => array(
				'status' => $status,
				'status_msg' => $status_msg,
				'response_date' => $date,
			),
		));
		return $ret;
	}

	private function reset_sync_stats( $what ) {

		global $wpdb, $WooZone;

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
		);
		$msg = array();

		//:: delete sync cycle options
		$optionsList = array(
			'WooZone_syncwidget_first_updated_date',
			'WooZone_syncwidget_cycle_stats',
			'WooZone_syncwidget_currentlist_prod_trashed',
			'WooZone_syncwidget_currentlist_prod_trash_tries',
		);
		foreach ($optionsList as $opt_todel) {
			delete_option( $opt_todel );
		}

		//:: delete products meta related to a sync cycle
		$tposts = $wpdb->posts;
		$tpostmeta = $wpdb->postmeta;

		$metas_todel = array();
		if ( 'yes_all' == $what ) {
			$metas_todel = array(
				'_amzaff_syncwidget_trash_tries',
				//'_amzaff_syncwidget_hits_prev',
				'_amzaff_syncwidget_hits',
				'_amzaff_syncwidget_last_date',
				'_amzaff_syncwidget_last_status_msg',
				'_amzaff_syncwidget_last_status',
				//'_amzaff_syncwidget_current_cycle',
			);
		}
		else if ( 'yes' == $what ) {
			$metas_todel = array(
				'_amzaff_syncwidget_trash_tries',
				'_amzaff_syncwidget_last_date',
			);
		}

		if ( empty($metas_todel) ) {
			$msg[] = 'You\'ve selected NO regarding reset sync stats for products, so no reset was made.';
		}
		else {
			$metas_todel2 = $metas_todel;
			$metas_todel2 = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $metas_todel2));

			$queries = array(
				"delete from $tpostmeta where 1=1 and meta_key in ($metas_todel2);",
			);
			$stat = 0;
			foreach ($queries as $query) {
				$stat += $wpdb->query( $query );
			}

			$msg[] = sprintf(
				'Deleted: %s postmetas.',
				$stat
			);
		}

		$msg = implode('<br />', $msg);

		$ret = array_replace_recursive( $ret, array(
			'status' => 'valid',
			'msg' => $msg,
		));

		return $ret;
	}

	/**
	 * Pretty-prints the difference in two times.
	 *
	 * @param time $older_date
	 * @param time $newer_date
	 * @return string The pretty time_since value
	 * @original link http://binarybonsai.com/code/timesince.txt
	 */
	public function time_since( $older_date, $newer_date ) {
		return $this->interval( $newer_date - $older_date );
	}
	public function interval( $since ) {
		// array of time period chunks
		$chunks = array(
			array(60 * 60 * 24 * 365 , _n_noop('%s year', '%s years', 'WooZone')),
			array(60 * 60 * 24 * 30 , _n_noop('%s month', '%s months', 'WooZone')),
			array(60 * 60 * 24 * 7, _n_noop('%s week', '%s weeks', 'WooZone')),
			array(60 * 60 * 24 , _n_noop('%s day', '%s days', 'WooZone')),
			array(60 * 60 , _n_noop('%s hour', '%s hours', 'WooZone')),
			array(60 , _n_noop('%s minute', '%s minutes', 'WooZone')),
			array( 1 , _n_noop('%s second', '%s seconds', 'WooZone')),
		);


		if( $since <= 0 ) {
			return __('now', 'woozone');
		}

		// we only want to output two chunks of time here, eg:
		// x years, xx months
		// x days, xx hours
		// so there's only two bits of calculation below:

		// step one: the first chunk
		for ($i = 0, $j = count($chunks); $i < $j; $i++)
			{
			$seconds = $chunks[$i][0];
			$name = $chunks[$i][1];

			// finding the biggest chunk (if the chunk fits, break)
			if (($count = floor($since / $seconds)) != 0)
				{
				break;
				}
			}

		// set output var
		$output = sprintf(_n($name[0], $name[1], $count, 'WooZone'), $count);

		// step two: the second chunk
		if ($i + 1 < $j)
			{
			$seconds2 = $chunks[$i + 1][0];
			$name2 = $chunks[$i + 1][1];

			if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0)
				{
				// add to output var
				$output .= ' '.sprintf(_n($name2[0], $name2[1], $count2, 'WooZone'), $count2);
				}
			}

		return $output;
	}

	private function _sync_currentlist_last_bulk() {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget']}";

		$query = "select a.ID from $table as a where 1=1 order by ID desc limit 1;";
		$res = $wpdb->get_var( $query );
		return ! empty($res) ? (int) $res : 0;
	}

	private function _sync_currentlist_nb_bulks() {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget']}";

		$query = "select count(a.ID) as nb from $table as a where 1=1;";
		$res = $wpdb->get_var( $query );
		return ! empty($res) ? (int) $res : 0;
	}

	private function _sync_currentlist_nb_parsed() {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget']}";

		$query = "select count(a.ID) as nb from $table as a where 1=1 and status != 'initial';";
		$res = $wpdb->get_var( $query );
		return ! empty($res) ? (int) $res : 0;
	}

	private function _syncwidget_last_updated_bulk() {

		global $wpdb;

		$table = "{$wpdb->prefix}{$this->sync_tables['amz_sync_widget']}";

		$query = "select a.ID from $table as a where 1=1 and status != 'initial' order by ID desc limit 1;";
		$res = $wpdb->get_var( $query );
		return ! empty($res) ? (int) $res : 0;
	}

	private function sync_stats_get() {

		$ss = $this->sync_settings;

		$recurrence = defined('WOOZONE_SYNCWIDGET_RECURRENCE') ? WOOZONE_SYNCWIDGET_RECURRENCE : 86400;
		$recurrence_sec = $recurrence; //(int) ( $recurrence * 3600 );

		//:: sync stats
		$optionsList = array(
			'WooZone_sync_cycle_stats' => array(),
			'WooZone_sync_last_updated_bulk' => $this->_syncwidget_last_updated_bulk(),
			'WooZone_sync_first_updated_date' => false,

			'WooZone_sync_currentlist_last_bulk' => $this->_sync_currentlist_last_bulk(),
			'WooZone_sync_currentlist_nb_bulks' => $this->_sync_currentlist_nb_bulks(),
			'WooZone_sync_currentlist_nb_parsed' => $this->_sync_currentlist_nb_parsed(),
		);
		foreach ($optionsList as $opt_key => $opt_val) {
			$opt_key_ = str_replace('WooZone_', '', $opt_key);
			$$opt_key_ = get_option( str_replace('_sync_', '_syncwidget_', $opt_key), $opt_val );
			if ( empty($$opt_key_) ) {
				$$opt_key_ = $opt_val;
			}
			//var_dump('<pre>',$$opt_key_ ,'</pre>');
		}
		//echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		//:: current sync cycle/ find duration of the cron
		$sync_start_time = $sync_first_updated_date;

		$sync_duration = $sync_cycle_stats;
		$sync_duration2 = 0;
		if ( isset($sync_duration['start_time']) && ! empty($sync_duration['start_time']) ) {
			$sync_start_time = $sync_duration['start_time'];
		}
		if ( ! isset($sync_duration['end_time']) || empty($sync_duration['end_time']) ) {
			$sync_duration['end_time'] = time();
		}
		if ( isset($sync_duration['start_time'], $sync_duration['end_time'])
			&& ! empty($sync_duration['start_time'])
			&& $sync_duration['end_time'] > $sync_duration['start_time'] ) {
			//$sync_duration2 = $sync_duration['end_time'] - $sync_duration['start_time'];
			$sync_duration2 = $this->time_since( (int) $sync_duration['start_time'], (int) $sync_duration['end_time']);
		}


		//:: current sync cycle/ remained products to be synced
		$sync_nb_all_bulks = $sync_currentlist_nb_bulks;
		if ( ! $sync_nb_all_bulks ) {
			$sync_nb_remained_bulks = $sync_nb_all_bulks;
		}
		else if ( ! $sync_currentlist_nb_parsed ) {
			$sync_nb_remained_bulks = $sync_nb_all_bulks;
		}
		else {
			$sync_nb_remained_bulks = (int) ( $sync_nb_all_bulks - $sync_currentlist_nb_parsed );
		}

		//:: current sync cycle/ cron status & text
		$sync_status = 0; // in progress
		$sync_status_text = __('in progress', 'woozone');
		if ( empty($sync_currentlist_nb_bulks)  ) {

			$sync_status = 2; // not initialized yet.
			$sync_status_text = __('to be initialized', 'woozone');
		} else if ( $sync_last_updated_bulk >= $sync_currentlist_last_bulk && ! $sync_nb_remained_bulks ) {

			$sync_status = 1; // success
			$sync_status_text = __('completed', 'woozone');
		}


		$opparsed = $this->sync_find_parsed_percent( array(
			'step' => 1,
			'sync_status' => $sync_status,
			'sync_nb_remained_bulks' => $sync_nb_remained_bulks,
			'sync_nb_all_bulks' => $sync_nb_all_bulks,
		));
		extract( $opparsed );


		//:: next sync cycle/ estimated time to sync all products in the cycle based on products number and products per request setting
		$nextsync_start_time = !empty($sync_start_time) ? $sync_start_time + $recurrence_sec : false;

		if ( !empty($sync_currentlist_nb_bulks) ) {

			$nextsync_start_time2 = ceil( $sync_currentlist_nb_bulks );
			// 1 minute * 60 seconds per minute
			$nextsync_start_time2 = $nextsync_start_time2 * 60;
			$nextsync_start_time2 = $sync_start_time + $nextsync_start_time2;
			//var_dump('<pre>', $recurrence_sec, $nextsync_start_time, $nextsync_start_time2 , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			if ( $nextsync_start_time2 > $nextsync_start_time ) {
				$nextsync_start_time = $nextsync_start_time2;
			}
		}


		// //:: deleted / moved to trash - products in this cycle
		// $current_prods_trashed = $sync_currentlist_prod_trashed;
		// $current_prods_trashed_nb = count($current_prods_trashed);

		// $percent_trashed = '0';
		// if ( $current_prods_trashed_nb && $sync_nb_all_products ) {
		// 	$percent_trashed = ( $current_prods_trashed_nb * 100 ) / $sync_nb_all_products;
		// 	$percent_trashed = floor( $percent_trashed );
		// 	$percent_trashed = number_format($percent_trashed, 0);
		// }

		// //:: marked as not found (sync tries) - products in this cycle
		// $current_prods_notfound = $sync_currentlist_prod_trash_tries;
		// $current_prods_notfound_nb = count($current_prods_notfound);

		// $percent_notfound = '0';
		// if ( $current_prods_notfound_nb && $sync_nb_all_products ) {
		// 	$percent_notfound = ( $current_prods_notfound_nb * 100 ) / $sync_nb_all_products;
		// 	$percent_notfound = floor( $percent_notfound );
		// 	$percent_notfound = number_format($percent_notfound, 0);
		// }

		return compact(
			'recurrence',
			'recurrence_sec',
			'sync_start_time',
			'sync_duration', 'sync_duration2',
			'sync_currentlist_last_bulk',
			'sync_last_updated_bulk',
			'sync_status', 'sync_status_text',
			'nextsync_start_time', 'nextsync_start_time2',
			'sync_currentlist_nb_bulks',
			'sync_nb_remained_bulks',
			'sync_nb_all_bulks',
			'text_sync_prods',
			'parsed_percent'
			//'current_prods_trashed', 'current_prods_trashed_nb', 'percent_trashed',
			//'current_prods_notfound', 'current_prods_notfound_nb', 'percent_notfound',
		);
	}

	private function sync_stats_build_html() {

		extract( $this->sync_stats_get() );
		$ss = $this->sync_settings;

		ob_start();
?>

<div class="WooZone-sync-cycle-header">
<ul>
	<li>
		<?php
			echo __('Started on: ', 'woozone');
			if ( !empty($sync_start_time) ) {
				echo '<span>' . $this->the_plugin->last_update_date('true', $sync_start_time) . '</span>';
			}
		?>
	</li>
	<li>
		<?php
			echo __('Current Sync Cycle: ', 'woozone');
			echo '<span class="WooZone-sync-cycle-main-status WooZone-message ' . ( $sync_status == 1 ? 'WooZone-success' : 'WooZone-info' ) . '">' . $sync_status_text . '</span>';
		?>
	</li>
	<li>
		<?php
			echo __('Duration: ', 'woozone');
			if ( !empty($sync_duration2) ) {
				echo '<span>' . $sync_duration2 . '</span>';
			}
		?>
	</li>
</ul>
</div>

<div class="WooZone-sync-process-progress-bar im-products">
<div class="WooZone-sync-process-progress-marker" style="width: <?php echo $parsed_percent; ?>%;"></div>
<div class="WooZone-sync-process-progress-percent">
	<div class="WooZone-sync-process-progress-circle-wrapp">
		<div class="WooZone-sync-process-progress-circle">
			<span><?php echo $parsed_percent; ?>%</span>
		</div>
	</div>
	<div class="WooZone-sync-process-progress-info">
		<div>
			<div>
				<div>
					<?php
						echo sprintf( __('%d remained to be synced from %d total bulks', 'woozone'), $sync_nb_remained_bulks, $sync_nb_all_bulks );
					?>
				</div>
				<div>
					<?php
						echo sprintf( __('ID Last bulk in cycle: %d', 'woozone'), $sync_currentlist_last_bulk );
					?>
				</div>
				<div>
					<?php
						echo sprintf( __('ID Last synced bulk: %d', 'woozone'), $sync_last_updated_bulk );
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php /*<div class="WooZone-sync-process-progress-text">
	<span><?php _e('Progress', $this->the_plugin->localizationName); ?>: <span>0%</span></span>
	<span><?php _e('Parsed', $this->the_plugin->localizationName); ?>: <span></span></span>
	<span><?php _e('Elapsed time', $this->the_plugin->localizationName); ?>: <span></span></span>
</div>*/ ?>
</div>

<div class="WooZone-sync-cycle-footer">
<ul>
	<li><?php _e('Products that are added after the current cycle has begun are not included in the current syncing process', $this->the_plugin->localizationName); ?></li>
	<li><?php //_e('item = product (simple or variable) or just a variation child', $this->the_plugin->localizationName); ?></li>
</ul>
</div>

<?php
		return ob_get_clean();
	}

	public function sync_stats( $with_wrapper=true ) {

		extract( $this->sync_stats_get() );
		$ss = $this->sync_settings;

		ob_start();

?>

	<?php if ( $with_wrapper ) { ?>
	<div class="WooZone-sync-stats" data-what="mainstats">
		<h3><?php _e('Synchronization Additional Info', $this->the_plugin->localizationName);?></h3>
	<?php } ?>

		<table>
			<thead>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>
				<tr>
					<td colspan="2">
						<?php echo $this->sync_stats_build_html(); ?>
					</td>
				</tr>
				<tr class="WooZone-sync-info-next-cycle">
					<td width="105%">
						<span class="title"><?php _e('Next Sync Cycle', $this->the_plugin->localizationName);?></span>
						<ul>
							<?php if ( !empty($nextsync_start_time) ) { ?>
							<li>
								<?php _e('Estimated Start - Date / Time', $this->the_plugin->localizationName);?>:
								<span><?php
									echo $this->the_plugin->last_update_date('true', $nextsync_start_time);
								?></span><br />
								<?php //_e('depends on last sync cycle start time', $this->the_plugin->localizationName);?>
							</li>
							<?php } else { ?>
							<li>
								<?php _e('not available yet.', $this->the_plugin->localizationName);?>
							</li>
							<?php } ?>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>

	<?php if ( $with_wrapper ) { ?>
	</div>
	<?php } ?>

<?php
		return ob_get_clean();
	}

	public function sync_find_parsed_percent( $pms=array() ) {

		$pms = array_replace_recursive(array(
			'step' => 1,
			'sync_status' => '',
			'sync_nb_remained_bulks' => '',
			'sync_nb_all_bulks' => '',
		), $pms);
		extract( $pms );

		if ( 1 == $sync_status ) {
			$sync_nb_remained_bulks = 0;
		}

		$parsed_percent = '0';
		if ( $sync_nb_remained_bulks <= 0 ) {
			$parsed_percent = '100';
			$sync_nb_remained_bulks = 0;
		}
		else if ( $sync_nb_all_bulks <= 0 ) {
			$parsed_percent = '0';
			$sync_nb_remained_bulks = 0;
			$sync_nb_all_bulks = 0;
		}
		else {
			$parsed_percent = ( ( $sync_nb_all_bulks - $sync_nb_remained_bulks ) * 100 ) / $sync_nb_all_bulks;
			$parsed_percent = floor( $parsed_percent );
			$parsed_percent = number_format($parsed_percent, 0);
		}

		if ( (float) $parsed_percent > 100 ) {
			$parsed_percent = '100';
		}
		//var_dump('<pre>', $parsed_percent , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if (1) {
			$text_sync_prods = sprintf( __('Estimated %s remained bulks of a %s total to be synced in current cycle (we do not include products added after the current cycle started)', 'woozone'), '<span>' . $sync_nb_remained_bulks . '</span>', '<span>' . $sync_nb_all_bulks . '</span>' );
		}

		if (1) {
			return array(
				'text_sync_prods' => $text_sync_prods,
				'parsed_percent' => $parsed_percent,
				'sync_nb_remained_bulks' => $sync_nb_remained_bulks,
				'sync_nb_all_bulks' => $sync_nb_all_bulks,
			);
		}
	}

} } // end class

// Initialize the WooZoneNoAwsSyncWidget class
$WooZoneNoAwsSyncWidget = WooZoneNoAwsSyncWidget::getInstance();
