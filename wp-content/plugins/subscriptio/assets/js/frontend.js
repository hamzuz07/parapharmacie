/**
 * Subscriptio Plugin Frontend Scripts
 */
jQuery(document).ready(function() {

    'use strict';

    // Subscription action confirmation
    jQuery.each(['pause', 'resume', 'set_to_cancel', 'reactivate', 'cancel'], function(index, action) {
        jQuery('.subscriptio-subscription-action-' + action).click(function(e) {
            e.preventDefault();
            if (confirm(rp_sub_frontend_vars['confirm_' + action])) {
                window.location = this.href;
            }
        });
    });





});
