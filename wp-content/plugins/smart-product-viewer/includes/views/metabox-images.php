<input type="button" id="smart-product-upload-photos" value="<?php _e('Choose Images', 'topdevs'); ?>"/>
<?php if ( $images != array() ) : ?>
<input type="button" id="smart-product-reorder" value="<?php _e('Reverse Images Order', 'topdevs'); ?>"/>
<p class="description"><?php _e('You can drag below images to change the order.','topdevs'); ?></p>
<?php endif; ?>
<div id="smart-product-images-wrap">
	<ul id="smart-product-sortable">
	<?php foreach ( $images as $id ) : ?>
		<li data-id="<?php echo $id; ?>">
			<?php echo wp_get_attachment_image( $id, 'smart-product-thumb' ); ?>
			<span><?php echo basename( get_attached_file( $id ) ); ?></span>
		</li>	
	<?php endforeach; ?>
	</ul>
</div>
<div class="clear"></div>
