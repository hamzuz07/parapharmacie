<footer class="footer text-center">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php
				if ( get_theme_mod('cart_checkout_footer_content', '') != '' ||
				     (get_theme_mod('cart_checkout_footer_content_sections', false) && get_theme_mod('cart_checkout_footer_content_section', '0') != '0' )) {
					echo html_blocks_callback( array(
						'section'         => 'cart_checkout_footer_content_section',
						'sections'        => 'cart_checkout_footer_content_sections',
						'html_backup'     => 'cart_checkout_footer_content',
						'section_content' => true
					) );
				}
				else {
				    $cart_checkout = Etheme_WooCommerce_Cart_Checkout::get_instance();
				    $cart_checkout->footer_default_content();
				} ?>
			</div>
		</div>
	</div>
	<div class="copyrights text-center"><?php
		    echo do_shortcode(get_theme_mod('cart_checkout_copyrights_content', esc_html__('Ⓒ Created by 8theme - Power Elite ThemeForest Author.', 'xstore')));
		?>
	</div>
</footer>
