/*
 * Admin Scripts
 */

jQuery(document).ready(function() {

    'use strict';

    /**
     * =====================================================================================================================
     * Subscription form validation
     * =====================================================================================================================
     */

    /**
     * Disable default HTML5 validation
     */
    jQuery('.post-type-rp_sub_subscription form#post').first().each(function() {

        var form = jQuery(this);

        /**
         * Prevent default validation
         */
        form.attr('novalidate', 'novalidate');

        /**
         * Submit handler
         */
        form.submit(function(e) {

            var invalid_element = false;

            // Customer
            if (!invalid_element) {
                form.find('[name="customer_user"]').first().each(function() {
                    if (!jQuery(this).val()) {
                        set_error(jQuery(this), rp_sub_vars.subscription_error_messages.invalid_customer);
                        invalid_element = jQuery(this);
                        return false;
                    }
                });
            }

            // Billing cycle
            if (!invalid_element) {
                form.find('[name="rp_sub_subscription_settings_billing_cycle_length"]').first().each(function() {
                    if (!jQuery(this).val()) {
                        set_error(jQuery(this), rp_sub_vars.subscription_error_messages.invalid_billing_cycle);
                        invalid_element = jQuery(this);
                        return false;
                    }
                });
            }

            // Subscription items
            if (!invalid_element) {
                form.find('#order_line_items').first().each(function() {
                    if (!jQuery(this).children().length) {
                        set_error(jQuery(this), rp_sub_vars.subscription_error_messages.no_subscription_items);
                        invalid_element = jQuery(this);
                        return false;
                    }
                });
            }

            // Form is not valid
            if (invalid_element) {

                // Get top offset
                var offset  = invalid_element.offset();
                var top     = offset.top;

                // Scroll to invalid input
                jQuery('html, body').animate({
                    scrollTop: ((top - 100) > 0 ? (top - 100) : 0)
                }, 500).promise().then(function() {
                    display_error(invalid_element);
                });

                // Do not submit form
                e.preventDefault();
            }
        });

        /**
         * Set element state to error
         */
        function set_error(element, message)
        {

            // Get message
            if (typeof message === 'undefined' || message === null) {
                message = rp_sub_vars.subscription_error_messages.generic_error;
            }

            // Set error
            element.data('rp-sub-subscription-validation-error', message);
        }

        /**
         * Display error
         */
        function display_error(element)
        {

            // Get message
            var message = element.data('rp-sub-subscription-validation-error');

            // Focus on element
            element.focus();

            // Set tooltip
            element.on('mouseleave', function (event) {
                event.stopImmediatePropagation();
            }).tooltip({
                content: message,
                items: ':data(rp-sub-subscription-validation-error)',
                tooltipClass: 'rp_sub_subscription_validation_error',
                classes: {
                    'ui-tooltip': 'rp_sub_subscription_validation_error'
                },
                position: {
                    my: 'center top',
                    at: 'left+110 bottom+10'
                },
                create: function() {

                    // Adjust position for multiselect fields
                    if (element.hasClass('select2-hidden-accessible')) {
                        element.tooltip('option', 'position', {
                            my: 'center top',
                            at: 'left+100 bottom+30'
                        });
                    }

                    // Remove tooltip on interaction
                    var removal_selectors = element.add('html, body');
                    removal_selectors.on('click keyup change', {element: element, removal_selectors: removal_selectors}, remove_tooltip);
                }
            }).tooltip('open');
        }

        /**
         * Remove tooltip
         */
        function remove_tooltip(event)
        {

            // Get args
            var element = event.data.element;
            var removal_selectors = event.data.removal_selectors;

            // Destroy tooltip
            if (element.data('ui-tooltip')) {
                element.tooltip('destroy');
            }

            // Remove error message
            element.removeData('rp-sub-subscription-validation-error');

            // Remove event listeners
            removal_selectors.off('click keyup change', remove_tooltip);
        }
    });

    /**
     * =====================================================================================================================
     * Show error details in activity log
     * =====================================================================================================================
     */

    jQuery('.rp-sub-log-entry-error-details-show').click(function() {
        jQuery(this).hide().parent().find('.rp-sub-log-entry-error-details, .rp-sub-log-entry-error-details-hide').show();
    });
    jQuery('.rp-sub-log-entry-error-details-hide').click(function() {
        jQuery(this).hide().parent().find('.rp-sub-log-entry-error-details-show').show().parent().find('.rp-sub-log-entry-error-details').hide();
    });

    /**
     * =====================================================================================================================
     * Payment gateway change prompt
     * =====================================================================================================================
     */

    jQuery('.post-type-rp_sub_subscription form#post').submit(function(e) {

        // Check if payment gateway is being changed from automatic to manual
        if (jQuery('select#rp_sub_subscription_payment_gateway').val() === 'rp_sub_manual_payments' && jQuery('select#rp_sub_subscription_payment_gateway option').length > 1) {

            // Ask for confirmation
            if (!confirm(rp_sub_vars.payment_gateway_change_confirmation_text)) {

                // Cancelled, do not submit form
                e.preventDefault();
            }
        }
    });

    /**
     * =====================================================================================================================
     * Product page
     * =====================================================================================================================
     */

    // Set up product settings controls
    jQuery.each(rp_sub_vars.product_settings_contexts, function(key, title) {
        jQuery('body').rightpress_product_settings_control({
            key:    key,
            title:  title
        });
    });





});
