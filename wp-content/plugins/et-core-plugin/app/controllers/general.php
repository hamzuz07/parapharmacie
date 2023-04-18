<?php
namespace ETC\App\Controllers;

use ETC\App\Controllers\Base_Controller;

/**
 * Import controller.
 *
 * @since      1.4.4
 * @package    ETC
 * @subpackage ETC/Controller
 */
class General extends Base_Controller {

	function hooks() {
		// Allow HTML in term (category, tag) descriptions
		foreach ( array( 'pre_term_description' ) as $filter ) {
			remove_filter( $filter, 'wp_filter_kses' );
		}

		foreach ( array( 'term_description' ) as $filter ) {
			remove_filter( $filter, 'wp_kses_data' );
		}

		add_filter( 'style_loader_src', array( $this, 'etheme_remove_cssjs_ver' ), 10, 2 );
		add_filter( 'script_loader_src', array( $this, 'etheme_remove_cssjs_ver' ), 10, 2 );
		add_action( 'init', array( $this, 'etheme_disable_emojis' ) );
	}

	function etheme_remove_cssjs_ver( $src ) {
		if ( function_exists( 'etheme_get_option' ) && etheme_get_option( 'cssjs_ver', 0 ) ) {

            // ! Do not do it for revslider and essential-grid.
			if ( strpos( $src, 'revslider' ) || strpos( $src, 'essential-grid' ) ) return $src;

			if( strpos( $src, '?ver=' ) ) $src = remove_query_arg( 'ver', $src );
		}
		return $src;   
	}

	function etheme_disable_emojis() {
		if ( function_exists( 'etheme_get_option' ) && etheme_get_option( 'disable_emoji', 0 ) ) {
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		}
	}
}