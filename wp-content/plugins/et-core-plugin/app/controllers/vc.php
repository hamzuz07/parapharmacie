<?php

namespace ETC\App\Controllers;

use ETC\App\Controllers\Base_Controller;

/**
 * VC registration.
 *
 * @since      1.4.4
 * @package    ETC
 * @subpackage ETC/Models/Admin
 */
class VC extends Base_Controller {
	
	/**
	 * Registered widgets.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public static $shortcodes = null;
	
	public function hooks() {
	 
		add_action( 'init', array( $this, 'register_vc' ), 11 );
		add_action( 'init', function () {
			add_filter( 'vc_iconpicker-type-xstore-icons', array( $this, 'vc_iconpicker_type_xstore_icons' ) );
		}, 12 );
		add_action( 'admin_print_scripts-post.php', array( $this, 'registerBackendCss' ) );
		add_action( 'admin_print_scripts-post-new.php', array( $this, 'registerBackendCss' ) );
		
		add_action( 'admin_init', array( $this, 'registerBackendJs' ), 999 );
		
		
		add_action('ma/icon_fonts/select', function() {
			?>
			<option value="xstore-icons"><?php _e( 'XStore Icons', 'xstore-core' ); ?></option>
			<?php
		});
		
		add_filter('ma/icon_font/url', array($this, 'ma_xstore_icons_font_url'), 10, 2);

		add_action('ma/icon_fonts/grid', array($this, 'ma_xstore_icons_font_list'));
	}
	
	public function ma_xstore_icons_font_url($is_custom_url, $font) {
		if ( $font == 'xstore-icons' ) {
			$is_custom_url = get_template_directory_uri() . '/css/xstore-icons.css';
		}
		return $is_custom_url;
	}
	
	public function ma_xstore_icons_font_list($font) {
		if ( $font === 'xstore-icons' ) :
			$class = self::get_instance();
			$xstore_icons = $class->vc_iconpicker_type_xstore_icons(array()); // tweak to get only xstore-icons
			?>
            <div class="mpc-xstore-icons">
				<?php foreach( $xstore_icons as $icon_key ) :
					foreach( $icon_key as $icon_real_key => $icon_value) : ?>
                        <i class="et-icon <?php esc_attr_e( $icon_value ); ?>" title="<?php esc_attr_e( $icon_value ); ?>"></i>
					<?php endforeach; ?>
				<?php endforeach; ?>
                <div class="mpc-copyright" style="clear: both;"><?php _e( 'XStore Icons set website: <a href="https://xstore.8theme.com/xstore-icons/">https://xstore.8theme.com/xstore-icons/</a>.', 'xstore-core' ); ?></div>
            </div>
		<?php
		endif;
	}
	
	public function registerBackendCss() {
		wp_register_style( 'xstore-icons-font', false );
		wp_enqueue_style( 'xstore-icons-font' );
		wp_add_inline_style( 'xstore-icons-font',
			"@font-face {
              font-family: 'xstore-icons';
              src:
                url('" . get_template_directory_uri() . "/fonts/xstore-icons-light.ttf') format('truetype'),
                url('" . get_template_directory_uri() . "/fonts/xstore-icons-light.woff2') format('woff2'),
                url('" . get_template_directory_uri() . "/fonts/xstore-icons-light.woff') format('woff'),
                url('" . get_template_directory_uri() . "/fonts/xstore-icons-light.svg#xstore-icons') format('svg');
              font-weight: normal;
              font-style: normal;
            }"
		);
		wp_enqueue_style( 'xstore-icons-font-style', get_template_directory_uri() . '/css/xstore-icons.css' );
	}
	
	public function registerBackendJs() {
		
		wp_enqueue_script( 'xstore-vc-config', ET_CORE_URL . 'config/vc/js/config.js', array(), false, true );
		
	}
	
	public function vc_iconpicker_type_xstore_icons( $icons ) {
		$xstore_icons = array(
			array( 'et-icon et-cafecito-o' => 'et-cafecito-o' ),
			array( 'et-icon et-cafecito' => 'et-cafecito' ),
			array( 'et-icon et-dribbble-o' => 'et-dribbble-o' ),
			array( 'et-icon et-dribbble' => 'et-dribbble' ),
			array( 'et-icon et-kofi-o' => 'et-kofi-o' ),
			array( 'et-icon et-kofi' => 'et-kofi' ),
			array( 'et-icon et-line-o' => 'et-line-o' ),
			array( 'et-icon et-line' => 'et-line' ),
			array( 'et-icon et-patreon-o' => 'et-patreon-o' ),
			array( 'et-icon et-patreon' => 'et-patreon' ),
			array( 'et-icon et-strava-o' => 'et-strava-o' ),
			array( 'et-icon et-strava' => 'et-strava' ),
			array( 'et-icon et-reddit' => 'et-reddit' ),
			array( 'et-icon et-reddit-o' => 'et-reddit-o' ),
			array( 'et-icon et-discord' => 'et-discord' ),
			array( 'et-icon et-discord-o' => 'et-discord-o' ),
			array( 'et-icon et-downloads' => 'et-downloads' ),
			array( 'et-icon et-logout' => 'et-logout' ),
			array( 'et-icon et-clear-filter' => 'et-clear-filter' ),
			array( 'et-icon et-grid-2-columns' => 'et-grid-2-columns' ),
			array( 'et-icon et-grid-4-columns' => 'et-grid-4-columns' ),
			array( 'et-icon et-emoji-happy' => 'et-emoji-happy' ),
			array( 'et-icon et-emoji-neutral' => 'et-emoji-neutral' ),
			array( 'et-icon et-emoji-sad' => 'et-emoji-sad' ),
			array( 'et-icon et-home' => 'et-home' ),
			array( 'et-icon et-shop' => 'et-shop' ),
			array( 'et-icon et-facebook' => 'et-facebook' ),
			array( 'et-icon et-behance' => 'et-behance' ),
			array( 'et-icon et-youtube' => 'et-youtube' ),
			array( 'et-icon et-snapchat' => 'et-snapchat' ),
			array( 'et-icon et-instagram' => 'et-instagram' ),
			array( 'et-icon et-google-plus' => 'et-google-plus' ),
			array( 'et-icon et-pinterest' => 'et-pinterest' ),
			array( 'et-icon et-linkedin' => 'et-linkedin' ),
			array( 'et-icon et-rss' => 'et-rss' ),
			array( 'et-icon et-tripadvisor' => 'et-tripadvisor' ),
			array( 'et-icon et-twitter' => 'et-twitter' ),
			array( 'et-icon et-tumblr' => 'et-tumblr' ),
			array( 'et-icon et-vk' => 'et-vk' ),
			array( 'et-icon et-vimeo' => 'et-vimeo' ),
			array( 'et-icon et-skype' => 'et-skype' ),
			array( 'et-icon et-whatsapp' => 'et-whatsapp' ),
			array( 'et-icon et-houzz' => 'et-houzz' ),
			array( 'et-icon et-telegram' => 'et-telegram' ),
			array( 'et-icon et-etsy' => 'et-etsy' ),
			array( 'et-icon et-tik-tok' => 'et-tik-tok' ),
			array( 'et-icon et-twitch' => 'et-twitch' ),
			array( 'et-icon et-untapped' => 'et-untapped' ),
			array( 'et-icon et-facebook-o' => 'et-facebook-o' ),
			array( 'et-icon et-behance-o' => 'et-behance-o' ),
			array( 'et-icon et-youtube-o' => 'et-youtube-o' ),
			array( 'et-icon et-snapchat-o' => 'et-snapchat-o' ),
			array( 'et-icon et-instagram-o' => 'et-instagram-o' ),
			array( 'et-icon et-google-plus-o' => 'et-google-plus-o' ),
			array( 'et-icon et-pinterest-o' => 'et-pinterest-o' ),
			array( 'et-icon et-linkedin-o' => 'et-linkedin-o' ),
			array( 'et-icon et-rss-o' => 'et-rss-o' ),
			array( 'et-icon et-tripadvisor-o' => 'et-tripadvisor-o' ),
			array( 'et-icon et-twitter-o' => 'et-twitter-o' ),
			array( 'et-icon et-tumblr-o' => 'et-tumblr-o' ),
			array( 'et-icon et-vk-o' => 'et-vk-o' ),
			array( 'et-icon et-vimeo-o' => 'et-vimeo-o' ),
			array( 'et-icon et-skype-o' => 'et-skype-o' ),
			array( 'et-icon et-whatsapp-o' => 'et-whatsapp-o' ),
			array( 'et-icon et-houzz-o' => 'et-houzz-o' ),
			array( 'et-icon et-telegram-o' => 'et-telegram-o' ),
			array( 'et-icon et-etsy-o' => 'et-etsy-o' ),
			array( 'et-icon et-tik-tok-o' => 'et-tik-tok-o' ),
			array( 'et-icon et-twitch-o' => 'et-twitch-o' ),
			array( 'et-icon et-untapped-o' => 'et-untapped-o' ),
			array( 'et-icon et-exclamation' => 'et-exclamation' ),
			array( 'et-icon et-play-button' => 'et-play-button' ),
			array( 'et-icon et-left-arrow' => 'et-left-arrow' ),
			array( 'et-icon et-up-arrow' => 'et-up-arrow' ),
			array( 'et-icon et-right-arrow' => 'et-right-arrow' ),
			array( 'et-icon et-down-arrow' => 'et-down-arrow' ),
			array( 'et-icon et-info' => 'et-info' ),
			array( 'et-icon et-view' => 'et-view' ),
			array( 'et-icon et-heart' => 'et-heart' ),
			array( 'et-icon et-delete' => 'et-delete' ),
			array( 'et-icon et-zoom' => 'et-zoom' ),
			array( 'et-icon et-shopping-cart' => 'et-shopping-cart' ),
			array( 'et-icon et-shopping-cart-2' => 'et-shopping-cart-2' ),
			array( 'et-icon et-star' => 'et-star' ),
			array( 'et-icon et-360-degrees' => 'et-360-degrees' ),
			array( 'et-icon et-plus' => 'et-plus' ),
			array( 'et-icon et-transfer' => 'et-transfer' ),
			array( 'et-icon et-minus' => 'et-minus' ),
			array( 'et-icon et-compare' => 'et-compare' ),
			array( 'et-icon et-shopping-basket' => 'et-shopping-basket' ),
			array( 'et-icon et-loader-gif' => 'et-loader-gif' ),
			array( 'et-icon et-tick' => 'et-tick' ),
			array( 'et-icon et-coupon' => 'et-coupon' ),
			array( 'et-icon et-share-arrow' => 'et-share-arrow' ),
			array( 'et-icon et-diagonal-arrow' => 'et-diagonal-arrow' ),
			array( 'et-icon et-checked' => 'et-checked' ),
			array( 'et-icon et-circle' => 'et-circle' ),
			array( 'et-icon et-heart-o' => 'et-heart-o' ),
			array( 'et-icon et-grid-list' => 'et-grid-list' ),
			array( 'et-icon et-list-grid' => 'et-list-grid' ),
			array( 'et-icon et-share' => 'et-share' ),
			array( 'et-icon et-controls' => 'et-controls' ),
			array( 'et-icon et-burger' => 'et-burger' ),
			array( 'et-icon et-calendar' => 'et-calendar' ),
			array( 'et-icon et-chat' => 'et-chat' ),
			array( 'et-icon et-internet' => 'et-internet' ),
			array( 'et-icon et-message' => 'et-message' ),
			array( 'et-icon et-shopping-bag-o' => 'et-shopping-bag-o' ),
			array( 'et-icon et-shopping-bag' => 'et-shopping-bag' ),
			array( 'et-icon et-delivery' => 'et-delivery' ),
			array( 'et-icon et-square' => 'et-square' ),
			array( 'et-icon et-sent' => 'et-sent' ),
			array( 'et-icon et-more' => 'et-more' ),
			array( 'et-icon et-upload' => 'et-upload' ),
			array( 'et-icon et-phone-call' => 'et-phone-call' ),
			array( 'et-icon et-gift' => 'et-gift' ),
			array( 'et-icon et-left-arrow-2' => 'et-left-arrow-2' ),
			array( 'et-icon et-right-arrow-2' => 'et-right-arrow-2' ),
			array( 'et-icon et-time' => 'et-time' ),
			array( 'et-icon et-size' => 'et-size' ),
			array( 'et-icon et-play-button-2' => 'et-play-button-2' ),
			array( 'et-icon et-gallery' => 'et-gallery' ),
			array( 'et-icon et-user' => 'et-user' ),
			array( 'et-icon et-star-o' => 'et-star-o' ),
		);
		
		return array_merge( $icons, $xstore_icons );
	}
	
	/**
	 * Register widget args
	 *
	 * @return mixed|null|void
	 */
	public static function vc_args() {
		
		if ( ! is_null( self::$shortcodes ) ) {
			return self::$shortcodes;
		}
		
		return self::$shortcodes = apply_filters( 'etc/add/vc', array() );
	}
	
