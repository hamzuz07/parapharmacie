<?php
/**
 * Description
 *
 * @package    sales-booster.php
 * @version    1.0.1
 * @since      3.2.2
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Don't duplicate me!
if ( ! class_exists( 'Etheme_Sales_Booster_Frontend' ) ) {


    /**
     * Main Etheme_Sales_Booster_Frontend class
     *
     * @since       3.2.2
     */
    class Etheme_Sales_Booster_Frontend {

        /**
         * Projects.
         *
         * @var array
         * @since 3.2.2
         */
        private $settings = [],
            $settings_name,
            $settings_tab,
            $scripts_url,
            $wp_query_args;

        public $tabs_default_options = [];

        /**
         * Class Constructor. Defines the args for the actions class
         *
         * @return      void
         * @since       3.2.2
         * @version     1.0.1
         * @access      public
         * @log fixed content elements for fake sale popup
         */
        public function __construct() {

            if ( ! get_option( 'xstore_sales_booster_settings_fake_sale_popup', false ) ) {
                return;
            }

            $this->scripts_url = plugin_dir_url( __FILE__ );

            $this->wp_query_args = array(
                'post_type'           => array( 'product' ),
                'post_status'         => 'publish',
                'ignore_sticky_posts' => 1,
                'no_found_rows'       => 1,
                'posts_per_page'      => 20,
                'limit'               => 20,
                'orderby'             => 'rand',
            );

            $this->settings_name        = 'xstore_sales_booster_settings';
            $this->tabs_default_options = array(
                'fake_sale_popup' => array(
                    'fake_sale_popup_title'    => true,
                    'fake_sale_popup_image'    => true,
                    'fake_sale_popup_price'    => false,
                    'fake_sale_popup_time'     => true,
                    'fake_sale_popup_location' => true,
                    'fake_sale_popup_button'   => true,
                    'fake_sale_popup_close'    => true,
                    'bag_icon' => 1,
                    'products_type'            => 'random',
                    'play_sound'               => false,
                    'hide_outofstock_products' => false,
                    'sound_file'               => $this->scripts_url . 'assets/audio/default.mp3',
                    'show_on_mobile'           => true,
                    'locations'                => '{{{Washington D.C., USA 🇺🇸}}}; {{{London, UK 🇬🇧}}}; {{{Madrid, Spain 🇪🇸}}}; {{{Berlin, Germany 🇩🇪}}}; {{{New Delhi, India 🇮🇳}}}; {{{Ottawa, Canada 🇨🇦}}}; {{{Paris, France 🇫🇷}}}; {{{Rome, Italy 🇮🇹}}}; {{{Dhaka, Bangladesh 🇧🇩}}}; {{{Kiev, Ukraine 🇺🇦}}}; {{{Islamabad, Pakistan 🇵🇰}}}; {{{Athens, Greece 🇬🇷}}}; {{{Brasilia, Brazil 🇧🇷}}}; {{{Lima, Peru 🇵🇪}}}; {{{Ankara, Turkey 🇹🇷}}}; {{{Colombo, Sri Lanka 🇱🇰}}}; {{{Warsaw, Poland 🇵🇱}}}; {{{Amsterdam, Netherlands 🇳🇱}}}; {{{Mexico City, Mexico 🇲🇽}}}; {{{Canberra, Australia 🇦🇺}}}',
                    'repeat_every'             => 20,
                    'animation_type'           => 'slide_right',
                ),
                'progress_bar'    => array(
                    'message_text'          => esc_html__( 'Spend {{et_price}} to get free shipping', 'xstore-core' ),
                    'process_icon'          => 'et_icon-delivery',
                    'process_icon_position' => 'before',
                    'price'                 => '350',
                    'message_success_text'  => esc_html__( 'Congratulations! You\'ve got free shipping.', 'xstore-core' ),
                    'success_icon'          => 'et_icon-star',
                    'success_icon_position' => 'before'
                )
            );
            $this->settings_tab         = 'fake_sale_popup';

            $this->settings = get_option( $this->settings_name, array() );

            $this->settings = wp_parse_args( $this->settings, $this->tabs_default_options );

//			foreach ( $this->settings as $key => $value ) {
//				$this->settings[ $key ] = wp_parse_args( $this->settings[ $key ], $this->tabs_default_options[ $key ] );
//			}

            if ( count( $this->settings ) ) {
                $this->load_scripts();
	            if ( $this->settings[ $this->settings_tab ]['products_type'] == 'orders' ) {
		            add_filter('etheme_sales_booster_fake_sale_popup_from_orders', '__return_true');
	            }
            }
            

//			add_action( 'template_redirect', array( $this, 'track_product_view' ), 21 );

        }

        /**
         * Load css/js on frontend.
         *
         * @return void
         * @since 3.2.2
         *
         */
        public function load_scripts() {

            if ( ! is_admin() && ! isset($_GET['elementor-preview']) ) {

                if ( !class_exists('WooCommerce')) return;

                wp_register_style( 'xstore_sales_booster_css', $this->scripts_url . 'assets/css/style.css' );
                wp_enqueue_style( 'xstore_sales_booster_css' );
                wp_register_script( 'xstore_sales_booster_frontend_js', $this->scripts_url . 'assets/js/script.min.js', array(
                    'wp-util',
                    'jquery'
                ) );
                wp_enqueue_script( 'xstore_sales_booster_frontend_js' );

                add_action( 'after_page_wrapper', array( $this, 'sale_booster_popup_wrapper' ), 20 );
                add_action( 'wp_footer', array( $this, 'template_fake_sale_popup' ) );
                add_action( 'wp_footer', array( $this, 'render_content' ) );

            }

        }

        public function sale_booster_popup_wrapper() {
            $settings = $this->settings;

            $local_settings = $settings[ $this->settings_tab ];

            if ( is_rtl() ) {
                $local_settings['animation_type'] = str_replace('slide_right', 'slide_left', $local_settings['animation_type']);
            }
            $popup_classes = array( 'animation-' . $local_settings['animation_type'] );

            if ( ! isset($local_settings['show_on_mobile']) || ! $local_settings['show_on_mobile'] ) {
                $popup_classes[] = 'mob-hide';
            }

            ?>
            <div id="sales-booster-popup" class="<?php echo implode( ' ', $popup_classes ); ?>"
                 data-repeat-time="<?php echo esc_attr( $local_settings['repeat_every'] ); ?>">
                <?php if ( isset($local_settings['fake_sale_popup_close']) && $local_settings['fake_sale_popup_close'] ) : ?>
                    <span class="close pos-absolute right top">
							<svg xmlns="http://www.w3.org/2000/svg" width="0.55em" height="0.55em" viewBox="0 0 24 24"
                                 fill="currentColor">
		                        <path d="M13.056 12l10.728-10.704c0.144-0.144 0.216-0.336 0.216-0.552 0-0.192-0.072-0.384-0.216-0.528-0.144-0.12-0.336-0.216-0.528-0.216 0 0 0 0 0 0-0.192 0-0.408 0.072-0.528 0.216l-10.728 10.728-10.704-10.728c-0.288-0.288-0.768-0.288-1.056 0-0.168 0.144-0.24 0.336-0.24 0.528 0 0.216 0.072 0.408 0.216 0.552l10.728 10.704-10.728 10.704c-0.144 0.144-0.216 0.336-0.216 0.552s0.072 0.384 0.216 0.528c0.288 0.288 0.768 0.288 1.056 0l10.728-10.728 10.704 10.704c0.144 0.144 0.336 0.216 0.528 0.216s0.384-0.072 0.528-0.216c0.144-0.144 0.216-0.336 0.216-0.528s-0.072-0.384-0.216-0.528l-10.704-10.704z"></path>
		                    </svg>
						</span>
                <?php endif; ?>
                <div class="sales-booster-popup-inner"></div>
            </div>
            <?php

            if ( isset($local_settings['play_sound']) && $local_settings['play_sound'] ) {
                $local_settings['sound_file'] = empty( trim( $local_settings['sound_file'] ) ) ?
                    $this->scripts_url . 'assets/audio/default.mp3' :
                    $local_settings['sound_file']; ?>
                <audio id="sales-booster-popup-audio">
                    <source src="<?php echo esc_url( $local_settings['sound_file'] ); ?>">
                </audio>
                <?php
            }
        }

        public function template_fake_sale_popup() {
            $settings = $this->settings;

            $local_settings = $settings[ $this->settings_tab ];
            $bag_icon = '';
            if ( isset($local_settings['bag_icon'] ) ) {
                if ( $local_settings['bag_icon'] != 1 ) {
                    $bag_icon = $local_settings['bag_icon'];
                }
                elseif ( $local_settings['bag_icon'] != '') {
                    $bag_icon = '👜';
                }
            }
            else {
                $bag_icon = '👜';
            }

            ob_start();
            ?>
            <script type="text/html" id="tmpl-sales-booster-fake-sale-popup">
                <?php if ( isset($local_settings['fake_sale_popup_image']) && $local_settings['fake_sale_popup_image'] ): ?>
                    <a href="{{data.href}}">
                        <img src="{{data.src}}" alt="{{data.title}}">
                    </a>
                <?php endif; ?>

                <div>

                    <?php if ( isset($local_settings['fake_sale_popup_title']) && $local_settings['fake_sale_popup_title'] ): ?>
                        <span class="heading"><?php if ( $bag_icon != '' ){ echo $bag_icon; } ?>
                            <span <?php if ( $bag_icon != '' ): ?>style="margin-<?php echo is_rtl() ? 'right' : 'left'; ?>: 7px;"<?php endif; ?>><?php esc_html_e( 'Someone recently bought a', 'xstore-core' ); ?>
                            <a href="{{data.href}}">{{{data.title}}}</a></span></span>
                    <?php endif; ?>

                    <?php if ( (isset($local_settings['fake_sale_popup_location']) && $local_settings['fake_sale_popup_location']) || (isset($local_settings['fake_sale_popup_time']) && $local_settings['fake_sale_popup_time']) ): ?>
                        <span class="info">
                            <?php if ( isset($local_settings['fake_sale_popup_time']) && $local_settings['fake_sale_popup_time'] ): ?>
                                <span>{{{data.time_ago}}} <?php
                                    if ( !apply_filters('etheme_sales_booster_fake_sale_popup_from_orders', false) ) {
                                        esc_html_e( 'minutes ago', 'xstore-core' );
                                    } ?></span>
                            <?php endif; ?>
                            <?php if ( isset($local_settings['fake_sale_popup_location']) && $local_settings['fake_sale_popup_location'] ): ?>
                                <span>&nbsp;<?php esc_html_e( 'from', 'xstore-core' ); ?> {{{data.location}}}</span>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>

                    <?php if ( isset($local_settings['fake_sale_popup_price']) && $local_settings['fake_sale_popup_price'] ): ?>
                        <span class="price-wrapper">{{{data.price}}}</span>
                    <?php endif; ?>

                    <?php if ( isset($local_settings['fake_sale_popup_button']) && $local_settings['fake_sale_popup_button'] ): ?>
                        <div class="read-more-wrapper">
                            <a href="{{{data.href}}}">
                                <span class="read-more">
                                    <?php esc_html_e( 'View product', 'xstore-core' ); ?>
                                </span>
                            </a>
                        </div>
                    <?php endif; ?>

                </div>

            </script>
            <?php echo ob_get_clean();
        }

        /**
         * Description of the function.
         *
         * @return void
         * @since 3.2.2
         *
         */
        public function track_product_view() {
            if ( ! is_singular( 'product' ) ) {
                return;
            }
            if ( is_active_widget( false, false, 'woocommerce_recently_viewed_products', true ) ) {
                return;
            }

            global $post;

            if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) { // @codingStandardsIgnoreLine.
                $viewed_products = array();
            } else {
                $viewed_products = wp_parse_id_list( (array) explode( '|',
                    wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) ); // @codingStandardsIgnoreLine.
            }

            // Unset if already in viewed products list.
            $keys = array_flip( $viewed_products );

            if ( isset( $keys[ $post->ID ] ) ) {
                unset( $viewed_products[ $keys[ $post->ID ] ] );
            }

            $viewed_products[] = $post->ID;

            if ( count( $viewed_products ) > 15 ) {
                array_shift( $viewed_products );
            }
            // Store for session only.
            wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ) );
        }

        public function get_recently_viewed_products_args() {

            $viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|',
                wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : array(); // @codingStandardsIgnoreLine
            $viewed_products = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );

            if ( ! empty( $viewed_products ) ) {
                $this->wp_query_args['post__in'] = $viewed_products;
                $this->wp_query_args['orderby']  = 'post__in';
            }
        }

        public function get_featured_products_args() {

            $featured_product_ids            = wc_get_featured_product_ids();
            $this->wp_query_args['post__in'] = array_merge( array( 0 ), $featured_product_ids );

        }

        public function get_sale_products_args() {

            $product_ids_on_sale             = wc_get_product_ids_on_sale();
            $this->wp_query_args['post__in'] = array_merge( array( 0 ), $product_ids_on_sale );

        }

        public function get_bestsellings_products_args() {
            $this->wp_query_args['meta_key'] = 'total_sales';
            $this->wp_query_args['orderby']  = 'meta_value_num';
        }

        public function render_content() {
         
	        global $wpdb;
	
	        $settings = $this->settings;
	
	        $local_settings = $settings[ $this->settings_tab ];
            $from_real_orders = apply_filters('etheme_sales_booster_fake_sale_popup_from_orders', false);
            
            if ( $from_real_orders ) {
	            $orders = wc_get_orders( array(
		            'status'         => array( 'wc-completed' ),
		            'limit'          => $this->wp_query_args['limit'],
		            'posts_per_page' => $this->wp_query_args['limit'],
		            'order'          => 'DESC',
		            'orderby'        => 'date',
	            ) );
	
	            $products_list = array();
	            $all_countries = WC()->countries->get_countries();
	
	            foreach ( $orders as $order ) {
		            $country = array();
		            if ( $order->get_shipping_country() ) {
                        if ( $order->get_shipping_city() )
                            $country[] = ucfirst($order->get_shipping_city());

			            $country[] = $all_countries[ $order->get_shipping_country() ];
		            } elseif ( $order->get_billing_country() ) {
			            if ( $order->get_billing_city() )
				            $country[] = ucfirst($order->get_billing_city());
			            $country[] = $all_countries[ $order->get_billing_country() ];
		            }
		
		            $items = $order->get_items();
		            foreach ( $items as $item ) {
			            $product = wc_get_product( $item->get_product_id() );
			
			            $product_options = array();
			
			            if ( isset( $local_settings['fake_sale_popup_title'] ) && $local_settings['fake_sale_popup_title'] ):
				            $product_options['title'] = $product->get_title();
			            endif;
			
			            if ( isset( $local_settings['fake_sale_popup_image'] ) && $local_settings['fake_sale_popup_image'] ):
				            $product_options['src'] = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id(), 'shop_thumbnail' ) );
				
				            if ( $product->get_type() == 'variation' ) {
					            $product_options['src'] = wp_get_attachment_image_src( $product->get_image_id(), 'shop_thumbnail' );
				            }
				
				
				            if ( is_array( $product_options['src'] ) && isset( $product_options['src'][0] ) && $product_options['src'][0] ) {
					            $product_options['src'] = $product_options['src'][0];
				            } else {
					            $product_options['src'] = wc_placeholder_img_src( 'shop_thumbnail' );
				            }
			
			            endif;
			
			            if ( isset( $local_settings['fake_sale_popup_price'] ) && $local_settings['fake_sale_popup_price'] ):
				            $product_options['price'] = $product->get_price_html();
			            endif;
			
			            if ( isset( $local_settings['fake_sale_popup_time'] ) && $local_settings['fake_sale_popup_time'] ):
//				            $product_options['time_ago'] = rand( 2, 59 );
			                $product_options['time_ago'] = $order->get_date_completed()->format( 'm M' );
			            endif;
			
			            if ( isset( $local_settings['fake_sale_popup_location'] ) && $local_settings['fake_sale_popup_location'] && count( $country ) ):
				            $product_options['location'] = implode( ', ', $country );
			            endif;
			
			            $product_options['href'] = $product->get_permalink();
			            $product_options['id']   = $product->get_id();
			
			            $products_list[] = $product_options;
		            }
	            }
	            
	            // mix products from orders so each product from order will be shown in different positions / time delay
	            shuffle($products_list);
	
            }
            
            else {
	
	            if ( isset( $local_settings['hide_outofstock_products'] ) && $local_settings['hide_outofstock_products'] ) {
		            $visibility                         = wc_get_product_visibility_term_ids();
		            $this->wp_query_args['tax_query'][] = array(
			            'taxonomy' => 'product_visibility',
			            'field'    => 'term_taxonomy_id',
			            'terms'    => $visibility['outofstock'],
			            'operator' => 'NOT IN',
		            );
	            }
	
	            switch ( $local_settings['products_type'] ) {
		            case 'recently_viewed':
			            $this->get_recently_viewed_products_args();
			            break;
		            case 'featured':
			            $this->get_featured_products_args();
			            break;
		            case 'sale':
			            $this->get_sale_products_args();
			            break;
		            case 'bestsellings':
			            $this->get_bestsellings_products_args();
			            break;
		            default: // random
			            break;
	            }
	
	            $products = new \WP_Query( $this->wp_query_args );
	
	            $locations_parsed = array(
		            array(
			            'Salvador',
			            'Brazil',
			            '🇧🇷'
		            ),
		            array(
			            'Sydney',
			            'Australia',
			            '🇦🇺'
		            ),
		            array(
			            'Taichung',
			            'Taiwan',
			            '🇹🇼'
		            )
	            );
	
	            $locations = ( isset( $local_settings['locations'] ) && ! empty( trim( $local_settings['locations'] ) ) ) ?
		            $local_settings['locations'] : '{{{Salvador, Brazil 🇧🇷}}}; {{{Sydney, Australia 🇦🇺}}}; {{{Taichung, Taiwan 🇹🇼}}}';
	
	            $locations = explode( ';', $locations );
	            if ( count( $locations ) ) {
		            $locations_parsed = array();
		            foreach ( $locations as $location ) {
			            $location           = str_replace( array( '{', '}', '; ', ' {' ), array( '' ), $location );
			            $location           = explode( ',', $location );
			            $locations_parsed[] = $location;
		            }
	            }
	
	            $products_list = array();
	
	            if ( $products->have_posts() ) {
		            while ( $products->have_posts() ) : $products->the_post();
			            global $product;
			
			            $product_options = array();
			
			            if ( isset( $local_settings['fake_sale_popup_title'] ) && $local_settings['fake_sale_popup_title'] ):
				            $product_options['title'] = $product->get_title();
			            endif;
			
			            if ( isset( $local_settings['fake_sale_popup_image'] ) && $local_settings['fake_sale_popup_image'] ):
				            $product_options['src'] = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id(), 'shop_thumbnail' ) );
				
				            if ( $product->get_type() == 'variation' ) {
					            $product_options['src'] = wp_get_attachment_image_src( $product->get_image_id(), 'shop_thumbnail' );
				            }
				
				
				            if ( is_array( $product_options['src'] ) && isset( $product_options['src'][0] ) && $product_options['src'][0] ) {
					            $product_options['src'] = $product_options['src'][0];
				            } else {
					            $product_options['src'] = wc_placeholder_img_src( 'shop_thumbnail' );
				            }
			
			            endif;
			
			            if ( isset( $local_settings['fake_sale_popup_price'] ) && $local_settings['fake_sale_popup_price'] ):
				            $product_options['price'] = $product->get_price_html();
			            endif;
			
			            if ( isset( $local_settings['fake_sale_popup_time'] ) && $local_settings['fake_sale_popup_time'] ):
				            $product_options['time_ago'] = rand( 2, 59 );
			            endif;
			
			            if ( isset( $local_settings['fake_sale_popup_location'] ) && $local_settings['fake_sale_popup_location'] ):
				            $product_options['location'] = implode( ', ', $locations_parsed[ rand( 0, count( $locations_parsed ) - 1 ) ] );
			            endif;
			
			            $product_options['href'] = $product->get_permalink();
			            $product_options['id']   = $product->get_id();
			
			            $products_list[] = $product_options;
		
		            endwhile;
	            }
            }

            echo '<script type="text/html" id="sales-booster-fake-sale-popup-products">' . wp_json_encode( $products_list ) . '</script>';

        }

    }

    $Etheme_Sales_Booster_Frontend = new Etheme_Sales_Booster_Frontend();
}