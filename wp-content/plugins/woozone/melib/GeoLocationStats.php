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

if (class_exists(GeoLocationStats::class) !== true) { class GeoLocationStats {

	//================================================
	//== PUBLIC
	//...

	//================================================
	//== PROTECTED & PRIVATE
	protected static $instance = null;

	protected static $debug = false;

	protected $amz_settings = array();

	protected $current = array(
		'services' => array(),
		'serv2check' => array(),
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
	}



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	public function check( $services, $pms=array() ) {
		// parameters
		$pms = array_merge(array(
		), $pms);
		extract($pms);
		//var_dump('<pre>', $pms, $services, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if ( ! is_array($services) || empty($services) ) {
			// WRONG INPUT PARAMETERS
			return false;
		}

		// get domains list
		$serv2check = array();
		foreach ($services as $key => $val) {
			$serv2check[] = $val['url'];
		}
		$this->current = array_merge( $this->current, array(
			'services' => $services,
			'serv2check' => $serv2check,
		));
		//var_dump('<pre>', $this->current, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		//return $this->current['services']; //DEBUG

		$this->do_requests();

		return $this->current['services'];
	}

	//================================================
	//== MISC



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================
	private function do_requests() {

		//:: config
		$domains = $this->current['serv2check'];
		$stats = array('success' => 0, 'error' => 0);

		//:: request
		$client = new Client();

		$requests = function() use($domains) {
			foreach ($domains as $domain) {
				$uri = $domain;

				yield new Request('GET', $uri, array(
					// 'headers' => [
					// 	'User-Agent' => UserAgent::random(),
					// 	'Cache-Control' => 'no-store', //'must-revalidate', //'no-cache',
					// ],
				));		    	
			}
		};

		$pool = new Pool($client, $requests(), array(
			'concurrency' => 20,
			'fulfilled' => function (Response $response, $index) use(&$stats) {
				// this is delivered each successful response
				$stats['success']++;
				$newstat = 200 === (int) $response->getStatusCode() ? 1 : 0;

				$newresponse = $newstat ? (string) $response->getBody() : null;
				$this->set_check_stat( $index, $newresponse );

				if ( self::$debug ) {
					var_dump('<pre>--fulfilled', $index, $this->current['serv2check'][$index], $response->getStatusCode(), $response->getReasonPhrase(), (string) $response->getBody(), '</pre>');
				}
			},
			'rejected' => function (RequestException $e, $index) use(&$stats) {
				// this is delivered each failed request
				$stats['error']++;
				$this->set_check_stat( $index, null );

				if ( self::$debug ) {
					var_dump('<pre>--rejected', $index, $this->current['serv2check'][$index], '</pre>');
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
			var_dump('<pre>', $this->current['services'], '</pre>');
			//echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		}
	}

	//================================================
	//== MISC
	private function set_check_stat( $index, $response ) {

		$url = $this->current['serv2check'][$index];

		foreach ( $this->current['services'] as $key => $value ) {
			if ( $url === $value['url']) {
				$__ = array_replace_recursive($value, array(
					'date' => time(),
					'response' => $response,
				));
				$this->current['services']["$key"] = $__;
			}
		}
	}

} } // end class
