<?php
/**
 * just some refactoring to the author original package
 * 
 * ORIGINAL AUTHOR: osushi/jsobj2php github package
 * LICENSE: osushi/jsobj2php github package
 * https://github.com/Osushi/jsobj2php
 */

//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\Misc\JavaScriptObjectToPHP;

if (class_exists(Converter::class) !== true) { class Converter {

	const VERSION = '1.0';

	static protected $_instance;



	// Required __construct() function
	public function __construct( $parent=null ) {

		//parent::__construct( $parent );
	}

	// Singleton pattern
	static public function getInstance( $parent=null ) {
		if (!self::$_instance) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	}

	// main method
	public static function execute( $str, $isArray=false ) {

		//var_dump('<pre>', $str ,'</pre>');

		$str = mbTrim( $str );
		$str = sTrim( $str );

		self::validate( $str );

		//$str = preg_replace( '/([a-zA-Z0-9\_]+):/', '"$1":', $str ); //original
		//$str = preg_replace( '/([a-zA-Z0-9\_\s]+)(?=:\s*:\s*)/', '"$1":', $str ); //?=: is a lookahead subpattern

		//$str = preg_replace( '/\"[a-zA-Z0-9\_]+\":undefined,/', '', $str ); //original
		//$str = preg_replace( '/\"[a-zA-Z0-9\_\s]+\"(?:\s*:\s*)undefined,/', '', $str );

		//$str = str_replace( array(',]', ',}'), array(']', '}'), $str ); //original
		//$str = preg_replace( '/,(\s*(?:]|}))/imu', '$1', $str );


		// fix bug: ,"singleton", ]
		$str = str_replace( ', ]', ']', $str);
		$str = str_replace( ',]', ']', $str);

		//var_dump('<pre>', $str, json_decode( $str, true ), json_last_error(), json_last_error_msg(), $str, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$ret = json_decode( unicode_decode($str), $isArray );

		//var_dump( "<pre>", $ret, $str  , "</pre>" ) ; 
		if ( is_null($ret) ) {
			$ret = json_decode( $str, $isArray );
		}
		if ( is_null($ret) ) {
			throw new \Exception('Converter : Couldn\'t decode the string');
		}
		return $ret;
	}


	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	private static function validate( $str ) {

		if ( mb_strlen($str) < 1 ) {
			throw new \Exception('Converter : The given string is null');
		}
		if ( $str[0] != '{' || mb_substr($str, -1) != '}' ) {
			throw new \Exception('Converter : The given string is not a Javascript object');
		}
	}

} } // end class

function sTrim( $string ) {

	$string = preg_replace('/\s+/imu', ' ', $string);

	//$string = preg_replace( '/(\s|　|\n)/', '', $string ); //original
	$string = preg_replace( '/(\n)/', '', $string );

	return $string;
}

function mbTrim( $string, $charlist='\\\\s', $ltrim=true, $rtrim=true ) {

	$bothEnds = $ltrim && $rtrim;

	$charClassInner = preg_replace(
    	array('/[\^\-\]\\\]/S', '/\\\{4}/S'),
    	array('\\\\\\0', '\\'),
		$charlist
	);

	$workHorse = '['.$charClassInner.']+';
	$ltrim && $leftPattern = '^'.$workHorse;
	$rtrim && $rightPattern = $workHorse.'$';

	if ( $bothEnds ) {
		$patternMiddle = $leftPattern.'|'.$rightPattern;
	}
	elseif ( $ltrim ) {
		$patternMiddle = $leftPattern;
	}
	else {
		$patternMiddle = $rightPattern;
	}

	return preg_replace( "/$patternMiddle/usSD", '', $string );
}

function unicode_decode($string) {
	
	return preg_replace_callback( "/((?:[^\x09\x0A\x0D\x20-\x7E]{3})+)/", function ($matches) {

		$char = mb_convert_encoding($matches[1], "UTF-16", "UTF-8");
		$escaped = "";

		for ( $i = 0, $l = strlen($char); $i < $l; $i += 2 ) {
			$escaped .=  "\u" . sprintf( "%02x%02x", ord($char[$i]), ord($char[$i+1]) );
		}
		return $escaped;
	}, $string );
}

