<?php

$spv_product = array (

	"id" => array (
		"type"		=> 'post',
		"post_type" => 'smart-product',
		"value"		=> '',
		"title"		=> __('Smart Product','topdevs'),
		"desc"		=> '',
	),

	"width" => array (
		"type"	=> 'text',
		"value"	=> '',
		"units"	=> 'px',
		"title"	=> __('Width','topdevs'),
		"desc"	=> __('Keep it empty to use original image size','topdevs') 
	),

	"border" => array (
		"type"	=> 'select',
		"value"	=> 'true',
		"options"	=> array (
			"true" 	=> __('Show','topdevs'),
			"false" => __('Don\'t Show','topdevs'),
			),
		"title"	=> __('Border','topdevs'),
		"desc"	=> __('Choose if you want to show or hide viewer border','topdevs'),
	),

	"autoplay" => array (
		"type"	=> 'select',
		"value"	=> 'false',
		"options"	=> array (
			"true" 	=> __('True', 'topdevs'),
			"false" => __('False', 'topdevs'),
			),
		"title"	=> __('Autoplay', 'topdevs'),
		"desc"	=> __('Set to True to make it autoplay on load', 'topdevs'),
	),

	"interval" => array (
		"type"	=> 'text',
		"value"	=> '40',
		"title"	=> __('Frames Interval', 'topdevs'),
		"units" => 'ms',
		"desc"	=> __('Time beetween frames change. Increase to slow down product spin', 'topdevs'),
	),

	"fullscreen" => array (
		"type"	=> 'select',
		"value"	=> 'false',
		"options"	=> array (
			"true" 	=> __('True', 'topdevs'),
			"false" => __('False', 'topdevs'),
			),
		"title"	=> __('Fullscreen Lightbox', 'topdevs'),
		"desc"	=> __('Set to True to allow users open product in fullscreen lightbox', 'topdevs'),
	),

	"move_on_scroll" => array (
		"type"	=> 'select',
		"value"	=> 'false',
		"options"	=> array (
			"true" 	=> __('True', 'topdevs'),
			"false" => __('False', 'topdevs'),
			),
		"title"	=> __('Move On Scroll', 'topdevs'),
		"desc"	=> __('Set to True to animate product on page scroll', 'topdevs'),
	),

	"move_on_hover" => array (
		"type"	=> 'select',
		"value"	=> 'false',
		"options"	=> array (
			"true" 	=> __('True', 'topdevs'),
			"false" => __('False', 'topdevs'),
			),
		"title"	=> __('Move On Hover', 'topdevs'),
		"desc"	=> __('Set to True to animate product on mouse hover', 'topdevs'),
	),
);

$navigation = array (

	"nav" => array (
		"type"	=> 'select',
		"value"	=> 'true',
		"options"	=> array (
			"true" 	=> __('Show', 'topdevs'),
			"false" => __('Don\'t Show', 'topdevs'),
			),
		"title"	=> __('Icons', 'topdevs'),
		"desc"	=> __('Choose if you want to show or hide navigation icons', 'topdevs'),
		"condition" => array (
				"scrollbar" => "false"
			)
	),

	"scrollbar" => array (
		"type"	=> 'select',
		"value"	=> 'false',
		"options"	=> array (
			"false" 	=> __('Don\'t Show', 'topdevs'),
			"top" 		=> __('Top', 'topdevs'),
			"bottom" 	=> __('Bottom', 'topdevs'),
			"left" 		=> __('Left', 'topdevs'),
			"right" 	=> __('Right', 'topdevs'),
			),
		"title"	=> __('Scrollbar', 'topdevs'),
		"desc"	=> __('Choose if you want to show scrollbar on top or bottom or hise it', 'topdevs'),
	),

	"scrollbar_start" => array (
		"type"	=> 'text',
		"value"	=> '0',
		"title"	=> __('Scrollbar Start', 'topdevs'),
		"units" => __('frame', 'topdevs'),
		"desc"	=> __('Scrollbar start position (frame number)', 'topdevs'),
	),
	
	"color"	=> array (
		"type"	=> 'select',
		"value"	=> 'gray',
		"options"   => array (
			"dark-blue" 	=> __('Dark Blue', 'topdevs'),
			"light-blue" 	=> __('Light Blue', 'topdevs'),
			"red" 			=> __('Red', 'topdevs'),
			"brown" 		=> __('Brown', 'topdevs'),
			"purple" 		=> __('Purple', 'topdevs'),
			"gray" 			=> __('Gray', 'topdevs'),
			"yellow" 		=> __('Yellow', 'topdevs'),
			"green" 		=> __('Green', 'topdevs'),
			),
		"title"  => __('Color', 'topdevs'),
		"desc"  => __('Icons or Scrollbar color', 'topdevs'),
	),

	"style"	=> array (
		"type"	=> 'select',
		"value"	=> 'glow',
		"options"   => array (
			"glow" 			=> __('Glow', 'topdevs'),
			"fancy" 		=> __('Fancy', 'topdevs'),
			"wave" 			=> __('Wave', 'topdevs'),
			"flat-round" 	=> __('Flat Round', 'topdevs'),
			"flat-square" 	=> __('Flat Square', 'topdevs'),
			"vintage" 		=> __('Vintage', 'topdevs'),
			"arrows" 		=> __('Arrows', 'topdevs'),
			"leather" 		=> __('Leather', 'topdevs'),
			),
		"title"  => __('Style', 'topdevs'),
		"desc"  => __('Icons Style', 'topdevs'),
	),
 
);



$params = array (
	"product" => array (
		"title" 	=> '<div class="dashicons dashicons-visibility"></div> ' . __('Viewer', 'topdevs'),
		"params"	=> $spv_product,
		),

	"navigation" => array (
		"title" 	=> '<div class="dashicons dashicons-editor-code"></div> ' . __('Navigation', 'topdevs'),
		"params"	=> $navigation,
		),
	);

// Create instance
$spv_tinymce = new SmartProductViewerTinyMCE( 'spv_', $params );

?>