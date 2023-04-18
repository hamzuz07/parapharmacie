<?php
/*
* Define class WooZoneImportStats
* Make sure you skip down to the end of this file, as there are a few$
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;

if (class_exists('WooZoneImportStats') != true) {
	class WooZoneImportStats
	{
		/*
		* Some required plugin information
		*/
		const VERSION = '1.0';

		/*
		* Store some helpers config
		*/
		public $the_plugin = null;

		private $module_folder = '';
		private $module_folder_path = '';
		private $module = '';

		static protected $_instance;
		
		public $localizationName;
		
		private $settings;

		public $providers_countries = null;

		
		/*
		 * Required __construct() function that initalizes the AA-Team Framework
		 */
		public function __construct( $is_cron=false )
		{
			//return false; // DEACTIVATED
			global $WooZone;

			$this->the_plugin = $WooZone;

			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/import_stats/';
			$this->module_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/import_stats/';
			$this->module = $this->the_plugin->cfg['modules']['import_stats'];
			
			$this->localizationName = $this->the_plugin->localizationName;
			
			$this->settings = $this->the_plugin->settings();

			$this->providers_countries = $this->the_plugin->providers_get_countries();

			if (is_admin() && !$is_cron) {
				add_action('admin_menu', array( &$this, 'adminMenu' ));
			}
			
			// ajax requests
			//add_action('wp_ajax_WooZone_ImportStatsAjax', array( &$this, 'ajax_request' ), 10, 2);
		}

		/**
		 * Singleton pattern
		 *
		 * @return WooZoneImportStats Singleton instance
		 */
		static public function getInstance()
		{
			if (!self::$_instance) {
				self::$_instance = new self;
			}

			return self::$_instance;
		}

		/**
		* Hooks
		*/
		static public function adminMenu()
		{
		   self::getInstance()
				->_registerAdminPages();
		}

		/**
		* Register plug-in module admin pages and menus
		*/
		protected function _registerAdminPages()
		{
			// import stats
			add_submenu_page(
				$this->the_plugin->alias,
				$this->the_plugin->alias . " " . __('Import Stats', $this->the_plugin->localizationName),
				__('Import Stats', $this->the_plugin->localizationName),
				'manage_options',
				$this->the_plugin->alias . "_import_stats",
				array($this, 'printInterface_stats')
			);

			return $this;
		}


		/*
		 * Queue - printBaseInterface, method
		 */
		public function printInterface_stats()
		{
			$ss = $this->settings;

			$module = 'import_stats';
			$mod_vars = array();

			// Auto Import
			$mod_vars['mod_menu'] = 'import|insane_import';
			$mod_vars['mod_title'] = __('Import Stats', $this->the_plugin->localizationName);
			extract($mod_vars);
			
			$module_data = $this->the_plugin->cfg['modules']["$module"];
			$module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . "modules/$module/";
		?>
			<!-- simplemodal -->
			<?php echo WooZone_asset_path( 'css', $this->the_plugin->cfg['paths']['freamwork_dir_url'] . 'js/jquery.simplemodal/basic.css', false ); ?>
			<!-- preload the images -->
			<div style='display:none'><img src='<?php echo $this->the_plugin->cfg['paths']['freamwork_dir_url'] . "js/jquery.simplemodal/x.png"; ?>' alt='' /></div>

			<!-- current module -->
			<?php echo WooZone_asset_path( 'js', $this->module_folder . 'app.import_stats.js', false ); ?>
			<style type="text/css">
				.WooZone-list-table-left-col {
					width: 64%;
				}
				.WooZone-list-table-right-col {
					width: 34%;
				}
			</style>
			
		<div id="<?php echo WooZone()->alias?>">
			<div id="WooZone-wrapper" class="<?php echo WooZone()->alias?>-content">
			
			<?php
			// show the top menu
			WooZoneAdminMenu::getInstance()->make_active($mod_menu)->show_menu(); 
			?>
			
			<?php
				// Lang Messages
				$lang = array(
					'loading'                   => __('Loading...', 'WooZone'),
					'closing'                   => __('Closing...', 'WooZone'),
				); 
			?>
			<!-- Lang Messages -->
			<div id="WooZone-lang-translation" style="display: none;"><?php echo htmlentities(json_encode( $lang )); ?></div>
			
			 <!-- Content -->
			<section class="WooZone-main">

			
				<?php 
				echo WooZone()->print_section_header(
					$module_data["$module"]['menu']['title'],
					$module_data["$module"]['description'],
					$module_data["$module"]['help']['url']
				);
				?>
				
				<div class="panel panel-default WooZone-panel">
					
<?php
	if (1) {

		$providers = $this->the_plugin->providers_is_enabled();
		foreach ( $providers as $provider ) {

			$provider_status = $this->the_plugin->provider_action_controller( 'is_process_allowed', $provider, array() );
			if ( 'invalid' == $provider_status['status'] ) {
				echo $provider_status['msg_html'];
			}
		}
?>

					<div class="panel-heading WooZone-panel-heading">
						<h2><?php echo $mod_title; ?></h2>
					</div>
					
					<div class="panel-body WooZone-panel-body">

						<!-- Content Area -->
						<div id="WooZone-content-area">
							<div class="WooZone-grid_4">
								<div class="WooZone-panel">

										<form class="WooZone-form" action="#save_with_ajax">
											<div class="WooZone-form-row WooZone-table-ajax-list" id="WooZone-table-ajax-response">
												
											<?php
											WooZoneAjaxListTable::getInstance( $this->the_plugin )
												->setup(array(
													'id' 				=> 'WooZoneImportStats',
													'show_header' 		=> true,
													'show_header_buttons' => true,
													'items_per_page' 	=> '10',
													'custom_table'		=> 'amz_import_stats',
													'orderby'			=> 'id',
													'order'				=> 'DESC',
													'filter_fields'		=> array(
														'provider' => array(
															'title' 			=> __('Provider', $this->the_plugin->localizationName),
															'options_from_db' 	=> true,
															'include_all'		=> true,
														),
														'country'  => array(
															'title' 			=> __('Country', $this->the_plugin->localizationName),
															'options_from_db' 	=> true,
															'include_all'		=> true,
															//'options'			=> $this->providers_countries->countries_allprov,
															'_options_extra' 	=> array(
																'aliases' 			=> $this->providers_countries->countries_allprov,
																'show_latest' 		=> false,
															),
														),
														'from_op' => array(
															'title' 			=> __('From', $this->the_plugin->localizationName),
															'options_from_db' 	=> true,
															'include_all'		=> true,
															'_options_extra' 	=> array(
																'aliases' 			=> array(
																	'auto'				=> array(
																		'title' => __('Async products import', $this->the_plugin->localizationName),
																		'type' 	=> 'timestamp',
																	),
																	'search'			=> array(
																		'title' => __('Auto Import Search ID', $this->the_plugin->localizationName),
																		'type' 	=> '',
																	),
																	'direct'			=> array(
																		'title' => __('Direct Import', $this->the_plugin->localizationName),
																		'type' 	=> 'date',
																	),
																	'insane'			=> array(
																		'title' => __('Insane Import', $this->the_plugin->localizationName),
																		'type' 	=> 'timestamp',
																	),
																),
																'show_latest' 		=> true,
															),
														),
														'from_op_p1'  => array(
															'title' 			=> __('From', $this->the_plugin->localizationName),
															'options_from_db' 	=> false,
															'include_all'		=> true,
															'options'			=> array(
																'auto'				=> __('Auto Import Queue', $this->the_plugin->localizationName),
																'search'			=> __('Auto Import Search', $this->the_plugin->localizationName),
																'direct'			=> __('Direct Import', $this->the_plugin->localizationName),
																'insane'			=> __('Insane Import', $this->the_plugin->localizationName),
															),
															'display'			=> 'links',
														),
													),
													'search_box'		=> array(
														'title' 	=> __('Search ASIN', $this->the_plugin->localizationName),
														'fields'	=> array('asin'),
													),
													'columns'			=> array(

														'checkbox'	=> array(
															'th'	=>  'checkbox',
															'td'	=>  'checkbox',
														),

														'id'		=> array(
															'th'	=> __('ID', $this->the_plugin->localizationName),
															'td'	=> '%ID%',
															'width' => '40'
														),
														
														/*'thumb'		=> array(
															'th'	=> __('Thumb', $this->the_plugin->localizationName),
															'td'	=> '%thumb%',
															'align' => 'center',
															'width' => '50'
														),
														
														'asin'		=> array(
															'th'	=> __('ASIN', $this->the_plugin->localizationName),
															'td'	=> '%asin%',
															'align' => 'center',
															'width' => '70'
														),
														
														'from_op'		=> array(
															'th'	=> __('From', $this->the_plugin->localizationName),
															'td'	=> '%from_op%',
															'align' => 'center',
															'width' => '250'
														),

														'product'		=> array(
															'th'	=> __('Product', $this->the_plugin->localizationName),
															'td'	=> '%product_links%',
															'align' => 'left',
															'width' => '120'
														),*/
														'asin_with_details'		=> array(
															'th'	=> __('Product Title <br /> ASIN || From || Actions', $this->the_plugin->localizationName),
															'td'	=> '%asin_with_details%',
															'align' => 'center',
															'width' => '250'
														),

														/*'status'		=> array(
															'th'	=> __('Status', $this->the_plugin->localizationName),
															'td'	=> '%status%',
															'align' => 'left',
															'width' => '120'
														),*/
														'duration'		=> array(
															'th'	=> __('Duration', $this->the_plugin->localizationName),
															'td'	=> '%duration%',
															'align' => 'left',
															'width' => '300'
														),
														'db_calc'		=> array(
															'th'	=> __('Database stats', $this->the_plugin->localizationName),
															'td'	=> '%db_calc%',
															'align' => 'left',
															'width' => '200'
														),

														'imported_date'		=> array(
															'th'	=> __('Imported Date', $this->the_plugin->localizationName),
															'td'	=> '%imported_date%',
															'width' => '120'
														),
														
														/*'delete_btn' => array(
															'th'	=> __('Delete', $this->the_plugin->localizationName),
															'td'	=> '%button%',
															'option' => array(
																'action' => 'do_item_delete',
																'value' => __('Delete row', $this->the_plugin->localizationName),
																'color' => 'WooZone-form-button-small WooZone-button WooZone-form-button-danger'
															),
															'width' => '60'
														),
														
														'publish_btn' => array(
															'th'	=> __('Active', 'psp'),
															'td'	=> '%button_publish%',
															'option' => array(
																'action' => 'do_item_publish',
																'value' => __('Unpublish', 'psp'),
																'color'	=> 'orange',
																'value_change' => __('Publish', 'psp'),
																'color_change' => 'green',
															),
															'width' => '60'
														),*/
													),
													'mass_actions' 	=> array(
														/*'delete_all' => array(
															'value' => __('Delete all rows', 'psp'),
															'action' => 'do_bulk_delete_rows',
															'color' => 'WooZone-form-button-small WooZone-form-button-danger'
														),*/
													),
												))
												->print_html();
											?>
											</div>
										</form>
									</div>
									
								</div>
							</div>
							<div class="clear"></div>
						</div>
						
<?php } // end demo keys ?>

					</div>
				</section>
			</div>
		</div>
		<?php
		}
	}
}

// Initialize the WooZoneImportStats class
$WooZoneImportStats = WooZoneImportStats::getInstance();