<?php 
/**
 * The template for single product wishlist
 *
 * @since   1.5.0
 * @version 1.0.0
 */

add_filter('yith_wcwl_add_to_wishlist_params', 'etheme_yith_wcwl_add_to_wishlist_params', 1, 10);
add_filter('yith_wcwl_add_to_wishlist_icon_html', 'etheme_yith_wcwl_add_to_wishlist_icon_html', 2, 999);
if ( function_exists('etheme_wishlist_btn') ) echo etheme_wishlist_btn( array(
	'class'=>'single-wishlist'
));
remove_filter('yith_wcwl_add_to_wishlist_icon_html', 'etheme_yith_wcwl_add_to_wishlist_icon_html', 2, 999);
remove_filter('yith_wcwl_add_to_wishlist_params', 'etheme_yith_wcwl_add_to_wishlist_params', 1, 10);