<?php  
	/**
	 * The template created for displaying header cart options when woocommerce plugin is not installed 
	 *
	 * @version 1.0.0
	 * @since 1.4.0
	 */

	// section cart_off
	// Kirki::add_section( 'cart_off', array(
	//     'title'          => esc_html__( 'Cart', 'xstore-core' ),
	//     'panel' => 'header-builder',
	//     'icon' => 'dashicons-cart',
	// ) );

	add_filter( 'et/customizer/add/sections', function($sections){

		$args = array(
			'cart_off'	 => array(
				'name'        => 'cart_off',
				'title'          => esc_html__( 'Cart', 'xstore-core' ),
				'panel' => 'header-builder',
				'icon' => 'dashicons-cart',
				'type'			 => 'kirki-lazy',
				'dependency'     => array()
			)
		);
		return array_merge( $sections, $args );
	});

	// cart_off_text
	// Kirki::add_field( 'et_kirki_options', array (
	// 	'type'     => 'custom',
	// 	'settings' => 'cart_off_text',
	// 	'section'  => 'cart_off',
 //        'default'     => esc_html__('To use Cart options please install ', 'xstore-core') . '<a href="https://uk.wordpress.org/plugins/woocommerce/" rel="nofollow" target="_blank">' . esc_html__('WooCommerce', 'xstore-core') . '</a>',
	// ) );

	add_filter('et/customizer/add/fields/cart_off', function ( $fields ) {
		$args = array();

				// Array of fields
		$args = array(
			'cart_off_text'	=>	array(
				'name'		  => 'cart_off_text',
				'type'     => 'custom',
				'settings' => 'cart_off_text',
				'section'  => 'cart_off',
				'default'     => esc_html__('To use Cart options please install ', 'xstore-core') . '<a href="https://uk.wordpress.org/plugins/woocommerce/" rel="nofollow" target="_blank">' . esc_html__('WooCommerce', 'xstore-core') . '</a>',
			)
		);

		return array_merge( $fields, $args );

	});

?>