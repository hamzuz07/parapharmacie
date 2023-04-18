<?php
/*
* Define class WooZoneNoAwsImport
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;

require_once( WOOZONE_ABSPATH . 'composer/amazon-scraper/vendor/autoload.php' );

use WooZone\AmazonScraper\ProductExtract\ProductExtract;
use WooZone\AmazonScraper\ProductExtract\ProductExtractException;

if (class_exists('WooZoneNoAwsImport') != true) { class WooZoneNoAwsImport {

	const VERSION = '1.0';

	static protected $_instance;

	public $the_plugin = null;

	private $module_folder = '';
	private $module = '';

	public $timer; // timer object

	private $module_version = '1.0.1'; // from wp.root/react-sources/.../package.json
	private $module_debug = true;


	// Required __construct() function that initalizes the AA-Team Framework
	public function __construct()
	{
		global $WooZone;

		$this->the_plugin = $WooZone;
		$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/noaws_import/';
		$this->module = $this->the_plugin->cfg['modules']['noaws_import'];

		if ( $this->the_plugin->is_admin ) {
			add_action('admin_menu', array( $this, 'adminMenu' ));
		}

		add_action( 'wp_ajax_WooZoneNoAWSImport', array( $this, 'ajax_request' ) );
		add_action( 'wp_ajax_nopriv_WooZoneNoAWSImport', array( $this, 'ajax_request' ) );

		// timer functions
		require_once( $this->the_plugin->cfg['paths']['scripts_dir_path'] . '/runtime/runtime.php' );
		if( class_exists('aaRenderTime') ){
			$this->timer = new aaRenderTime( $this );
		}
	}



	//====================================================================================
	//== AJAX REQUEST
	//====================================================================================

	public function ajax_request() {

		$requestData = array(
			'action' 		=> isset($_REQUEST['sub_action']) ? (string) $_REQUEST['sub_action'] : '',
			'debug_step' 	=> isset($_REQUEST['debug_step']) ? (string) $_REQUEST['debug_step'] : '',
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

			case 'add_product':

				$duration = array();

				// extract product data
				$timer_start = $this->timer_start();
  				if( isset($_REQUEST['from']) && $_REQUEST['from'] == 'extension_amz_page' ){
  					$request = $this->the_plugin->wp_filesystem->get_contents( 'php://input' );
					if ( ! $product ) {
						$request = file_get_contents( 'php://input' );
					}
					$request = json_decode( $request, true );

					$_REQUEST = array_merge($_REQUEST, $request);
  					
  					// chrome.runtime.sendMessage dont' add slashes
  					$_REQUEST['variations'] = addslashes($_REQUEST['variations']);
  				}
				  
				$pmsGetProduct = array(
					'debug_step' 	=> $debug_step,
					'page_content' 	=> isset($_REQUEST['page_content']) ? (string) $_REQUEST['page_content'] : null,
					'asin' 			=> isset($_REQUEST['asin']) ? (string) $_REQUEST['asin'] : null,
					'country' 		=> isset($_REQUEST['country']) ? (string) $_REQUEST['country'] : null,
					'variations' 	=> isset($_REQUEST['variations']) ? (string) $_REQUEST['variations'] : null,
				);
  				
				
				if ( 'get_params' === $debug_step ) {
					var_dump('<pre>', $pmsGetProduct , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
				}

				$opGetProduct = $this->_product_extract_data( $pmsGetProduct );
				if ( 'extract_data' === $debug_step ) {
					var_dump('<pre>', $opGetProduct , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
				}
				$ret = array_replace_recursive( $ret, $opGetProduct, array( 'msg_full' => '', 'msg_summary' => '', 'msg_arr' => array() ) );
				//die( json_encode( $ret ) ); //DEBUG
				unset( $ret['data'] );

				$timer_end = $this->timer_end();
				$duration["_product_extract_data"] = $timer_end;

				if ( 'invalid' === $opGetProduct['status'] ) {
					break;
				}

				// import product
				$timer_start = $this->timer_start();

				$pmsImportProduct = array(
					//'avi_nbvars' 	=> isset($_REQUEST['avi_nbvars']) ? (int) $_REQUEST['avi_nbvars'] : 1,
					'idcateg' 		=> isset($_REQUEST['idcateg']) ? (int) $_REQUEST['idcateg'] : 0,
					'nbimages' 		=> isset($_REQUEST['nbimages']) ? (string) $_REQUEST['nbimages'] : 'all',
					'nbvariations' 	=> isset($_REQUEST['nbvariations']) ? (string) $_REQUEST['nbvariations'] : 5,
					'spin' 			=> isset($_REQUEST['spin']) ? (int) $_REQUEST['spin'] : 0,
					'attributes' 	=> isset($_REQUEST['attributes']) ? (int) $_REQUEST['attributes'] : 1,
				);
				//var_dump('<pre>', $pmsImportProduct , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
				//var_dump('<pre>', $opGetProduct['data'] , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
				$opImportProduct = $this->_product_import( $opGetProduct['data'], $pmsImportProduct );
				$ret = array_replace_recursive( $ret, $opImportProduct );
				//die( json_encode( $ret ) ); //DEBUG

				$timer_end = $this->timer_end();
				$duration["_product_import"] = $timer_end;

				foreach ( $duration as $kk => $vv ) {
					$duration["$kk"] = $this->the_plugin->format_duration( $vv );
				}

				$ret = array_replace_recursive( $ret, array('duration' => $duration) );

				break;
		}

		die( json_encode( $ret ) );
	}

	private function _product_extract_data( $pms=array() ) {

		$pms = array_replace_recursive( array(
			'debug_step' 	=> '',
			'page_content' 	=> null, // product page content
			'asin' 			=> null,
			'country' 		=> null,
			'variations' 	=> null, // widget content (with variations)
		), $pms);
		extract( $pms );

		$ret = array(
			'status' 	=> 'invalid',
			'msg' 		=> 'unknown',
			'data' 		=> null,
		);

		if ( ! is_null($page_content) ) {
			//$page_content = rawurldecode( $page_content );
			//$page_content = html_entity_decode( $page_content, ENT_COMPAT | ENT_HTML401, 'UTF-8' );
			$page_content = html_entity_decode( $page_content );
			$page_content = stripslashes( $page_content );
		}
		if ( 'page_content' === $debug_step ) {
			var_dump('<pre>', $page_content , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		}

		if ( ! is_null($variations) ) {
			//$variations = html_entity_decode( $variations );
			$variations = stripslashes( $variations );
		}
		if ( 'variations' === $debug_step ) {
			var_dump('<pre>', $variations , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		}

		$wzpe = ProductExtract::getInstance( WooZone() );

		$product_data = array();

		try {

			$wzpe
				->set_content( $page_content )
				->set_product_main( array(
					'ASIN' => $asin,
					'ParentASIN' => null,
					'country' => $country,
				));

			if ( 'product_info' === $debug_step ) {
				var_dump('<pre>', sprintf( 'is ebook: %s ; is variable (nb dimensions): %s ; nb of variations: %s ; is variation child: %s ; parent asin: %s', $wzpe->is_ebook(), $wzpe->is_variable(), $wzpe->get_nb_variations(), $wzpe->is_variation(), $wzpe->get_parent_asin() ), '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			}

			$wzpe->set_cfg( array(
				//'measure_duration' => true,
			));
			$wzpe->set_widget_vars( $variations, 'react' );
			
			$product_data = $wzpe->get_product_data();
  
			// [FIX] Zero price on specific amazon page templates with product variations
			// Set default main product price of the lowest variation price
			if( !$product_data['amazon_price'] || trim($product_data['amazon_price']) == '' ) {
				if( is_array( $product_data['variations'] ) && count( $product_data['variations'] ) > 0 ) {
					$lowest_variation_price = 9999999;
					foreach( $product_data['variations'] as $variation ) {
						$get_variation_price = $this->the_plugin->directimport_get_product_price_format($variation['list_price'], $country);  
						if( $get_variation_price['_OnlyPrice'] <= $lowest_variation_price  ) {
							$lowest_variation_price = $get_variation_price['_OnlyPrice'];
						}
					}
					
					$product_data['amazon_price'] = $lowest_variation_price;
				}
			}
			
			//die( var_dump( "<pre>wzone: ", $product_data  , "<pre>" ) . PHP_EOL .  __FILE__ . ":" . __LINE__  ); 
		}
		catch (ProductExtractException $e) {
			$ee = $e->render( WooZoneDebug() );
			if ( 'catch_exception' === $debug_step ) {
				echo $ee; echo __FILE__ . ":" . __LINE__;die . PHP_EOL; // DEBUG
			}
			return array_replace_recursive( $ret, array(
				'msg' 		=> $ee,
			));
		}
		catch (\Exception $e) {
			if ( 'catch_exception' === $debug_step ) {
				echo $e; echo __FILE__ . ":" . __LINE__;die . PHP_EOL; // DEBUG
			}
			return array_replace_recursive( $ret, array(
				'msg' 		=> $e->getMessage(),
			));
		}

		if ( 'catch_exception' === $debug_step ) {
			var_dump('<pre>', 'notices', $wzpe->get_notices(), 'extra details', $wzpe->get_extra_details(), 'product data', $product_data, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		}

		return array_replace_recursive( $ret, array(
			'status' 	=> 'valid',
			'msg' 		=> 'ok',
			'data' 		=> $product_data,
		));
	}

	private function _product_import( $product, $pms=array() ) {

		$pms = array_replace_recursive( array(
			'where_from' 	=> 'module-noawskeys',
			'avi_nbvars' 	=> 1,
			'idcateg' 		=> 0,
			'nbimages' 		=> 'all',
			'nbvariations' 	=> 5,
			'spin' 			=> 0,
			'attributes' 	=> 1,
		), $pms);
		//extract( $pms );

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'msg_arr' => array(),
			'msg_full' => '',
			'msg_summary' => '',
			'product_id' => 0,
			'duration' => 0,
		);

		$opStatus = WooZoneDirectImport()->add_product( $product, $pms );
		$ret['duration_import'] = $ret['duration'];
		unset( $ret['duration'] );
		return array_replace_recursive( $ret, $opStatus );
	}


	// Hooks
	public function adminMenu()
	{
	   self::getInstance()
			->_registerAdminPages();

		if( isset($_GET['page']) && $_GET['page'] == $this->the_plugin->alias . "_no_aws_keys_import" ) {
			
			wp_enqueue_script(
				'WooZone/noaws_import',
				$this->module_folder . 'assets/app.build.noaws_import.js',
				array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-dom-ready' ),
				$this->module_version . ($this->module_debug ? '&time=' . time() : ''), //'1.0',
				true
			);

			wp_localize_script( 'WooZone/noaws_import', 'WooZoneNoAwsKeysImport', array(
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
			) );

			wp_enqueue_style(
				'WooZone/noaws_import',
				$this->module_folder . 'assets/app.noaws_import.css',
				array(),
				$this->module_version . ($this->module_debug ? '&time=' . time() : '') //'1.0',
			);
		}
	}

	// Register plug-in module admin pages and menus
	protected function _registerAdminPages()
	{
		add_submenu_page(
			$this->the_plugin->alias,
			$this->the_plugin->alias . " " . __('No AWS Keys Import', $this->the_plugin->localizationName),
			__('No AWS Keys Import', $this->the_plugin->localizationName),
			'manage_options',
			$this->the_plugin->alias . "_no_aws_keys_import",
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
			WooZoneAdminMenu::getInstance()->make_active('import|noaws_import')->show_menu();
			?>
			
			<!-- Content -->
			<section class="WooZone-main">
				
				<?php 
				echo WooZone()->print_section_header(
					$this->module['noaws_import']['menu']['title'],
					$this->module['noaws_import']['description'],
					$this->module['noaws_import']['help']['url']
				);
				?>
				<div id="<?php echo WooZone()->alias?>-NAWS-wrapper"></div>
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
 
// Initialize the WooZoneNoAwsImport class
$WooZoneNoAwsImport = WooZoneNoAwsImport::getInstance();
