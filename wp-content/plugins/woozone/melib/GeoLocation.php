<?php
namespace WooZone\Melib;
defined( 'ABSPATH' ) || exit;

use WooZone\Melib\Utils;
use WooZone\Melib\GeoLocationStats;

if (class_exists(GeoLocation::class) !== true) { class GeoLocation {

	//================================================
	//== PUBLIC
	//...

	//================================================
	//== PROTECTED & PRIVATE
	protected static $instance = null;

	protected $amz_settings = array();



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

		// $_SESSION = array();
		//var_dump('<pre>', $_SESSION , '</pre>'); echo __FILE__ . ":". __LINE__;die . PHP_EOL;
	}



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	// return array(...)
	public function get_services( $key='all', $return_field='all' ) {
		$config = WooZone()->amz_settings;

		//:: services list
		$services = array(
			// api.hostip.info = not working anymore //http://api.hostip.info/country.php?ip={ipaddress}
			// www.telize.com = was shut down on November 15th, 2015 //http://www.telize.com/geoip/{ipaddress}
			// www.geoplugin.net = not working anymore - verified on 2020-april-13 //http://www.geoplugin.net/json.gp?ip={ipaddress}

			'local_csv' => array(
				'title' => 'Local IP detection', //plugin local csv file with IP range lists
			),
			'ipinfo.io' => array(
				'title' => 'ipinfo.io',
				//'api' => 'http://ipinfo.io/{ipaddress}/geo',
				'api' => 'http://ipinfo.io/{ipaddress}/?token={access_token}',
				'api_url' => null,
				'website' => 'https://ipinfo.io/pricing',
				'keys' => array(
					'access_token' => array(
						'value' => null,
						'type' => 'input',
					),
				),
			),
			'api.ipstack.com' => array(
				'title' => 'api.ipstack.com',
				'api' => 'http://api.ipstack.com/{ipaddress}?access_key={access_key}',
				'api_url' => null,
				'website' => 'https://ipstack.com/signup/free',
				'keys' => array(
					'access_key' => array(
						'value' => null,
						'type' => 'input',
					),
				),
			),
			'ipapi.com' => array(
				'title' => 'ipapi.com',
				'api' => 'http://api.ipapi.com/{ipaddress}?access_key={access_key}',
				'api_url' => null,
				'website' => 'https://ipapi.com/signup/free',
				'keys' => array(
					'access_key' => array(
						'value' => null,
						'type' => 'input',
					),
				),
			),
			'ipapi.co' => array(
				'title' => 'ipapi.co',
				'api' => 'https://ipapi.co/{ipaddress}/json/',
				'api_url' => null,
				'website' => 'https://ipapi.co',
			),
			'whoisxmlapi.com' => array(
				'title' => 'whoisxmlapi.com',
				'api' => 'https://ip-geolocation.whoisxmlapi.com/api/v1?apiKey={apiKey}&ipAddress={ipaddress}',
				'api_url' => null,
				'website' => 'https://ip-geolocation.whoisxmlapi.com/api/signup',
				'keys' => array(
					'apiKey' => array(
						'value' => null,
						'type' => 'input',
					),
				),
			),
			'geo.ipify.org' => array(
				'title' => 'geo.ipify.org',
				'api' => 'https://geo.ipify.org/api/v1?apiKey={apiKey}&ipAddress={ipaddress}',
				'api_url' => null,
				'website' => 'https://geo.ipify.org/pricing',
				'keys' => array(
					'apiKey' => array(
						'value' => null,
						'type' => 'input',
					),
				),
			),
			'ipinfodb.com' => array(
				'title' => 'ipinfodb.com',
				'api' => 'http://api.ipinfodb.com/v3/ip-country/?key={key}&ip={ipaddress}&format=json',
				'api_url' => null,
				'website' => 'https://ipinfodb.com/register',
				'keys' => array(
					'key' => array(
						'value' => null,
						'type' => 'input',
					),
				),
			),
			'db-ip.com' => array(
				'title' => 'db-ip.com',
				'api' => 'http://api.db-ip.com/v2/{apiKey}/{ipaddress}',
				'api_url' => null,
				'website' => 'https://db-ip.com/api/pricing/',
				'keys' => array(
					'apiKey' => array(
						'value' => null,
						'type' => 'input',
						'default' => 'free',
					),
				),
			),
			'ipdata.co' => array(
				'title' => 'ipdata.co',
				'api' => 'https://api.ipdata.co/{ipaddress}?api-key={api-key}',
				'api_url' => null,
				'website' => 'https://ipdata.co/sign-up.html',
				'keys' => array(
					'api-key' => array(
						'value' => null,
						'type' => 'input',
						'default' => 'test',
					),
				),
			),
			'ipgeolocation.io' => array(
				'title' => 'ipgeolocation.io',
				'api' => 'https://api.ipgeolocation.io/ipgeo?apiKey={apiKey}&ip={ipaddress}&output=json',
				'api_url' => null,
				'website' => 'https://ipgeolocation.io/pricing.html',
				'keys' => array(
					'apiKey' => array(
						'value' => null,
						'type' => 'input',
					),
				),
			),
			'ip2location.com' => array(
				'title' => 'ip2location.com',
				'api' => 'https://api.ip2location.com/v2/?ip={ipaddress}&key={key}&package=WS1&addon=country&format=json&lang=en',
				'api_url' => null,
				'website' => 'https://www.ip2location.com/register',
				'keys' => array(
					'key' => array(
						'value' => null,
						'type' => 'input',
					),
				),
			),
			'ip-api.com' => array(
				'title' => 'ip-api.com',
				'api' => 'http://ip-api.com/json/{ipaddress}',
				'api_url' => null,
				'website' => 'https://ip-api.com/',
			),
			'api.ipgeolocationapi.com' => array(
				'title' => 'api.ipgeolocationapi.com',
				'api' => 'https://api.ipgeolocationapi.com/geolocate/{ipaddress}',
				'api_url' => null,
				'website' => 'https://ipgeolocationapi.com/',
			),
			'freegeoip.live' => array(
				'title' => 'freegeoip.live',
				'api' => 'https://freegeoip.live/json/{ipaddress}',
				'api_url' => null,
				'website' => 'https://freegeoip.live/',
			),
			'api.snoopi.io' => array(
				'title' => 'api.snoopi.io',
				'api' => 'https://api.snoopi.io/{ipaddress}?apikey={apikey}',
				'api_url' => null,
				'website' => 'https://www.snoopi.io/',
				'keys' => array(
					'apikey' => array(
						'value' => null,
						'type' => 'input',
					),
				),
			),
			'iplocate.io' => array(
				'title' => 'iplocate.io',
				'api' => 'https://www.iplocate.io/api/lookup/{ipaddress}',
				'api_url' => null,
				'website' => 'https://www.iplocate.io/',
			),
			'ipwhois.io' => array(
				'title' => 'ipwhois.io',
				'api' => 'http://free.ipwhois.io/json/{ipaddress}',
				'api_url' => null,
				'website' => 'https://ipwhois.io/',
			),
			'extreme-ip-lookup.com' => array(
				'title' => 'extreme-ip-lookup.com',
				'api' => 'https://extreme-ip-lookup.com/json/{ipaddress}',
				'api_url' => null,
				'website' => 'https://extreme-ip-lookup.com/',
			),
		);

		//:: add api keys saved in database for each service
		$config = WooZone()->amz_settings;
		// services_saved array( '<service>' => array( 'keyname1' => 'keyvalue1', 'keyname2' => 'keyvalue2' ... ) )
		$servicesdb = isset($config['services_saved']) ? $config['services_saved'] : array();
		//var_dump('<pre>', count($services), count($servicesdb), '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		foreach ($services as $sname => $sinfo) {
			if ( ! isset($sinfo['keys']) || empty($sinfo['keys']) ) {
				continue 1;
			}

			$servicesdb_keys = isset($servicesdb["$sname"]) ? $servicesdb["$sname"] : array();
			$servicesdb_keys = isset($servicesdb_keys['keys']) ? $servicesdb_keys['keys'] : array();

			foreach ($sinfo['keys'] as $skey_name => $skey_info) {
				if ( isset($servicesdb_keys["$skey_name"]) ) {
					$valfinal = $servicesdb_keys["$skey_name"];
				} else {
					$valfinal = isset($skey_info['default']) ? $skey_info['default'] : null;
				}

				$services["$sname"]['keys']["$skey_name"]['value'] = $valfinal;
			}
		}
		//var_dump('<pre>', $services , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		//:: build api urls
		foreach ($services as $sname => $sinfo) {
			if ( in_array($sname, array('local_csv')) ) {
				continue 1;
			}
			$url_api = $this->_get_service_apiurl( $sinfo );
			$services["$sname"]['api_url'] = $url_api;
		}

		//:: return
		if ( 'all' !== $key ) {
			$services = isset($services["$key"]) ? $services["$key"] : array();

			if ( 'all' !== $return_field ) {
				$services = isset($services["$return_field"]) ? $services["$return_field"] : null;
			}
		}
		return $services;
	}

	// return
	// array(
	// 		ip => (string)
	// 		user_country => array(
	//			["key"] => string(3) "com"
	//			["website"] => string(4) ".com" //always has the prefixed point (it's the amazon location, not main_aff_id)
	//			["affID"] => string(8) "jimmy-us"
	// 		)
	// 		country => array(
	// 			country => NOT-FOUND | US etc...,
	// 			status => invalid | valid,
	// 			msg => (string),
	// 		)
	// )
	public function get_country_perip_external( $pms=array() ) {
		$provider = WooZone()->cur_provider;

		$config = WooZone()->amz_settings;
		$service = '';
		if ( isset($config['services_used_forip']) && !empty($config['services_used_forip']) ) {
			$service = $config['services_used_forip'];
		}
		//$service = isset($_REQUEST['service_used']) ? $_REQUEST['service_used'] : ''; //DEBUG
		//$service = 'local_csv'; //DEBUG
		//var_dump('<pre>', $service , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$cache_client_country = isset(WooZone()->ss['cache_client_country'])
			? WooZone()->ss['cache_client_country'] : true;
		//$cache_client_country = false; //DEBUG

		// parameters
		$pms = array_merge(array(
			'service' => $service,
			'cache_client_country' => $cache_client_country,
		), $pms);
		extract($pms);
		//var_dump('<pre>', $pms, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$ret = array(
			'ip' => Utils::getInstance()->get_client_ip(),
			'service' => $service,
			'user_country' => array(),
			'country' => array(),
		);


		//if ( isset($_SESSION['WooZone_country']) ) unset($_SESSION["WooZone_country"]); // DEBUG
		//var_dump('<pre>', $_SESSION, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		//if ( isset($_COOKIE["WooZone_country"]) && !empty($_COOKIE["WooZone_country"]) ) {
		//  return unserialize($_COOKIE["WooZone_country"]);
		//}
		if ( $cache_client_country ) {
			if (
				isset($_SESSION['WooZone_country'], $_SESSION['WooZone_country']["$provider"])
				&& ! empty($_SESSION['WooZone_country']["$provider"])
				&& isset($_SESSION['WooZone_country']["$provider"]['user_country'])
				&& ! empty($_SESSION['WooZone_country']["$provider"]['user_country'])
				&& is_array($_SESSION['WooZone_country']["$provider"]['user_country'])
			) {
				$__ = $_SESSION['WooZone_country']["$provider"];
				$__ = maybe_unserialize($__);
				$ret = array_replace_recursive($ret, $__);
				return $ret;
			}
		}

		$country = $this->get_service_stat( $service );
		//var_dump('<pre>', 'country', $country, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$user_country = WooZone()->amzForUser($country['country']);
		//var_dump('<pre>', 'user_country', $user_country,'</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$ret = array_replace_recursive($ret, array(
			'user_country' => $user_country,
			'country' => $country,
		));

		//$this->cookie_set(array(
		//  'name'          => 'WooZone_country',
		//  'value'         => serialize($user_country),
		//  'expire_sec'    => strtotime( '+30 days' ) // time() + 604800, // 1 hour = 3600 || 1 day = 86400 || 1 week = 604800
		//));
		if ( $cache_client_country ) {
			// do cache only if we received a valid country from service api (or local csv), but don't cache if we'll use country per main affiliate id
			if (
				isset($country['country'])
				&& 'NOT-FOUND' !== $country['country']
				&& ! empty($country['country'])
			) {
				if ( ! isset($_SESSION['WooZone_country']) || ! is_array($_SESSION['WooZone_country']) ) {
					$_SESSION['WooZone_country'] = array();
				}

				$__ = maybe_serialize($ret);
				$_SESSION['WooZone_country']["$provider"] = $__;
			}
		}

		//var_dump('<pre>', $ret , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		return $ret;
	}

	// returns (string)
	public function get_service_apiurl( $service ) {

		$curservice_info = $this->get_services( $service );
		$service_url = isset($curservice_info['api_url']) ? $curservice_info['api_url'] : null;

		return $service_url;
	}

	// return
	// array(
	// 		country => NOT-FOUND | US etc...,
	// 		status => invalid | valid,
	// 		msg => (string),
	// )
	public function get_service_stat( $service ) {

		// local csv file with ip lists
		if ( 'local_csv' === $service ) {
			$country = $this->_get_service_local_csv();
		}
		// external service
		else {
			$country = $this->_get_service_external( $service );
		}
		//var_dump('<pre>', 'country', $country, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		return $country;
	}

	// return
	// array(
	// 		ip => (string)
	// 		checked_date => integer
	// 		response => array(
	// 			[service_key] => array(
	//				["checked_date"] => integer
	//				["response"] => array(
	// 					country => NOT-FOUND | US etc...,
	// 					status => invalid | valid,
	// 					msg => (string),
	//				)
	//			)
	// 		)
	// )
	public function check_all( $pms=array() ) {
		// parameters
		$pms = array_merge(array(
			'dosave' => false, // true | false
		), $pms);
		extract($pms);
		//var_dump('<pre>', $pms, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$ret = array(
			'ip' => Utils::getInstance()->get_client_ip(),
		);

		//:: check external apis
		$services = $this->get_services();
		$checkServices = array();
		$cc = 0;
		foreach ($services as $sname => $sinfo) {
			//if ( $cc >= 3 ) { break 1; } //DEBUG
			//if ( !in_array($sname, array('ipinfo.io')) ) { continue 1; } //DEBUG
			if ( in_array($sname, array('local_csv')) ) {
				$cc++;
				continue 1;
			}

			$checkServices["$sname"] = array(
				'url' => $sinfo['api_url'],
			);
			$cc++;
		}

		$checkApiStats = GeoLocationStats::getInstance()->check( $checkServices );
		//var_dump('<pre>', $checkApiStats , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		//:: build final response for checked apis (including local csv)
		$checkResp = array();
		foreach ($services as $sname => $sinfo) {

			if ( 'local_csv' === $sname ) {
				$check_response = $this->_get_service_local_csv();
				$check_response_date = time();
				$check_response_content = $check_response;
			}
			else {
				$check_response = isset($checkApiStats["$sname"]) ? $checkApiStats["$sname"] : array();
				$check_response_date = isset($check_response["date"]) ? $check_response["date"] : time();
				$check_response_content = isset($check_response["response"]) ? $check_response["response"] : null;
				$check_response_content = $this->_parse_service_external( $sname, $check_response_content );
			}

			$checkResp["$sname"] = array(
				'checked_date' => $check_response_date,
				'response' => $check_response_content,
			);
		}
		//var_dump('<pre>', $checkResp , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$ret = array_replace_recursive($ret, array(
			'checked_date' => time(),
			'response' => $checkResp,
		));

		if ( $dosave ) {
			update_option('WooZone_services_saved', $ret);
		}
		return $ret;
	}

	//================================================
	//== HTML Boxes
	public function get_box_config( $istab='', $is_subtab='' ) {

		$html = array();

		$options = WooZone()->settings();

		//start main box
		$html[] = '<div class="wzadmin-geolocation panel-body WooZone-panel-body WooZone-form-row ' . ($istab!='' ? ' '.$istab : '') . ($is_subtab!='' ? ' '.$is_subtab : '') . '">';

		$html[] = 	'<label class="WooZone-form-label">' . __('Geo Location', WooZone()->localizationName) . '</label>';

		$services = $this->get_services();
		$services_used_forip = isset($options['services_used_forip'])
			? (string) $options['services_used_forip'] : 'db-ip.com';

		$services_saved = get_option('WooZone_services_saved', array());
		$checked_ip = isset($services_saved["ip"]) ? $services_saved["ip"] : Utils::getInstance()->get_client_ip();
		//var_dump('<pre>', $checked_ip, $services_used_forip, $services , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		
		$allowed_countries = WooZone()->get_all_country2mainaffid( array(
			'uk2gb' 	=> true,
		));
		//var_dump('<pre>', $allowed_countries , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		ob_start();
	?>

	<div class="wzadmin-geolocation-wrapper">
		<div class="wzadmin-geolocation-header">
			<select name="services_used_forip" id="services_used_forip">
				<option disabled="disabled" value="">Choose a service and then "Save the settings" so it can be used!</option>
				<?php
					foreach ($services as $sname => $sinfo) {
						$is_selected = $services_used_forip === $sname ? "selected=\"selected\"" : '';
						echo "<option value=\"$sname\" $is_selected>{$sinfo['title']}</option>";
					}
				?>
			</select>

			<input type="button" value="Check All" class="WooZone-form-button WooZone-form-button-info WooZone-geolocation-check-all">
		</div>

		<div class="wzadmin-geolocation-desc">
			<p class="wzadmin-geolocation-text-desc">This option allows you to use an external server api for detecting client country per IP address or you can try our local IP detection. <br />Client country (per IP address) is used on website frontend, when we redirect to the amazon store for that country.</p>
		</div>

		<div class="wzadmin-geolocation-desc">
			<p class="wzadmin-geolocation-text-desc">
				<span>IP = <span class="wzadmin-geolocation-ip"><?php echo $checked_ip; ?></span></span>
				<span>!!! if country = NOT-FOUND or if country it's not one of the following countries (<?php echo implode(', ', $allowed_countries); ?>) then the selected Main Affiliate ID (from this amazon config module / Amazon Setup tab / General & Amazon) will be used instead.</span>
			</p>
		</div>

		<div class="wzadmin-geolocation-services wzg-grid-container">
			<?php
				echo $this->get_box_list_services( array(
					'services' => $services,
					'services_used_forip' => $services_used_forip,
					'services_saved' => $services_saved,
				));
			?>
		</div>
	</div>

	<script>
	(function($) {
		$("body").on("click", ".WooZone-geolocation-check-all", function(){

			var $box = $('.wzadmin-geolocation-services');
			$box.html('<div class="WooZone-meloader"></div>');

			$.post(ajaxurl, {
				'action' 		: 'WooZone_GeoLocation',
				'sub_action'	: 'check-all'
			}, function(response) {

				//console.log( 'WooZone-geolocation-check-all', $box, response );
				$('.wzadmin-geolocation-ip').html( response.ip );
				$box.html( response.html );
				return true;
			}, 'json');
		});
	})(jQuery);
	</script>

	<?php
		$html[] = ob_get_clean();

		$html[] = '</div>'; //end main box
		return implode( "\n", $html );
	}

	public function get_box_list_services( $pms=array() ) {

		// parameters
		$pms = array_merge(array(
			'services' => null,
			'services_used_forip' => null,
			'services_saved' => null,
		), $pms);

		if ( is_null($pms['services']) ) {
			$pms['services'] = $this->get_services();
		}
		if ( is_null($pms['services_used_forip']) ) {
			$pms['services_used_forip'] = isset($options['services_used_forip'])
				? (string) $options['services_used_forip'] : 'db-ip.com';
		}
		if ( is_null($pms['services_saved']) ) {
			$pms['services_saved'] = get_option('WooZone_services_saved', array());
		}
		//var_dump('<pre>', $services_used_forip, $services_saved, $services, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		extract($pms);
		//var_dump('<pre>', $pms, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$services_saved_list = isset($services_saved['response'])
			? $services_saved['response'] : array();

		$html = array();

		ob_start();
	?>

		<div class="wzg-grid-item"></div>
		<div class="wzg-grid-item">Service API</div>
		<div class="wzg-grid-item">Service Keys</div>
		<div class="wzg-grid-item">Last check</div>
		<div class="wzg-grid-item">Last status</div>

		<?php
			$cc = 0;
			foreach ($services as $sname => $sinfo) {
				$is_selected = $services_used_forip === $sname ? true : false;
				$css_is_selected = $is_selected ? 'wzg-item-selected' : '';

				$url_website = isset($sinfo['website']) ? $sinfo['website'] : null;
				//$url_api = GeoLocation::getInstance()->get_service_apiurl( $sname );
				$url_api = isset($sinfo['api_url']) ? $sinfo['api_url'] : null;
				$api_keys = isset($sinfo['keys']) ? $sinfo['keys'] : array();

				$checked_serv = isset($services_saved_list["$sname"]) ? $services_saved_list["$sname"] : null;
				$checked_data = isset($checked_serv["checked_date"]) ? $checked_serv["checked_date"] : null;
				$checked_data = ! is_null($checked_data) ? date('Y-m-d h:i', $checked_data) : '';
				$checked_resp = isset($checked_serv["response"]) ? $checked_serv["response"] : array();
				//var_dump('<pre>',$checked_data, $checked_resp ,'</pre>'); continue 1; //DEBUG

				$css_resp_status = '';
				if ( 'invalid' === $checked_resp['status'] ) {
					$css_resp_status = 'wzg-item-status-invalid';
				} else if ( 'valid' === $checked_resp['status'] ) {
					$css_resp_status = 'wzg-item-status-valid';
				}

				$css_item = $css_is_selected;
				$css_item2 = implode(' ', array($css_is_selected, $css_resp_status));

				$checked_resp_show = array();
				if ( isset($checked_resp['country']) ) {
					$checked_resp_show[] = "<span>COUNTRY = {$checked_resp['country']}</span>";
				}
				if ( isset($checked_resp['status']) && 'invalid' === $checked_resp['status'] ) {
					$checked_resp_show[] = " | {$checked_resp['msg']}";
				}
				$checked_resp_show = implode('', $checked_resp_show);

		?>
				<div class="wzg-grid-item wzg-col-nb <?php echo $css_item; ?>"><?php echo $cc+1; ?></div>
				<div class="wzg-grid-item wzg-col-api <?php echo $css_item; ?>">
					<?php echo $sinfo['title']; ?>

					<?php if ( !empty($url_website) ) { ?>
					<a href="<?php echo $url_website; ?>" target="_blank">
						<i class="fa fa-lg fa-external-link"></i>
					</a>
					<?php } ?>

					<?php if ( !empty($url_api) ) { ?>
					<a href="<?php echo $url_api; ?>" target="_blank">
						<i class="fa fa-lg fa-external-link"></i>
					</a>
					<?php } ?>
				</div>
				<div class="wzg-grid-item wzg-col-keys <?php echo $css_item; ?>">
					<?php
						foreach ($api_keys as $skey_name => $skey_info) {
							$elem_name = "services_saved[$sname][keys][$skey_name]";
							$elem_value = isset($skey_info['value']) ? $skey_info['value'] : null;

							$elem_placeh = sprintf( __('enter %s here', WooZone()->localizationName), $skey_name );
					?>
							<input type="text" id="<?php echo $elem_name; ?>" name="<?php echo $elem_name; ?>" value="<?php echo $elem_value; ?>" placeholder="<?php echo $elem_placeh; ?>">
					<?php
						}
					?>
				</div>
				<div class="wzg-grid-item wzg-col-date <?php echo $css_item; ?>">
					<?php echo $checked_data; ?>
				</div>
				<div class="wzg-grid-item wzg-col-status <?php echo $css_item2; ?>">
					<?php echo $checked_resp_show; ?>
				</div>
		<?php
				$cc++;
			}
		?>

	<?php
		$html[] = ob_get_clean();
		return implode( "\n", $html );
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	private function _get_service_external( $service ) {
		$service = trim($service);

		$country = 'NOT-FOUND';
		$msg = '';
		$ret = array(
			'country' => 'NOT-FOUND',
			'status' => 'invalid',
			'msg' => '',
		);


		$service_url = $this->get_service_apiurl( $service );
		if ( empty($service_url) ) {
			$msg = 'api service not found!';
			$ret = array_replace_recursive($ret, array(
				//'country' => $country,
				'status' => 'invalid',
				'msg' => $msg,
			));
			return $ret;
		}

		$get_user_location = wp_remote_get( $service_url );
		if ( is_wp_error( $get_user_location ) ) {
			//$msg = is_array($get_user_location->errors) ? maybe_serialize($get_user_location->errors) : $get_user_location->errors;
			$msg = maybe_serialize( $get_user_location->get_error_message() );
			$ret = array_replace_recursive($ret, array(
				//'country' => $country,
				'status' => 'invalid',
				'msg' => $msg,
			));
			//var_dump('<pre>', $service, $service_url, $ret , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
			return $ret;
		}
		//if ( wp_remote_retrieve_response_code( $get_user_location ) !== 200 ) ;

		//$content = isset($get_user_location['body']) ? $get_user_location['body'] : '';
		$content = wp_remote_retrieve_body( $get_user_location );
		//var_dump('<pre>', $service, $service_url, $content , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$content = $this->_parse_service_external( $service, $content );

		$ret = array_replace_recursive($ret, $content);
		//var_dump('<pre>', $service, $service_url, $ret , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		return $ret;
	}

	private function _parse_service_external( $service, $content ) {
		$country = 'NOT-FOUND';
		$msg = '';
		$ret = array(
			'country' => 'NOT-FOUND',
			'status' => 'invalid',
			'msg' => '',
		);

		if ( is_null($content) ) {
			$msg = 'api service json response: content is empty!';
			$ret = array_replace_recursive($ret, array(
				'msg' => $msg,
			));
			return $ret;
		}

		$content = json_decode($content);
		//var_dump('<pre>', $service, $service_url, $content , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if ( ! is_object($content) ) {
			$msg = 'api service json response: jsone decoded is invalid!';
			$ret = array_replace_recursive($ret, array(
				'msg' => $msg,
			));
			return $ret;
		}

		$country = $content;
		switch ($service) {
			// case 'www.geoplugin.net':
			// 	if ( isset($country->geoplugin_countryCode) ) {
			// 		$country = (string) $country->geoplugin_countryCode;
			// 		$country = strtoupper( $country );
			// 	} else {
			// 		$country = 'NOT-FOUND';
			// 		$msg = 'api service json response: missing field geoplugin_countryCode';
			// 	}
			// 	break;

			case 'ipinfo.io':
				if ( isset($country->country) ) {
					$country = (string) $country->country;
					$country = strtoupper( $country );
				}
				else if ( isset($country->error->message) ) {
					$msg = 'api service json response: ' . ((string) $country->error->message);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'api.ipstack.com':
				if ( isset($country->country_code) ) {
					$country = (string) $country->country_code;
					$country = strtoupper( $country );
				}
				else if ( isset($country->error->info) ) {
					$msg = 'api service json response: ' . ((string) $country->error->info);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'ipapi.com':
				if ( isset($country->country_code) ) {
					$country = (string) $country->country_code;
					$country = strtoupper( $country );
				}
				else if ( isset($country->error->info) ) {
					$msg = 'api service json response: ' . ((string) $country->error->info);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'whoisxmlapi.com':
				if ( isset($country->location->country) ) {
					$country = (string) $country->location->country;
					$country = strtoupper( $country );
				}
				else if ( isset($country->error) ) {
					$msg = 'api service json response: ' . ((string) $country->error);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'geo.ipify.org':
				if ( isset($country->location->country) ) {
					$country = (string) $country->location->country;
					$country = strtoupper( $country );
				}
				else if ( isset($country->messages) ) {
					$msg = 'api service json response: ' . ((string) $country->messages);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'ipinfodb.com':
				if ( isset($country->countryCode) && ! empty($country->countryCode) ) {
					$country = (string) $country->countryCode;
					$country = strtoupper( $country );
				}
				else if ( isset($country->statusMessage) ) {
					$msg = 'api service json response: ' . ((string) $country->statusMessage);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'db-ip.com':
				if ( isset($country->countryCode) && ! empty($country->countryCode) ) {
					$country = (string) $country->countryCode;
					$country = strtoupper( $country );
				}
				else if ( isset($country->error) ) {
					$msg = 'api service json response: ' . ((string) $country->error);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'ipdata.co':
				if ( isset($country->country_code) && ! empty($country->country_code) ) {
					$country = (string) $country->country_code;
					$country = strtoupper( $country );
				}
				else if ( isset($country->message) ) {
					$msg = 'api service json response: ' . ((string) $country->message);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'ipgeolocation.io':
				if ( isset($country->country_code2) && ! empty($country->country_code2) ) {
					$country = (string) $country->country_code2;
					$country = strtoupper( $country );
				}
				else if ( isset($country->message) ) {
					$msg = 'api service json response: ' . ((string) $country->message);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'ip2location.com':
				if ( isset($country->country_code) && ! empty($country->country_code) ) {
					$country = (string) $country->country_code;
					$country = strtoupper( $country );
				}
				else if ( isset($country->response) ) {
					$msg = 'api service json response: ' . ((string) $country->response);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'ip-api.com':
				if ( isset($country->countryCode) && ! empty($country->countryCode) ) {
					$country = (string) $country->countryCode;
					$country = strtoupper( $country );
				}
				else if ( isset($country->message) ) {
					$msg = 'api service json response: ' . ((string) $country->message);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'ipapi.co':
				if ( isset($country->country_code) && ! empty($country->country_code) ) {
					$country = (string) $country->country_code;
					$country = strtoupper( $country );
				}
				else if ( isset($country->reason) ) {
					$msg = 'api service json response: ' . ((string) $country->reason);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'api.ipgeolocationapi.com':
				if ( isset($country->alpha2) && ! empty($country->alpha2) ) {
					$country = (string) $country->alpha2;
					$country = strtoupper( $country );
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'freegeoip.live':
				if ( isset($country->country_code) && ! empty($country->country_code) ) {
					$country = (string) $country->country_code;
					$country = strtoupper( $country );
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'api.snoopi.io':
				if ( isset($country->CountryCode) && ! empty($country->CountryCode) ) {
					$country = (string) $country->CountryCode;
					$country = strtoupper( $country );
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'iplocate.io':
				if ( isset($country->country_code) && ! empty($country->country_code) ) {
					$country = (string) $country->country_code;
					$country = strtoupper( $country );
				}
				else if ( isset($country->error) ) {
					$msg = 'api service json response: ' . ((string) $country->error);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'ipwhois.io':
				if ( isset($country->country_code) && ! empty($country->country_code) ) {
					$country = (string) $country->country_code;
					$country = strtoupper( $country );
				}
				else if ( isset($country->message) ) {
					$msg = 'api service json response: ' . ((string) $country->message);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			case 'extreme-ip-lookup.com':
				if ( isset($country->countryCode) && ! empty($country->countryCode) ) {
					$country = (string) $country->countryCode;
					$country = strtoupper( $country );
				}
				else if ( isset($country->message) ) {
					$msg = 'api service json response: ' . ((string) $country->message);
					$country = 'NOT-FOUND';
				}
				else {
					$msg = 'api service json response: missing field country';
					$country = 'NOT-FOUND';
				}
				break;

			default:
				$msg = 'api service not found!';
				$country = 'NOT-FOUND';
				break;
		}

		$ret = array_replace_recursive($ret, array(
			'country' => $country,
			'status' => 'NOT-FOUND' === $country ? 'invalid' : 'valid',
			'msg' => $msg,
		));
		//var_dump('<pre>', $service, $service_url, $ret , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		return $ret;
	}

	private function _get_service_local_csv() {

		$country = 'NOT-FOUND';
		$msg = '';
		$ret = array(
			'country' => 'NOT-FOUND',
			'status' => 'invalid',
			'msg' => '',
		);


		$ip = Utils::getInstance()->get_client_ip();
		$ip2number = Utils::getInstance()->ip2number( $ip );
		//var_dump('<pre>', "ip = $ip, ipnumber = $ip2number", '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		// read csv hash (string with ip from list)
		$file = WooZone()->cfg['paths']['plugin_dir_path'] . 'assets/geolocation/GeoIPCountryWhois-hash.csv';
		$csv_hash = file_get_contents($file);
		//var_dump('<pre>', $csv_hash, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$csv_hash = explode(',', $csv_hash);
		//var_dump('<pre>', $csv_hash, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		// read csv full (ip from, ip to, country)
		$file = WooZone()->cfg['paths']['plugin_dir_path'] . 'assets/geolocation/GeoIPCountryWhois-full.csv';
		$csv_full = file_get_contents($file);
		//var_dump('<pre>', $csv_full, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$csv_full = explode(PHP_EOL, $csv_full);
		//var_dump('<pre>', $csv_full, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$ipHashIndex = $this->_binary_search( $ip2number, $csv_hash, array($this, '_binary_search_cmp') );
		//var_dump('<pre>', $ipHashIndex , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		// verify if is between (ip_from, ip_to) of csv row
		if ( $ipHashIndex < 0 ) {
			$ipHashIndex = abs( $ipHashIndex );
			$ipFullRow = $csv_full["$ipHashIndex"];
			$csv_row = explode(',', $ipFullRow);

			if ( $ip2number >= $csv_row[0] && $ip2number <= $csv_row[1] ) {
				$country = $csv_row[2];
			} else {
				$msg = 'local_csv: country not found!';
			}
		}
		// exact match in the list as ip_from of csv row
		else {
			$ipFullRow = $csv_full["$ipHashIndex"];
			$country = explode(',', $ipFullRow);
			$country = end($country);
		}

		$country = strtoupper( $country );

		//var_dump('<pre>', $ipHashIndex, $ipFullRow, $country , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		$ret = array_replace_recursive($ret, array(
			'country' => $country,
			'status' => 'NOT-FOUND' === $country ? 'invalid' : 'valid',
			'msg' => $msg,
		));
		return $ret;
	}

	//================================================
	//== MISC
	private function _get_service_apiurl( $curservice_info ) {
		$ip = Utils::getInstance()->get_client_ip();
		$ip2number = null; //Utils::getInstance()->ip2number( $ip );
		//var_dump('<pre>', "ip = $ip, ipnumber = $ip2number", '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$service_keys = isset($curservice_info['keys']) ? $curservice_info['keys'] : array();
		$service_url = isset($curservice_info['api']) ? $curservice_info['api'] : null;

		if ( empty($service_url) ) {
			return null;
		}

		// api url
		$service_url = str_replace('{ipaddress}', $ip, $service_url);

		// api keys
		foreach ($service_keys as $skey_name => $skey_info) {
			$service_url = str_replace('{'.$skey_name.'}', $skey_info['value'], $service_url);
		}
		//var_dump('<pre>', $service, $service_url, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		return $service_url;
	}

	/**
	 * Parameters:
	 *   $key - The key to be searched for.
	 *   $list - The sorted array.
	 *   $compare_func - A user defined function for comparison. Same definition as the one in usort
	 *   $low - First index of the array to be searched (local parameters).
	 *   $high - Last index of the array to be searched (local parameters).
	 *
	 * Return:
	 *   index of the search key if found, otherwise return -(insert_index + 1).
	 *   insert_index is the index of greatest element that is smaller than $key or count($list) if $key
	 *   is larger than all elements in the array.
	 *
	 * License: Feel free to use the code if you need it.
	 */
	private function _binary_search( $key, array $list, $compare_func ) {
		$low = 0;
		$high = count($list) - 1;

		while ($low <= $high) {
			$mid = (int) (($high - $low) / 2) + $low; // could use php ceil function
			$cmp = call_user_func($compare_func, $list[$mid], $key);

			if ($cmp < 0) {
				$low = $mid + 1;
			}
			else if ($cmp > 0) {
				$high = $mid - 1;
			}
			else {
				return $mid;
			}
		}
		return -($low - 1);
	}

	private function _binary_search_cmp( $a, $b ) {

		return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
	}

} } // end class
