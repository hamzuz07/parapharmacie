<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract\Fields;

use WooZone\AmazonScraper\ProductExtract\ProductExtractException;
use Symfony\Component\DomCrawler\Crawler;

if (class_exists(AdditionalInfo::class) !== true) { class AdditionalInfo extends AbstractField {

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
			'brand' 		=> null,
			'amazonprime' 	=> null,
			'freeshipping' 	=> null,
		);

		$fields = array();
		$fields[] = array(
			'name' 		=> 'brand',
			'selector' 	=> '#bylineInfo',
			'attribute' => 'text',
			'callback' 	=> function ($value) {
				return $this->clean_text( $value );
			},
		);
		$fields[] = array(
			'name' 		=> 'amazonprime',
			'selector' 	=> '#price .a-icon.a-icon-prime',
			'attribute' => 'text',
			'callback' 	=> function ($value) {
				return ! is_null($value) && ! empty($value) ? 1 : 0;
			},
		);
		$fields[] = array(
			'name' 		=> 'freeshipping',
			'selector' 	=> '#price #ourprice_shippingmessage #price-shipping-message',
			'attribute' => 'html',
			'callback' 	=> function ($value) {
				return ! is_null($value) && ! empty($value) ? 1 : 0;
			},
		);

		$data = $this->_extract( $fields );
		$data = array_replace_recursive( $data_def, $data );

		//:: ebook case
		if ( $this->is_ebook() ) {
			$data['brand'] = '';
		}

		$this->_validate_fields( $data );

		return $data;
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

} } // end class