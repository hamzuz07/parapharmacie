<?php

add_action('init', 'wwp_vc_gmaps_create');
add_shortcode( 'wwp_vc_gmaps', 'wwp_vc_gmaps');

function wwp_vc_gmaps_create()
{
    if(!function_exists('vc_map'))
    {
        return;
    }

    vc_map(array(
        "name" => 'GMAPS for WPBakery Page Builder',
        'as_parent' => array( 'only' => 'wwp_vc_gmaps_marker' ),
        "base" => "wwp_vc_gmaps",
        'content_element' => true,
        'icon' => 'map',
        'show_settings_on_create' => true,
        "js_view" => 'VcColumnView',
        "description" => "Display Google Maps to indicate your location.",
        "category" => wwp_vc_gmaps_name,
        "params" => array(

            array(
                "type" => "textfield",
                "class" => "",
                "heading" => "Map Name",
                "param_name" => "map_name",
                "admin_label" => true,
                "value" => "",
                "group" => "Styling",
                "description" => "Title for a styled map (default: Custom Map)"
            ),

            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Auto Zoom",
                "param_name" => "disable_auto_zoom",
                "admin_label" => false,
                "value" => [
                    "Yes" => "true",
                    "No" => "false"
                ],
                "group" => "Styling",
                "std" => "true",
                "description" => "Auto center map based on marker(s)"
            ),

            array(
                "type" => "textfield",
                "class" => "",
                "heading" => "Zoom",
                "param_name" => "zoom",
                "admin_label" => false,
                "value" => "17",
                "group" => "Styling",
                "dependency" => [
                    "element" => "disable_auto_zoom",
                    "value" => "false"
                ],
                "description" => "Zoom level"
            ),

            array(
                "type" => "textfield",
                "class" => "",
                "heading" => "Width",
                "param_name" => "width",
                "admin_label" => false,
                "value" => "100",
                "group" => "Styling",
                'edit_field_class' => 'vc_col-sm-6 vc_column'
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Width Type",
                "param_name" => "width_type",
                "admin_label" => false,
                "value" => [
                    "Pixels" => "px",
                    "Percentage" => "%"
                ],
                "group" => "Styling",
                'edit_field_class' => 'vc_col-sm-6 vc_column',
                "std" => "%"
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => "Height",
                "param_name" => "height",
                "admin_label" => false,
                "value" => "300",
                "group" => "Styling",
                'edit_field_class' => 'vc_col-sm-6 vc_column'
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Height Type",
                "param_name" => "height_type",
                "admin_label" => false,
                "value" => [
                    "Pixels" => "px",
                    "Percentage" => "%"
                ],
                "group" => "Styling",
                'edit_field_class' => 'vc_col-sm-6 vc_column',
                "std" => "px"
            ),

            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Custom Map Style",
                "param_name" => "custom_map_style",
                "admin_label" => false,
                "value" => [
                    "Default" => "default",
                    "Custom" => "custom",
                    "Apple Maps" => "apple_maps",
                    "Light Gray" => "light_gray",
                    "Dark" => "dark",
                    "Neutral Blue" => "neutral_blue",
                    "Orange Ocean" => "orange_ocean",
                    "Magenta" => "magenta",
                    "Flat Map" => "flat_map",
                    "Winter" => "winter"
                ],
                "group" => "Styling",
                "std" => 0,
                "description" => "Apply predefined or custom styles to the map"
            ),

            array(
                "type" => "textarea_raw_html",
                "class" => "",
                "heading" => "Custom Map Style",
                "description" => 'Take a look at <a href="https://snazzymaps.com/" target="_blank">Snazzy Maps</a>',
                "param_name" => "styles",
                "admin_label" => false,
                "value" => "",
                "group" => "Styling",
                "dependency" => [
                    "element" => "custom_map_style",
                    "value" => "custom"
                ],
            ),

            array(
                "type" => "dropdown",
                "heading" => "Marker Clustering",
                "param_name" => "marker_clustering",
                "admin_label" => false,
                "value" => [
                    "No" => "no",
                    "Yes" => "yes"
                ],
                "std" => "no",
                "description" => "Option to enable or disable marker clustering",
                "group" => "Styling",
            ),

            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Disable All Map Controls",
                "param_name" => "disable_map_controls",
                "admin_label" => false,
                "value" => [
                    "Yes" => "true",
                    "No" => "false"
                ],
                "group" => "Controls",
                "std" => "false",
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Map Type",
                "param_name" => "map_type_control",
                "admin_label" => false,
                "value" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "group" => "Controls",
                "dependency" => [
                    "element" => "disable_map_controls",
                    "value" => "false"
                ],
            ),
            array(
                "type" => "checkbox",
                "class" => "",
                "heading" => "Map Type Options",
                "param_name" => "map_type_control_options",
                "admin_label" => false,
                "value" => [
                    "Roadmap" => "ROADMAP",
                    "Terrain" => "TERRAIN",
                    "Satellite" => "SATELLITE",
                    "Hybrid" => "HYBRID"
                ],
                "group" => "Controls",
                "dependency" => [
                    "element" => "map_type_control",
                    "value" => "true"
                ],
                "std" => "ROADMAP"
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Map Type Position",
                "param_name" => "map_type_control_position",
                "admin_label" => false,
                "value" => [
                    'Top Left' => 'TOP_LEFT',
                    'Top Center' => 'TOP_CENTER',
                    'Top Right' => 'TOP_RIGHT',
                    'Bottom Left' => 'BOTTOM_LEFT',
                    'Bottom Right' => 'BOTTOM_RIGHT',
                    'Left Top' => 'LEFT_TOP',
                    'Left Center' => 'LEFT_CENTER',
                    'Left Bottom' => 'LEFT_BOTTOM',
                    'Right Top' => 'RIGHT_TOP',
                    'Right Center' => 'RIGHT_CENTER',
                    'Right Bottom' => 'RIGHT_BOTTOM',
                ],
                "group" => "Controls",
                "dependency" => [
                    "element" => "map_type_control",
                    "value" => "true"
                ],
                "std" => "LEFT_TOP"
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Streetview",
                "param_name" => "streetview_control",
                "admin_label" => false,
                "value" => [
                    "Yes" => "true",
                    "No" => "false"
                ],
                "group" => "Controls",
                "std" => "false",
                "dependency" => [
                    "element" => "disable_map_controls",
                    "value" => "false"
                ],
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Streetview Position",
                "param_name" => "streetview_control_position",
                "admin_label" => false,
                "value" => [
                    'Top Left' => 'TOP_LEFT',
                    'Top Center' => 'TOP_CENTER',
                    'Top Right' => 'TOP_RIGHT',
                    'Bottom Left' => 'BOTTOM_LEFT',
                    'Bottom Right' => 'BOTTOM_RIGHT',
                    'Left Top' => 'LEFT_TOP',
                    'Left Center' => 'LEFT_CENTER',
                    'Left Bottom' => 'LEFT_BOTTOM',
                    'Right Top' => 'RIGHT_TOP',
                    'Right Center' => 'RIGHT_CENTER',
                    'Right Bottom' => 'RIGHT_BOTTOM',
                ],
                "group" => "Controls",
                "dependency" => [
                    "element" => "streetview_control",
                    "value" => "true"
                ],
                "std" => "RIGHT_BOTTOM"
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Zoom",
                "param_name" => "zoom_control",
                "admin_label" => false,
                "value" => [
                    "Yes" => "true",
                    "No" => "false"
                ],
                "group" => "Controls",
                "dependency" => [
                    "element" => "disable_map_controls",
                    "value" => "false"
                ],
                "std" => "true"
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Zoom Position",
                "param_name" => "zoom_control_position",
                "admin_label" => false,
                "value" => [
                    'Top Left' => 'TOP_LEFT',
                    'Top Center' => 'TOP_CENTER',
                    'Top Right' => 'TOP_RIGHT',
                    'Bottom Left' => 'BOTTOM_LEFT',
                    'Bottom Right' => 'BOTTOM_RIGHT',
                    'Left Top' => 'LEFT_TOP',
                    'Left Center' => 'LEFT_CENTER',
                    'Left Bottom' => 'LEFT_BOTTOM',
                    'Right Top' => 'RIGHT_TOP',
                    'Right Center' => 'RIGHT_CENTER',
                    'Right Bottom' => 'RIGHT_BOTTOM',
                ],
                "group" => "Controls",
                "dependency" => [
                    "element" => "zoom_control",
                    "value" => "true"
                ],
                "std" => "RIGHT_BOTTOM"
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Fullscreen",
                "param_name" => "fullscreen_control",
                "admin_label" => false,
                "value" => [
                    "Yes" => "true",
                    "No" => "false"
                ],
                "group" => "Controls",
                "std" => "false",
                "dependency" => [
                    "element" => "disable_map_controls",
                    "value" => "false"
                ],
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Dragging",
                "param_name" => "dragging_control",
                "admin_label" => false,
                "value" => [
                    "Yes" => "true",
                    "No" => "false"
                ],
                "group" => "Controls",
                "dependency" => [
                    "element" => "disable_map_controls",
                    "value" => "false"
                ],
                "std" => "true"
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Dragging Position",
                "param_name" => "dragging_control_position",
                "admin_label" => false,
                "value" => [
                    'Top Left' => 'TOP_LEFT',
                    'Top Center' => 'TOP_CENTER',
                    'Top Right' => 'TOP_RIGHT',
                    'Bottom Left' => 'BOTTOM_LEFT',
                    'Bottom Right' => 'BOTTOM_RIGHT',
                    'Left Top' => 'LEFT_TOP',
                    'Left Center' => 'LEFT_CENTER',
                    'Left Bottom' => 'LEFT_BOTTOM',
                    'Right Top' => 'RIGHT_TOP',
                    'Right Center' => 'RIGHT_CENTER',
                    'Right Bottom' => 'RIGHT_BOTTOM',
                ],
                "group" => "Controls",
                "dependency" => [
                    "element" => "dragging_control",
                    "value" => "true"
                ],
                "std" => "LEFT_CENTER"
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Traffic",
                "param_name" => "traffic_layer",
                "admin_label" => false,
                "value" => [
                    "Yes" => "true",
                    "No" => "false"
                ],
                "group" => "Layers",
                "std" => "false",
                "description" => "Adds real-time traffic information (where supported)."
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Transit",
                "param_name" => "transit_layer",
                "admin_label" => false,
                "value" => [
                    "Yes" => "true",
                    "No" => "false"
                ],
                "group" => "Layers",
                "std" => "false",
                "description" => "Adds a layer of transit paths, showing major transit lines as thick, colored lines."
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Bicycling",
                "param_name" => "bicycling_layer",
                "admin_label" => false,
                "value" => [
                    "Yes" => "true",
                    "No" => "false"
                ],
                "group" => "Layers",
                "std" => "false",
                "description" => 'Adds a layer of bike paths, suggested bike routes and other overlays specific to bicycling usage.'
            ),

            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Dragging on Mobile",
                "param_name" => "dragging_mobile",
                "value" => [
                    "Enable" => "true",
                    "Disable" => "false"
                ],
                "group" => "Dragging"
            ),

            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Dragging on Desktop",
                "param_name" => "dragging_desktop",
                "value" => [
                    "Enable" => "true",
                    "Disable" => "false"
                ],
                "group" => "Dragging"
            ),

            array(
                "type" => "textfield",
                "heading" => "API Key",
                "param_name" => "api_key",
                "admin_label" => false,
                "value" => "",
                "group" => "API",
            ),

            array(
                "type" => "dropdown",
                "heading" => "Language",
                "param_name" => "language",
                "value" => array(
                    "Arabic" => "ar",
                    "Bulgarian" => "bg",
                    "Bengali" => "bn",
                    "Catalan" => "ca",
                    "Czech" => "cs",
                    "Danish" => "da",
                    "German" => "de",
                    "Greek" => "el",
                    "English" => "en",
                    "English (Australian)" => "en-AU",
                    "English (Great Britain)" => "en-GB",
                    "Spanish" => "es",
                    "Basque" => "eu",
                    "Farsi" => "fa",
                    "Finnish" => "fi",
                    "Filipino" => "fil",
                    "French" => "fr",
                    "Galician" => "gl",
                    "Gujarati" => "gu",
                    "Hindi" => "hi",
                    "Croatian" => "hr",
                    "Hungarian" => "hu",
                    "Indonesian" => "id",
                    "Italian" => "it",
                    "Hebrew" => "iw",
                    "Japanese" => "ja",
                    "Kannada" => "kn",
                    "Korean" => "ko",
                    "Lithuanian" => "lt",
                    "Latvian" => "lv",
                    "Malayalam" => "ml",
                    "Marathi" => "mr",
                    "Dutch" => "nl",
                    "Norwegian" => "no",
                    "Polish" => "pl",
                    "Portuguese" => "pt",
                    "Portuguese (Brazil)" => "pt-BR",
                    "Portuguese (Portugal)" => "pt-PT",
                    "Romanian" => "ro",
                    "Russian" => "ru",
                    "Slovak" => "sk",
                    "Slovenian" => "sl",
                    "Serbian" => "sr",
                    "Swedish" => "sv",
                    "Tamil" => "ta",
                    "Telugu" => "te",
                    "Thai" => "th",
                    "Tagalog" => "tl",
                    "Turkish" => "tr",
                    "Ukrainian" => "uk",
                    "Vietnamese" => "vi",
                    "Chinese (Simplified)" => "zh-CN",
                    "Chinese (Traditional)" => "zh-TW",
                ),
                "std" => "en",
                "group" => "Localization"
            ),


            array(
                "type" => "dropdown",
                "heading" => "Show locations below map",
                "param_name" => "show_locations_below_map",
                "admin_label" => false,
                "value" => array( "No" => "no", "Yes" => "yes"),
                "std" => "no",
                "description" => "Option to show locations under the map",
                "group" => "Locations",
            ),
            array(
                "type" => "dropdown",
                "heading" => "Show location marker",
                "param_name" => "show_location_marker",
                "admin_label" => false,
                "value" => array( "No" => "no", "Yes" => "yes"),
                "std" => "no",
                "description" => "Option to show location marker on the list",
                "group" => "Locations",
                "dependency" => array('element'=>'show_locations_below_map','value'=>"yes"),
            ),
            array(
                "type" => "dropdown",
                "heading" => "Show location address",
                "param_name" => "show_location_address",
                "admin_label" => false,
                "value" => array( "No" => "no", "Yes" => "yes"),
                "std" => "yes",
                "description" => "Please note that API Requests limits could apply",
                "group" => "Locations",
                "dependency" => array('element'=>'show_locations_below_map','value'=>"yes"),
            ),
            array(
                "type" => "dropdown",
                "heading" => "Styling",
                "param_name" => "show_location_styling",
                "admin_label" => false,
                "value" => array( "None" => "none", "Rounded border (white background)" => "rounded_border", "Square border (white background)" => "square_border"),
                "std" => "none",
                "group" => "Locations",
                "dependency" => array('element'=>'show_locations_below_map','value'=>"yes"),
            )
        )
    ));
}

