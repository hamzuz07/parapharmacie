<?php
/*
    Plugin Name: GMAPS for WPBakery Page Builder (Visual Composer)
    Plugin URI: https://workingwithpixels.com/gmaps-for-visual-composer
    Description: A beautiful Google Maps add-on for WPBakery Page Builder.
    Version: 1.7
    Author: WWP
    Author URI: https://www.workingwithpixels.com/
    Copyright: WWP, 2016-2021
*/

if (!defined('ABSPATH')) die('-1');

if(!class_exists('WWP_VC_GMAPS'))
{
    function wwp_vc_gmaps_check()
    {
        if(!defined('WPB_VC_VERSION'))
        {
            add_action('admin_notices', 'wwp_vc_gmaps_notice__error');
        }
    }
    add_action('admin_init', 'wwp_vc_gmaps_check');

    function wwp_vc_gmaps_notice__error()
    {
        $class = 'notice notice-error';
        $message = 'GMAPS for WPBakery Page Builder: Please check if WPBakery Page Builder is active on your website.';

        printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
    }

    class WWP_VC_GMAPS
    {
        function defines()
        {
            defined('wwp_vc_gmaps_name')  ||  define('wwp_vc_gmaps_name', 'WWP');
            defined('wwp_vc_gmaps_dir')  ||  define('wwp_vc_gmaps_dir', plugin_dir_path( __FILE__ ));
            defined('wwp_vc_gmaps_inc')  ||  define('wwp_vc_gmaps_inc', wwp_vc_gmaps_dir . 'include/');
            defined('wwp_vc_gmaps_inc_dir')  ||  define('wwp_vc_gmaps_inc_dir', plugins_url( 'include/' , __FILE__ ));
            defined('wwp_vc_gmaps_images_path')  ||  define('wwp_vc_gmaps_images_path', plugins_url( 'include/core/img/' , __FILE__ ));
        }

        function __construct()
        {
            $this->defines();

            if(function_exists('vc_add_shortcode_param'))
            {
                vc_add_shortcode_param('wwp_vc_gmaps_marker_icons' , array(&$this, 'wwp_vc_gmaps_marker_icons' ) );
            }

            function wwp_vc_gmaps_init_admin_css()
            {
                wp_enqueue_style('wwp-vc-gmaps-admin', plugins_url( 'include/core/css/wwp-vc-gmaps-admin.css', __FILE__ ));
            }
            add_action('admin_enqueue_scripts', 'wwp_vc_gmaps_init_admin_css');

            add_action('init', array(__CLASS__, 'wwp_vc_gmaps_register_assets'));
            add_action('wp_head', array(__CLASS__, 'wwp_vc_gmaps_print_assets'));

            require_once(wwp_vc_gmaps_inc . 'core/wwp_vc_gmaps.php');
            require_once(wwp_vc_gmaps_inc . 'core/wwp_vc_gmaps_marker.php');
        }

        static function wwp_vc_gmaps_register_assets()
        {
            wp_register_style('wwp-vc-gmaps', plugins_url( 'include/core/css/wwp-vc-gmaps.css', __FILE__ ));
        }

        static function wwp_vc_gmaps_print_assets()
        {
            global $post;

            if($post !== null)
            {
                if(has_shortcode($post->post_content, 'wwp_vc_gmaps'))
                {
                    wp_print_styles('wwp-vc-gmaps');
                }
            }
        }

        function wwp_vc_gmaps_marker_icons($settings, $value)
        {
            $param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
            $type = isset($settings['type']) ? $settings['type'] : '';
            $class = isset($settings['class']) ? $settings['class'] : '';

            $pins = array('black', 'blue', 'green', 'purple', 'red', 'gray', 'orange', 'blackv2', 'bluev2', 'greenv2', 'purplev2', 'redv2', 'grayv2', 'orangev2');

            $output = '<input type="hidden" name="'.$param_name.'" class="wpb_vc_param_value '.$param_name.' '.$type.' '.$class.'" value="'.$value.'" id="trace"/>
					<div class="pin-preview"><img src="'.plugins_url('include/core/img/pins/pin_'.$value, __FILE__ ).'.png"></div>';
            $output .='<div id="markers-dropdown" >';
            $output .= '<ul class="pin-list">';
            $x = 1;
            foreach($pins as $pin)
            {
                $selected = ($pin == $value) ? 'class="selected"' : '';
                $output .= '<li '.$selected.' data-pin-url="'.plugins_url('include/core/img/pins/pin_'.$pin, __FILE__ ).'.png" data-pin="'.$pin.'"><img src="'.plugins_url('include/core/img/pins/pin_'.$pin, __FILE__ ).'.png"><label class="pin">'.$pin.'</label></li>';
                $x++;
            }
            $output .='</ul>';
            $output .='</div>';
            $output .= '<script type="text/javascript">
                    ( function ( $ ) {
                        "use strict";
                        
                        $( document ).ready( function () {
                            $(document).on("click", "#markers-dropdown li", function() 
                            {
                                $(this).attr("class","selected").siblings().removeAttr("class");
                            
                                var icon = $(this).attr("data-pin"),
                                    icon_url = $(this).attr("data-pin-url");
                                
                                $("#trace").val(icon);
                                $(".pin-preview img").attr("src", icon_url);
                            });
                        })
                    } ( jQuery ) )
                </script>';

            return $output;
        }
    }
}

new WWP_VC_GMAPS();