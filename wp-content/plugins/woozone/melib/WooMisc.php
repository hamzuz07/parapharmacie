<?php
namespace WooZone\Melib;
defined( 'ABSPATH' ) || exit;

if (class_exists(WooMisc::class) !== true) { class WooMisc {

	//================================================
	//== PUBLIC
	//...

	//================================================
	//== PROTECTED & PRIVATE
	protected static $instance = null;



	//================================================
	//== CONSTRUCTOR
	public static function getInstance() {
		if (is_null(static::$instance)) {
			static::$instance = new self(); //new static();
		}
		return static::$instance;
	}

	protected function __construct() {
	}



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	//================================================
	//== MISC
	public function get_asin_first_variation( $product_id ) {
		$asin = false;
		$_product = wc_get_product( $product_id );
		if ( $_product->is_type( 'variable' ) ){
			
			$variations = $_product->get_available_variations();
			if( isset($variations[0]['variation_id']) ){
				$variation_asin = get_post_meta( $variations[0]['variation_id'], '_amzASIN', true);
				if ( !empty($variation_asin) ) {
					$asin = $variation_asin;
				}
			}
		}

		return $asin;
	}


	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	//================================================
	//== MISC
	//...

} } // end class