function wwp_vc_gmaps_add_api_key($api_key, $language)
{
    $url = 'https://maps.googleapis.com/maps/api/js?language='.$language;

    if($api_key != '')
    {
        $url = 'https://maps.googleapis.com/maps/api/js?key='.$api_key.'&language='.$language;
    }

    wp_enqueue_script("google-maps-api", $url, array('jquery'), '', true);
    wp_enqueue_script("gmaps-marker-clustering", wwp_vc_gmaps_inc_dir.'core/js/markerclusterer.js', array('jquery'), '', true);
    wp_enqueue_script("wwp-gmaps-init", wwp_vc_gmaps_inc_dir.'core/js/wwp-gmaps-init.js', array('jquery'), '', true);
}

function wwp_vc_gmaps($atts, $content = null)
{
    global $WWP_GMAPS_SHORTCODE;

    $WWP_GMAPS_SHORTCODE['markers'] = array();

    $width = $add_dragging_control = $add_transit_layer = $add_traffic_layer = $add_bicycling_layer = $height = $lat = $lng = $add_map_height = $add_map_width = $add_map_width = $add_map_styling = $add_map_pin = $add_icon = $marker_description = '';

    extract(shortcode_atts(array(
        "fullscreen_control" => "false",
        "streetview_control" => "false",
        "streetview_control_position" => 'RIGHT_BOTTOM',
        "zoom_control" => "true",
        "zoom_control_position" => 'RIGHT_BOTTOM',
        "map_type_control" => "true",
        "map_type_control_position" => 'TOP_LEFT',
        "map_type_control_options" => 'ROADMAP',
        "dragging_control" => "true",
        "dragging_control_position" => "LEFT_CENTER",
        "width" => "100",
        "width_type" => "%",
        "height" => "300",
        "height_type" => "px",
        "lat" => "",
        "lng" => "",
        "map_name" => "",
        "styles" => "",
        "custom_map_style" => "default",
        "zoom" => 17,
        "disable_auto_zoom" => "true",
        "dragging_mobile" => "true",
        "dragging_desktop" => "true",
        "traffic_layer" => "false",
        "transit_layer" => "false",
        "bicycling_layer" => "false",
        "marker_animation" => "",
        "disable_map_controls" => "false",
        "marker_clustering" => "no",
        "api_key" => "",
        "language" => "en",
        "show_locations_below_map" => "no",
        "show_location_marker" => "no",
        "show_location_address" => "yes",
        "show_location_styling" => "none"
    ), $atts));

    wwp_vc_gmaps_add_api_key($api_key, $language);
    add_action('wp_enqueue_scripts', 'wwp_vc_gmaps_add_api_key');

    $map_types_concat = $marker_clustering_images_path = $map_types_concat_separator = '';

    if($marker_clustering == "yes")
    {
        $marker_clustering_images_path = wwp_vc_gmaps_images_path .'m';
    }

    if($map_type_control_options != '')
    {
        $map_types = explode(",", $map_type_control_options);

        foreach($map_types as $map_type)
        {
            $map_types_concat .= $map_types_concat_separator.strtolower($map_type);
            $map_types_concat_separator = ',';
        }
    }
    else
    {
        $map_types_concat = 'roadmap';
    }

    if($map_name == "" && ($styles != "" || $custom_map_style != "default") )
    {
        $map_name = 'Custom Map';
    }

    if($map_name != '')
    {
        $map_types_concat .= ',map_style';
    }

    $id = "map_".uniqid();

    if($height != '')
    {
        if($height_type == '%')
        {
            $height_type = 'vh';
        }

        $add_map_height = 'height: '. $height . $height_type.'; ';
    }

    if($width != '')
    {
        $add_map_width = 'width: '. $width . $width_type.'; ';

    }

    if(wp_is_mobile())
    {
        $dragging = $dragging_mobile;
    }
    else
    {
        $dragging = $dragging_desktop;
    }

    if($dragging_control == "true")
    {
        wp_enqueue_style( 'font-awesome' );
    }

    $markers = do_shortcode( $content );

    $all_locations = array();

    foreach($WWP_GMAPS_SHORTCODE['markers'] as $key => $location)
    {
        if($location['lat'] == '' || $location['lng'] == '')
        {
            continue;
        }

        $pin_path = $pin_width = $pin_height = '';

        if($location['icon_url'] != '')
        {
            if($location['marker_type'] == 'custom')
            {
                $pin_icon = wp_get_attachment_image_src($location['icon_url'], 'full');

                if($pin_icon)
                {
                    $pin_path = $pin_icon[0];
                    $pin_width = $pin_icon[1];
                    $pin_height = $pin_icon[2];
                }
            }

            if($location['marker_type'] == 'predefined')
            {
                $pin_width = 45;
                $pin_height = 60;

                $pin_path = $location['icon_url'];
                $pin_info = @getimagesize($pin_path);
                if($pin_info)
                {
                    $pin_width = $pin_info[0];
                    $pin_height = $pin_info[1];
                }
            }
        }

        $all_locations[$key]['animation'] = $location['animation'];
        $all_locations[$key]['lat'] = $location['lat'];
        $all_locations[$key]['lng'] = $location['lng'];
        $all_locations[$key]['description'] = base64_encode($location['description']);
        $all_locations[$key]['pin_path'] = $pin_path;
        $all_locations[$key]['pin_width'] = $pin_width;
        $all_locations[$key]['pin_height'] = $pin_height;
        $all_locations[$key]['marker_friendly_name'] = $location['marker_friendly_name'];
        $all_locations[$key]['marker_link'] = $location['marker_link'];
        $all_locations[$key]['marker_link_open'] = $location['marker_link_open'];
    }

    if($disable_map_controls == "true")
    {
        $map_type_control = "false";
        $zoom_control = "false";
        $fullscreen_control = "false";
        $streetview_control = "false";
        $dragging_control = "false";
    }

    wp_localize_script('wwp-gmaps-init', 'wwp_gmaps_'.$id, array(
       "mapID" => $id,
       "draggable" => $dragging,
       "draggingControl" => $dragging_control,
       "dragging_control_position" => $dragging_control_position,
       "fullscreenControl" => $fullscreen_control,
       "streetViewControl" => $streetview_control,
       "streetview_control_position" => $streetview_control_position,
       "zoomControl" => $zoom_control,
       "zoom_control_position" => $zoom_control_position,
       "mapTypeControl" => $map_type_control,
       "map_type_control_position" => $map_type_control_position,
       "add_places" => json_encode($all_locations),
       "enable_marker_clustering" => $marker_clustering,
       "marker_clustering_images_path" => $marker_clustering_images_path,
       "transitLayer" => $transit_layer,
       "trafficLayer" => $traffic_layer,
       "bicyclingLayer" => $bicycling_layer,
       "disable_auto_zoom" => $disable_auto_zoom,
       "zoom" => $zoom, 
       "styles" => base64_encode(rawurldecode(base64_decode(strip_tags($styles)))),
       "map_name" => $map_name,
       "map_name_show" => $map_types_concat,
       "custom_map_style" => $custom_map_style,
       "show_locations_below_map" => $show_locations_below_map,
       "show_location_marker" => $show_location_marker,
       "show_location_address" => $show_location_address,
       "show_location_styling" => $show_location_styling
    ));

    $output = '<div id="'.$id.'" data-instance="wwp_gmaps_'.$id.'" class="wwp-vc-gmaps-map wpb_content_element" style="'.$add_map_height.$add_map_width.'"><div class="map_lock"></div></div>';

    $output .= '<div id="wwp-gmaps-locations-'.$id.'"></div>';

    return $output;
}

if(class_exists('WPBakeryShortCodesContainer')){class WPBakeryShortCode_wwp_vc_gmaps extends WPBakeryShortCodesContainer{}}
if(class_exists('WPBakeryShortCode')){class WPBakeryShortCode_wwp_vc_gmaps_marker extends WPBakeryShortCode{}}