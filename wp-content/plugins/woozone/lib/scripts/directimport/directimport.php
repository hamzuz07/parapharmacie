<?php
/*
* Define class WooZoneDebugBar
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
	  
if (class_exists('WooZoneDirectImport') != true) { class WooZoneDirectImport {

	const VERSION = '1.0';
	public $the_plugin = null;

	private $module_folder = '';
	private $module = '';

	static protected $_instance;

	private $plugin_icon_url = '';

	public $localizationName;

	private $settings;

	private $avi_nbvars = 1; // value received from chrome extension by ajax


	// Required __construct() function
	public function __construct( $parent ) {
		//global $WooZone;
		//$this->the_plugin = $WooZone;
		$this->the_plugin = $parent;

		$this->plugin_icon_url = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'assets/icon_16.png';

		$this->localizationName = $this->the_plugin->localizationName;
			
		$this->settings = $this->the_plugin->settings();

		add_action( 'wp_ajax_WooZoneDirectImport', array( $this, 'ajax_request' ) );
		add_action( 'wp_ajax_nopriv_WooZoneDirectImport', array( $this, 'ajax_request' ) );
	}

	// Singleton pattern
	static public function getInstance( $parent ) {
		if (!self::$_instance) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	}



	//====================================================================================
	//== AJAX REQUEST
	//====================================================================================

	public function ajax_request() {

		$requestData = array(
			'action' 	=> isset($_REQUEST['sub_action']) ? (string) $_REQUEST['sub_action'] : '',
			'accesskey' => isset($_REQUEST['accesskey']) ? (string) $_REQUEST['accesskey'] : '',
		);
		extract($requestData);
		//var_dump('<pre>', $requestData , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$ret = array(
			'status' => 'invalid',
			'msg' => 'Invalid action!',
		);

		if ( empty($action) || !in_array($action, array(
			'get_imported_products',
			'get_site_categories',
			'check_if_asin_exists',
			'add_product',
		)) ) {
			die(json_encode($ret));
		}

		//:: validate access key
		$opValidateAccess = $this->validate_accesskey( $accesskey );
		if ( 'invalid' === $opValidateAccess['status'] ) {

			$ret = array_replace_recursive($ret, $opValidateAccess);
			die( json_encode( $ret ) );
		}

		//:: actions
		switch ( $action ) {

			case 'get_imported_products':
				$opStatus = $this->get_imported_products();
				break;

			case 'get_site_categories':
				$opStatus = $this->get_site_categories();
				break;

			case 'check_if_asin_exists':
				$opStatus = $this->check_if_asin_exists( 'php://input' );
				break;

			case 'add_product':
				$opArgs = array(
					//'debug' 		=> false,
					'avi_nbvars' 	=> isset($_REQUEST['avi_nbvars']) ? (int) $_REQUEST['avi_nbvars'] : 1,
					'idcateg' 		=> isset($_REQUEST['idcateg']) ? (int) $_REQUEST['idcateg'] : 0,
					'nbimages' 		=> isset($_REQUEST['nbimages']) ? (string) $_REQUEST['nbimages'] : 'all',
					'nbvariations' 	=> isset($_REQUEST['nbvariations']) ? (string) $_REQUEST['nbvariations'] : 'all',
					'spin' 			=> isset($_REQUEST['spin']) ? (int) $_REQUEST['spin'] : 0,
					'attributes' 	=> isset($_REQUEST['attributes']) ? (int) $_REQUEST['attributes'] : 1,
				);
				$opStatus = $this->add_product( 'php://input', $opArgs );
				break;
		}

		$ret = array_replace_recursive($ret, array( 'msg' => 'ok' ), $opStatus);
		die( json_encode( $ret ) );
	}



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	public function get_imported_products() {

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
		);

		$list = $this->the_plugin->getAllProductsMeta('text', '_amzASIN', true, 'all');
		$list = explode("\n", $list);
		$list = implode(',', $list);

		$ret = array_replace_recursive($ret, array(
			'status' => 'valid',
			'asins_imported' => $list,
		));
		return $ret;
	}

	public function get_site_categories() {

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
		);

		ob_start();
		wp_dropdown_categories( array(
			'taxonomy' => 'product_cat',
			'hierarchical' => 1,
			'hide_empty' => 0,
			'show_option_all' => 'Auto detect categories from Amazon',
			'id' => 'WooZoneDirectImport-dropdown-categ',
		) );

		$dropdown = ob_get_clean();
		//die($dropdown);

		$is_avi = $this->the_plugin->is_plugin_avi_active();

		$ret = array_replace_recursive($ret, array(
			'status' => 'valid',
			'html' => $dropdown,
			'is_avi' => $is_avi ? 'yes' : 'no',
		));
		return $ret;
	}

	// asins = 'php://input' | array()
	// 'php://input' = json received from direct-import chrome extension
	public function check_if_asin_exists( $asins='php://input' ) {

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
		);

		if ( 'php://input' === $asins ) {

			$asins = $this->the_plugin->wp_filesystem->get_contents( 'php://input' );
			if ( ! $asins ) {
				$asins = file_get_contents( 'php://input' );
			}
			$asins = json_decode( $asins, true );
		}
		//var_dump('<pre>', $asins , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if ( empty($asins) || ! is_array($asins) ) {

			$ret = array_replace_recursive($ret, array(
				'msg' => 'no asins received in the request',
			));
			return $ret;
		}


		//:: Temporary disable duplicate products
		//$ret['status'] = 'valid'; return $ret;


		//:: verify if product already is imported?
		$opAsinExist = WooZone_product_by_asin( $asins );

		//var_dump('<pre>', $opAsinExist["$asins[0]"], $asins[0], $opAsinExist , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		if ( isset($opAsinExist["$asins[0]"]) && ! empty($opAsinExist["$asins[0]"]) ) {

			$products_url = array();
			foreach ($opAsinExist as $product) {
				if( isset($product->ID) ){
					$products_url[] = '<a href="' . ( get_permalink( $product->ID ) ) . '" target="_blank">' . $product->ID . "</a>";
				}
			}
			
			$ret = array_replace_recursive($ret, array(
				'msg' => sprintf( 'the product is already imported: %s', implode(" ", $products_url) ),
			));
			return $ret;
		}

		$ret = array_replace_recursive($ret, array(
			'status' => 'valid',
		));
		return $ret;
	}

	// product = ( 'php://input' | array ) - structured as returned by method _product_sanitize_data
	// 'php://input' = json received from direct-import chrome extension
	public function add_product( $product, $pms=array() ) {
		$pms = array_replace_recursive( array(
			// !!! true - only when you know what you're doing on this code
			'debug' 		=> false,

			'where_from' 	=> 'chrome-extension', // chrome-extension | module-noawskeys

			// (integer) number of images per variation child for additional variation images woozone plugin
			'avi_nbvars' 	=> 1,

			// (integer from 0) 0 = use category from amazon (use browse nodes to build a category structure like on amazon)
			'idcateg' 		=> 0,

			// (integer from 1 or string 'all')
			'nbimages' 		=> 'all',

			// (integer from 0 or string 'all')
			'nbvariations' 	=> 'all',

			// (integer 0 | 1)
			'spin' 			=> 0,

			// (integer 0 | 1)
			'attributes' 	=> 1,
		), $pms);
		extract( $pms );

		$ret = array(
			'status' => 'invalid',
			'msg' => '',
			'msg_arr' => array(),
			'msg_full' => '',
			'msg_summary' => '',
			'product_id' => 0,
			'duration' => 0,
		);

		if ( $avi_nbvars < 1 || $avi_nbvars > $this->the_plugin->ss['max_images_per_variation'] ) {
			$avi_nbvars = 1;
			$pms['avi_nbvars'] = $avi_nbvars;
		}
		$this->avi_nbvars = $avi_nbvars;


		if ( 'php://input' === $product ) {
			$product = $this->the_plugin->wp_filesystem->get_contents( 'php://input' );
			if ( ! $product ) {
				$product = file_get_contents( 'php://input' );
			}
			$product = json_decode( $product, true );
		}

		if ( $debug ) {
			//require_once( '_test/product.inc.php' );
			$product = $this->the_plugin->cfg['paths']['scripts_dir_path'] . '/directimport/_test/B0769XD5YC.json';
			$product = file_get_contents( $product );
			$product = json_decode( $product, true );
		}

		//die( var_dump( "<pre>", $product  , "<pre>" ) . PHP_EOL .  __FILE__ . ":" . __LINE__  );


		//:: verify product has an asin?
		$opValidProduct = $this->is_valid_product_asin( $product );
		if ( ! $opValidProduct ) {
			$ret = array_replace_recursive($ret, array(
				'msg' => 'Product ASIN is missing!',
			));
			$ret['msg_summary'] = $ret['msg'];
			return $ret;
		}
		$asin = $product['ASIN'];


		//:: verify if product already is imported?
		$opAsinExist = WooZone_product_by_asin( array($asin) );
		//var_dump('<pre>', $opAsinExist , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if ( isset($opAsinExist["$asin"]) && ! empty($opAsinExist["$asin"]) ) {
		//if (0) { //DEBUG
			$ret = array_replace_recursive($ret, array(
				'msg' => sprintf( 'The Product is Already Imported: ASIN %s already exist(s) in the database!', $asin ),
			));
			$ret['msg_summary'] = $ret['msg'];
			return $ret;
		}


		//:: build & verify product data
		$retProd = $this->build_product_data( $product );
		//var_dump('<pre>', $retProd , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$retProd = $this->the_plugin->get_ws_object( $this->the_plugin->cur_provider )->build_product_data( $retProd );
		//var_dump('<pre>', $retProd , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if ( ! $this->is_valid_product_data($retProd) ) {
			$ret = array_replace_recursive($ret, array(
				'msg' => 'Product data is invalid!',
			));
			$ret['msg_summary'] = $ret['msg'];
			return $ret;
		}


		//:: import product
		$import_args = array_merge( array(), $this->the_plugin->importProdDefaultParams( $this->settings ) );
		$import_args = array_merge( $import_args, array(
			//'country' 				=> $retProd['country'], //not needed, extracted from $retProd
			'ws' 					=> 'amazon',
			'asin' 					=> $this->the_plugin->prodid_set($asin, 'amazon', 'add'),
			'from_op' 				=> 'direct#' . gmdate('Y-m-d'),
			'stop_at_same_title' 	=> true,

			'import_to_category' 	=> $idcateg ? $idcateg : 'amz',

			'import_images' 		=> (int) $nbimages > 0 ? (int) $nbimages : 'all',

			'import_variations' 	=> (string) $nbvariations === '0' ? 'no' : 'yes_' . $nbvariations,

			'spin_at_import' 		=> $spin ? true : false,

			'import_attributes' 	=> $attributes ? true : false,
		));
		//var_dump('<pre>', $import_args , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$import_stat = $this->the_plugin->addNewProduct( $retProd, $import_args );
		$insert_id = (int) $import_stat['insert_id'];

		if ( 'chrome-extension' === $where_from ) {
			update_post_meta( $insert_id, '_amzaff_direct_import', true );
		}
		else if ( 'module-noawskeys' === $where_from ) {
			update_post_meta( $insert_id, '_amzaff_direct_import_noawskeys', true );
		}

		// Successfully adding product in database
		$status_final = true;
		if ( $insert_id ) {

			$ret['status'] = 'valid';
			$ret['product_id'] = $insert_id;

			$ret['msg_summary'] = 'Product was Successfully added into the DB with ID: '. $insert_id.' . Click here to <a href="' . ( get_permalink( $insert_id ) ) . '" target="_blank"> view the product </a>';
			$ret['msg'] = $ret['msg_summary'];

			// download images
			$import_type = 'default';
			if ( isset($this->settings['import_type']) && $this->settings['import_type']=='asynchronous' ) {
				$import_type = $this->settings['import_type' ];
			}

			if ( !empty($import_type) && $import_type=='default' ) {
				if ( !$this->the_plugin->is_remote_images ) {

					// assets download module
					// Initialize the WooZoneAssetDownload class
					require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/assets_download/init.php' );
					$WooZoneAssetDownload = new WooZoneAssetDownload(true);
					//$WooZoneAssetDownload = WooZoneAssetDownload::getInstance();

					$assets_stat = $WooZoneAssetDownload->product_assets_download( $insert_id );
				}
			}
		}
		// Error when trying to insert product in database
		else {
			$status_final = false;
			$ret['msg_summary'] = 'Error trying to add product to database.';
			$ret['msg'] = $ret['msg_summary'];
		}

		//:: FULL MESSAGE WITH DETAILS
		$_msg = array();
		$_msg_arr = array();

		//$opStatusMsg = $this->the_plugin->opStatusMsgGet();
		//$_msg[] = $opStatusMsg['msg'];

		if ( $insert_id ) {
			$_msg[] = '<span style="display: block;height: 0px;"></span>';
		}

		if ( isset($assets_stat) && is_array($assets_stat) && isset($assets_stat['msg']) ) {
			$_msg[] = $assets_stat['msg'];
			$_msg_arr[] = $assets_stat['msg'];
		}

		$_msg[] = $import_stat['msg'];
		$_msg_arr = $_msg_arr + $import_stat['msg_arr'];

		$_msg = implode('<br />', $_msg);
		$ret['msg_full'] = $_msg;
		$ret['msg_arr'] = $_msg_arr;

		if ( isset($import_stat['duration_total']) ) {
			$ret['duration'] = $import_stat['duration_total'];
		}

		$ret = array_merge($ret, array(
			'status' 	=> $status_final ? 'valid' : 'invalid',
			'product_id' => $insert_id,
		));
		return $ret;
	}

	// product = array - structured as returned by method _product_sanitize_data
	public function build_product_data( $product, $pms=array() ) {

		$pms = array_replace_recursive( array(
			'_is_variation_child' => false,
		), $pms );
		extract( $pms );

		$product = $this->_product_sanitize_data( $product, $_is_variation_child );

		// attributes
		$item_attributes = isset($product['item_attributes']) ? (array) $product['item_attributes'] : array();

		// short description
		$short_desc = isset($product['short_description']) && is_array($product['short_description'])
			? $product['short_description'] : array();
		$short_desc = array_map( 'strip_tags', $short_desc );
		$short_desc = array_map( 'trim', $short_desc );


		//:: main item
		$item = array(
			'ASIN'                  	=> isset($product['ASIN']) ? trim( $product['ASIN'] ) : '',
			'ParentASIN'            	=> isset($product['ParentASIN']) ? trim( $product['ParentASIN'] ) : '',

			'SalesRank'             	=> isset($product['SalesRank']) ? trim( $product['SalesRank'] ) : 999999,
			'DetailPageURL'         	=> isset($product['DetailPageURL']) ? trim( $product['DetailPageURL'] ) : '',

			'Tags' 						=> array(),
			'CustomerReviews' 			=> array(),

			'OfferSummary'          	=> array(),
			'Offers' 					=> array(),
			'VariationSummary' 			=> array(),
		);

		//if ( empty($item['ParentASIN']) ) {
		//	$item['ParentASIN'] = $item['ASIN'];
		//}


		//:: product country
		$country = $this->get_country_from_url( $item['DetailPageURL'] );
		$item['country'] = $country;
		//var_dump('<pre>', $country , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;


		//:: full description
		$desc = isset($product['description']) ? $product['description'] : '';

		$item['EditorialReviews'] = array(
			'EditorialReview' => array(
				'Content' => $desc,
			)
		);


		//:: attributes
		$item['ItemAttributes'] = $item_attributes;
		$item['ItemAttributes'] = array_replace_recursive( $item['ItemAttributes'], array(
			//'Binding'               	=> isset($product['binding']) ? $product['binding'] : '', //not needed

			'Title'                 	=> isset($product['title']) ? trim( stripslashes( $product['title'] ) ) : '',
			'Brand'                 	=> isset($product['brand']) ? trim( $product['brand'] ) : '',

			// short description
			'Feature'               	=> $short_desc,

			'SKU'                   	=> isset($product['SKU']) ? trim( $product['SKU'] ) : '',
		));


		//:: categories
		$categories = isset($product['categories']) ? $product['categories'] : array();
		$categories = array_reverse( $categories );
		$categories = $this->product_categories_clean( $categories );

		$categories_new = array();
		if ( ! empty($categories) ) {
			$categories_new = $this->product_categories_build( $categories );
		}

		$item['BrowseNodes'] = $categories_new;


		//:: images
		$images = isset($product['images']) ? $product['images'] : array();
		$images = $this->product_images_clean( $images );

		if ( $_is_variation_child ) {
			$images = array_slice($images, 0, $this->avi_nbvars);
		}

		$images_new = array();
		if ( ! empty($images) ) {
			$images_new = $this->product_images_build( $images );
		}

		$item = array_replace_recursive( $item, array(
			'ImageSets' => array(
				'ImageSet' => isset($images_new['ImageSet']) ? $images_new['ImageSet'] : array(),
			),
			//'SmallImage' => isset($images_new['SmallImage']) ? $images_new['SmallImage'] : array(),
			//'LargeImage' => isset($images_new['LargeImage']) ? $images_new['LargeImage'] : array(),
		));


		//:: price
		$price = $this->product_price( $product, array(
			'country' => $country,
		));


		//:: offer
		$offer = $this->product_offer( $product, array(
			'country' => $country,
			'price' => $price,
		));


		//:: variations
		$variations = $this->product_variations_build( $product );

		$vars_dim = array();
		$vars_dim_len = count($variations['vars_dim']);
		if ( $vars_dim_len ) {
			$vars_dim = $vars_dim_len > 1 ? $variations['vars_dim'] : $variations['vars_dim'][0];
		}

		$variations_new = array(
			'Variations' => array(
				'TotalVariations' => count( $variations['vars'] ),
				'TotalVariationPages' => count( $variations['vars'] ) ? 1 : 0,
				'VariationDimensions' => array(
					'VariationDimension' => $vars_dim,
				),
				'Item' => $variations['vars'],
			),
		);

		$_is_variable = count( $variations['vars'] ) ? true : false;

		$item = array_replace_recursive( $item, $variations_new );


		//:: set price & offer
		$item['ItemAttributes'] = array_replace_recursive( $item['ItemAttributes'], array(
			'ListPrice' => $price['ListPrice'],
		));

		$item = array_replace_recursive( $item, array(
			'Offers' => isset($offer['Offers']) ? $offer['Offers'] : array(),
		));


		//:: variable product
		if ( $_is_variable ) {

			if ( isset($item['ItemAttributes']['ListPrice']) ) {
				unset( $item['ItemAttributes']['ListPrice'] );
			}


			$offer = $this->product_offer( $product, array(
				'country' => $country,
				'price' => $price,
				'_is_variable' => true,
			));
			//var_dump('<pre>', $offer, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

			$item['Offers'] = array();
			$item = array_replace_recursive( $item, array(
				'Offers' => isset($offer['Offers']) ? $offer['Offers'] : array(),
			));


			$variation_summary = $this->product_variation_summary( $product, array(
				'price' => $price,
			));
			//var_dump('<pre>', $variation_summary, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			$item = array_replace_recursive( $item, array(
				'VariationSummary' => isset($variation_summary['VariationSummary']) ? $variation_summary['VariationSummary'] : array(),
			));
		}
		else if ( $_is_variation_child ) {

			if ( isset($item['OfferSummary']) ) {
				unset( $item['OfferSummary'] );
			}
			if ( isset($item['VariationSummary']) ) {
				unset( $item['VariationSummary'] );
			}
		}


		//:: return
		if ( ! $_is_variation_child ) {
			//var_dump('<pre>', $item , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		}
		return $item;
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	//====================================================================================
	//== Import Product - related

	private function _product_sanitize_data( $product=array(), $is_var_child=false ) {

		$default = array(
			'ASIN' => '', // string, ex.: B07FHXH3S7
			'ParentASIN' => '', // string, ex.: B07FHXH3S7
			'DetailPageURL' => '', // string, ex.: https://www.amazon.com/dp/B07FHXH3S7
			'title' => '', // string, ex.: Sony XBR49X900F 49-Inch 4K Ultra HD Smart LED TV
			'brand' => '', // string, ex.: Sony

			'description' => '', // string, ex.: <p></p><p>BEYOND HIGH DEFINITIONThe Sony X900F model television offers the best in...
			'SalesRank' => 999999, // string
			'SKU' => '', // string

			'amazon_price' => '', // Sale Price/ string, ex.: $155.22 | EU222,33
			'list_price' => '', // Regular Price/ string, ex.: $155.22 | EU222,33
			'amazonprime' => '', // integer, posible values: 0 | 1, (0 = no flag, 1 = has flag)
			'freeshipping' => '', // integer, posible values: 0 | 1, (0 = no flag, 1 = has flag)
			'merchant' => '', // string

			// must be a numeric array
			// ex.: array(
			// 		0: "BEYOND HIGH DEFINITION: 4K HDTV picture offers stunning clarity & high dynamic range color & detail",
			// 		1: "ENHANCED QUALITY: With the X1 Extreme Processor enjoy controlled contrast & wide range of brightness.",
			// )
			'short_description' => array(),

			// associative array
			// ex.: array(
			// 		"Batteries": "2 AA batteries required. (included)",
			// 		"Brand Name": "Sony",
			// 		"California residents": "Click here for Proposition 65 warning",
			// )
			'item_attributes' => array(),
			'authors' => array(), // is contained in item_attributes (same structure as item_attributes)

			// numeric array
			// ORDER is: primary/root category > secondary category > sub-secondary category...
			// ex.: Books > Self-Help > Motivational
			// ex.: array(
			// 		0: array( "id": "172282", "name": "Electronics" ),
			// 		1: array( "id": "1266092011", "name": "Television & Video" ),
			// )
			'categories' => array(),

			// numeric array
			// ex.: array(
			// 		0: array(
			// 			"large": array( "height": 333, "width": 500 ),
			// 			"url": "https://images-na.ssl-images-amazon.com/images/I/5164jDHoHzL.jpg",
			// 		),
			// 		1: array(
			// 			"large": array( "height": 333, "width": 500 ),
			// 			"url": "https://images-na.ssl-images-amazon.com/images/I/419hoMfZfzL.jpg",
			// 		),
			// )
			'images' => array(),

			// associative array
			// -- dimCombinations: associative array
			// ex.: array( "0:0": "B078H2DWZT", "0:1": "B07GWKDDFV", "1:0": "B078GWPQRB", "1:1": "B07GVQSV93", "1:2": "B07F44JFZ8", "2:0": "B078GZYDFK", "2:1": "B07GW1LJZG", "2:2": "B07F3PRVM4" )
			// -- dimtoValueMap: associative array
			// ex.: array(
			// 	 	"size_name": array( 0: "49 inches", 1: "55 inches", 2: "65 inches" ),
			// 		"style_name": array( 0: "TV", 1: "TV with Blu-Ray Player", 2: "TV with 2.1ch Soundbar" ),
			// )
			// -- dimensionDisplayText: associative array
			// ex.: array( "size_name": "Size", "style_name": "Style" )
			// -- dimensionList: numeric array
			// ex.: array( 0: "size_name", 1: "style_name" )
			'variations_dimensions' => array(
				'dimCombinations' => array(),
				'dimtoValueMap' => array(),
				'dimensionDisplayText' => array(),
				'dimensionList' => array(),
			),

			// numeric array
			// ex.: array(
			// 		0: array( ASIN: "B078H2DWZT", DetailPageURL: "https://www.amazon.com/dp/B078H2DWZT?th=1&psc=1", amazon_price: ""...)
			// )
			// !!!  each item in this array should contains an array like the above ITEM, but without:
			// 		brand, categories, authors, variations_dimensions, variations
			'variations' => array(),
		);

		if ( $is_var_child ) {
			unset( $default['variations_dimensions'], $default['variations'] );
		}

		$product = array_replace_recursive( $default, $product );

		foreach ( $product as $key => $val ) {
			if ( is_null($val) ) {
				$product["$key"] = isset($default["$key"]) ? $default["$key"] : '';
			}
		}
		return $product;
	}

	private function is_valid_product_asin( $product=array() ) {
		if ( empty($product) || !is_array($product) ) return false;
		
		$rules = isset($product['ASIN']) && !empty($product['ASIN']);
		return $rules ? true : false;
	}

	private function is_valid_product_data( $product=array() ) {
		if ( empty($product) || !is_array($product) ) return false;
		
		$rules = isset($product['ASIN']) && !empty($product['ASIN']);
		$rules = isset($product['Title']) && !empty($product['Title']);
		return $rules ? true : false;
	}

	//:: categories
	private function product_categories_clean( $categories=array() ) {

		if ( empty($categories) || ! is_array($categories) ) return array();

		foreach ( $categories as $key => $current ) {

			$categ_id = isset($current['id']) ? $current['id'] : 0;
			$categ_name = isset($current['name']) ? trim( $current['name'] ) : '';

			if ( ! $categ_id || empty($categ_name) ) {
				unset( $categories["$key"]);
			}
		}
		return $categories;
	}

	private function product_categories_build( $categories=array() ) {

		$current = array_shift( $categories );

		$item = array();
		$item['BrowseNode'] = array(
			'BrowseNodeId' => $current['id'],
			'Name' => $current['name'],
			'Ancestors' => array(),
		);

		if ( empty($categories) ) {
			if ( isset($item['BrowseNode']['Ancestors']) ) {
				unset( $item['BrowseNode']['Ancestors'] );
			}
			return $item;
		}

		$_stat = $this->product_categories_build( $categories );
		$item['BrowseNode']['Ancestors'] = $_stat;
		return $item;
	}

	//:: images
	private function product_images_clean( $images=array() ) {

		if ( empty($images) || ! is_array($images) ) return array();

		$images_ = array();
		foreach ( $images as $key => $image ) {
			$large = isset($image['large']) ? $image['large'] : array();
			$url = isset($image['url']) ? $image['url'] : '';

			if ( empty($large) || ! is_array($large) || empty($url) ) {
				continue 1;
			}

			$width = isset($large['width']) ? $large['width'] : '';
			$height = isset($large['height']) ? $large['height'] : '';

			if ( empty($width) || empty($height) ) {
				continue 1;
			}

			$images_[] = array(
				'url' => $url,
				'width' => $width,
				'height' => $height,
			);
		}

		//$images = array_map( 'trim', $images );
		//$images = array_unique( array_filter( $images ) );
		//return $images;

		return $images_;
	}

	private function product_images_build( $images=array() ) {

		$ret = array(
			'ImageSet' => array(),
			'SmallImage' => array(),
			'LargeImage' => array(),
		);

		if ( empty($images) ) return $ret;

		// key => (height, width)
		$sizes_wh = array(
			//array( '_' => 30, 'Units' => 'pixels' )
			'SwatchImage' => array( 30, 0 ),
			'SmallImage' => array( 75, 0 ),
			'ThumbnailImage' => array( 75, 0 ),
			'TinyImage' => array( 110, 0 ),
			'MediumImage' => array( 160, 0 ),
			'LargeImage' => array( 500, 0 ),
		);

		$new = array();
		$cc = 0;
		foreach ( $images as $image ) {

			$image_link = $image['url'];
			$large_h = $image['height'];
			$large_w = $image['width'];

			$sizes_wh['LargeImage'][0] = $large_h;
			$sizes_wh['LargeImage'][1] = $large_w;

			foreach ( $sizes_wh as $image_size => $image_wh ) {
				$height = $image_wh[0];
				$width = $image_wh[1];

				if ( 'LargeImage' != $image_size ) {
					$width = ( $height * $large_w ) / $large_h;
					$width = (int) floor( $width );
				}

				$sufix = "_SL$height.";
				if ( 'LargeImage' == $image_size ) {
					$sufix = '';
				}

				$new[$cc]["$image_size"] = array(
					'URL' => $this->product_image_size_name( $image_link, $sufix ),
					'Height' => $height,
					'Width' => $width,
				);
			}

			$cc++;
		}

		$ret = array_replace_recursive( $ret, array(
			'ImageSet' => $new,
			'SmallImage' => isset($new[0]['SmallImage']) ? $new[0]['SmallImage'] : array(),
			'LargeImage' => isset($new[0]['LargeImage']) ? $new[0]['LargeImage'] : array(),
		));

		//die( var_dump( "<pre>", $ret  , "<pre>" ) . PHP_EOL .  __FILE__ . ":" . __LINE__  ); 
		return $ret;
	}

	private function product_image_size_name( $image_link, $sufix='_SL30.' ) {

		if ( '' == $sufix ) {
			return $image_link;
		}

		//https://images-na.ssl-images-amazon.com/images/I/41pQyhJ3xIL.jpg
		$regex = '~(\.)([a-zA-Z]{1,5})$~imu';
		$image_link = preg_replace( $regex, '${1}' . $sufix . '${2}', $image_link );
		//preg_match( $regex, $image_link, $m );
		//var_dump('<pre>',$image_link, $m ,'</pre>');
		return $image_link;
	}

	//:: variations
	private function product_variations_build( $product=array() ) {

		$ret = array(
			'vars' => array(),
			'vars_dim' => array(),
		);

		$vars_dim = isset($product['variations_dimensions']) ? $product['variations_dimensions'] : array();
		$vars = isset($product['variations']) ? $product['variations'] : array();

		if ( empty($vars) || empty($vars_dim) ) return $ret;
		if ( isset($vars_dim['dimCombinations']) && empty($vars_dim['dimCombinations']) ) return $ret;

		$parent_asin = $product['ASIN'];

		$all_vars_comb = $this->product_variations_get_combinations( $vars_dim );

		//:: loop through vars
		$vars_dim_new = array();
		$vars_new = array();
		foreach ( $vars as $idx => $var_item ) {

			$var_item_ = $this->build_product_data( $var_item, array(
				'_is_variation_child' => true,
			));

			$var_asin = $var_item_['ASIN'];
			if ( empty($var_asin) ) {
				continue 1;
			}

			$var_comb = isset($all_vars_comb["$var_asin"]) ? $all_vars_comb["$var_asin"] : array();
			if ( empty($var_comb) ) {
				continue 1;
			}

			$vars_dim_new = array_merge( $vars_dim_new, array_keys( $var_comb) );

			$var_comb_ = $this->product_variations_set_combination( $var_comb );

			$var_item_['ParentASIN'] = $parent_asin;
			$var_item_['VariationAttributes'] = array(
				'VariationAttribute' => $var_comb_,
			);

			if ( isset($var_item_['Variations']) ) {
				unset( $var_item_['Variations'] );
			}
			//var_dump('<pre>',$var_item_ ,'</pre>');
			$vars_new[] = $var_item_;
		}

		$vars_dim_new = array_unique( array_filter( $vars_dim_new ) );


		//:: return
		$ret = array_replace_recursive( $ret, array(
			'vars' 	=> $vars_new,
			'vars_dim' => $vars_dim_new,
		));
		return $ret;
	}

	private function product_variations_get_combinations( $vars_dim=array() ) {

		$__ = array( 'dimCombinations', 'dimtoValueMap', 'dimensionDisplayText', 'dimensionList' );
		foreach ( $__ as $what ) {
			$$what = isset($vars_dim["$what"]) && is_array($vars_dim["$what"]) && ! empty($vars_dim["$what"])
				? $vars_dim["$what"] : array();
		}
		//var_dump('<pre>', compact( $__ ) , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$comb = array();

		// main foreach
		foreach ( $dimCombinations as $key => $asin ) {

			if ( empty($asin) ) {
				continue 1;
			}

			$key_ = trim( $key );
			$key_ = explode( ':', $key_ );
			$key_ = array_map( 'trim', $key_ );

			$_comb = array();

			// secondary foreach
			foreach ( $key_ as $kk => $vv ) {

				$__ = isset($dimensionList["$kk"]) ? trim( $dimensionList["$kk"] ) : '';
				if ( '' == $__ ) {
					continue 1;
				}

				$__2 = isset($dimtoValueMap["$__"]) ? (array) $dimtoValueMap["$__"] : array();
				if ( empty($__2) ) {
					continue 1;
				}

				$__3 = isset($__2["$vv"]) ? trim( $__2["$vv"] ) : '';
				if ( '' == $__3 ) {
					continue 1;
				}

				$__4 = isset($dimensionDisplayText["$__"]) ? trim( $dimensionDisplayText["$__"] ) : $__;

				$_comb["$__4"] = $__3;
			}
			// end secondary foreach 

			if ( count($key_) == count($_comb) ) {
				$comb["$asin"] = $_comb;
			}
		}
		// end main foreach

		//var_dump('<pre>', $comb , '</pre>'); echo __FILE__ . ":" .__LINE__;die . PHP_EOL;
		return $comb;
	}

	private function product_variations_set_combination( $var_comb=array() ) {

		$new = array();
		foreach ( $var_comb as $key => $val ) {
			$new[] = array(
				'Name' => $key,
				'Value' => $val,
			);
		}
		return $new;
	}

	//:: price
	private function product_price( $product=array(), $pms=array() ) {

		$pms = array_replace_recursive( array(
			'country' => '',
		), $pms );
		extract( $pms );

		$noprice = $this->the_plugin->directimport_get_product_price_format( '', '' );
		$ret = array(
			'ListPrice' => $noprice,
			'OfferListingPrice' => $noprice,
			'VariationSummaryPrice' => $noprice,
		);

		//:: init
		$__ = array( 'amazon_price', 'list_price' );
		$__2 = array( 'amazon_price_' => array(), 'list_price_' => array() );

		foreach ( $__ as $what ) {
			$$what = isset($product["$what"]) ? trim( $product["$what"] ) : '';

			$__2["{$what}_"] = $this->the_plugin->directimport_get_product_price_format( $$what, $country );
		}
		extract( $__2 );
		//var_dump('<pre>', compact( $__ ), $__2, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;


		//:: regular & sale price
		$price_final = $amazon_price_['Amount'];
		$price_cut = $list_price_['Amount'];

		if ( ! empty($price_final) && ! empty($price_cut) ) {
			$ret['ListPrice'] = $list_price_;
			$ret['OfferListingPrice'] = $amazon_price_;
		}
		else if ( ! empty($price_final) ) {
			$ret['ListPrice'] = $amazon_price_;
			$ret['OfferListingPrice'] = $amazon_price_;
		}
		else if ( ! empty($price_cut) ) {
			$ret['ListPrice'] = $list_price_;
			$ret['OfferListingPrice'] = $list_price_;
		}

		return $ret;
	}

	//:: Offer/OfferListing/OfferListingId
	private function product_offer( $product=array(), $pms=array() ) {

		$pms = array_replace_recursive( array(
			'country' => '',
			'price' => array(),
			'_is_variable' => false,
		), $pms );
		extract( $pms );

		$flags = array();

		//IsEligibleForPrime
		$amazonprime = isset($product['amazonprime']) && $product['amazonprime'] ? true : false;
		$flags['IsEligibleForPrime'] = $amazonprime;

		//IsEligibleForSuperSaverShipping
		$freeshipping = isset($product['freeshipping']) && $product['freeshipping'] ? true : false;
		$flags['IsEligibleForSuperSaverShipping'] = $freeshipping;

		$merchant = isset($product['merchant']) && ! empty($product['merchant'])
			? $product['merchant'] : 'Amazon.' . $country;

		$ret = array(
			'Offers' => array(
				'TotalOffers' => 1,
				'TotalOfferPages' => 1,
				'Offer' => array(
					'Merchant' => array(
						'Name' => $merchant,
					),
					'OfferAttributes' => array(
						'Condition' => 'New',
					),
					'OfferListing' => array_merge( array(
						'OfferListingId' => 'directimport',
						'Price' => $price['OfferListingPrice'],
					), $flags ),
				),
			),
		);

		if ( $_is_variable ) {
			$ret = array(
				'Offers' => array(
					'TotalOffers' => 1,
					'TotalOfferPages' => 1,
				),
			);
		}
		return $ret;
	}

	//:: VariationSummary
	private function product_variation_summary( $product=array(), $pms=array() ) {

		$pms = array_replace_recursive( array(
			'price' => array(),
		), $pms );
		extract( $pms );

		if ( ! isset($price['OfferListingPrice']) ) {
			$ret = array(
				'VariationSummary' => array(),
			);
		}
		else {
			$ret = array(
				'VariationSummary' => array(
					'LowestPrice' => $price['OfferListingPrice'],
				),
			);
		}
		return $ret;
	}


	//====================================================================================
	//== MISC

	private function validate_accesskey( $accesskey='' ) {

		$ret = array(
			'status' 	=> 'invalid',
			'msg' 		=> __('Unknown error occured!', 'WooZone'),
		);

		if ( empty($accesskey) ) {
			$ret = array_replace_recursive($ret, array(
				'msg' => 'Request access: you are using an invalid access key',
			));
			return $ret;
		}

		$directimport_opt = get_option('WooZone_direct_import', array());
		$accesskey_db = isset($directimport_opt['api_secret']) ? $directimport_opt['api_secret'] : '';

		if ( empty($accesskey_db) ) {
			$ret = array_replace_recursive($ret, array(
				'msg' => 'Request access: no valid key found in website database',
			));
			return $ret;
		}

		if ( $accesskey != $accesskey_db ) {
			$ret = array_replace_recursive($ret, array(
				'msg' => 'Request Access: key(s) don\'t match. Re-authorize <a target="_blank" href="' . ( admin_url( 'admin.php?page=WooZone_direct_import' ) ) . '">here</a>',
			));
			return $ret;
		}

		$ret = array_replace_recursive($ret, array(
			'status' => 'valid',
			'msg' => 'request access: ok',
		));
		return $ret;
	}

	private function get_country_from_url( $url ) {
		$country = isset($this->settings['country']) ? $this->settings['country'] : '';
		if ( ! empty($url) ) {
			$country = $this->the_plugin->get_country_from_url( $url );
			if ( ! empty($country) ) {
				$country = $country;
			}
		}
		//var_dump('<pre>', $url, $country , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		return $country;
	}

} }

// Initialize class
//$WooZoneDirectImport = WooZoneDirectImport::getInstance();