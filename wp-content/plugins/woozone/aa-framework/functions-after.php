<?php
! defined( 'ABSPATH' ) and exit;

require_once( dirname(__FILE__) . '/polyfill.php');

if ( !function_exists('amzStore_bulk_wp_exist_post_by_args') ) {
	function amzStore_bulk_wp_exist_post_by_args( $args ) {
		global $WooZone;
		return $WooZone->bulk_wp_exist_post_by_args( $args );
	}
}

if ( !function_exists('WooZone') ) {
	function WooZone() {
		global $WooZone;
		return $WooZone;
	}
}

if ( !function_exists('WooZone_product_by_asin') ) {
	function WooZone_product_by_asin( $asins=array() ) {
		global $WooZone;
		return $WooZone->product_by_asin( $asins );
	}
}

if ( !function_exists('WooZone_asset_path') ) {
	function WooZone_asset_path( $asset_type='css', $path='', $is_wp_enqueue=false, $pms=array() ) {
		global $WooZone;
		return $WooZone->plugin_asset_get_path( $asset_type, $path, $is_wp_enqueue, $pms );
	}
}

if ( !function_exists('WooZone_asset_version') ) {
	function WooZone_asset_version( $asset_type='css', $pms=array() ) {
		global $WooZone;
		return $WooZone->plugin_asset_get_version( $asset_type, $pms );
	}
}

if ( !function_exists('WooZone_debugbar') ) {
	function WooZone_debugbar() {
		global $WooZone;
		return $WooZone->debugbar;
	}
}

if ( !function_exists('WooZone_doing_it_wrong') ) {
	function WooZone_doing_it_wrong( $function, $message, $version ) {
		global $WooZone;
		return $WooZone->doing_it_wrong( $function, $message, $version );
	}
}

if ( !function_exists('WooZone_get_template') ) {
	function WooZone_get_template( $template_name, $pms=array() ) {
		global $WooZone;
		return $WooZone->tplsystem_get_template( $template_name, $pms );
	}
}

if ( !function_exists('WooZone_get_template_html') ) {
	function WooZone_get_template_html( $template_name, $pms=array() ) {
		global $WooZone;
		return $WooZone->tplsystem_get_template_html( $template_name, $pms );
	}
}

if ( !function_exists('WooZone_locate_template') ) {
	function WooZone_locate_template( $template_name, $pms=array() ) {
		global $WooZone;
		return $WooZone->tplsystem_locate_template( $template_name, $pms );
	}
}

if ( !function_exists('WooZone_dropshiptax_is_active') ) {
	function WooZone_dropshiptax_is_active() {
		global $WooZone;
		return $WooZone->dropshiptax_is_active();
	}
}

if ( !function_exists('WooZone_disable_amazon_checkout') ) {
	function WooZone_disable_amazon_checkout() {
		global $WooZone;
		return $WooZone->disable_amazon_checkout;
	}
}

if ( !function_exists('WooZone_get_post_meta') ) {
	function WooZone_get_post_meta( $post_id, $key='', $single=false, $withPrefix=true ) {
		global $WooZone;
		return $WooZone->get_post_meta( $post_id, $key, $single, $withPrefix );
	}
}

if ( !function_exists('WooZoneDirectImport') ) {
	function WooZoneDirectImport() {
		global $WooZone;
		return $WooZone->DirectImport;
	}
}