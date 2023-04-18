<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract\Fields;

use WooZone\AmazonScraper\ProductExtract\ProductExtractException;
use Symfony\Component\DomCrawler\Crawler;

if (class_exists(Price::class) !== true) { class Price extends AbstractField {

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
			'amazon_price' 		=> null, // Sale Price/ string, ex.: $155.22 | EU222,33
			'list_price'		=> null, // Regular Price/ string, ex.: $155.22 | EU222,33
		);

		$fields = array();
		$fields[] = array(
			'name' 		=> 'amazon_price',
			'selector' 	=> '#priceblock_ourprice, #priceblock_dealprice, #priceblock_saleprice, #price_inside_buybox, #corePrice_feature_div .a-price, #corePrice_feature_div .a-text-price',
			'attribute' => 'text',
			'callback' 	=> function ($value) {
				return $this->clean_text( $value );
			},
		);
		$fields[] = array(
			'name' 		=> 'list_price',
			'selector' 	=> '#price .a-text-strike',
			'attribute' => 'text',
			'callback' 	=> function ($value) {
				return $this->clean_text( $value );
			},
		);

		$data = $this->_extract( $fields );
		$data = array_replace_recursive( $data_def, $data );

		$data['amazon_price'] = $this->_get_amazon_price( $data['amazon_price'] );

		$this->_validate_fields( $data );

		if ( empty($data['amazon_price']) && empty($data['list_price']) ) {
			$this->notices['price'] = 'both price fields are empty!';
		}

		return $data;
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	private function _get_amazon_price( $price=null ) {

		if ( ! is_null($price) && '' !== $price ) {
			return $price;
		}

		if ( is_null($price) || '' === $price ) {
			//https://www.amazon.com/gp/product/1680229001/
			//.offer-price.a-color-price
			$price = $this->crawler->filter( '#buybox #buyNewSection .a-color-price' );
			$price = $price->count() ? $price->text() : null;
		}

		if ( is_null($price) || '' === $price ) {
			$price = $this->crawler->filter( '#buybox .kindle-price .a-color-price' );
			$price = $price->count() ? $price->text() : null;
		}

		if ( is_null($price) || '' === $price ) {
			$price = $this->crawler->filter( '#buybox #unqualifiedBuyBox .a-color-price' );
			$price = $price->count() ? $price->text() : null;
		}

		if ( is_null($price) || '' === $price ) {
			$price = $this->crawler->filter( '#newOfferAccordionRow .header-price' );
			$price = $price->count() ? $price->text() : null;
		}

		if ( is_null($price) || '' === $price ) {
			$price = $this->crawler->filter( '#mediaNoAccordion .header-price' );
			$price = $price->count() ? $price->text() : null;
		}

		return $price;
	}

} } // end class