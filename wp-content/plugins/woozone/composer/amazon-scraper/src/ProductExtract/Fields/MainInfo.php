<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract\Fields;

use WooZone\AmazonScraper\ProductExtract\ProductExtractException;
//use Symfony\Component\DomCrawler\Crawler;

if (class_exists(MainInfo::class) !== true) { class MainInfo extends AbstractField {

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
			'ASIN' 			=> $this->product_main['ASIN'],
			'ParentASIN' 	=> $this->product_main['ParentASIN'],
			'DetailPageURL' => null,
		);

		$data['DetailPageURL'] = sprintf( 'https://www.amazon.%s/dp/%s/', $this->product_main['country'], $this->product_main['ASIN'] );

		$this->_validate_fields( $data );

		return $data;
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

} } // end class