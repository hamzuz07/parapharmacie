<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract\Fields;

use WooZone\AmazonScraper\ProductExtract\ProductExtractException;
use Symfony\Component\DomCrawler\Crawler;

if (class_exists(Description::class) !== true) { class Description extends AbstractField {

	const VERSION = '1.0';

	static protected $_instance;



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

	// main method
	public function extract() {

		$data_def = array(
			'description' 		=> null,
			'short_description'	=> null,
		);

		$fields = array();
		$fields[] = array(
			'name' 		=> 'description',
			'selector' 	=> '#productDescription, #mas-product-description > div, #dpx-aplus-3p-product-description_feature_div #aplus3p_feature_div #aplus .aplus-v2',
			'attribute' => 'html',
			'callback' 	=> function ($value) {
				return $this->clean_text( $value );
			},
		);

		$data = $this->_extract( $fields );
		$data = array_replace_recursive( $data_def, $data );

		$data['short_description'] = $this->_get_short_desc();

		$this->_validate_fields( $data );

		return $data;
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	private function _get_short_desc() {

		$shortdesc = $this->crawler->filter( '#featurebullets_feature_div ul li, #mas-product-feature ul li' );

		$desc = array();

		if ( $shortdesc->count() ) {

			$desc = $shortdesc->each( function( $node, $i ) {

				$text = $node->text();
				$text = $this->clean_text( $text );
				return $text;
			});
		}
		//var_dump('<pre>', $desc , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if ( empty($desc) ) {
			$desc = $this->_get_ebook_short_desc();
		}
		return $desc;
	}

	private function _get_ebook_short_desc() {

		//:: PAGE CONTENT
		$content = $this->content_orig;

		//$node = $this->crawler->filter( '#bookDescription_feature_div #bookDesc_iframe_wrapper #bookDesc_iframe' );
		// !!! won't work, it's an iframe loaded dynamicaly after page content is retrieved!

		//P.when('DynamicIframe').execute(function(DynamicIframe){
		//var BookDescriptionIframe = null,
	    //bookDescEncodedData = "

		$s = preg_match( '/bookDescEncodedData = "(.*)",/imu', $content, $m);
		//var_dump('<pre>', $s, $m , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		if ( ! $s || ! isset($m[1]) ) {
			return array();
		}

		$desc = $m[1];
		$desc = rawurldecode( $desc );
		$desc = $this->clean_html( $desc );
		return array( $desc );
	}

} } // end class