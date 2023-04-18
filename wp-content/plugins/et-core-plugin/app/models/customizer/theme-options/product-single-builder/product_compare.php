<?php  
/**
 * The template created for displaying product compare options
 *
 * @since   2.2.4
 * @version 1.0.0
 */
add_filter( 'et/customizer/add/sections', function($sections){

	$args = array(
		'product_compare'	 => array(
			'name'        => 'product_compare',
			'title'          => esc_html__( 'Compare', 'xstore-core' ),
			'panel'          => 'single_product_builder',
			'icon' => 'dashicons-update-alt',
			'type'			 => 'kirki-lazy',
			'dependency'     => array()
		)
	);
	return array_merge( $sections, $args );
});

add_filter('et/customizer/add/fields/product_compare', function ( $fields ) use($separators,$strings,$choices){
	$args = array();

			// Array of fields
	$args = array(

					// style separator
		'product_compare_style_separator'	=>	 array(
			'name'		  => 'product_compare_style_separator',
			'type'        => 'custom',
			'settings'    => 'product_compare_style_separator',
			'section'     => 'product_compare',
			'default'     => $separators['style'],
		),

					// product_compare_align
		'product_compare_align_et-desktop'	=>	 array(
			'name'		  => 'product_compare_align_et-desktop',
			'type'        => 'radio-buttonset',
			'settings'    => 'product_compare_align_et-desktop',
			'label'       => $strings['label']['alignment'],
			'description' => $strings['description']['size_bigger_attention'],
			'section'     => 'product_compare',
			'default'     => 'start',
			'choices'     => $choices['alignment_with_inherit'],
			'transport' => 'auto',
			'output'	  => array(
				array(
					'context'   => array('editor', 'front'),
					'element' => '.et_product-block .single-compare',
					'property' => 'text-align'
				)
			)
		),

					// product_compare_proportion
		'product_compare_proportion_et-desktop'	=>	 array(
			'name'		  => 'product_compare_proportion_et-desktop',
			'type'        => 'slider',
			'settings'    => 'product_compare_proportion_et-desktop',
			'label'       => $strings['label']['size_proportion'],
			'section'     => 'product_compare',
			'default'     => 1,
			'choices'     => array(
				'min'  => '0',
				'max'  => '5',
				'step' => '.01',
			),
			'transport' => 'auto',
			'output' => array(
				array(
					'context'   => array('editor', 'front'),
					'element' => 'body',
					'property' => '--single-product-compare-proportion',
				),
			),
		),

					// product_compare_background
					// 'product_compare_background_et-desktop'	=>	 array(
					// 'name'		  => 'product_compare_background_et-desktop',
					// 	'type'        => 'select',
					// 	'settings'    => 'product_compare_background_et-desktop',
					// 	'label'       => esc_html__( 'Background', 'xstore-core' ),
					// 	'section'     => 'product_compare',
					// 	'default'     => 'transparent',
					// 	'choices'     => $choices['colors'],
					// 	'transport' => 'auto',
					// 	'output'      => array(
					// 		array(
					// 'context'   => array('editor', 'front'),
					// 			'element' => '.et_product-block .tinvwl-shortcode-add-to-cart > a',
					// 			'property' => 'background-color',
					// 			'value_pattern' => 'var(--$-color)'
					// 		),
					// 	),
					// ),

					// product_compare_background_custom
		'product_compare_background_custom_et-desktop'	=>	 array(
			'name'		  => 'product_compare_background_custom_et-desktop',
			'type'        => 'color',
			'settings'    => 'product_compare_background_custom_et-desktop',
			'label'       => $strings['label']['wcag_bg_color'],
			'description' => $strings['description']['wcag_bg_color'],
			'section'     => 'product_compare',
			'choices' 	  => array (
				'alpha' => true
			),
			'default' => '#ffffff',
			'transport' => 'auto',
			'output'      => array(
				array(
					'context'   => array('editor', 'front'),
					'element' => '.et_product-block .single-compare > a',
					'property' => 'background-color',
				),
			),
		),

		'product_compare_color_et-desktop'	=>	array(
			'name'		  => 'product_compare_color_et-desktop',
			'settings'    => 'product_compare_color_et-desktop',
			'label'       => $strings['label']['wcag_color'],
			'description' => $strings['description']['wcag_color'],
			'type'        => 'kirki-wcag-tc',
			'section'     => 'product_compare',
			'default'     => '#000000',
			'choices'     => array(
				'setting' => 'setting(product_compare)(product_compare_background_custom_et-desktop)',
							// 'maxHueDiff'          => 60,   // Optional.
							// 'stepHue'             => 15,   // Optional.
							// 'maxSaturation'       => 0.5,  // Optional.
							// 'stepSaturation'      => 0.1,  // Optional.
							// 'stepLightness'       => 0.05, // Optional.
							// 'precissionThreshold' => 6,    // Optional.
							// 'contrastThreshold'   => 4.5   // Optional.	
				'show'    => array(
								// 'auto'        => false,
								// 'custom'      => false,
					'recommended' => false,
				),	
			),
			'transport' => 'auto',
			'output'	  => array(
				array(
					'context'   => array('editor', 'front'),
					'element' => '.et_product-block .single-compare > a',
					'property' => 'color'
				)
			),
		),

					// product_compare_background_hover_custom
		'product_compare_background_hover_custom_et-desktop'	=>	 array(
			'name'		  => 'product_compare_background_hover_custom_et-desktop',
			'type'        => 'color',
			'settings'    => 'product_compare_background_hover_custom_et-desktop',
			'label'       => $strings['label']['wcag_bg_color_hover'],
			'description' => $strings['description']['wcag_bg_color'],
			'section'     => 'product_compare',
			'choices' 	  => array (
				'alpha' => true
			),
			'default' => '#ffffff',
			'transport' => 'auto',
			'output'      => array(
				array(
					'context'   => array('editor', 'front'),
					'element' => '.et_product-block .single-compare a:hover',
					'property' => 'background-color',
				),
			),
		),

		'product_compare_hover_color_et-desktop'	=>	array(
			'name'		  => 'product_compare_hover_color_et-desktop',
			'settings'    => 'product_compare_hover_color_et-desktop',
			'label'       => $strings['label']['wcag_color_hover'],
			'description' => $strings['description']['wcag_color'],
			'type'        => 'kirki-wcag-tc',
			'section'     => 'product_compare',
			'default'     => '#000000',
			'choices'     => array(
				'setting' => 'setting(product_compare)(product_compare_background_hover_custom_et-desktop)',
							// 'maxHueDiff'          => 60,   // Optional.
							// 'stepHue'             => 15,   // Optional.
							// 'maxSaturation'       => 0.5,  // Optional.
							// 'stepSaturation'      => 0.1,  // Optional.
							// 'stepLightness'       => 0.05, // Optional.
							// 'precissionThreshold' => 6,    // Optional.
							// 'contrastThreshold'   => 4.5   // Optional.	
				'show'    => array(
								// 'auto'        => false,
								// 'custom'      => false,
					'recommended' => false,
				),	
			),
			'transport' => 'auto',
			'output'	  => array(
				array(
					'context'   => array('editor', 'front'),
					'element' => '.et_product-block .single-compare a:hover',
					'property' => 'color'
				)
			),
		),

					// product_compare_border_radius
		'product_compare_border_radius_et-desktop'	=>	 array(
			'name'		  => 'product_compare_border_radius_et-desktop',
			'type'        => 'slider',
			'settings'    => 'product_compare_border_radius_et-desktop',
			'label'       => $strings['label']['border_radius'],
			'section'     => 'product_compare',
			'default'     => 0,
			'choices'     => array(
				'min'  => '0',
				'max'  => '100',
				'step' => '1',
			),
			'transport' => 'auto',
			'output' => array(
				array(
					'context'   => array('editor', 'front'),
					'element' => '.et_product-block .single-compare > a',
					'property' => 'border-radius',
					'units' => 'px'
				)
			)
		),

		'product_compare_box_model_et-desktop'	=> array(
			'name'		  => 'product_compare_box_model_et-desktop',
			'settings'    => 'product_compare_box_model_et-desktop',
			'label'       => $strings['label']['computed_box'],
			'description' => $strings['description']['computed_box'],
			'type'        => 'kirki-box-model',
			'section'     => 'product_compare',
			'default'     => array(
				'margin-top'          => '0px',
				'margin-right'        => '0px',
				'margin-bottom'       => '10px',
				'margin-left'         => '0px',
				'border-top-width'    => '0px',
				'border-right-width'  => '0px',
				'border-bottom-width' => '0px',
				'border-left-width'   => '0px',
				'padding-top'         => '0px',
				'padding-right'       => '0px',
				'padding-bottom'      => '0px',
				'padding-left'        => '0px',
			),
			'output'      => array(
				array(
					'context'   => array('editor', 'front'),
					'element' => '.et_product-block .single-compare > a'
				),
			),
			'transport' => 'postMessage',
			'js_vars'   => box_model_output('.et_product-block .single-compare > a')
		),

					// product_compare_border
		'product_compare_border_et-desktop'	=>	 array(
			'name'		  => 'product_compare_border_et-desktop',
			'type'        => 'select',
			'settings'    => 'product_compare_border_et-desktop',
			'label'       => $strings['label']['border_style'],
			'section'     => 'product_compare',
			'default'     => 'solid',
			'choices'     => $choices['border_style'],
			'transport' => 'auto',
			'output'      => array(
				array(
					'context'   => array('editor', 'front'),
					'element' => '.et_product-block .single-compare > a',
					'property' => 'border-style',
				),
			),
		),

					// product_compare_border_color_custom
		'product_compare_border_color_custom_et-desktop'	=>	 array(
			'name'		  => 'product_compare_border_color_custom_et-desktop',
			'type'        => 'color',
			'settings'    => 'product_compare_border_color_custom_et-desktop',
			'label'		  => $strings['label']['border_color'],
			'section'     => 'product_compare',
			'default'	  => '#e1e1e1',
			'choices' 	  => array (
				'alpha' => true
			),
			'transport' => 'auto',
			'output'      => array(
				array(
					'context'   => array('editor', 'front'),
					'element' => '.et_product-block .single-compare > a',
					'property' => 'border-color',
				),
			),
		),


	);

return array_merge( $fields, $args );

});
