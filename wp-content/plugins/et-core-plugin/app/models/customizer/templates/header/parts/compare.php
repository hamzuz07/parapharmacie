<?php
/**
 * The template for displaying header compare block
 *
 * @since   2.3.7
 * @version 1.0.0
 */
?>

<?php

global $et_icons, $et_builder_globals;

$element_options = array();
$element_options['is_YITH_Woocompare'] = defined('YITH_WOOCOMPARE') && class_exists('YITH_Woocompare_Frontend');

if ( !$element_options['is_YITH_Woocompare'] ) { ?>
	<div class="et_element et_b_header-compare" data-title="<?php esc_html_e( 'Compare', 'xstore-core' ); ?>">
            <span class="flex flex-wrap full-width align-items-center currentColor">
                <span class="flex-inline justify-content-center align-items-center flex-nowrap">
                    <?php esc_html_e( 'Compare ', 'xstore-core' ); ?>
                    <span class="mtips" style="text-transform: none;">
                        <i class="et-icon et-exclamation" style="margin-left: 3px; vertical-align: middle; font-size: 75%;"></i>
                        <span class="mt-mes"><?php esc_html_e('Please, install YITH WoCommerce Compare plugin', 'xstore-core'); ?></span>
                    </span>
                </span>
            </span>
	</div>
	<?php
	return;
}

$html = '';

$element_options['compare_style'] = get_theme_mod( 'compare_style_et-desktop', 'type1' );
$element_options['compare_style'] = apply_filters('compare_style', $element_options['compare_style']);

if ( $et_builder_globals['in_mobile_menu'] ) {
	$element_options['compare_style'] = 'type1';
}

// header compare classes
$element_options['wrapper_class'] = ' flex align-items-center';
if ( $et_builder_globals['in_mobile_menu'] ) $element_options['wrapper_class'] .= ' justify-content-inherit';
$element_options['wrapper_class'] .= ' compare-' . $element_options['compare_style'];
$element_options['wrapper_class'] .= ( $et_builder_globals['in_mobile_menu'] ) ? '' : ' et_element-top-level';

$element_options['is_customize_preview'] = apply_filters('is_customize_preview', false);
$element_options['attributes'] = array();

if ( $element_options['is_customize_preview'] )
	$element_options['attributes'] = array(
		'data-title="' . esc_html__( 'Compare', 'xstore-core' ) . '"',
		'data-element="compare"'
	);

?>
	
	<div class="et_element et_b_header-compare <?php echo $element_options['wrapper_class']; ?>" <?php echo implode( ' ', $element_options['attributes'] ); ?>>
		<?php echo header_compare_callback(); ?>
	</div>

<?php unset($element_options);