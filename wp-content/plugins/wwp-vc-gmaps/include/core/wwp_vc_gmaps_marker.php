<?php

add_action('init', 'wwp_vc_gmaps_marker_create');
add_shortcode( 'wwp_vc_gmaps_marker', 'wwp_vc_gmaps_marker');

function wwp_vc_gmaps_marker_create()
{
    if (!function_exists('vc_map'))
    {
        return;
    }

    vc_map(array(
        "name" => 'GMAPS Marker',
        'as_child' => array( 'only' => 'wwp_vc_gmaps' ),
        "base" => "wwp_vc_gmaps_marker",
        'content_element' => true,
        'icon' => 'marker',
        "show_settings_on_create" => true,
        "description" => "Allows you to add markers on GMAPS for Visual Composer.",
        "category" => wwp_vc_gmaps_name,
        'params' => array(
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Location Type",
                "param_name" => "location_type",
                "admin_label" => false,
                "value" => [
                    "Coordinates" => "coordinates",
                    "Location" => "location"
                ],
                "group" => "Location",
                "std" => "coordinates",
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => "Latitude",
                "param_name" => "lat",
                "admin_label" => true,
                "value" => "",
                "group" => "Location",
                "dependency" => array('element'=>'location_type','value'=>"coordinates"),
                'edit_field_class' => 'vc_col-sm-6 vc_column'
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => "Longitude",
                "param_name" => "lng",
                "admin_label" => true,
                "value" => "",
                "group" => "Location",
                "dependency" => array('element'=>'location_type','value'=>"coordinates"),
                'edit_field_class' => 'vc_col-sm-6 vc_column'
            ),

            array(
                "type" => "textfield",
                "class" => "",
                "heading" => "Location",
                "param_name" => "map_location",
                "admin_label" => true,
                "value" => "",
                "group" => "Location",
                "dependency" => array('element'=>'location_type','value'=>"location"),
            ),

            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Marker Icon",
                "param_name" => "marker_icon_option",
                "admin_label" => false,
                "value" => [
                    "Predefined" => "predefined",
                    "Custom" => "custom"
                ],
                "group" => "Marker",
                "std" => "predefined",
            ),
            array(
                "type" => "wwp_vc_gmaps_marker_icons",
                "class" => "",
                "heading" => "Selected Marker Icon",
                "param_name" => "predefined_marker_icon",
                "admin_label" => true,
                "value" => "blue",
                "group" => "Marker",
                "dependency" => array('element'=>'marker_icon_option','value'=>"predefined"),
            ),
            array(
                "type" => "attach_image",
                "class" => "",
                "heading" => "Custom Marker Icon",
                "param_name" => "pin_icon",
                "admin_label" => false,
                "group" => "Marker",
                "dependency" => array('element'=>'marker_icon_option','value'=>"custom"),
            ),

            array(
                "type" => "textarea_raw_html",
                "class" => "",
                "heading" => "Marker Description",
                "description" => "On click marker description",
                "param_name" => "marker_description",
                "admin_label" => false,
                "value" => "",
                "group" => "Marker"
            ),

            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => "Marker Animation",
                "param_name" => "marker_animation",
                "admin_label" => false,
                "value" => [
                    "Drop" => "DROP",
                    "Bounce" => "BOUNCE"
                ],
                "group" => "Marker",
                "std" => "DROP",
            ),

            array(
                "type" => "textfield",
                "class" => "",
                "heading" => "Location friendly name",
                "param_name" => "marker_friendly_name",
                "group" => "Location Listing",
            ),

            array(
                "type" => "vc_link",
                "heading" => "Location Link",
                "param_name" => "marker_location_link",
                "group" => "Location Listing",
            ),

            array(
                "type" => "dropdown",
                "heading" => "Open link on location click",
                "param_name" => "marker_location_link_open",
                "group" => "Location Listing",
                "value" => [
                    "No" => "no",
                    "Yes" => "yes"
                ],
                "std" => "no",
            ),
        )
    ));
}

function wwp_vc_gmaps_marker($atts, $content = null)
{
    global $WWP_GMAPS_SHORTCODE;

    extract(shortcode_atts(array(
        "lat" => "",
        "lng" => "",
        "marker_icon_option" => "predefined",
        "pin_icon" => "",
        "predefined_marker_icon" => "blue",
        "marker_description" => "",
        "marker_animation" => "DROP",
        "map_location" => "",
        "location_type" => "coordinates",
        "marker_friendly_name" => "",
        "marker_location_link" => "",
        "marker_location_link_open" => "no"
    ), $atts));

    if($marker_icon_option == 'predefined')
    {
        $pin_icon = plugins_url( 'img/pins/pin_'.$predefined_marker_icon, __FILE__ ).'.png';
    }

    if($location_type == "location")
    {
        $response = wp_remote_get( 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($map_location) . '&sensor=false' );

        if ("OK" !== wp_remote_retrieve_response_message($response) || 200 !== wp_remote_retrieve_response_code($response))
        {
            return '';
        }

        $location_json = wp_remote_retrieve_body( $response );
        $location_data = json_decode( $location_json );

        if (isset($location_data->results[0]->geometry->location))
        {
            $lat = $location_data->results[0]->geometry->location->lat;
            $lng = $location_data->results[0]->geometry->location->lng;
        }
    }

    $marker_options = array(
        'lat' => $lat,
        'lng' => $lng,
        'icon_url' => $pin_icon,
        'marker_type' => $marker_icon_option,
        'description' => rawurldecode(base64_decode(strip_tags($marker_description))),
        'animation' => $marker_animation,
        'marker_friendly_name' => $marker_friendly_name,
        'marker_link' => vc_build_link($marker_location_link),
        'marker_link_open' => $marker_location_link_open
    );

    $WWP_GMAPS_SHORTCODE['markers'][] = $marker_options;
}

