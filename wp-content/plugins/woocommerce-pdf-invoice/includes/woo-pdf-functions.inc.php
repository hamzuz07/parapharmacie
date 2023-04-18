<?php

/**
 * Functions to allow developer interaction
 */

/**
 * Get invoice url
 *
 * @param object|int $order
 * @param bool $admin
 * @return string|bool
 */
function woo_pdf_get_invoice_url($order = null, $admin = false)
{

    // Get order
    if ($order = _woo_pdf_get_order($order)) {

        // Get regular invoice url
        if ($url = WooPDF::get_regular_invoice_url($order, $admin)) {

            // Return url
            return $url;
        }
        // Get proforma invoice url
        else if ($url = WooPDF::get_proforma_invoice_url($order, $admin)) {

            // Return url
            return $url;
        }
    }

    // Unable to get invoice url
    return false;
}

/**
 * Get regular invoice url
 *
 * @param object|int $order
 * @param bool $admin
 * @return string|bool
 */
function woo_pdf_get_regular_invoice_url($order = null, $admin = false)
{

    // Get order
    if ($order = _woo_pdf_get_order($order)) {

        // Get regular invoice url
        if ($url = WooPDF::get_regular_invoice_url($order, $admin)) {

            // Return url
            return $url;
        }
    }

    // Unable to get regular invoice url
    return false;
}

/**
 * Get proforma invoice url
 *
 * @param object|int $order
 * @param bool $admin
 * @return string|bool
 */
function woo_pdf_get_proforma_invoice_url($order = null, $admin = false)
{

    // Get order
    if ($order = _woo_pdf_get_order($order)) {

        // Get proforma invoice url
        if ($url = WooPDF::get_proforma_invoice_url($order, $admin)) {

            // Return url
            return $url;
        }
    }

    // Unable to get proforma invoice url
    return false;
}

/**
 * Get regular invoice path
 *
 * @param object|int $order
 * @return string|bool
 */
function woo_pdf_get_regular_invoice_path($order = null)
{

    // Get order
    if ($order = _woo_pdf_get_order($order)) {

        // Get invoice details
        if ($invoice = WooPDF::get_invoice($order->get_id())) {

            // Get and return path
            return WooPDF::get_regular_invoice_full_path($invoice['code']);
        }
    }

    // Unable to get invoice path
    return false;
}

/**
 * Order getter
 *
 * @param object|int $order
 * @return object|bool
 */
function _woo_pdf_get_order($order)
{

    global $post;

    // Load order object
    if (!is_a($order, 'WC_Order')) {
        $order = wc_get_order($order);
    }

    // Get order from current post
    if (!$order && is_a($post, 'WP_Post') && !empty($post->ID)) {
        if ($current_order = wc_get_order($post->ID)) {
            $order = $current_order;
        }
    }

    return $order ? $order : false;
}