	/**
	 * Register Widgets
	 * @return null
	 */
	public function register_vc() {
		
		$args = self::vc_args();
		
		if ( ! is_array( $args ) ) {
			return;
		}
		
		// Check for vc map to register
		if ( ! function_exists( 'vc_map' ) ) {
			return;
		}
		
		foreach ( $args as $vc_class ) {
			$class    = $vc_class['class'];
			$function = $vc_class['function'];
			// include vc class if exist
			if ( isset( $vc_class['extra'] ) ) {
				include_once ET_CORE_DIR . 'app/controllers/vc/class/' . $vc_class['extra'] . '.php';
			}
			
			$class = $class::get_instance();
			$class->$function();
		}
	}
	
	function etheme_productBrandBrandAutocompleteSuggester( $query, $slug = false ) {
		global $wpdb;
		$cat_id          = (int) $query;
		$query           = trim( $query );
		$post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT a.term_id AS id, b.name as name, b.slug AS slug
            FROM {$wpdb->term_taxonomy} AS a
            INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id
            WHERE a.taxonomy = 'brand' AND (a.term_id = '%d' OR b.slug LIKE '%%%s%%' OR b.name LIKE '%%%s%%' )", $cat_id > 0 ? $cat_id : - 1, stripslashes( $query ), stripslashes( $query ) ), ARRAY_A );
		
		$result = array();
		if ( is_array( $post_meta_infos ) && ! empty( $post_meta_infos ) ) {
			foreach ( $post_meta_infos as $value ) {
				$data          = array();
				$data['value'] = $slug ? $value['slug'] : $value['id'];
				$data['label'] = __( 'Id', 'xstore-core' ) . ': ' . $value['id'] . ( ( strlen( $value['name'] ) > 0 ) ? ' - ' . __( 'Name', 'xstore-core' ) . ': ' . $value['name'] : '' ) . ( ( strlen( $value['slug'] ) > 0 ) ? ' - ' . __( 'Slug', 'xstore-core' ) . ': ' . $value['slug'] : '' );
				$result[]      = $data;
			}
		}
		
		return $result;
	}
	
