<?php 
/**
 * The template for single product compare
 *
 * @since   2.2.4
 * @version 1.0.0
 */

ob_start();

	echo '<div class="single-compare">';

	if ( defined('YITH_WOOCOMPARE') && class_exists('YITH_Woocompare_Frontend') ) {
	    $obj = new YITH_Woocompare_Frontend();
	    echo $obj->add_compare_link();
	}
	else { ?>
		<a class="flex flex-wrap align-items-center">
			<span class="flex-inline justify-content-center align-items-center flex-nowrap">
	            <?php esc_html_e( 'Compare', 'xstore-core' ); ?> 
		            <span class="mtips" style="text-transform: none;">
		                <i class="et-icon et-exclamation" style="margin-left: 3px; vertical-align: middle; font-size: 75%;"></i>
		                <span class="mt-mes"><?php esc_html_e('To use Compare please install YITH WooCommerce Compare plugin', 'xstore-core'); ?></span>
		            </span>
		        </span>
			</a>
		<?php 
	}

	echo '</div>';

echo ob_get_clean(); 