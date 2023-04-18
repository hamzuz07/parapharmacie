<?php
namespace WooZone\Melib;
defined( 'ABSPATH' ) || exit;

use WooZone\Melib\WooMisc;
use WooZone\Melib\GeoLocation;
use WooZone\Melib\CountryAvailabilityStats;

if (class_exists(CountryAvailability::class) !== true) { class CountryAvailability {

	//================================================
	//== PUBLIC
	//...

	//================================================
	//== PROTECTED & PRIVATE
	protected static $instance = null;

	protected $amz_settings = array();
	protected $p_type = null;
	protected $countryflags_aslink = false;


	//================================================
	//== CONSTRUCTOR
	public static function getInstance() {
		if (is_null(static::$instance)) {
			static::$instance = new self(); //new static();
		}
		return static::$instance;
	}

	protected function __construct() {

		$this->amz_settings = WooZone()->amz_settings;
		$this->p_type = WooZone()->p_type;
		$this->countryflags_aslink = isset($this->amz_settings['product_countries_countryflags'])
			&& $this->amz_settings['product_countries_countryflags'] == "yes" ? true : false;
	}



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	public function update_product_countries( $pms=array() ) {
		// parameters
		$pms = array_merge(array(
			'product_id' => 0,
			'asin' => null,
		), $pms);
		extract($pms);
		//var_dump('<pre>', $pms, $countries, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$available_countries = $this->get_product_countries_available( $product_id, array(
			'asin' => $asin,
		));
		$available_rule = !empty($available_countries)
			&& isset($available_countries['available'])
			&& !empty($available_countries['available']);
		$available_countries = $available_rule ? $available_countries['available'] : false;

		return CountryAvailabilityStats::getInstance()->update( $available_countries, array(
			'product_id' => $product_id,
			'asin' => $asin
		));
	}

	//================================================
	//== BOXES
	// build minicart box with product country check
	public function box_country_check_minicart( $pms=array() ) {
		// parameters
		$pms = array_merge(array(
			'with_wrapper'			=> true,
			'box_position'			=> false,
		), $pms);
		extract($pms);
		
		// theme: kingdom
		$cart_items_nb = (int) WC()->cart->get_cart_contents_count();
		if ( ! $cart_items_nb ) {
			return false;
		}

		$minicart_items = array();

		$cart_items = WC()->cart->get_cart();
		foreach ( $cart_items as $key => $value ) {

			//$prod_id = isset($value['variation_id']) && (int)$value['variation_id'] > 0 ? $value['variation_id'] : $value['product_id'];
			$product_id = $value['product_id'];

			$asin = get_post_meta( $product_id, '_amzASIN', true );
			if ( empty($asin) ) continue 1;

			$product_country = $this->get_product_country_current( $product_id, array() );
			$product_country__ = $product_country;
			if ( !empty($product_country) && isset($product_country['website']) ) {
				$product_country = substr($product_country['website'], 1);
			}
			
			$country_name = $product_country__['name'];
			
			$country_status = $product_country__['available'];
			$country_status_css = 'available-todo'; $country_status_text = WooZone()->_translate_string( 'not verified yet' );
			switch ($country_status) {
				case 1:
					$country_status_css = 'available-yes';
					$country_status_text = WooZone()->_translate_string( 'is available' );
					break;
					
				case 0:
					$country_status_css = 'available-no';
					$country_status_text = WooZone()->_translate_string( 'not available' );
					break;
			}
			
			$minicart_items[] = array(
				'cart_item_key'				=> $key,
				'product_id'				=> $product_id,
				'asin'						=> $asin,
				'product_country'			=> $product_country,
				'country_name'				=> $country_name,
				'country_status_css'		=> $country_status_css,
				'country_status_text'		=> $country_status_text,
			);
		}

		ob_start();
	?>

<div class="WooZone-cc-small-cached" style="display: none;">
	<?php echo json_encode( $minicart_items ); ?>
</div>
<script type="text/template" id="WooZone-cc-small-template">
	<span class="WooZone-country-check-small WooZone-cc-custom">

		<span>
			<span class="WooZone-cc_domain"></span>
			<span class="WooZone-cc_status"></span>
		</span>

	</span>
</script>

	<?php
		$contents = ob_get_clean();
		return $contents;
	}

	// build small box with product country check
	public function box_country_check_small( $product, $pms=array() ) {
		// get product id
		$product_id = $product;
		if ( is_object($product) ) {
			$prod_id = 0;
			if ( method_exists( $product, 'get_id' ) ) {
				$prod_id = (int) $product->get_id();
			} else if ( isset($product->id) && (int) $product->id > 0 ) {
				$prod_id = (int) $product->id;
			}
			$product_id = $prod_id;
		}
		if ( empty($product_id) ) return false;

		// parameters
		$pms = array_merge(array(
			'with_wrapper'			=> true,
			'box_position'			=> false,
		), $pms);
		extract($pms);

		// get asin meta key
		$asin = get_post_meta($product_id, '_amzASIN', true);

		if ( empty($asin) ) return false; // verify to be amazon product!

		$first_variation_asin = WooMisc::getInstance()->get_asin_first_variation( $product_id );
		if( $first_variation_asin !== false ){
			$asin = $first_variation_asin;
		}
		//$asin = 'B000P0ZSHK'; // DEBUG
		//var_dump('<pre>',$asin,'</pre>');

		$product_country = $this->get_product_country_current( $product_id, array() );
		$product_country__ = $product_country;
		//var_dump('<pre>', $product_id, $product_country, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		if ( !empty($product_country) && isset($product_country['website']) ) {
			$product_country = substr($product_country['website'], 1);
		}
		
		//$all_countries_affid = WooZone()->get_ws_object( WooZone()->cur_provider )->get_countries('main_aff_id');
		//$country_affid = $product_country__['key'];
		//$country_name = isset($all_countries_affid["$country_affid"]) ? $all_countries_affid["$country_affid"] : 'missing country name';
		$country_name = $product_country__['name'];

		$country_status = $product_country__['available'];
		$country_status_css = 'available-todo'; $country_status_text = WooZone()->_translate_string( 'not verified yet' );
		switch ($country_status) {
			case 1:
				$country_status_css = 'available-yes';
				$country_status_text = WooZone()->_translate_string( 'is available' );
				break;
				
			case 0:
				$country_status_css = 'available-no';
				$country_status_text = WooZone()->_translate_string( 'not available' );
				break;
		}

		ob_start();
	?>

