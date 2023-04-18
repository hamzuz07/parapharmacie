<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract\Fields;

use WooZone\AmazonScraper\ProductExtract\ProductExtractException;
use WooZone\AmazonScraper\ProductExtract\WidgetExtract;
use WooZone\AmazonScraper\Misc\JavaScriptObjectToPHP\Converter;
//use Symfony\Component\DomCrawler\Crawler;
//use JsObj2Php\Converter;

if (class_exists(Variations::class) !== true) { class Variations extends AbstractField {

	const VERSION = '1.0';

	static protected $_instance;

	private $widget_vars = array(); // MANDATORY if it's a variable product - content of widget

	private $vars_from_widget = array(); // the variations list from the widget_vars content

	private $content_with_vars = array(); // extracted content from product page containing variations

	private $vars_from_content = array(); // the variations list from the content_with_vars content

	private $is_variation = -1; //-1 = not a variable product OR we don't know yet!

	private $parent_asin = null; //null = not a variable product OR we don't know yet!

	private $variations = array(); // final variations list after combining those from page content with widget



	// Required __construct() function
	protected function __construct( $parent=null ) {

		parent::__construct( $parent );
	}

	// Singleton pattern
	static public function getInstance( $parent=null ) {
		if (!self::$_instance) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	}



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	public function set_widget_vars( $widget_vars=array() ) {

		$this->widget_vars = $widget_vars;
	}

	// main method
	public function extract() {

		$data = array(
			'variations' => null,
			'variations_dimensions' => null,
		);

		$variations = $this->_get_variations( true );
		if ( $this->_has_variations( $variations ) ) {
			$data = array_replace_recursive( $data, $variations );
		}

		// overwrite parent asin
		if ( ! empty($this->parent_asin) ) {
			$data['ParentASIN'] = $this->parent_asin;

			$this->notices['ParentASIN'] = false; // TO RESET NOTICE IN THE MAIN CLASS
		}
		//var_dump('<pre>', $this->notices, $data, $this->widget_vars, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return $data;
	}

	// product is a variable child product? (a variation of a variable product)
	public function is_variation() {

		$variations = $this->_get_variations( false );
		return $this->is_variation;
	}

	// return null ( if simple | variable parent ) | the parent of the variation child
	public function get_parent_asin() {

		$variations = $this->_get_variations( false );
		return $this->parent_asin;
	}

	// how many variations the parent product has?
	public function get_nb_variations() {
		
		$variations = $this->_get_variations( false );
		if ( $this->_has_variations( $variations ) ) {
			return count( $variations['variations'] );
		}
		return 0;
	}

	// variations list the parent product has?
	public function get_variations_asin() {
		
		$variations = $this->_get_variations( false );
		if ( $this->_has_variations( $variations ) ) {
			return array_column( $variations['variations'], 'ASIN' );
		}
		return array();
	}

	public function get_variations() {

		return $this->_get_variations( true );
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	private function _has_variations( $variations, $is_from_widget=false ) {

		if (
			! empty($variations) && is_array($variations)
			&& isset($variations['variations']) && ! empty($variations['variations']) && is_array($variations['variations'])
		) {
			return true;
		}
		return false;
	}

	private function _get_variations( $parse_widget=true, $with_cache=true ) {

		$this->notices = array();
		$this->errors = array();

		//:: we already have the final list (combined content & widget) of variations
		if ( $with_cache && $this->_has_variations( $this->variations ) ) {
			return $this->variations;
		}

		//:: get variations from page content
		$opStat = $this->_get_variations_from_content( $with_cache );
		if ( true !== $opStat ) {
			return array();
		}

		//:: do we want to parse widget?
		if ( ! $parse_widget ) {
			return $this->vars_from_content;
		}

		//:: get variations from widget
		$opStat = $this->_get_variations_from_widget( $with_cache );
		if ( true !== $opStat ) {
			return array();
		}

		//:: combine vars_from_content with vars_from_widget
		$opStat = $this->_combine_vars_from_content_widget( $this->vars_from_content, $this->vars_from_widget );

		return $this->variations;
	}

	private function _get_variations_from_content( $with_cache=true ) {

		//:: get content with variations
		if ( ! $with_cache || ! $this->_content_with_vars_isvalid( $this->content_with_vars ) ) {
			$this->content_with_vars = $this->_find_content_with_vars();
		}

		// content with variations is not valid OR product page doesn't have variations (simple product?)
		if ( ! $this->_content_with_vars_isvalid( $this->content_with_vars )
			|| $this->_content_with_vars_iswithout( $this->content_with_vars )
		) {
			$msg = "\"variations\" content with vars is empty!";

			if ( $this->_content_with_vars_iswithout( $this->content_with_vars ) ) {
				if ( ! isset($this->notices['variations']) ) {
					$this->notices['variations'] = $msg;
				}
				return false;
			}
			else {
				throw new ProductExtractException( $msg );
			}
		}

		//:: get variations from page content
		if ( ! $with_cache || ! $this->_has_variations( $this->vars_from_content ) ) {
			$this->vars_from_content = $this->_get_vars_from_content( $this->content_with_vars );
		}

		if ( ! $this->_has_variations( $this->vars_from_content ) ) {
			$msg = "\"variations\" content list of variations is empty!";
			$this->notices['variations'] = $msg;
			return false;
		}

		return true;
	}

	private function _get_variations_from_widget( $with_cache=true ) {

		//:: get widget variations
		if ( ! $with_cache || ! $this->_has_variations( $this->vars_from_widget, true ) ) {
			$this->vars_from_widget = $this->_get_vars_from_widget( $this->widget_vars );
		}

		$widget_notices = is_array($this->vars_from_widget) && isset($this->vars_from_widget['widget_notices'])
			? $this->vars_from_widget['widget_notices'] : array();

		$msg_widget = $this->_get_widget_notices( $widget_notices );

		if ( ! $this->_has_variations( $this->vars_from_widget, true ) ) {

			$msg = "\"variations\" widget list of variations is empty!";

			$this->notices['variations'] = array( "from_content" => $msg, "from_widget" => $msg_widget );

			return false;
		}
		else if ( ! empty($widget_notices) ) {

			$this->notices['variations'] = array( "from_widget" => $msg_widget );
		}
		return true;
	}

	// get variations from page content
	private function _find_content_with_vars() {

		//:: PAGE CONTENT
		$content = $this->content;


		//:: GET ONLY THE CONTENT WE NEED
		//<script type="text/javascript">
		//	P.register('twister-js-init-dpx-data', function() {
		//		[[CONTENT]]
		//		return dataToReturn;
		//	});
		//</script>

		//preg_quote - the special regular expression characters are: . \ + * ? [ ^ ] $ ( ) { } = ! < > | : - #
		//https://stackoverflow.com/questions/18988536/php-regex-how-to-match-r-and-n-without-using-r-n
		// \R = \r\n
		$regexp = array();
		$regexp[] = '~';
		$regexp[] = 	'<script[^>]*>';
		$regexp[] = 		'P\.register\( \'twister-js-init-dpx-data\' , function \( \) {';
		$regexp[] = 			'var dataToReturn = {';
		$regexp[] = 				'(.*?)';
		$regexp[] = 			'} ;';
		$regexp[] = 		'return dataToReturn ; } \) ;';
		$regexp[] = 		'.*';
		$regexp[] = 	'</script>';
		$regexp[] = '~imu';
		$regexp = implode(' ', $regexp);
		$regexp = str_replace(' ', '(?:[\s\\\R]*)', $regexp);

		$found = preg_match( $regexp, $content, $matches);
		//var_dump('<pre>', $regexp, $found, $matches, $content, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if ( ! $found || ! isset($matches[1]) ) {
			$msg = "\"variations\" content 'twister-js-init-dpx-data' wasn\'t found!";
			//throw new FieldException( $msg );
			$this->notices['variations'] = $msg;

			return 'content-without-vars';
		}

		$content = $matches[1];


		//:: FILTER & CLEAN
		$regexp = array();

		$regexp[0] = array();
		$regexp[0][] = '~';
		$regexp[0][] = 		'"updateDivLists" : { (.*?) } ,? "';
		$regexp[0][] = '~imu';

		foreach ( $regexp as $kk => $vv ) {
			$vv = implode(' ', $vv);
			$vv = str_replace(' ', '(?:[\s\\\R]*)', $vv);
			$regexp["$kk"] = $vv;
		}
		// $found = preg_match( $regexp[0], $content, $matches);
		// var_dump('<pre>', $regexp, $found, $matches , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$content = preg_replace(
			$regexp,
			array(
				'"',
			),
			$content,
			-1,
			$count
		);
		//var_dump('<pre>', $count, $content, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;


		//:: CONVERT JavaScript object to PHP Array
		try {
			$content = sprintf( '{%s}', $content );
			//var_dump('<pre>', $content , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			$content = Converter::execute( $content, true );
			//var_dump('<pre>', $content , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		}
		catch (\Exception $e) {
			$msg = "\"variations\" content 'twister-js-init-dpx-data' - cannot convert javascript object to json object! " . $e->getMessage();
			throw new ProductExtractException( $msg );
		}

		if ( ! $this->_content_with_vars_isvalid( $content ) ) {
			$msg = "\"variations\" content 'twister-js-init-dpx-data' - object is missing mandatory property!";
			throw new ProductExtractException( $msg );
			//$this->notices['variations'] = $msg;
		}

		return $content;
	}

	private function _content_with_vars_isvalid( $content ) {

		if ( 'content-without-vars' === $content ) {
			return true;
		}
		if ( ! empty($content) && is_array($content)
			&& isset($content['dimensions'], $content['variationDisplayLabels'], $content['dimensionValuesData'], $content['dimensionToAsinMap'])
		) {
			return true;
		}
		return false;
	}

	private function _content_with_vars_iswithout( $content ) {

		if ( 'content-without-vars' === $content ) {
			return true;
		}
		return false;
	}

	private function _get_vars_from_content( $content=array() ) {

		$vars = array();
		$varsdim = array(
			'dimCombinations' => array(),
			'dimtoValueMap' => array(),
			'dimensionDisplayText' => array(),
			'dimensionList' => array(),
		);
		$ret = array(
			'variations' => $vars,
			'variations_dimensions' => $varsdim,
		);

		if ( empty($content) || ! is_array($content) ) {
			return $ret;
		}


		//$currentAsin = isset($content['currentAsin']) && ! empty($content['currentAsin'])
		//	? $content['currentAsin'] : null;

		$parentAsin = isset($content['parentAsin']) && ! empty($content['parentAsin'])
			? $content['parentAsin'] : null;
		//var_dump('<pre>',$currentAsin, $parentAsin ,'</pre>');

		//:: get dimensionList from content/dimensions
		$dimensions = isset($content['dimensions']) && is_array($content['dimensions'])
			? $content['dimensions'] : array();

		if ( ! empty($dimensions) ) {
			foreach ( $dimensions as $kk => $vv ) {
				$varsdim['dimensionList'][(int) $kk] = (string) $vv;
			}
		}


		//:: get dimensionDisplayText from content/variationDisplayLabels
		$variationDisplayLabels = isset($content['variationDisplayLabels']) && is_array($content['variationDisplayLabels'])
			? $content['variationDisplayLabels'] : array();

		if ( ! empty($variationDisplayLabels) ) {
			foreach ( $variationDisplayLabels as $kk => $vv ) {
				$varsdim['dimensionDisplayText'][(string) $kk] = (string) $vv;
			}
		}


		//:: get dimtoValueMap from content/dimensionValuesData
		$dimensionValuesData = isset($content['dimensionValuesData']) && is_array($content['dimensionValuesData'])
			? $content['dimensionValuesData'] : array();

		if ( ! empty($dimensionValuesData) ) {
			foreach ( $dimensionValuesData as $kk => $vv ) {

				$_key = $varsdim['dimensionList']["$kk"];
				$dimtoValueMap = array();

				foreach ( $vv as $kk2 => $vv2 ) {
					$dimtoValueMap[(int) $kk2] = (string) $vv2;
				}

				$varsdim['dimtoValueMap']["$_key"] = $dimtoValueMap;
			}
		}


		//:: get dimCombinations from content/dimensionToAsinMap
		$dimensionToAsinMap = isset($content['dimensionToAsinMap']) && is_array($content['dimensionToAsinMap'])
			? $content['dimensionToAsinMap'] : array();

		$found_parent = false;
		$this->is_variation = 0;

		if ( ! empty($dimensionToAsinMap) ) {
			foreach ( $dimensionToAsinMap as $kk => $vv ) {

				$asin = trim( $vv );
				if ( '' === $asin ) {
					continue 1;
				}

				$kk_comb = str_replace('_', ':', $kk);
				$varsdim['dimCombinations']["$kk_comb"] = $asin;

				$variation_child = array();
				$variation_child['ASIN'] = $asin;
				//if ( ! empty($parentAsin) ) {
					$variation_child['ParentASIN'] = $parentAsin;
				//}
				$variation_child['DetailPageURL'] = sprintf( 'https://www.amazon.%s/dp/%s/', $this->product_main['country'], $asin );
				$vars[] = $variation_child;

				// is variation child?
				$currentAsin = $this->product_main['ASIN'];
				if ( ! $found_parent && 0 === strcmp( $currentAsin, $variation_child['ASIN'] ) ) {

					//var_dump('<pre>', sprintf( 'currentAsin: %s ; parentAsin: %s', $currentAsin, $parentAsin ), '</pre>');
					if ( 0 !== strcmp( $currentAsin, $parentAsin ) ) {

						$this->parent_asin = $parentAsin;
						$this->is_variation = 1;
						$found_parent = true;
					}
				}
			}
		}

		$ret['variations_dimensions'] = array_replace_recursive( $ret['variations_dimensions'], $varsdim );
		$ret['variations'] = array_replace_recursive( $ret['variations'], $vars );
		//var_dump('<pre>', $ret , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		return $ret;
	}

	// get variations from widget
	private function _get_vars_from_widget( $content=array() ) {

		try {
			$widget = WidgetExtract::getInstance( $this->the_plugin );
			$widget->set_content( $content );
			$vars = $widget->extract();
			$widget_notices = $widget->get_notices();

			return array( 'widget_notices' => $widget_notices, 'variations' => $vars );
		}
		catch (\Exception $e) {
			$msg = "\"variations\" widget content parsing exception occured! " . $e->getMessage();
			throw new ProductExtractException( $msg );
		}
	}

	private function _get_widget_notices( $notices=array() ) {

		if ( empty($notices) ) {
			return '';
		}
		//return http_build_query( $notices );
		$ret = array();
		foreach ( $notices as $key => $val ) {
			$ret[] = "[$key] : $val";
		}
		return $ret;
	}

	// combine variations from page content with widget
	private function _combine_vars_from_content_widget( $vars_from_content, $vars_from_widget ) {

		$vars_content = $vars_from_content['variations'];
		$vars_widget = $vars_from_widget['variations'];

		$notices = array();

		foreach ( $vars_content as $key => $variation ) {

			$asin = $variation['ASIN'];
			$widget_var = isset($vars_widget["$asin"]) ? $vars_widget["$asin"] : array();

			if ( empty($widget_var) ) {
				$notices["$asin"] = "variation $asin was not found in widget";
			}
			$vars_content["$key"] = array_replace_recursive( $variation, $widget_var );
		}

		$vars_from_content['variations'] = $vars_content;
		$this->variations = $vars_from_content;

		// notices
		if ( ! empty($notices) ) {
			if ( isset($this->notices['variations']) ) {
				if ( is_array($this->notices['variations']) ) {
					$this->notices['variations']["combined"] = $notices;
				}
				else {
					$this->notices['variations'] = array( "default" => $this->notices['variations'], "combined" => $notices );
				}
			}
			else {
				$this->notices['variations'] = array( "combined" => $notices );
			}
		}

		return $this->variations;
	}



	//================================================
	//== MISC

} } // end class