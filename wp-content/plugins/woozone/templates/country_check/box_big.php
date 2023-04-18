<?php
!defined('ABSPATH') and exit;

global $WooZone;

do_action( 'woozone_template_country_check_box_big_before' );

$__ = compact( 'with_wrapper', 'box_position', 'product_id', 'asin', 'product_country', 'available_countries', 'aff_ids', 'p_type', 'countryflags_aslink', 'product_data' );
//var_dump('<pre>', $__ , '</pre>');
/*
	data-prodid="<?php echo $product_id; ?>"
	data-asin="<?php echo $asin; ?>"
	data-prodcountry="<?php echo $product_country; ?>"
	data-boxpos="<?php echo $box_position; ?>"
	data-do_update="<?php echo $do_update; ?>"
*/

?>

<?php if ($with_wrapper) { ?>
<ul class="WooZone-country-check" <?php echo !empty($box_position) ? 'style="display: none;"' : ''; ?>>
<?php } ?>

	<div class="WooZone-product-data" style="display: none;"><?php echo json_encode( $product_data ); ?></div>
	<div class="WooZone-country-cached" style="display: none;"><?php echo json_encode( $available_countries ); ?></div>
	<div class="WooZone-country-affid" style="display: none;"><?php echo json_encode( $aff_ids ); ?></div>
	<div class="WooZone-country-loader">
		<div>
			<div id="floatingBarsG">
				<div class="blockG" id="rotateG_01"></div>
				<div class="blockG" id="rotateG_02"></div>
				<div class="blockG" id="rotateG_03"></div>
				<div class="blockG" id="rotateG_04"></div>
				<div class="blockG" id="rotateG_05"></div>
				<div class="blockG" id="rotateG_06"></div>
				<div class="blockG" id="rotateG_07"></div>
				<div class="blockG" id="rotateG_08"></div>
			</div>
			<div class="WooZone-country-loader-text"></div>
		</div>
	</div>
	<div class="WooZone-country-loader bottom">
		<div>
			<div id="floatingBarsG">
				<div class="blockG" id="rotateG_01"></div>
				<div class="blockG" id="rotateG_02"></div>
				<div class="blockG" id="rotateG_03"></div>
				<div class="blockG" id="rotateG_04"></div>
				<div class="blockG" id="rotateG_05"></div>
				<div class="blockG" id="rotateG_06"></div>
				<div class="blockG" id="rotateG_07"></div>
				<div class="blockG" id="rotateG_08"></div>
			</div>
			<div class="WooZone-country-loader-text"></div>
		</div>
	</div>
	<div style="display: none;" id="WooZone-cc-template">
		<li>
			<?php if ( 'external' != $p_type ) { ?>
			<span class="WooZone-cc_checkbox">
				<input type="radio" name="WooZone-cc-choose[<?php echo $asin; ?>]" />
			</span>
			<?php } ?>
			<span class="WooZone-cc_domain<?php echo $countryflags_aslink ? ' WooZone-countryflag-aslink' : ''; ?>">
				<?php if ( $countryflags_aslink ) { ?>
				<a href="#" target="_blank"></a>
				<?php } ?>
			</span>
			<span class="WooZone-cc_name"><a href="#" target="_blank"></a></span>
			-
			<span class="WooZone-cc-status">
				<span class="WooZone-cc-loader">
					<span class="WooZone-cc-bounce1"></span>
					<span class="WooZone-cc-bounce2"></span>
					<span class="WooZone-cc-bounce3"></span>
				</span>
			</span>
		</li>
	</div>

<?php if ($with_wrapper) { ?>
</ul>
<?php } ?>

<?php do_action( 'woozone_template_country_check_box_big_after' ); ?>