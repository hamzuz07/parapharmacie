<?php
namespace ETC\App\Controllers\Shortcodes;

use ETC\App\Controllers\Shortcodes;

/**
 * Twitter shortcode.
 *
 * @since      1.4.4
 * @package    ETC
 * @subpackage ETC/Controllers/Shortcodes
 */
class Twitter extends Shortcodes {

	function hooks() {}

	function twitter_shortcode( $atts, $content ) {

			$atts = shortcode_atts( array(
				'title' => '',
				'username' => '',
				'consumer_key' => '',
				'consumer_secret' => '',
				'user_token' => '',
				'user_secret' => '',
				'limit' => 10,
				'design' => 'slider',
				'class' => 10,
				'is_preview' => false
			), $atts );

			$options = array();
			$options['box_id'] = rand(100,999);
			
			if(empty($atts['consumer_key']) || empty($atts['consumer_secret']) || empty($atts['user_token']) || empty($atts['user_secret']) || empty($atts['username'] ) )
				return esc_html__('Not enough information', 'xstore-core');
			
			$options['tweets'] = etheme_get_tweets($atts['consumer_key'], $atts['consumer_secret'], $atts['user_token'], $atts['user_secret'], $atts['username'], $atts['limit'], 100, 'slider');

			ob_start();

			?>
	
			<div class="et-twitter-<?php echo esc_attr($atts['design']) . ' ' . esc_attr($atts['class']); ?>">

				<?php 
				if( $atts['title'] != '' ) { ?>
					<h2 class="twitter-slider-title">
						<span><?php echo esc_html($atts['title']); ?></span>
					</h2>
				<?php } ?>
				
				<ul class="et-tweets <?php echo esc_attr($atts['design']) . esc_attr($options['box_id']); ?>">
				
				<?php 
				foreach ( $options['tweets'] as $tweet ) { ?>
					<li class="et-tweet">
						<?php etheme_tweet_linkify($tweet['text']); ?>
						<div class="twitter-info">
				            <a href="<?php echo esc_url($tweet['user']['url']); ?>" class="active" target="_blank">
				            	<?php echo '@'.$tweet['user']['screen_name']; ?>
			            	</a> 
		            		<?php echo date("l M j \- g:ia",strtotime($tweet['created_at'])); ?>
						</div>
					</li>
				<?php } ?>

				</ul>
				
			</div>
			
			<?php 

			if( $atts['design'] == 'slider' ) { 
				if ( $atts['is_preview'] ) {
					echo '<script>';
						echo 'jQuery(document).ready(function(){
							jQuery("' . $atts['design'] . $options['box_id'] . '").owlCarousel({
						    items:1, 
						    navigation: true,
						    navigationText:false,
						    rewindNav: false,
						    itemsCustom: [[0, 1], [479,1], [619,1], [768,1],  [1200, 1], [1600, 1]]
						})';
					echo '</script>';
				}
				else {
					wp_add_inline_script( 'etheme', "
						jQuery(.'" . $atts['design'] . $options['box_id'] . "').owlCarousel({
					    items:1, 
					    navigation: true,
					    navigationText:false,
					    rewindNav: false,
					    itemsCustom: [[0, 1], [479,1], [619,1], [768,1],  [1200, 1], [1600, 1]]
					});
					", 'after' );
				}
			} 
			
			unset($atts);
			unset($options);

			return ob_get_clean();
	}
}
