<?php
! defined( 'ABSPATH' ) and exit;

if ( !function_exists('WooZoneDebug') ) {
	function WooZoneDebug() {
		return defined( 'WOOZONE_DEBUG' ) ? WOOZONE_DEBUG : 0;
	}
}

if ( !function_exists('WooZone_generateRandomString') ) {
	function WooZone_generateRandomString( $length = 10 ) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}

if ( !function_exists('WooZone_get_plugin_data') ) {
	function WooZone_get_plugin_data( $path='' ) {
		if ( empty($path) ) {
			$path = str_replace('aa-framework/', '', plugin_dir_path( (__FILE__) )) . "plugin.php";
		}
  
		$source = file_get_contents( $path );
		$tokens = token_get_all( $source );
		$data   = array();
		if( trim($tokens[1][1]) != "" ){
			$__ = explode("\n", $tokens[1][1]);
			foreach ($__ as $key => $value) {
				$___ = explode(": ", $value);
				if( count($___) == 2 ){
					$data[trim(strtolower(str_replace(" ", '_', $___[0])))] = trim($___[1]);
				}
			}               
		}
  
		// For another way to implement it:
		//      see wp-admin/includes/update.php function get_plugin_data
		//      see wp-includes/functions.php function get_file_data
		return $data;  
	}
}

if ( !function_exists('WooZone_session_start') ) {
	function WooZone_session_start() {

		// we'll skip session init if on site health page
		$is_site_health = false;
		if (
			( isset($_SERVER['REQUEST_URI']) && strpos( $_SERVER['REQUEST_URI'], 'site-health.php') )
			|| ( isset($_SERVER['REQUEST_URI']) && strpos( $_SERVER['REQUEST_URI'], 'tools.php?page=health-check') )
			|| ( isset($_SERVER['HTTP_REFERER']) && strpos( $_SERVER['HTTP_REFERER'], 'site-health.php') )
			|| ( isset($_SERVER['HTTP_REFERER']) && strpos( $_SERVER['HTTP_REFERER'], 'tools.php?page=health-check') )
			|| ( isset($_SERVER['HTTP_REFERER']) && strpos( $_SERVER['HTTP_REFERER'], 'theme-editor.php') )
			|| ( isset($_SERVER['HTTP_REFERER']) && strpos( $_SERVER['HTTP_REFERER'], 'plugin-editor.php') )
		) {
			$is_site_health = true;
		}

		$session_id = null;
		if ( isset($_COOKIE['PHPSESSID']) ) {
			$session_id = $_COOKIE['PHPSESSID'];
		}
		else if ( isset($_REQUEST['PHPSESSID']) ) {
			$session_id = $_REQUEST['PHPSESSID'];
		}
		else {
			$session_id = @session_id();
		}

		if ( !$session_id ) {
			$session_id = wp_unslash( $session_id );
			@session_id( $session_id );
		}


		$sess_start = -1;
		$new_sess_id = -1;

		if ( ! $is_site_health ) {
			// session isn't started

			// Moodify Andrei D. 12.08.2020 wp 5.5 error
			if( !isset($_SESSION) ) {
				$sess_start = @session_start();
			}

			$new_sess_id = @session_id();
		}

		return array(
			'session_id' => $session_id,
			'session_start' => $sess_start,
			'new_sess_id' => $new_sess_id,
		);
	}
}

if ( !function_exists('WooZone_session_close') ) {
	function WooZone_session_close() {
		session_write_close(); // close the session
		//session_destroy();
	}
}

if ( !function_exists('WooZoneGetExceptionMsg') ) {
	function WooZoneGetExceptionMsg( $e ) {

		$msg = '';
		if ( isset($e->faultcode) ) {
			$msg = $e->faultcode .  ' : ' . (isset($e->faultstring) ? $e->faultstring : $e->getMessage());
		}
		else if ( is_callable( array($e, 'getMessage') ) ) {
			$msg = $e->getMessage();
		}
		//var_dump('<pre>', $msg , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		return $msg;
	}
}

if ( !function_exists('WooZoneGetExceptionCode') ) {
	function WooZoneGetExceptionCode( $e ) {

		$code = '';
		if ( isset($e->faultcode) ) {
			$code = $e->faultcode;
		}
		else if ( is_callable( array($e, 'getCode') ) ) {
			$code = $e->getCode();
		}
		//var_dump('<pre>', $code , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		return $code;
	}
}