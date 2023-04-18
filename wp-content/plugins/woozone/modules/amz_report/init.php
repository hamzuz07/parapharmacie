<?php
/*
* Define class WooZoneAmzReport
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;

if (class_exists('WooZoneAmzReport') != true) { class WooZoneAmzReport {

	const VERSION = '1.0';

	static protected $_instance;

	public $the_plugin = null;

	private $module_folder = '';
	private $module = '';

	public $timer; // timer object



	// Required __construct() function that initalizes the AA-Team Framework
	public function __construct()
	{
		global $WooZone;

		$this->the_plugin = $WooZone;
		$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/amz_report/';
		$this->module = $this->the_plugin->cfg['modules']['amz_report'];

		if ( $this->the_plugin->is_admin ) {
			add_action('admin_menu', array( $this, 'adminMenu' ));
		}

		add_action( 'wp_ajax_WooZoneAmzReport', array( $this, 'ajax_request' ) );
	}



	//====================================================================================
	//== AJAX REQUEST
	//====================================================================================

	public function ajax_request() {

		$requestData = array(
			'action' 	=> isset($_REQUEST['sub_action']) ? (string) $_REQUEST['sub_action'] : '',
		);
		extract($requestData);
		//var_dump('<pre>', $requestData , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$ret = array(
			'status' => 'invalid',
			'msg' => 'Invalid action!',
		);

		if ( empty($action) || !in_array($action, array(
			'add_product',
		)) ) {
			die(json_encode($ret));
		}

		//:: actions
		switch ( $action ) {


		}

		die( json_encode( $ret ) );
	}


	// Hooks
	public function adminMenu()
	{
	   self::getInstance()
			->_registerAdminPages();

		if( isset($_GET['page']) && $_GET['page'] == $this->the_plugin->alias . "_amz_report" ) {
			wp_enqueue_script(
				'WooZone/amz_report',
				$this->module_folder . 'assets/app.build.amz_report.js',
				array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-dom-ready' ),
				//'1.0&time=' . time(),
				'1.0',
				true
			);

			wp_localize_script( 'WooZone/amz_report', 'WooZoneAmzReport', array(
				'assets_url' => $this->module_folder,
				'settings' => get_option( 'WooZone_amazon' ),
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
			) );

			wp_enqueue_style(
				'WooZone/amz_report',
				$this->module_folder . 'assets/app.amz_report.css'
			);
		}
	}

	// Register plug-in module admin pages and menus
	protected function _registerAdminPages()
	{
		add_submenu_page(
			$this->the_plugin->alias,
			$this->the_plugin->alias . " " . __('Associates Reports', $this->the_plugin->localizationName),
			__('Associates Reports', $this->the_plugin->localizationName),
			'manage_options',
			$this->the_plugin->alias . "_amz_report",
			array($this, 'printBaseInterface')
		);

		return $this;
	}

	public function printBaseInterface()
	{
		global $wpdb;
?>
	<div id="<?php echo WooZone()->alias?>" class="WooZone-direct-import">

		<div class="<?php echo WooZone()->alias?>-content">

			<?php
			// show the top menu
			WooZoneAdminMenu::getInstance()->make_active('import|amz_report')->show_menu();
			?>

			<!-- Content -->
			<section class="WooZone-main">

				<?php
				echo WooZone()->print_section_header(
					$this->module['amz_report']['menu']['title'],
					$this->module['amz_report']['description'],
					$this->module['amz_report']['help']['url']
				);
				?>
				<div id="<?php echo WooZone()->alias?>-AMZReport-wrapper"></div>
			</section>
		</div>
	</div>

<?php
	}

	private function get_importin_category() {
		$args = array(
			'orderby'   => 'menu_order',
			'order'     => 'ASC',
			'hide_empty' => 0,
			'post_per_page' => '-1'
		);
		$categories = get_terms('product_cat', $args);

		$args = array(
			'show_option_all'    => '',
			'show_option_none'   => 'Use category from (Amazon)',
			'orderby'            => 'ID',
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0,
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => 0,
			'hierarchical'       => 1,
			'name'               => 'WooZone-to-category',
			'id'                 => 'WooZone-to-category',
			'class'              => 'postform',
			'depth'              => 0,
			'tab_index'          => 0,
			'taxonomy'           => 'product_cat',
			'hide_if_empty'      => false,
		);

		return wp_dropdown_categories( $args );
	}

	// Singleton pattern
	static public function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function timer_start() {
		$this->timer->start();
	}
	public function timer_end( $debug=false ) {
		$this->timer->end( $debug );
		$duration = $this->timer->getRenderTime(1, 0, false);
		return $duration;
	}

	public function format_duration( $duration, $precision=1 ) {
		$prec = $this->timer->getUnit( $precision );
		$ret = $duration . ' ' . $prec;
		$ret = '<i>' . $ret . '</i>';
		return $ret;
	}

} } // end class

// Initialize the WooZoneAmzReport class
$WooZoneAmzReport = WooZoneAmzReport::getInstance();
