<?php
namespace ETC\App\Models\Widgets;

use ETC\App\Models\Widgets;

/**
 * Static block Widget.
 * 
 * @since      1.4.4
 * @version    1.0.2
 * @package    ETC
 * @subpackage ETC/Models/Widgets
 * @log
 * 1.0.1
 * ADDED: select2
 */
class Static_Block extends Widgets {

    function __construct() {
        $widget_ops = array('classname' => 'etheme_widget_satick_block', 'description' => esc_html__( "Insert a static block", 'xstore-core') );
        parent::__construct('etheme-static-block', '8theme - '.esc_html__('Static Block', 'xstore-core'), $widget_ops);
        $this->alt_option_name = 'etheme_widget_satick_block';
    }

    function widget($args, $instance) {

        if (parent::admin_widget_preview(esc_html__('Static Block', 'xstore-core')) !== false) return;

        extract($args);

        $block_id = isset( $instance['block_id'] ) ? $instance['block_id'] : false;

        echo $before_widget;

	    echo parent::etheme_widget_title($args, $instance); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

        if ( function_exists( 'etheme_static_block' ) && get_theme_mod('static_blocks', true) ) {
            etheme_static_block( $block_id, true );
        }
        else {
            echo '<div class="woocommerce-info">'.esc_html__('To use this widget, please enable static block via Customizer settings', 'xstore-core').'</div>';
        }

        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title']    = strip_tags($new_instance['title']);
        $instance['block_id'] = $new_instance['block_id'];
        return $instance;
    }

    function form( $instance ) {
        $block_id = 0;
        $title = isset($instance['title']) ? $instance['title'] : '';
        $sb    = array();
        $sb    = etheme_get_static_blocks();
	    $rand = rand(1000,10000);
        if(!empty($instance['block_id']))
            $block_id = esc_attr($instance['block_id']);

        global $wp_version;
	    if (
		    version_compare( $wp_version, '5.8', '>=' )
		    && apply_filters( 'gutenberg_use_widgets_block_editor', true )
		    && apply_filters( 'use_widgets_block_editor', true )
	    ){ ?>
		    <?php parent::widget_input_text( esc_html__( 'Widget title:', 'xstore-core' ), $this->get_field_id( 'title' ),$this->get_field_name( 'title' ), $title ); ?>
		    <?php parent::widget_input_text( esc_html__( 'Static block id:', 'xstore-core' ), $this->get_field_id( 'block_id' ),$this->get_field_name( 'block_id' ), $block_id ); ?>
        <?php return; }
        ?>
        <?php parent::widget_input_text( esc_html__( 'Widget title:', 'xstore-core' ), $this->get_field_id( 'title' ),$this->get_field_name( 'title' ), $title ); ?>
        <p class="et_select2-holder-<?php echo $rand; ?>"><label for="<?php echo $this->get_field_id( 'block_id' ); ?>"><?php esc_html_e( 'Block name:', 'xstore-core' ); ?></label>
            <select class="et_select2-select-<?php echo $rand; ?>" name="<?php echo $this->get_field_name( 'block_id' ); ?>" id="<?php echo $this->get_field_id( 'block_id' ); ?>">
                <option>--Select--</option>
                <?php if ( count( $sb ) > 0 && $block_id ): ?>
                    <option value="<?php echo $block_id; ?>" selected><?php echo get_the_title($block_id); ?></option>
                <?php endif ?>
            </select>

            <script>
                jQuery(document).ready(function($) {
                    let select = $('.et_select2-select-<?php echo $rand; ?>');
                    // console.log(select);
                    if (select.hasClass("select2-hidden-accessible")) {
                        select.parent().find('.select2-container').remove();
                    }
                    select.select2({
                        width : '100%',
                        ajax: {
                            url: ajaxurl,
                            dataType: 'json',
                            data: function (params) {
                                var query = {
                                    search: params.term,
                                    action: 'et_ajax_get_static_blocks',
                                }
                                return query;
                            },
                            processResults: function (data) {
                                return {
                                    results: data
                                };
                            }
                        }
                    });
                });
            </script>
        </p>
<?php
    }
}