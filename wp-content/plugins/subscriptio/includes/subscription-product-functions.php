<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Get subscription product
 *
 * @param int|object $identifier
 * @return RP_SUB_Subscription_Product
 */
function subscriptio_get_subscription_product($identifier)
{

    // Check if system is ready
    RP_SUB::ready_or_fail(__FUNCTION__);

    // Get subscription product object
    $controller = RP_SUB_Subscription_Product_Controller::get_instance();
    return $controller->get_object($identifier);
}

/**
 * Check if product is subscription product
 *
 * If variable product is provided, checks if any of its variations are subscriptions
 *
 * @param WC_Product|int $product
 * @return bool
 */
function subscriptio_is_subscription_product($product)
{

    // Load product object if needed
    if (!is_a($product, 'WC_Product') && is_numeric($product)) {
        $product = wc_get_product($product);
    }

    // Check if this is WooCommerce product
    if (!is_a($product, 'WC_Product')) {
        return false;
    }

    // Check for flag in meta
    if (RightPress_WC::product_get_meta($product, '_rp_sub:subscription_product')) {
        return true;
    }

    // Check children
    // TODO: Probably we should cache this in parent meta when variations are updated
    foreach ($product->get_children() as $child_id) {

        // Load child product object
        $child_product = wc_get_product($child_id);

        // Check for flag in meta
        if (RightPress_WC::product_get_meta($child_product, '_rp_sub:subscription_product')) {
            return true;
        }
    }

    return false;
}

/**
 * Check if cart contains subscription product
 *
 * @access public
 * @return bool
 */
function subscriptio_cart_contains_subscription_product()
{

    // Iterate over cart items
    foreach (RightPress_Help::get_wc_cart_items() as $cart_item) {

        // Check if cart contains subscription product
        if (subscriptio_is_subscription_product($cart_item['data'])) {

            // Cart contains subscription product
            return true;
        }
    }

    // Cart does not contain subscription product
    return false;
}
