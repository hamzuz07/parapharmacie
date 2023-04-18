<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract\Fields;

use WooZone\AmazonScraper\ProductExtract\ProductExtractException;
use Symfony\Component\DomCrawler\Crawler;

if (class_exists(Categories::class) !== true) { class Categories extends AbstractField {

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

		$data['categories'] = array();

		$categs = $this->_get_categories();
		if ( ! empty($categs) && is_array($categs) ) {
			$data['categories'] = $categs;
		}

		$this->_validate_fields( $data );

		return $data;
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	private function _get_categories() {

		$categs = $this->crawler->filter( '#wayfinding-breadcrumbs_feature_div ul li, #wayfinding-breadcrumbs_container ul li' );

		$categs_ = array();

		if ( $categs->count() ) {

			$categs_ = $categs->each( function( $node, $i ) {

				$cssClass = $node->attr('class');
				$node_html = $node->html();
				$node_text = $node->text();
				$node_text = $this->clean_html( $node_text );
				//var_dump('<pre>', $i, $cssClass, $node_text, $node_html, '</pre>');

				if ( preg_match( '/(a-breadcrumb-divider)/iu', $cssClass, $mCssClass ) && isset($mCssClass[1]) ) {
					return false;
				}

				if ( preg_match_all( '/node=([0-9]*)/imu', $node_html, $mHtml ) && isset($mHtml[1]) ) {
					$node_id = array_value_last( $mHtml[1] );
					return array(
						'id' 	=> $node_id,
						'name' 	=> $node_text,
					);
				}
				return false;
			});
		}

		$categs_ = array_values( array_filter( $categs_ ) );
		//var_dump('<pre>', $categs_ , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		return $categs_;
	}

} } // end class