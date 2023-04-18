/**
 * Subscriptio Plugin Frontend Product Scripts
 */
jQuery(document).ready(function() {

    'use strict';

    // Maybe change add to cart text
    jQuery('.single_variation_wrap').on('show_variation', function (event, variation) {
        if (typeof variation.rp_sub_add_to_cart_text !== 'undefined') {
            jQuery('.single_variation_wrap button[type="submit"]').html(variation.rp_sub_add_to_cart_text);
        }
    });





});