<?php if ($with_wrapper) { ?>
<span class="WooZone-country-check-small" data-prodid="<?php echo $product_id; ?>" data-asin="<?php echo $asin; ?>" data-prodcountry="<?php echo $product_country; ?>">
<?php } ?>

	<span>
		<span class="WooZone-cc_domain <?php echo str_replace('.', '-', $product_country); ?>" title="<?php echo $country_name; ?>"></span>
		<span class="WooZone-cc_status <?php echo $country_status_css; ?>" title="<?php echo $country_status_text; ?>"></span>
	</span>

<?php if ($with_wrapper) { ?>
</span>
<?php } ?>

	<?php
		$contents = ob_get_clean();
		return $contents;
	}

	// build main box with product country check
	public function box_country_check_details( $product, $pms=array() ) {
		// get product id
		$product_id = $product;
		if ( is_object($product) ) {
			$prod_id = 0;
			if ( method_exists( $product, 'get_id' ) ) {
				$prod_id = (int) $product->get_id();
			} else if ( isset($product->id) && (int) $product->id > 0 ) {
				$prod_id = (int) $product->id;
			}
			$product_id = $prod_id;
		}
		if ( empty($product_id) ) return false;

		// parameters
		$pms = array_merge(array(
			'with_wrapper'			=> true,
			'box_position'			=> false,
			'p_type' 				=> '',
		), $pms);
		extract($pms);
		
		// get asin meta key
		$asin = get_post_meta($product_id, '_amzASIN', true);
		if ( empty($asin) ) return false; // verify to be amazon product!

		$first_variation_asin = WooMisc::getInstance()->get_asin_first_variation( $product_id );
		if( $first_variation_asin !== false ){
			$asin = $first_variation_asin;
		}

		//$asin = 'B000P0ZSHK'; // DEBUG
		//var_dump('<pre>',$asin,'</pre>');
		
		$available_countries = $this->get_product_countries_available( $product_id, array(
			'asin' => $asin,
		));
		$do_update = is_array($available_countries)
			&& isset($available_countries['do_update']) ? (int) $available_countries['do_update'] : 0;
		$available_rule = !empty($available_countries)
			&& isset($available_countries['available'])
			&& !empty($available_countries['available']);
		$available_countries = $available_rule ? $available_countries['available'] : false;
		//var_dump('<pre>', $product_id, $available_countries, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if ( empty($available_countries) ) {
			return false;
		}

		$product_country = $this->get_product_country_current( $product_id, array() );
		//var_dump('<pre>', $product_id, $product_country, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		if ( !empty($product_country) && isset($product_country['website']) ) {
			$product_country = substr($product_country['website'], 1);
		}
		
		// aff ids
		$aff_ids = WooZone()->get_aff_ids();

		$product_data = array(
			'prodid' => $product_id,
			'asin' => $asin,
			'prodcountry' => $product_country,
			'boxpos' => $box_position,
			'do_update' => $do_update,
		);

		//:: get template
		$contents = WooZone_get_template_html( 'country_check/box_big.php', array_replace_recursive( array(
			'with_wrapper' 				=> $with_wrapper,
			'box_position' 				=> $box_position,
			'product_id' 				=> $product_id,
			'asin' 						=> $asin,
			'product_country' 			=> $product_country,
			'available_countries' 		=> $available_countries,
			'aff_ids' 					=> $aff_ids,
			'p_type' 					=> $this->p_type,
			'countryflags_aslink' 		=> $this->countryflags_aslink,
			'product_data' 				=> $product_data,
		), $pms ));

		//ob_start();
		//$contents = ob_get_clean();
		return $contents;
	}

	//================================================
	//== MISC
	// get product available amazon countries shops
	// - returns: false | main array( do_update => 0 | 1, available => array of countries )
	// - each item of 'available' from main array is of type:
	// 	array(
	// 		["domain"] => string(3) "com"
	//		["name"] => string(4) "United States"
	//		["available"] => 0 | 1 | -1 //!!! verify to be setted!
	// 	)
	public function get_product_countries_available( $product, $pms=array() ) {
		// get product id
		$product_id = $product;
		if ( is_object($product) ) {
			$prod_id = 0;
			if ( method_exists( $product, 'get_id' ) ) {
				$prod_id = (int) $product->get_id();
			} else if ( isset($product->id) && (int) $product->id > 0 ) {
				$prod_id = (int) $product->id;
			}
			$product_id = $prod_id;
		}
		if ( empty($product_id) ) return false;

		// parameters
		$pms = array_merge(array(
			'asin' => null,
		), $pms);
		extract($pms);

		if ( is_null($asin) ) {
			$asin = get_post_meta($product_id, '_amzASIN', true);
			$first_variation_asin = WooMisc::getInstance()->get_asin_first_variation( $product_id );
			if( $first_variation_asin !== false ){
				$asin = $first_variation_asin;
			}
		}

		// amazon location & main affiliate ids
		$affIds = (array) ( isset($this->amz_settings['AffiliateID']) ? $this->amz_settings['AffiliateID'] : array() );
		if ( empty($affIds) ) return false;

		$main_aff_id = WooZone()->main_aff_id();
		$main_aff_site = WooZone()->main_aff_site();

		// countries
		$all_countries = WooZone()->get_ws_object( 'amazon' )->get_countries('country');
		$all_countries_affid = WooZone()->get_ws_object( 'amazon' )->get_countries('main_aff_id');

		// loop through setted affiliate ids from amazon config
		$available = array(); $cc = 0;
		foreach ($affIds as $key => $val) {
			if ( empty($val) ) continue 1;

			$convertCountry = WooZone()->discount_convert_country2country();
			$domain = isset($convertCountry['amzwebsite']["$key"]) ? $convertCountry['amzwebsite']["$key"] : '';
			if ( empty($domain) ) continue 1;

			$available[$cc] = array(
				'domain'	=> $domain,
				'name'		=> isset($all_countries_affid["$key"]) ? $all_countries_affid["$key"] : 'missing country name',
			);
			$cc++;
		}
		if ( empty($available) ) {
			return false;
		}

		// verify affiliate ids based on product cached/saved available countries
		$meta_frontend = get_post_meta($product_id, '_amzaff_frontend', true);
		$cache_countries = isset($meta_frontend['countries']) && is_array($meta_frontend['countries']) ? $meta_frontend['countries'] : array();
		$cache_time = isset($meta_frontend['countries_cache_time']) ? $meta_frontend['countries_cache_time'] : 0;

		$cache_need_refresh = empty($cache_countries)
			|| !$cache_time
			|| ( ($cache_time + WooZone()->ss['countries_cache_time']) < time() );
		//var_dump('<pre>', $cache_need_refresh, $available , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		// product amazon countries availability needs refresh (mandatory)
		if ( $cache_need_refresh ) {
			//TODO
			return array(
				'available' => $available,
				'do_update' => 1,
			);
		}

		foreach ($available as $key => $val) {
			foreach ($cache_countries as $key2 => $val2) {
				// country founded
				if ( isset($val2['domain'], $val2['available']) && ($val['domain'] == $val2['domain']) ) {
					$available["$key"]['available'] = $val2['available'];
					break 1;
				}
			}
		}
		//var_dump('<pre>', $available , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		// we need to refresh if one country availability verification is missing!
		$c2refresh = array();
		foreach ($available as $key => $val) {
			if ( isset($val['domain'], $val['available']) ) {
				continue 1;
			}

			$c2refresh[] = $val['domain'];
		}
		//var_dump('<pre>', $c2refresh, $available , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return array(
			'available' => $available,
			'do_update' => ! empty($c2refresh) ? 1 : 0,
		);
	}

	// get product default country when added to cart (based on client country and main affiliate id)
	// return is of type:
	// array(
	//		["key"] => string(3) "com"
	//		["website"] => string(4) ".com" //always has the prefixed point (it's the amazon location, not main_aff_id)
	//		["affID"] => string(8) "jimmy-us"
	//		["name"] => string(4) "United States"
	//		["available"] => 0 | 1 | -1 //!!! verify to be setted!
	// )
	public function get_product_country_default( $product, $pms=array(), $find_client_country=true ) {
		// get product id
		$product_id = $product;
		if ( is_object($product) ) {
			$prod_id = 0;
			if ( method_exists( $product, 'get_id' ) ) {
				$prod_id = (int) $product->get_id();
			} else if ( isset($product->id) && (int) $product->id > 0 ) {
				$prod_id = (int) $product->id;
			}
			$product_id = $prod_id;
		}
		if ( empty($product_id) ) return false;

		// parameters
		$pms = array_merge(array(
			'asin' => null,
		), $pms);
		extract($pms);

		if ( is_null($asin) ) {
			$asin = get_post_meta($product_id, '_amzASIN', true);
			$first_variation_asin = WooMisc::getInstance()->get_asin_first_variation( $product_id );
			if( $first_variation_asin !== false ){
				$asin = $first_variation_asin;
			}
		}

		// client country
		$user_country = false;
		if ( $find_client_country ) {
			$user_country = GeoLocation::getInstance()->get_country_perip_external();
			$user_country = $user_country['user_country'];
		}
		//var_dump('<pre>', $user_country, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		// product available countries
		$available_countries = $this->get_product_countries_available( $product_id, array(
			'asin' => $asin
		));
		$available_rule = !empty($available_countries)
			&& isset($available_countries['available'])
			&& !empty($available_countries['available']);
		$available_countries = $available_rule ? $available_countries['available'] : false;
		$found = false; $first = false; $first_available = false;
		//var_dump('<pre>', $product_id, $available_countries, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if ( !empty($available_countries) ) {
			foreach ($available_countries as $key => $val) {

				if ( empty($first) ) {
					$first = $val['domain'];
				}

				if ( isset($val['available']) ) {
					if ( empty($first) ) {
						$first = $val['domain'];
					}
					if ( empty($first_available) && $val['available'] ) {
						$first_available = $val['domain'];
					}
				}

				if ( ! empty($user_country)
					&& isset($user_country['website'])
					&& substr($user_country['website'], 1) == $val['domain']
				) {
					$found = $val['domain'];
				}
			}
		}
		//var_dump('<pre>',$found, $first, $first_available,'</pre>'); echo __FILE__ . ":". __LINE__;die . PHP_EOL;

		// default country based on: first from all valid countries, first available country or found client country
		$the_country = false;
		if ( !empty($first) ) {
			$the_country = $first;
		}
		if ( !empty($first_available) ) {
			$the_country = $first_available;
		}
		if ( !empty($found) ) {
			$the_country = $found;
		}

		$country = WooZone()->domain2amzForUser( $the_country );
		if ( !empty($available_countries) ) {
			foreach ($available_countries as $key => $val) {
				if ( substr($country['website'], 1) == $val['domain'] ) {
					$country = array_merge($country, array(
						'name'			=> $val['name'],
						'available'		=> isset($val['available']) ? $val['available'] : -1,
					));
				}
			}
		}
		return $country;
	}

	// get product current country when added to cart (based on default country and if client choose a country by himself)
	// return is of type:
	// array(
	//		["key"] => string(3) "com"
	//		["website"] => string(4) ".com" //always has the prefixed point (it's the amazon location, not main_aff_id)
	//		["affID"] => string(8) "jimmy-us"
	//		["name"] => string(4) "United States"
	//		["available"] => 0 | 1 | -1 //!!! verify to be setted!
	// )
	public function get_product_country_current( $product, $pms=array(), $find_client_country=true ) {
		// get product id
		$product_id = $product;
		if ( is_object($product) ) {
			$prod_id = 0;
			if ( method_exists( $product, 'get_id' ) ) {
				$prod_id = (int) $product->get_id();
			} else if ( isset($product->id) && (int) $product->id > 0 ) {
				$prod_id = (int) $product->id;
			}
			$product_id = $prod_id;
		}
		if ( empty($product_id) ) return false;

		// parameters
		$pms = array_merge(array(
			'asin' => null,
		), $pms);
		extract($pms);

		if ( is_null($asin) ) {
			$asin = get_post_meta($product_id, '_amzASIN', true);
			$first_variation_asin = WooMisc::getInstance()->get_asin_first_variation( $product_id );
			if( $first_variation_asin !== false ){
				$asin = $first_variation_asin;
			}
		}

		$is_found = false;
		if ( !empty($asin)
			 && isset(
				$_SESSION['WooZone'],
				$_SESSION['WooZone']['product_country'],
				$_SESSION['WooZone']['product_country']["$asin"]
			 )
			 && !empty($_SESSION['WooZone']['product_country']["$asin"])
		) {
			$sess_country = $_SESSION['WooZone']['product_country']["$asin"];

			// product available countries
			$available_countries = $this->get_product_countries_available( $product_id, array(
				'asin' => $asin,
			));
			$available_rule = !empty($available_countries)
				&& isset($available_countries['available'])
				&& !empty($available_countries['available']);
			$available_countries = $available_rule ? $available_countries['available'] : false;

			if ( !empty($available_countries) ) {
				foreach ($available_countries as $key => $val) {

					if ( $sess_country == $val['domain'] ) {
						$the_country = $sess_country;
						$country = WooZone()->domain2amzForUser( $the_country );
						$country = array_merge($country, array(
							'name'			=> $val['name'],
							'available'		=> isset($val['available']) ? $val['available'] : -1,
						));
						$is_found = true;
					}
				}
			}
		}

		if ( ! $is_found ) {
			$the_country = $this->get_product_country_default( $product_id, array(
				'asin' => $asin
			), $find_client_country);
			$country = $the_country;
		}
		return $country;
	}

	// get product current country when added to cart (based on default country and if client choose a country by himself)
	// return is of type:
	// array(
	//		["key"] => string(3) "com"
	//		["website"] => string(4) ".com" //always has the prefixed point (it's the amazon location, not main_aff_id)
	//		["affID"] => string(8) "jimmy-us"
	//		["name"] => string(4) "United States"
	//		["available"] => 0 | 1 | -1 //!!! verify to be setted!
	// )
	public function get_product_country_import( $product, $pms=array() ) {
		// get product id
		$product_id = $product;
		if ( is_object($product) ) {
			$prod_id = 0;
			if ( method_exists( $product, 'get_id' ) ) {
				$prod_id = (int) $product->get_id();
			} else if ( isset($product->id) && (int) $product->id > 0 ) {
				$prod_id = (int) $product->id;
			}
			$product_id = $prod_id;
		}
		if ( empty($product_id) ) return false;

		// use product import country
		$country = get_post_meta( $product_id, '_amzaff_country', true );
		$country = WooZone()->domain2amzForUser( $country );
		if ( is_array($country)
			&& isset($country['key'])
			&& ! empty($country['key'])
		) {
			$country = array_merge($country, array(
				'available'		=> -1,
			));
			return $country;
		}

		//:: default if no import country!
		$country = $this->get_product_country_default( $product_id, array(), true );
		return $country;
	}


	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	//================================================
	//== MISC
	//...

} } // end class
