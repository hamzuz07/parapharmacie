<form action="">
	<?php wp_nonce_field('smart_product_woo_metabox', 'smart_product_woo'); ?>
	<p>
		<label for="smart_product_show"><?php _e('Show as Product Image'); ?></label>
		<input type="checkbox" <?php checked('true', esc_attr( $show )) ?> id="smart_product_show" name="smart_product_show" value="true"/>
	</p>
	<p>	
		<label for=""><?php _e('Smart Product'); ?></label>
		<select class="widefat" name="smart_product_id">
			<option value=""><?php _e('None'); ?></option>
			<?php foreach ( $threesxity_sliders as $slider ) : ?>
			<option <?php echo selected( $slider->ID, esc_attr( $id )); ?> value="<?php echo $slider->ID; ?>"><?php echo get_the_title( $slider->ID ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<table>
		<tr>
			<td>	
				<label for="smart_product_nav"><?php _e('Show Navigation'); ?></label>
			</td>
			<td>
				<input type="checkbox" <?php checked('true', esc_attr( $nav )) ?> id="smart_product_nav" name="smart_product_nav" value="true"/>
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_border"><?php _e('Show Border'); ?></label>
			</td>
			<td>
				<input type="checkbox" <?php checked('true', esc_attr( $border )) ?> id="smart_product_nav" name="smart_product_border" value="true"/>
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_width"><?php _e('Width'); ?></label> 
			</td>
			<td>
				<input size="5" id="smart_product_width" name="smart_product_width" type="text" value="<?php echo esc_attr( $width ); ?>" />px
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_scrollbar"><?php _e('Show Scrollbar'); ?></label>
			</td>
			<td>
				<select class="widefat" name="smart_product_scrollbar" id="smart_product_scrollbar">
					<option <?php echo selected('', esc_attr( $scrollbar )); ?> value=""><?php _e('No', 'topdevs'); ?></option>
					<option <?php echo selected('top', esc_attr( $scrollbar )); ?> value="top"><?php _e('Top', 'topdevs'); ?></option>
					<option <?php echo selected('bottom', esc_attr( $scrollbar )); ?> value="bottom"><?php _e('Bottom', 'topdevs'); ?></option>
					<option <?php echo selected('left', esc_attr( $scrollbar )); ?> value="left"><?php _e('Left', 'topdevs'); ?></option>
					<option <?php echo selected('right', esc_attr( $scrollbar )); ?> value="right"><?php _e('Right', 'topdevs'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_scrollbar_start"><?php _e('Scrollbar Start'); ?></label> 
			</td>
			<td>
				<input size="5" id="smart_product_scrollbar_start" name="smart_product_scrollbar_start" type="text" value="<?php echo esc_attr( $scrollbar_start ); ?>" /> frame
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_direction"><?php _e('RTL Scrollbar'); ?></label>
			</td>
			<td>
				<input type="checkbox" <?php checked('rtl', esc_attr( $direction )) ?> id="smart_product_direction" name="smart_product_direction" value="rtl"/>
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_color"><?php _e('Color'); ?></label>
			</td>
			<td>
				<select class="widefat" name="smart_product_color" id="smart_product_color">
					<option <?php echo selected('dark-blue', 	esc_attr( $color )); ?> value="dark-blue"><?php _e('Dark Blue', 'topdevs');?></option>
					<option <?php echo selected('light-blue', 	esc_attr( $color )); ?> value="light-blue"><?php _e('Light Blue', 'topdevs');?></option>
					<option <?php echo selected('red', 			esc_attr( $color )); ?> value="red"><?php _e('Red', 'topdevs');?></option>
					<option <?php echo selected('brown', 		esc_attr( $color )); ?> value="brown"><?php _e('Brown', 'topdevs');?></option>
					<option <?php echo selected('purple', 		esc_attr( $color )); ?> value="purple"><?php _e('Purple', 'topdevs');?></option>
					<option <?php echo selected('gray', 		esc_attr( $color )); ?> value="gray"><?php _e('Gray', 'topdevs');?></option>
					<option <?php echo selected('yellow', 		esc_attr( $color )); ?> value="yellow"><?php _e('Yellow', 'topdevs');?></option>
					<option <?php echo selected('green', 		esc_attr( $color )); ?> value="green"><?php _e('Green', 'topdevs');?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_style"><?php _e('Style'); ?></label>
			</td>
			<td>
				<select class="widefat" name="smart_product_style" id="smart_product_style">
					<option <?php echo selected('glow', 		esc_attr( $style )); ?> value="glow"><?php _e('Glow', 'topdevs'); ?></option>
					<option <?php echo selected('fancy', 		esc_attr( $style )); ?> value="fancy"><?php _e('Fancy', 'topdevs'); ?></option>
					<option <?php echo selected('wave', 		esc_attr( $style )); ?> value="wave"><?php _e('>Wave', 'topdevs'); ?></option>
					<option <?php echo selected('flat-round', 	esc_attr( $style )); ?> value="flat-round"><?php _e('Flat Round', 'topdevs'); ?></option>
					<option <?php echo selected('flat-square', 	esc_attr( $style )); ?> value="flat-square"><?php _e('Flat Square', 'topdevs'); ?></option>
					<option <?php echo selected('vintage', 		esc_attr( $style )); ?> value="vintage"><?php _e('Vintage', 'topdevs'); ?></option>
					<option <?php echo selected('arrows', 		esc_attr( $style )); ?> value="arrows"><?php _e('Arrows', 'topdevs'); ?></option>
					<option <?php echo selected('leather', 		esc_attr( $style )); ?> value="leather"><?php _e('Leather', 'topdevs'); ?></option>
				</select>		
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_autoplay"><?php _e('Autoplay'); ?></label>
			</td>
			<td>
				<input type="checkbox" <?php checked('true', esc_attr( $autoplay )) ?> id="smart_product_autoplay" name="smart_product_autoplay" value="true"/>
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_interval"><?php _e('Frames Interval'); ?></label> 
			</td>
			<td>
				<input size="5" id="smart_product_interval" name="smart_product_interval" type="text" value="<?php echo esc_attr( $interval ); ?>" />ms
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_autoplay"><?php _e('Fullscreen Lightbox'); ?></label>
			</td>
			<td>
				<input type="checkbox" <?php checked('true', esc_attr( $fullscreen )) ?> id="smart_product_fullscreen" name="smart_product_fullscreen" value="true"/>
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_autoplay"><?php _e('Move On Page Scroll'); ?></label>
			</td>
			<td>
				<input type="checkbox" <?php checked('true', esc_attr( $move_on_scroll )) ?> id="smart_product_move_on_scroll" name="smart_product_move_on_scroll" value="true"/>
			</td>
		</tr>
	</table>
	<br/>
	<table>
		<tr>
			<td colspan="2">	
				<b><?php _e("WooCommerce Product Gallery"); ?></b>
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_show_gallery"><?php _e('Show Product Gallery'); ?></label>
			</td>
			<td>
				<input type="checkbox" <?php checked('true', esc_attr( $show_gallery )) ?> id="smart_product_show_gallery" name="smart_product_show_gallery" value="true"/>
			</td>
		</tr>
		<tr>
			<td>	
				<label for="smart_product_show_thumbnails"><?php _e('Show Thumbnails'); ?></label>
			</td>
			<td>
				<input type="checkbox" <?php checked('true', esc_attr( $show_thumbnails )) ?> id="smart_product_show_thumbnails" name="smart_product_show_thumbnails" value="true"/>
			</td>
		</tr>
		<tr>
			<td colspan="2">	
				<span class="dashicons dashicons-warning"></span> <span class="description"><?php _e("Caution! Above may work with only some themes and may brake your page layout!"); ?></span>
			</td>
		</tr>
	</table>
</form>