	function etheme_productBrandBrandRenderByIdExact( $query ) {
		$query  = $query['value'];
		$cat_id = (int) $query;
		$term   = get_term( $cat_id, 'brand' );
		
		return self::etheme_productBrandTermOutput( $term );
	}
	
	function etheme_vc_shortcodes_strings() {
		return array(
			'category' => 'XStore',
			'heading'  => array(
				'use_custom_font' => esc_html__( 'Use custom font ?', 'xstore-core' ),
				'align'           => esc_html__( 'Horizontal align', 'xstore-core' ),
				'valign'          => esc_html__( 'Vertical align', 'xstore-core' ),
				'ajax'            => esc_html__( 'Lazy loading for this element', 'xstore-core' ),
				'el_class'        => esc_html__( 'Extra Class', 'xstore-core' ),
				'order'           => esc_html__( 'Order way', 'xstore-core' ),
				'orderby'         => esc_html__( 'Order by', 'xstore-core' ),
				'text_transform'  => esc_html__( 'Text transform', 'xstore-core' ),
				'font_size'       => esc_html__( 'Font size', 'xstore-core' ),
				'line_height'     => esc_html__( 'Line height', 'xstore-core' ),
				'image_size'      => esc_html__( 'Image size', 'xstore-core' ),
				'letter_spacing'  => esc_html__( 'Letter spacing', 'xstore-core' ),
			),
			'hint'     => array(
				'img_size'          => esc_html__( 'Enter image size (Ex.: "medium", "large" etc.) or enter size in pixels (Ex.: 200x100 (WxH))', 'xstore-core' ),
				'el_class'          => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'xstore-core' ),
				'rs_font_size'      => esc_html__( 'Responsive font-size', 'xstore-core' ),
				'rs_line_height'    => esc_html__( 'Responsive line height', 'xstore-core' ),
				'rs_letter_spacing' => esc_html__( 'Responsive letter spacing', 'xstore-core' ),
			),
			'value'    => array(
				'align'         => array(
					'<span class="dashicons dashicons-editor-alignleft"></span>'   => 'left',
					'<span class="dashicons dashicons-editor-aligncenter"></span>' => 'center',
					'<span class="dashicons dashicons-editor-alignright"></span>'  => 'right'
				),
				'valign'        => array(
					'<span class="dashicons dashicons-align-none" style="transform: rotate(90deg);"></span>'  => 'top',
					'<span class="dashicons dashicons-align-center"></span>'                                  => 'middle',
					'<span class="dashicons dashicons-align-none" style="transform: rotate(-90deg);"></span>' => 'bottom'
				),
				'valign2'       => array(
					esc_html__( 'Start', 'xstore-core' )  => 'start',
					esc_html__( 'Center', 'xstore-core' ) => 'center',
					esc_html__( 'End', 'xstore-core' )    => 'end'
				),
				'font_style'    => array(
					'type1' => array(
						esc_html__( 'Dark', 'xstore-core' )  => 'dark',
						esc_html__( 'Light', 'xstore-core' ) => 'light',
					),
					'type2' => array(
						esc_html__( 'Dark', 'xstore-core' )  => 'dark',
						esc_html__( 'Light', 'xstore-core' ) => 'white',
					),
				),
				'order'         => array(
					esc_html__( 'Ascending', 'xstore-core' )  => 'ASC',
					esc_html__( 'Descending', 'xstore-core' ) => 'DESC',
				),
				'order_reverse' => array(
					esc_html__( 'Descending', 'xstore-core' ) => 'DESC',
					esc_html__( 'Ascending', 'xstore-core' )  => 'ASC',
				),
			),
			'tooltip'  => array(
				'align'  => array(
					'left'    => esc_html( 'Left', 'xstore-core' ),
					'center'  => esc_html( 'Center', 'xstore-core' ),
					'right'   => esc_html( 'Right', 'xstore-core' ),
					'justify' => esc_html( 'Justify', 'xstore-core' ),
				),
				'valign' => array(
					'top'    => esc_html( 'Top', 'xstore-core' ),
					'middle' => esc_html( 'Middle', 'xstore-core' ),
					'bottom' => esc_html( 'Bottom', 'xstore-core' )
				),
			)
		);
	}
	
	function etheme_vc_blog_shortcodes_strings() {
		return array(
			'value' => array(
				'blog_hover' => array(
					esc_html__( 'Zoom', 'xstore-core' )     => 'zoom',
					esc_html__( 'Default', 'xstore-core' )  => 'default',
					esc_html__( 'Animated', 'xstore-core' ) => 'animated',
				),
				'orderby'    => array(
					esc_html__( 'Date', 'xstore-core' )                  => 'date',
					esc_html__( 'Order by post ID', 'xstore-core' )      => 'ID',
					esc_html__( 'Author', 'xstore-core' )                => 'author',
					esc_html__( 'Title', 'xstore-core' )                 => 'title',
					esc_html__( 'Last modified date', 'xstore-core' )    => 'modified',
					esc_html__( 'Post/page parent ID', 'xstore-core' )   => 'parent',
					esc_html__( 'Number of comments', 'xstore-core' )    => 'comment_count',
					esc_html__( 'Menu order/Page Order', 'xstore-core' ) => 'menu_order',
					esc_html__( 'Meta value', 'xstore-core' )            => 'meta_value',
					esc_html__( 'Meta value number', 'xstore-core' )     => 'meta_value_num',
					// esc_html__('Matches same order you passed in via the 'include' parameter.', 'xstore-core') => 'post__in'
					esc_html__( 'Random order', 'xstore-core' )          => 'rand',
				),
			),
		);
	}
	
	public static function etheme_productBrandTermOutput( $term ) {
		$term_slug  = $term->slug;
		$term_title = $term->name;
		$term_id    = $term->term_id;
		
		$term_slug_display = '';
		if ( ! empty( $term_slug ) ) {
			$term_slug_display = ' - ' . __( 'Sku', 'xstore-core' ) . ': ' . $term_slug;
		}
		
		$term_title_display = '';
		if ( ! empty( $term_title ) ) {
			$term_title_display = ' - ' . __( 'Title', 'xstore-core' ) . ': ' . $term_title;
		}
		
		$term_id_display = __( 'Id', 'xstore-core' ) . ': ' . $term_id;
		
		$data          = array();
		$data['value'] = $term_id;
		$data['label'] = $term_id_display . $term_title_display . $term_slug_display;
		
		return ! empty( $data ) ? $data : false;
	}
}
