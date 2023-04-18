<?php
namespace WooZone\Melib;
defined( 'ABSPATH' ) || exit;

use WooZoneVendor\Campo\UserAgent;
use WooZoneVendor\GuzzleHttp\Client;
use WooZoneVendor\GuzzleHttp\Exception\RequestException;
use WooZoneVendor\GuzzleHttp\Pool;
//use WooZoneVendor\GuzzleHttp\Psr7;
use WooZoneVendor\GuzzleHttp\Psr7\Request;
use WooZoneVendor\GuzzleHttp\Psr7\Response;

if (class_exists(CountryAvailabilityStats::class) !== true) { class CountryAvailabilityStats {

	//================================================
	//== PUBLIC
	//...

	//================================================
	//== PROTECTED & PRIVATE
	protected static $instance = null;

	protected static $debug = false;

	protected $amz_settings = array();

	protected $current = array(
		'product_id' => 0,
		'asin' => null,
		'countries' => array(),
		'domains' => array(),
	);



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
		//if ( defined('ISAADEV') ) {
		//	self::$debug = true;
		//}
	}



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	public function update( $countries, $pms=array() ) {
		// parameters
		$pms = array_merge(array(
			'product_id' => 0,
			'asin' => null,
		), $pms);
		extract($pms);
		//var_dump('<pre>', $pms, $countries, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if ( (int) $product_id <= 0 || is_null($asin) || ! is_array($countries) || empty($countries) ) {
			// WRONG INPUT PARAMETERS
			return false;
		}

		// get domains list
		$domains = array();
		foreach ($countries as $key => $val) {
			$domains[] = $val['domain'];
		}
		$this->current = array_merge( $this->current, array(
			'product_id' => $product_id,
			'asin' => $asin,
			'countries' => $countries,
			'domains' => $domains,
		));
		//var_dump('<pre>', $this->current, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		//return $this->current['countries']; //DEBUG

		$this->do_requests();
		$this->save();

		return $this->current['countries'];
	}

	//================================================
	//== MISC



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================
	private function do_requests() {

		//:: config
		$asin = $this->current['asin'];
		$domains = $this->current['domains'];

		$stats = array('success' => 0, 'error' => 0);


		//:: request
		$client = new Client();

		$requests = function() use($domains, $asin) {
			// $uri = 'https://www.amazon.it/gp/product/B07FBL6WSC/'; //OK
			// $uri = 'https://www.amazon.es/gp/product/B07FBL6WSX/'; //404 NOT FOUND
			// $uri = 'https://github.com/_abc_123_404';

			foreach ($domains as $domain) {
				$uri = $this->build_product_link( $domain, $asin );

				yield new Request('GET', $uri, array(
					'headers' => ['User-Agent' => UserAgent::random()]
				));		    	
			}
		};

		$pool = new Pool($client, $requests(), array(
			'concurrency' => 20,
			'fulfilled' => function (Response $response, $index) use(&$stats) {
				// this is delivered each successful response
				$stats['success']++;
				$newstat = 200 === (int) $response->getStatusCode() ? 1 : 0;
				$this->set_country_availability( $index, $newstat );

				if ( self::$debug ) {
					var_dump('<pre>--fulfilled', $index, '</pre>');
					var_dump($response->getStatusCode()); // HTTP status code;
					var_dump($response->getReasonPhrase()); // Response message;
				}
			},
			'rejected' => function (RequestException $e, $index) use(&$stats) {
				// this is delivered each failed request
				$stats['error']++;
				$this->set_country_availability( $index, 0 );

				if ($e->hasResponse()) {
					$response = $e->getResponse();
					$http_code = (int) $response->getStatusCode();
					if ( 404 !== $http_code ) {
						$this->set_country_availability( $index, 1 );
					}
				}

				if ( self::$debug ) {
					var_dump('<pre>--rejected', $index, '</pre>');
					//var_dump('<pre>-- request', Psr7\str($e->getRequest()) ,'</pre>');
					if ($e->hasResponse()) {
						//var_dump('<pre>-- response', Psr7\str($e->getResponse()) ,'</pre>');
						$response = $e->getResponse();
						var_dump($response->getStatusCode()); // HTTP status code;
						var_dump($response->getReasonPhrase()); // Response message;
						//var_dump((string) $response->getBody()); // Body, normally it is JSON;
						//var_dump(json_decode((string) $response->getBody())); // Body as the decoded JSON;
						//var_dump($response->getHeaders()); // Headers array;
						//var_dump($response->hasHeader('Content-Type')); // Is the header presented?
						//var_dump($response->getHeader('Content-Type')[0]); // Concrete header value;
					}
				}
			},
		));

		// Initiate the transfers and create a promise
		$promise = $pool->promise();

		// Force the pool of requests to complete.
		$promise->wait();

		if ( self::$debug ) {
			var_dump('<pre>', $this->current['countries'], '</pre>');
			//echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		}
	}

	private function save() {

		$product_id = $this->current['product_id'];

		$countries = $this->current['countries'];
		foreach ($countries as $key => $val) {
			if ( isset($countries["$key"]['name']) ) {
				unset($countries["$key"]['name']);
			}
		}

		$meta_value = array(
			'countries' => $countries,
			'countries_cache_time' => time(),
		);
		//var_dump('<pre>', $meta_value , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		update_post_meta( $product_id, '_amzaff_frontend', $meta_value );
	}

	//================================================
	//== MISC
	private function build_product_link( $domain, $asin ) {

		return "https://www.amazon.{$domain}/dp/{$asin}";
	}

	private function set_country_availability( $index, $status ) {

		if ( isset($this->current['countries'][$index]['domain']) ) {
			$this->current['countries'][$index]['available'] = $status;
		}
	}

} } // end class
