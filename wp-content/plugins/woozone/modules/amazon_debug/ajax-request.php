<?php 
if (class_exists('_WooZoneAmazonDebugUtils') != true) {
    class _WooZoneAmazonDebugUtils
    {
        /*
        * Some required plugin information
        */
        const VERSION = '1.0';
		
		static protected $_instance;
		

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct() {
		}
		
		public function escape($str) {
			return preg_replace("!([\b\t\n\r\f\"\\'])!", "\\\\\\1", $str);
		}

		// php.net / bohwaz / This is intended to be a simple readable json encode function for PHP 5.3+ (and licensed under GNU/AGPLv3 or GPLv3 like you prefer)
		public function json_readable_encode($in, $indent = 0, $from_array = false) {
		    $out = '';
		
		    foreach ($in as $key=>$value)
		    {
		        $out .= str_repeat("\t", $indent + 1);
		        $out .= "\"".$this->escape((string)$key)."\": ";
		
		        if (is_object($value) || is_array($value))
		        {
		            $out .= "\n";
		            $out .= $this->json_readable_encode($value, $indent + 1);
		        }
		        elseif (is_bool($value))
		        {
		            $out .= $value ? 'true' : 'false';
		        }
		        elseif (is_null($value))
		        {
		            $out .= 'null';
		        }
		        elseif (is_string($value))
		        {
		            $out .= "\"" . $this->escape($value) ."\"";
		        }
		        else
		        {
		            $out .= $value;
		        }
		
		        $out .= ",\n";
		    }
		
		    if (!empty($out))
		    {
		        $out = substr($out, 0, -2);
		    }
		
		    $out = str_repeat("\t", $indent) . "{\n" . $out;
		    $out .= "\n" . str_repeat("\t", $indent) . "}";
		
		    return $out;
		}

		/**
	    * Singleton pattern
	    *
	    * @return WooZonePriceSelect Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }
  
	        return self::$_instance;
	    }
    }
}
// Initialize the _WooZoneAmazonDebugUtils class
//$_WooZoneAmazonDebugUtils = _WooZoneAmazonDebugUtils::getInstance();

add_action('wp_ajax_WooZoneAmazonDebugGetResponseDev', 'WooZoneAmazonDebugGetResponse');
add_action('wp_ajax_WooZoneAmazonDebugGetResponse', 'WooZoneAmazonDebugGetResponse');

function WooZoneAmazonDebugGetResponse() {
	$html = array();
	$ret = array(
		'status' => 'invalid',
		'html'	=> implode( PHP_EOL, $html )
	);
   
	$req = array(
		'asin'	=> isset($_REQUEST['asin']) ? (string) $_REQUEST['asin'] : 0,
		'rg'	=> isset($_REQUEST['rg']) ? $_REQUEST['rg'] : 'ItemAttributes,Large,OfferFull,PromotionSummary,Variations',
		'req_type' => isset($_REQUEST['req_type']) ? (string) $_REQUEST['req_type'] : '',
		'provider' => isset($_REQUEST['provider']) ? (string) $_REQUEST['provider'] : 'amazon',
		'country' => isset($_REQUEST['country']) ? (string) $_REQUEST['country'] : '',

		'subact' => isset($_REQUEST['subact']) ? $_REQUEST['subact'] : '',
	);
	extract($req);
	//var_dump('<pre>', $req , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

	global $WooZone;


	$prefix = 'amazon' != $provider ? $provider.'_' : '';

	if ( empty($country) ) {
		$country = $WooZone->amz_settings[$prefix.'country'];
	}

	//:: init import object
	$WooZone->cur_provider = $provider;

	$pmsSetupHelper = array(
		$prefix.'country' 				=> $country,
		$prefix.'main_aff_id'			=> $WooZone->main_aff_id(),
	);
	$WooZone->setupAmazonHelper( $pmsSetupHelper, array(
		'provider' 	=> $provider,
	));
	//var_dump('<pre>', $pmsSetupHelper , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

	$rsp = $WooZone->get_ws_object( $provider )->api_main_request(array(
		'what_func' 			=> 'api_make_request',
		'method'				=> 'lookup',
		'amz_settings'			=> $WooZone->amz_settings,
		'from_file'				=> str_replace($WooZone->cfg['paths']['plugin_dir_path'], '', __FILE__),
		'from_func'				=> __FUNCTION__ != __METHOD__ ? __METHOD__ : __FUNCTION__,
		'requestData'			=> array(
			'asin'					=> $WooZone->prodid_set($asin, $provider, 'sub'),
		),
		'optionalParameters'	=> array(),
		'responseGroup'			=> $rg,
	));
	$product = $rsp['response'];
	//var_dump('<pre>', $product, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

	if ( ! empty($req_type) ) {

		if ( 'images' == $subact ) {
			//:: EBAY VARIATIONS IMAGES
			$retProd = $product;
			//var_dump('<pre>', $product , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			$retProd = $retProd['Item'];
			//var_dump('<pre>', $product , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			$retProd = $WooZone->get_ws_object( 'ebay' )->build_product_data( $retProd );
			//var_dump('<pre>', $retProd , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

			$total = $WooZone->get_amazon_variations_nb( $retProd['Variations']['Variation'], 'ebay' );
			$variations = array();
			if ($total <= 1) {
				$variations[] = $retProd['Variations']['Variation'];
			} else {
				$variations = (array) $retProd['Variations']['Variation'];
			}
			$compatVariations = $WooZone->get_ws_object( 'ebay' )->ebay_product_compatible_variations( $retProd, $variations );
			//var_dump('<pre>', $compatVariations , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			//:: END EBAY VARIATIONS IMAGES
		}

		var_dump('<pre>', $product, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;		
	}
			
  	//global $_WooZoneAmazonDebugUtils;
  	$_WooZoneAmazonDebugUtils = _WooZoneAmazonDebugUtils::getInstance();
	//$product = $_WooZoneAmazonDebugUtils->json_readable_encode( $product );
	$product = json_encode(	$product ); 
	
	die( json_encode(array(
		'status' => 'valid',
		'html' => $product
	)) );
}