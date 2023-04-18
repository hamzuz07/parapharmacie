<div class="threesixty-shortcodes">
	<div class="dashicons dashicons-info"></div> <?php _e('You can also use shortcode generator when edit your post or page. ', 'topdevs'); ?>
	<?php _e('Click on "360&deg;" icon in text editor (use "Classic" block for WordPress 5.0 and higher) to see pop-up with all available parameters.', 'topdevs'); ?>
	<br/>
<?php if ( get_post_status( $post->ID ) == "auto-draft" ) : ?>
	<p class="description"><?php _e('You will see shortcode examples after save this Smart Product.', 'topdevs'); ?></p>
<?php elseif ( $images == "") : ?>
	<p class="description"><?php _e('Please choose images and save product before using shortcodes.', 'topdevs'); ?></p>
<?php else : ?>
	<p><?php _e('Below are examples of some shortcode options (please use shortcode generator to see all options available). You can use these shortcodes as is or combine the options the way you want.', 'topdevs'); ?></p>
	<table>
		<tr>
			<td><h4><?php _e('Default', 'topdevs'); ?></h4></td>
			<td><code>[smart-product id=<?php echo $post->ID; ?>]</code></td>
		</tr>
		<tr>
			<td>
				<h4><?php _e('Add Scrollbar', 'topdevs'); ?></h4>
			</td>
			<td>
				<code>[smart-product id=<?php echo $post->ID; ?> scrollbar=top]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> scrollbar=bottom]</code>
			</td>
			<td>
				<p class="description"><?php _e('More suitable for product animation than for 360 degree spin.', 'topdevs'); ?></p>
			</td>
		</tr>
		<tr>
			<td><h4><?php _e('Hide Border', 'topdevs'); ?></h4></td>
			<td>
				<code>[smart-product id=<?php echo $post->ID; ?> border=false]</code>
			</td>
			<td>
				<p class="description"><?php _e('Use for transparent images.', 'topdevs'); ?></p>
			</td>
		</tr>
		<tr>
			<td><h4><?php _e('Hide Navigation', 'topdevs'); ?></h4></td>
			<td>
				<code>[smart-product id=<?php echo $post->ID; ?> nav=false]</code>
			</td>
			<td>
				<p class="description"><?php _e('Use for small images, drag intreraction will be still active.', 'topdevs'); ?></p>
			</td>
		</tr>
		<tr>
			<td><h4><?php _e('Change Width', 'topdevs'); ?></h4></td>
			<td>
				<code>[smart-product id=<?php echo $post->ID; ?> width=300]</code><br/>
			</td>
			<td>
				<p class="description"><?php _e('If not set width of first image will be used. Height will be based on image ratio.', 'topdevs'); ?></p>
			</td>
		</tr>
		<tr>
			<td><h4><?php _e('Choose Color', 'topdevs'); ?></h4></td>
			<td>
				<code>[smart-product id=<?php echo $post->ID; ?> color=dark-blue]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> color=light-blue]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> color=red]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> color=brown]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> color=purple]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> color=gray]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> color=yellow]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> color=green]</code><br/>
			</td>
			<td>
				<p class="description"></p>
			</td>
		</tr>
		<tr>
			<td><h4><?php _e('Choose Style', 'topdevs'); ?></h4></td>
			<td>
				<code>[smart-product id=<?php echo $post->ID; ?> style=glow]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> style=fancy]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> style=wave]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> style=flat-round]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> style=flat-square]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> style=vintage]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> style=arrows]</code><br/>
				<code>[smart-product id=<?php echo $post->ID; ?> style=leather]</code><br/>
			</td>
			<td>
				<p class="description"></p>
			</td>
		</tr>
	</table>
<?php endif; ?>
</div>