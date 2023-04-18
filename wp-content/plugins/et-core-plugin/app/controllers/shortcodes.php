<?php
namespace ETC\App\Controllers;

use ETC\App\Controllers\Base_Controller;

/**
 * Shortcode controller.
 *
 * @since      1.4.4
 * @package    ETC
 * @subpackage ETC/Models/Admin
 */
class Shortcodes extends Base_Controller {

	/**
     * Registered shortcodes.
     *
     * @since 1.0.0
     *
     * @var array
     */
	public static $shortcodes = NULL;
  
	public function hooks() {   
  
		add_action( 'init', array( $this, 'register_shortcodes' ) ); 
  
	}  
  
    /**  
     * Register widget args  
     *  
     * @return mixed|null|void  
     */  
    public static function shortcodes_args() {  
  
    	if ( ! is_null( self::$shortcodes ) ) {
    		return self::$shortcodes;
    	}

    	return self::$shortcodes = apply_filters( 'etc/add/shortcodes', array() );
    }

    /**
     * Register shortcodes
     * @return null
     */
    public function register_shortcodes() {
    	// Get shortcodes
    	$shortcodes_args = self::shortcodes_args();
    	// Check shortcodes need to load from child theme?
    	$shortcodes_args = self::load_shortcodes( $shortcodes_args );

    	if ( ! is_array( $shortcodes_args ) ) {
    		return;
    	}

    	foreach ( $shortcodes_args as $shortcode_arg ) {
    		add_shortcode( $shortcode_arg['name'], array( $shortcode_arg['class']::get_instance(), $shortcode_arg['function'] ) );
    	}

        // Register theme shortcode
        // @todo move shortcodes from theme to core plugin
    	add_shortcode( 'etheme_product_name'        , 'etheme_product_name_render'          );
    	add_shortcode( 'etheme_product_image'       , 'etheme_product_image_render'         );
    	add_shortcode( 'etheme_product_excerpt'     , 'etheme_product_excerpt_render'       );
    	add_shortcode( 'etheme_product_rating'      , 'etheme_product_rating_render'        );
    	add_shortcode( 'etheme_product_price'       , 'etheme_product_price_render'         );
    	add_shortcode( 'etheme_product_sku'         , 'etheme_product_sku_render'           );
    	add_shortcode( 'etheme_product_brands'      , 'etheme_product_brands_render'        );
    	add_shortcode( 'etheme_product_categories'  , 'etheme_product_categories_render'    );
        add_shortcode( 'etheme_product_stock'       , 'etheme_product_stock_render'         );
    	add_shortcode( 'etheme_product_buttons'     , 'etheme_product_buttons_render'       );
    	add_shortcode( 'project_links'              , 'etheme_project_links'                );

        // add custom fields in vc 
        if ( function_exists( 'vc_add_shortcode_param' ) ) {
            $fields = self::fields();
            foreach ( $fields as $key ) {
                require_once ET_CORE_DIR . 'config/vc/fields/'.$key.'.php';
                vc_add_shortcode_param( 'xstore_' . str_replace('-', '_', $key), 'xstore_add_' . str_replace('-', '_', $key) . '_param' );
            }
        }

    }

    public static function load_shortcodes( $shortcodes_args ) {

    	if ( ! function_exists( 'etheme_load_shortcode' ) ) {
    		return $shortcodes_args;
    	}

    	$shortcodes = array( 'banner', 'title', 'images-carousel', 'carousel', 'icon-box', 'tabs', 'team-member', 'testimonials', 'twitter', 'instagram', 'blog', 'blog-timeline', 'blog-list', 'blog-carousel', 'follow', 'countdown' ,'category', 'categories', 'categories-lists', 'products', 'special-offer', 'brands', 'brands_list', 'looks', 'the-look', 'menu-list', 'menu-list-item', 'custom-tabs', 'menu', 'post-meta', 'et-slider', 'et-slider-item', 'scroll-text', 'scroll-text-item', 'portfolio', 'portfolio-recent', 'quick-view', 'button', 'counter', 'dropcap', 'mark', 'blockquote', 'checklist', 'qrcode', 'tooltip', 'share', 'static-block', 'fancy-button' );

    	foreach ( $shortcodes as $key => $value ) {
    		if ( etheme_load_shortcode( $value ) ) {
    			// Remove shortcode from core plugin so child theme version will be load
    			unset( $shortcodes_args[$value] ); 
    		}
    	}
    	// Include images carousel
    	require_once ET_CORE_DIR . 'app/controllers/shortcodes/images-carousel.php';

    	return $shortcodes_args;
    }


