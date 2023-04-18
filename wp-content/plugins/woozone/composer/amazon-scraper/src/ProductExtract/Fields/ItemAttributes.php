<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract\Fields;

use WooZone\AmazonScraper\ProductExtract\ProductExtractException;
use Symfony\Component\DomCrawler\Crawler;
//use DOMDocument, DOMXPath;
//use DOMWrap\Document;
//use PHPHtmlParser\Dom;

if (class_exists(ItemAttributes::class) !== true) { class ItemAttributes extends AbstractField {

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

		$data = array(
			'item_attributes' 	=> array(),
			'authors' 			=> array(),
		);


		//:: item attributes
		$item_attributes = $this->_get_item_attributes();

		if ( ! empty($item_attributes) && is_array($item_attributes) ) {
			$data['item_attributes'] = $item_attributes;
		}


		//:: authors
		$authors = $this->_get_authors();

		if ( ! empty($authors) && is_array($authors) ) {
			$data['authors'] = $authors;
		}

		$data['item_attributes'] = array_replace_recursive( $data['item_attributes'], $data['authors'] );

		$this->_validate_fields( $data );

		return $data;
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	private function _get_item_attributes() {

		$itemattr_first = $this->_get_item_attributes_first();
		$itemattr_second = $this->_get_item_attributes_second();

		$itemattr = array_replace_recursive( $itemattr_first, $itemattr_second );

		return $itemattr;
	}

	private function _get_item_attributes_first() {

		$itemattr = $this->crawler->filter( '#productDetails_techSpec_section_1 tr, #productDetails_detailBullets_sections1 tr' );

		$itemattr_ = array();

		if ( $itemattr->count() ) {

			$itemattr_ = $itemattr->each( function( $node, $i ) {

				$skip_elements = array(
					'ASIN',
					'Amazon Best Sellers Rank',
					'Average Customer Review',
					'Best Sellers Rank',
					'Customer Reviews',
				);

				$td1 = ( $__ = $node->filter('th') ) && $__->count() ? $__->text() : '';
				$td1 = trim( $td1 );
				$td2 = ( $__ = $node->filter('td') ) && $__->count() ? $__->text() : '';
				$td2 = trim( $td2 );
				//var_dump('<pre>',$td1, $td2 ,'</pre>'); return true;

				if ( '' === $td2 || '' === $td1 || in_array($td1, $skip_elements) ) {
					return false;
				}
				return array( $td1, $td2 );
			});
		}

		$itemattr_ = array_values( array_filter( $itemattr_ ) );

		$itemattr_sel = $this->_array_to_selected( $itemattr_ );
		//var_dump('<pre>', $itemattr_sel , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return $itemattr_sel;
	}

	private function _get_item_attributes_second() {

		//<div id="detail-bullets"> #detail-bullets table .content ul
		$itemattr = $this->crawler->filter( '#detail-bullets table .content ul > li, #detailBullets #detailBullets_feature_div ul > li' );

		$itemattr_ = array();

		if ( $itemattr->count() ) {

			$itemattr_ = $itemattr->each( function( $node, $i ) {

				//var_dump('<pre>',$node->html() ,'</pre>'); return true;
				$skip_elements = array(
					'ASIN',
					'Amazon Best Sellers Rank',
					'Average Customer Review',
					'Best Sellers Rank',
					'Customer Reviews',
				);

				$td1 = ( $__td1 = $node->filter('b, strong') ) && $__td1->count() ? $__td1->text() : '';
				//$td1 = str_replace( ':', '', $td1 );
				$td1 = trim( $td1 );
				$td1 = trim( $td1, ':' );

				$td2 = $node->html();
				$td2 = $this->strip_tags_content( $td2 );
				$td2 = trim( $td2 );
				if ( '' === $td2 && ( $__alink = $node->filter('a') ) && $__alink->count() ) {
					$td2 = $__alink->text();
					$td2 = trim( $td2 );

					if ( ( $__spanrank = $node->filter('span.zg_hrsr_rank') ) && $__spanrank->count() ) {
						$td1 = $td2;
						$td2 = $__spanrank->text();
						$td2 = trim( $td2 );
					}
				}

				if ( '' === $td2 || '' === $td1 ) {
					$__td1 = ( $__td1 = $node->filter('.a-list-item') ) && $__td1->count() ? $__td1->text() : '';
					$__td1 = trim( $__td1 );
					$__td1 = explode(':', $__td1);
					$td1 = is_array($__td1) && isset($__td1[0]) ? $__td1[0] : '';
					$td1 = trim($td1);
					$td2 = is_array($__td1) && isset($__td1[1]) ? $__td1[1] : '';
					$td2 = trim($td2);
				}
				//var_dump('<pre>', 'td1=', $td1, 'td2=', $td2 ,'</pre>'); return true;

				if ( '' === $td2 || '' === $td1 || in_array($td1, $skip_elements) ) {
					return false;
				}
				return array( $td1, $td2 );
			});
		}

		$itemattr_ = array_values( array_filter( $itemattr_ ) );

		$itemattr_sel = $this->_array_to_selected( $itemattr_ );
		//var_dump('<pre>', $itemattr_sel , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return $itemattr_sel;
	}

} } // end class