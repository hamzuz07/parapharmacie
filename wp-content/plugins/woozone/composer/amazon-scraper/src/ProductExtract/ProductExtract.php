<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract;

use Symfony\Component\DomCrawler\Crawler;

if (class_exists(ProductExtract::class) !== true) { class ProductExtract {

	const VERSION = '1.0';

	static protected $_instance;

	public $the_plugin = null;

	protected $u; // utils function object!

	protected $paths;

	protected $content_orig = null; // original raw content
	protected $content = null; // content minified (no more than one consecutive spaces)
	protected $content_clean = null; // content striped of (style|script) tags

	// SETTER/ set some config settings - to be used internally
	protected $cfg = array(
		'measure_duration' => false,
	);

	// SETTER/ set some main product information
	protected $product_main = array(
		'ASIN' => null, // MANDATORY
		'ParentASIN' => null,
		'country' => null, // MANDATORY
	);
	protected $widget_vars = array(); // MANDATORY if it's a variable product

	// GETTER/ get product final data after extracting it from page content
	protected $product_data = array();

	// GETTER/ get extra details
	protected $extra_details = array(
		'duration' => array(),
		'duration_total' => 0,
	);

	protected $notices = array();
	protected $errors = array();



	// Required __construct() function
	protected function __construct( $parent=null ) {
		
		$this->the_plugin = $parent;

		$this->u = $this->the_plugin->u;

		// paths
		$this->paths = array(
			'composer_dir_url' => $this->the_plugin->cfg['paths']['composer_dir_url'],
			'composer_dir_path' => $this->the_plugin->cfg['paths']['composer_dir_path'],
		);
		$this->paths = array_merge( array(
			'product_extract_url' 	=> $this->paths['composer_dir_url'] . 'amazon-scraper/src/ProductExtract/', 
			'product_extract_path' 	=> $this->paths['composer_dir_path'] . 'amazon-scraper/src/ProductExtract/',
		));
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

	// set content
	public function set_content( $content ) {

		//$content = ''; //DEBUG
		if ( empty($content) ) {
			$msg = 'page content is empty!';
			throw new ProductExtractException( $msg );
		}

		$this->content_orig = $content;
		$this->content = $this->clean_text( $content );
		$this->content_clean = $this->clean_content( $content );

		return $this;
	}

	// set some config settings - to be used interlly
	public function set_cfg( $cfg=array() ) {

		$this->cfg = array_replace_recursive( $this->cfg, $cfg );
		return $this;
	}

	// set some main product information (like asin, parent asin, country...)
	public function set_product_main( $product_main=array() ) {

		$this->product_main = array_replace_recursive( $this->product_main, $product_main );

		if ( empty($this->product_main['ASIN']) || empty($this->product_main['country']) ) {
			$msg = 'product ASIN and country are mandatory for initialization!';
			throw new ProductExtractException( $msg );
		}
		return $this;
	}

	public function set_widget_vars( $widget_vars=array() ) {

		$this->widget_vars = $widget_vars;

		// update instance of variations with widget content
		$field_obj = $this->_load_field_class( 'Variations' );
		if ( is_object($field_obj) ) {
			return $field_obj->set_widget_vars( $this->widget_vars );
		}
	}

	// notices & errors during parsing
	public function get_notices( $class_name='' ) {

		$this->notices = array_filter( $this->notices );
		if ( ! empty($class_name) ) {
			return isset($this->notices["$class_name"]) ? $this->notices["$class_name"] : '';
		}
		return $this->notices;
	}
	public function get_errors() {

		return $this->errors;
	}

	// get product final data after extracting it from page content
	public function get_product_data() {

		$this->extract();
		return $this->product_data;
	}

	// get extra details
	public function get_extra_details() {

		return $this->extra_details;
	}

	// product is an ebook?
	// returns 0 | 1 | false on exception
	public function is_ebook() {

		$field_obj = $this->_load_field_class( 'Title' );
		if ( is_object($field_obj) ) {
			return $field_obj->is_ebook();
		}
		return false;
	}

	// product is a variable parent product? (returns the number of dimensions)
	// returns 0 | number of dimensions | false on exception
	public function is_variable() {

		$field_obj = $this->_load_field_class( 'Title' );
		if ( is_object($field_obj) ) {
			return $field_obj->is_variable();
		}
		return false;
	}

	// product is a variable child product? (a variation of a variable product)
	// returns 0 | 1 | false on exception
	public function is_variation() {

		$field_obj = $this->_load_field_class( 'Variations' );
		if ( is_object($field_obj) ) {
			return $field_obj->is_variation();
		}
		return false;
	}

	// return null ( if simple | variable parent ) | the parent of the variation child
	public function get_parent_asin() {

		$field_obj = $this->_load_field_class( 'Variations' );
		if ( is_object($field_obj) ) {
			return $field_obj->get_parent_asin();
		}
		return false;
	}

	// how many variations the parent product has?
	// returns 0 | number of variations | false on exception
	public function get_nb_variations() {

		$field_obj = $this->_load_field_class( 'Variations' );
		if ( is_object($field_obj) ) {
			return $field_obj->get_nb_variations();
		}
		return false;
	}

	// get asin for variation childs the parent product has?
	// returns array | false on exception
	public function get_variations_asin() {

		$field_obj = $this->_load_field_class( 'Variations' );
		if ( is_object($field_obj) ) {
			return $field_obj->get_variations_asin();
		}
		return false;
	}

	// get variations list
	// returns array( 'variations'=> array, 'variations_dimensions' => array ) | false
	public function get_variations() {

		$field_obj = $this->_load_field_class( 'Variations' );
		if ( is_object($field_obj) ) {
			return $field_obj->get_variations();
		}
		return false;
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	// main method to extract product data
	private function extract() {

		$this->notices = array();
		$this->errors = array();

		// importance order (ASCENDING) in which these important classes we'll be executed to extract data
		$classOrder = array(
			'MainInfo',
			'Title',
			'Variations',
			'Price',
			'Description',
			'Categories',
			'Images',
			'ItemAttributes',
			'AdditionalInfo',
		);
		$classOrder = array_fill_keys( $classOrder, false );

		$classFields = glob( $this->paths['product_extract_path'] . 'Fields/*.php' );

		foreach ( $classFields as $classField ) {

			$class_name = preg_replace('~(.*/)([\w\-]*)(\.php)$~iu', '$2', $classField);
			//$class_file = "Fields/{$class_name}.php";

			if ( in_array($class_name, array('AbstractField')) ) {
				continue 1;
			}

			$classOrder["$class_name"] = true;
		}
		$classOrder = array_keys( $classOrder );
		//var_dump('<pre>', $classOrder , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$duration = array();

		foreach ( $classOrder as $class_name ) {

			// start timer
			if ( $this->cfg['measure_duration'] ) {
				$timer_start = $this->the_plugin->timer_start();
			}

			$field_obj = $this->_load_field_class( $class_name );
			if ( false === $field_obj || ! is_object($field_obj) ) {
				continue 1;
			}

			$this->_product_add_field( $field_obj );

			// end timer & get duration
			if ( $this->cfg['measure_duration'] ) {
				$timer_end = $this->the_plugin->timer_end();
				$duration["$class_name"] = $timer_end;
			}
		}

		// calc total duration
		if ( $this->cfg['measure_duration'] ) {

			$duration_total = array_sum( $duration );
			$duration_total = $this->the_plugin->format_duration( $duration_total );

			foreach ( $duration as $kk => $vv ) {
				$duration["$kk"] = $this->the_plugin->format_duration( $vv );
			}

			$this->extra_details['duration'] = $duration;
			$this->extra_details['duration_total'] = $duration_total;
		}

		//$this->_exception_throw_message();

		return $this;
	}

	private function _product_add_field( $field_obj=null ) {

		if ( false === $field_obj || ! is_object($field_obj) ) {
			return false;
		}

		try {
			$data = $field_obj->extract();
			$this->notices = array_merge( $this->notices, $field_obj->get_notices() );
		}
		catch (ProductExtractException $e) {
			$this->errors[] = $e;
			throw $e;
		}
		catch (\Exception $e) {
			$this->errors[] = $e;
			throw $e;
		}

		//$this->_exception_throw_message();

		if ( isset($data) && ! empty($data) && is_array($data) ) {
			$this->product_data = array_replace_recursive( $this->product_data, $data );
			return $data;
		}
		return false;
	}

	private function _load_field_class( $class_name ) {

		$class_fullname = __NAMESPACE__.'\\Fields\\'.$class_name;

		if ( ! class_exists($class_fullname) ) {
			$this->notices[] = "php class $class_fullname doesn't exist.";
			return false;
		}

		$field_obj = $class_fullname::getInstance( $this->the_plugin );

		$pmsCrawler = array();
		if ( 'Description' === $class_name ) {
			$pmsCrawler['use_content'] = 'standard';
		}
		//var_dump('<pre>', $class_name, $pmsCrawler , '</pre>');

		$field_obj
			->set_content( $this->content_orig, $this->content, $this->content_clean )
			->set_crawler( null, $pmsCrawler )
			->set_product_main( $this->product_main );

		return $field_obj;
	}

	private function _exception_throw_message() {

		if ( ! empty($this->errors) ) {
			$msg = implode( PHP_EOL, array_merge( array('Hey, errors occured during data extraction'), array(''), $this->errors, array('')) );
			throw new ProductExtractException( $msg );
		}
	}



	//================================================
	//== MISC

	protected function clean_text( $text ) {

		$textNew = preg_replace('/\s+/imu', ' ', $text);
		//var_dump('<pre>', preg_last_error(), $this->preg_last_error_code( preg_last_error() ), '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		if ( null === $textNew || PREG_NO_ERROR !== preg_last_error() ) {
			$textNew = preg_replace('/\s+/im', ' ', $text);
		}
		//var_dump('<pre>', preg_last_error(), $this->preg_last_error_code( preg_last_error() ), '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		if ( null === $textNew || PREG_NO_ERROR !== preg_last_error() ) {
			return $text;
		}
		$textNew = trim( $textNew );
		return $textNew;
	}

	protected function clean_content( $html ) {

		$html = preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', "$1$3", $html);
		$html = $this->clean_text( $html );
		$html = str_replace("\x00", "", $html); // [FIX] - replace a null characted (\x00) by an empty string
		return $html;
	}

	public function preg_last_error_code( $errcode ) {

		static $errtext;

		if ( ! isset($errtxt) ) {
			$errtext = array();
			$constants = get_defined_constants(true);
			foreach ($constants['pcre'] as $c => $n) {
				if ( preg_match('/_ERROR$/', $c) ) {
					$errtext[$n] = $c;
				}
			}
		}
		return array_key_exists($errcode, $errtext)? $errtext[$errcode] : NULL;
	}

} } // end class