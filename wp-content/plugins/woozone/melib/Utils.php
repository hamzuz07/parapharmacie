<?php
namespace WooZone\Melib;
defined( 'ABSPATH' ) || exit;

use WooZone\MobileDetect\MobileDetect;

if (class_exists(Utils::class) !== true) { class Utils {

	//================================================
	//== PUBLIC
	//...

	//================================================
	//== PROTECTED & PRIVATE
	protected static $instance = null;
	//protected $amz_settings = array();



	//================================================
	//== CONSTRUCTOR
	public static function getInstance() {
		if (is_null(static::$instance)) {
			static::$instance = new self(); //new static();
		}
		return static::$instance;
	}

	protected function __construct() {
		//$this->amz_settings = WooZone()->amz_settings;
	}



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	public function get_client_ip() {
		$ipaddress = '';

		if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		}
		else if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		else if (isset($_SERVER['HTTP_X_FORWARDED']) && $_SERVER['HTTP_X_FORWARDED']) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		}
		else if (isset($_SERVER['HTTP_FORWARDED_FOR']) && $_SERVER['HTTP_FORWARDED_FOR']) {
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		}
		else if(isset($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED']) {
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		}
		else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		return $ipaddress;
	}

	public function ip2number( $ip ) {

		$long = ip2long($ip);
		if ($long == -1 || $long === false) {
			return false;
		}
		return sprintf("%u", $long);
	}

	public function let_to_num($size) {
		if ( function_exists('wc_let_to_num') ) {
			return wc_let_to_num( $size );
		}

		$l = substr($size, -1);
		$ret = substr($size, 0, -1);
		switch( strtoupper( $l ) ) {
			case 'P' :
				$ret *= 1024;
			case 'T' :
				$ret *= 1024;
			case 'G' :
				$ret *= 1024;
			case 'M' :
				$ret *= 1024;
			case 'K' :
				$ret *= 1024;
		}
		return $ret;
	}

	//verify if file exists!
	public function verifyFileExists($file, $type='file') {
		clearstatcache();
		if ($type=='file') {
			if (!file_exists($file) || !is_file($file) || !is_readable($file)) {
				return false;
			}
			return true;
		} else if ($type=='folder') {
			if (!is_dir($file) || !is_readable($file)) {
				return false;
			}
			return true;
		}
		// invalid type
		return 0;
	}

	//================================================
	//== MISC
	public function isMobileOnly() {
		$md = new MobileDetect();
		if ( $md->isMobile() && !$md->isTablet() ) {
			return true;
		}
		return false;
	}

	//================================================
	//== AMAZON related
	public function getAmazonCartLink( $domain ) {

		$link = "//www.amazon.$domain/gp/aws/cart/add%s.html";
		if ( $this->isMobileOnly() ) {
			return sprintf($link, '-res');
		}
		return sprintf($link, '');
	}


	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	//================================================
	//== MISC
	//...

} } // end class