    // @todo move it to vc config folder
    public static function fields() {
        return array(
            'title-divider',
            'image-select',
            'slider',
            'responsive-size',
            'button-set',
            'uniqid'
        );
    }

    public static function getHeading( $tag, $atts, $class = '' ) {

        if ( ! class_exists( 'Vc_Manager' ) ) {
            return '';
        }

    	$inline_css = '';
    	if ( isset( $atts[ $tag ] ) && '' !== trim( $atts[ $tag ] ) ) {
    		if ( isset( $atts[ 'use_custom_fonts_' . $tag ] ) && 'true' === $atts[ 'use_custom_fonts_' . $tag ] ) {
    			$custom_heading = visual_composer()->getShortCode( 'vc_custom_heading' );
    			$data = vc_map_integrate_parse_atts( 'banner', 'vc_custom_heading', $atts, $tag . '_' );
    			$data['el_class'] = $class;
				$data['text'] = $atts[ $tag ]; // provide text to shortcode
				$data['css_animation'] = ( $data['css_animation'] == $data['google_fonts'] || $data['css_animation'] == 'none' ) ? '' : $data['css_animation'];
			    if ( $data['css_animation'] && isset($data['el_id']) ) {
				    $data['el_id'] = str_replace($data['css_animation'], '', $data['el_id']);
			    }
				if ( isset($data['el_id']) && isset($data['google_fonts']) && $data['google_fonts'] != '' ) {
					$data['el_id'] = str_replace( $data['google_fonts'], '', $data['el_id'] );
				}
				return $custom_heading->render( ( $data ) );
			} else {
				if ( isset( $atts['style'] ) && 'custom' === $atts['style'] ) {
					if ( ! empty( $atts['custom_text'] ) ) {
						$inline_css[] = vc_get_css_color( 'color', $atts['custom_text'] );
					}
				}
				if ( ! empty( $inline_css ) ) {
					$inline_css = ' style="' . implode( '', $inline_css ) . '"';
				}

				return '<h2 ' . (($class != '') ? 'class="' . $class . '"' : '') . $inline_css . '>' . $atts[ $tag ] . '</h2>';
			}
		}

		return '';
	}

    public static function initPreviewJs() {
        ob_start();
            echo '<script>';
                echo 'jQuery(document).ready(function(){ 
                    etTheme.swiperFunc();
			        etTheme.secondInitSwipers();
			        etTheme.global_image_lazy();
			        if ( etTheme.contentProdImages !== undefined )
	                    etTheme.contentProdImages();
			        if ( etTheme.countdown !== undefined )
	                    etTheme.countdown();
			        etTheme.customCss();
			        etTheme.customCssOne();
			        if ( etTheme.reinitSwatches !== undefined )
	                    etTheme.reinitSwatches();
                });';
            echo '</script>';
        return ob_get_clean();
    }

    public static function initPreviewCss( $global_css = array(), $tablet_css = array(), $mobile_css = array() ) {
        ob_start();
            echo '<style>';
                if ( count($global_css) ) echo implode(' ', $global_css);
                if ( count($tablet_css) ) echo '@media only screen and (max-width: 992px) {' . implode(' ', $tablet_css) . '}';
                if ( count($mobile_css) ) echo '@media only screen and (max-width: 767px) {' . implode(' ', $mobile_css) . '}';
            echo '</style>';
        return ob_get_clean();
    }

    public static function initCss( $global_css = array(), $tablet_css = array(), $mobile_css = array() ) {
        if ( count($global_css) ) wp_add_inline_style( 'xstore-inline-css', implode(' ', $global_css) );
        if ( count($tablet_css) ) wp_add_inline_style( 'xstore-inline-tablet-css', implode(' ', $tablet_css) );
        if ( count($mobile_css) ) wp_add_inline_style( 'xstore-inline-mobile-css', implode(' ', $mobile_css) );
    }

    /**
     * build link from string
     * @param  string of link
     * @return array
     */
    public static function build_link( $value ) {
        return self::parse_multi_attribute( $value, array(
            'url' => '',
            'title' => '',
            'target' => '',
            'rel' => '',
        ) );
    }    

    /**
     * parse data to array 
     * @param  value  to parse
     * @param  array  default args
     * @return return array
     */
    public static function parse_multi_attribute(  $value, $default = array()  ) {
        $result = $default;
        $params_pairs = explode( '|', $value );
        if ( ! empty( $params_pairs ) ) {
            foreach ( $params_pairs as $pair ) {
                $param = preg_split( '/\:/', $pair );
                if ( ! empty( $param[0] ) && isset( $param[1] ) ) {
                    $result[ $param[0] ] = rawurldecode( $param[1] );
                }
            }
        }

        return $result;

    }

}

