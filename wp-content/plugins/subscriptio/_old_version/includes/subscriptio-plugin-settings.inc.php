<?php

/*
 * Returns settings for this plugin
 *
 * @return array
 */
if (!function_exists('subscriptio_plugin_settings')) {
function subscriptio_plugin_settings()
{
    return array(
        'general' => array(
            'title' => esc_html__('General', 'subscriptio'),
            'children' => array(
                'general' => array(
                    'title' => esc_html__('General', 'subscriptio'),
                    'children' => array(
                        'add_to_cart' => array(
                            'title' => esc_html__('Add To Cart button label', 'subscriptio'),
                            'type' => 'text',
                            'default' => '',
                            'placeholder' => esc_html__('No change', 'subscriptio'),
                            'validation' => array(
                                'rule' => 'default',
                                'empty' => true
                            ),
                            'hint' => '<p></p>',
                        ),
                        'multiproduct_subscription' => array(
                            'title' => esc_html__('Enable multi-product subscriptions', 'subscriptio'),
                            'type' => 'checkbox',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false
                            ),
                            'hint' => '<p>' . __('If checked, will create one subscription with multiple products, if not - will create separate subscription for each product.', 'subscriptio') . '</p>',
                        ),
                    ),
                ),
                'pricing' => array(
                    'title' => esc_html__('Pricing', 'subscriptio'),
                    'children' => array(
                        'cheapest_price_method' => array(
                            'title' => esc_html__('Cheapest product is one with', 'subscriptio'),
                            'type' => 'dropdown',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'option',
                                'empty' => false
                            ),
                            'values' => array(
                                '0' => esc_html__('Lowest calculated per-day price', 'subscriptio'),
                                '1' => esc_html__('Lowest price as set in product settings', 'subscriptio'),
                            ),
                            'hint' => '<p>' . __('How to display the price in variable/grouped subscription products (i.e. after "From:" text).', 'subscriptio') . '</p>',
                        ),
                        'sale_price_handling' => array(
                            'title' => esc_html__('Product sale price applies to', 'subscriptio'),
                            'type' => 'dropdown',
                            'default' => 'all_orders',
                            'validation' => array(
                                'rule' => 'option',
                                'empty' => false
                            ),
                            'values' => array(
                                'all_orders'      => esc_html__('Initial and renewal orders', 'subscriptio'),
                                'initial_order'   => esc_html__('Initial order only', 'subscriptio'),
                            ),
                        ),
                        'shipping_renewal_charge' => array(
                            'title' => esc_html__('Charge shipping for renewal orders', 'subscriptio'),
                            'type' => 'checkbox',
                            'default' => 1,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false
                            ),
                            'hint' => '<p></p>',
                        ),
                    ),
                ),
                'limits' => array(
                    'title' => esc_html__('Limits', 'subscriptio'),
                    'children' => array(
                        'limit_subscriptions' => array(
                            'title' => esc_html__('Subscription limit', 'subscriptio'),
                            'type' => 'dropdown',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'option',
                                'empty' => false
                            ),
                            'values' => array(
                                '0' => esc_html__('Do not limit subscriptions', 'subscriptio'),
                                '1' => esc_html__('One active subscription of specific product per customer', 'subscriptio'),
                                '2' => esc_html__('One active subscription per customer', 'subscriptio'),
                            ),
                            'hint' => '<p>' . __('How to limit the amount of subscriptions.', 'subscriptio') . '</p>',
                        ),
                        'limit_trials' => array(
                            'title' => esc_html__('Trial limit', 'subscriptio'),
                            'type' => 'dropdown',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'option',
                                'empty' => false
                            ),
                            'values' => array(
                                '0' => esc_html__('Do not limit trials', 'subscriptio'),
                                '1' => esc_html__('One trial per product per customer', 'subscriptio'),
                                '2' => esc_html__('One trial per site per customer', 'subscriptio'),
                            ),
                            'hint' => '<p>' . __('How to limit the amount of trials.', 'subscriptio') . '</p>',
                        ),
                    ),
                ),
            ),
        ),
        'capabilities' => array(
            'title' => esc_html__('Capabilities', 'subscriptio'),
            'children' => array(
                'pause_resume' => array(
                    'title' => esc_html__('Pausing & Resuming', 'subscriptio'),
                    'children' => array(
                        'customer_pausing_allowed' => array(
                            'title' => esc_html__('Allow customers to pause subscriptions', 'subscriptio'),
                            'type' => 'checkbox',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false
                            ),
                            'hint' => '<p></p>',
                        ),
                        'max_pauses' => array(
                            'title' => esc_html__('Max number of pauses', 'subscriptio'),
                            'type' => 'text',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'number',
                                'empty' => false
                            ),
                            'hint' => '<p></p>',
                        ),
                        'max_pause_duration' => array(
                            'title' => esc_html__('Max duration of a pause', 'subscriptio'),
                            'after' => esc_html__('day(s)', 'subscriptio'),
                            'type' => 'text',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'number',
                                'empty' => false
                            ),
                            'hint' => '<p></p>',
                        ),
                    ),
                ),
                'cancel' => array(
                    'title' => esc_html__('Cancelling', 'subscriptio'),
                    'children' => array(
                        'customer_cancelling_allowed' => array(
                            'title' => esc_html__('Allow customers to cancel subscriptions', 'subscriptio'),
                            'type' => 'checkbox',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false
                            ),
                            'hint' => '<p></p>',
                        ),
                    ),
                ),
            ),
        ),
        'flow' => array(
            'title' => esc_html__('Flow', 'subscriptio'),
            'children' => array(
                'subscription_flow' => array(
                    'title' => esc_html__('Subscription Flow', 'subscriptio'),
                    'children' => array(
                    ),
                ),
                'renewal_orders' => array(
                    'title' => esc_html__('Renewal Orders', 'subscriptio'),
                    'children' => array(
                        'renewal_order_day_offset' => array(
                            'title' => esc_html__('Generate renewal orders', 'subscriptio'),
                            'after' => esc_html__('day(s) before payment due date', 'subscriptio'),
                            'type' => 'text',
                            'default' => 1,
                            'validation' => array(
                                'rule' => 'number',
                                'empty' => false
                            ),
                            'hint' => '<p></p>',
                        ),
                    ),
                ),
                'reminders' => array(
                    'title' => esc_html__('Payment Reminders', 'subscriptio'),
                    'children' => array(
                        'reminders_enabled' => array(
                            'title' => esc_html__('Enable payment reminders', 'subscriptio'),
                            'type' => 'checkbox',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false
                            ),
                            'hint' => '<p></p>',
                        ),
                        'reminders_days' => array(
                            'title' => esc_html__('Send reminders before', 'subscriptio'),
                            'after' => esc_html__('day(s) (separate values by comma)', 'subscriptio'),
                            'type' => 'text',
                            'default' => '',
                            'validation' => array(
                                'rule' => 'number',
                                'empty' => true
                            ),
                            'hint' => '<p></p>',
                        ),
                    ),
                ),
                'overdue' => array(
                    'title' => esc_html__('Overdue Period', 'subscriptio'),
                    'children' => array(
                        'overdue_enabled' => array(
                            'title' => esc_html__('Enable overdue period', 'subscriptio'),
                            'type' => 'checkbox',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false
                            ),
                            'hint' => '<p></p>',
                        ),
                        'overdue_length' => array(
                            'title' => esc_html__('Overdue period length', 'subscriptio'),
                            'after' => esc_html__('day(s)', 'subscriptio'),
                            'type' => 'text',
                            'default' => '',
                            'validation' => array(
                                'rule' => 'number',
                                'empty' => true
                            ),
                            'hint' => '<p></p>',
                        ),
                    ),
                ),
                'suspensions' => array(
                    'title' => esc_html__('Suspensions', 'subscriptio'),
                    'children' => array(
                        'suspensions_enabled' => array(
                            'title' => esc_html__('Enable suspensions', 'subscriptio'),
                            'type' => 'checkbox',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false
                            ),
                            'hint' => '<p></p>',
                        ),
                        'suspensions_length' => array(
                            'title' => esc_html__('Suspension period length', 'subscriptio'),
                            'after' => esc_html__('day(s)', 'subscriptio'),
                            'type' => 'text',
                            'default' => '',
                            'validation' => array(
                                'rule' => 'number',
                                'empty' => true
                            ),
                            'hint' => '<p></p>',
                        ),
                    ),
                ),
            ),
        ),
        'gateways' => array(
            'title' => esc_html__('Gateways', 'subscriptio'),
            'children' => array(
                'stripe_gateway' => array(
                    'title' => esc_html__('Stripe', 'subscriptio'),
                    'children' => array(
                        'stripe_enabled' => array(
                            'title' => esc_html__('Enable Stripe', 'subscriptio'),
                            'type' => 'checkbox',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false,
                            ),
                            'hint' => '<p></p>',
                        ),
                    ),
                ),
                'paypal_gateway' => array(
                    'title' => esc_html__('PayPal Adaptive Payments (deprecated)', 'subscriptio'),
                    'children' => array(
                        'paypal_enabled' => array(
                            'title' => esc_html__('Enable PayPal Adaptive Payments', 'subscriptio'),
                            'type' => 'checkbox',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false,
                            ),
                            'hint' => '<p></p>',
                        ),
                        'paypal_hide_on_checkout' => array(
                            'title' => esc_html__('Use for existing subscriptions only', 'subscriptio'),
                            'type' => 'checkbox',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false,
                            ),
                            'hint' => '<p></p>',
                        ),
                    ),
                ),
                'paypal_ec_gateway' => array(
                    'title' => esc_html__('PayPal Express Checkout', 'subscriptio'),
                    'children' => array(
                        'paypal_ec_enabled' => array(
                            'title' => esc_html__('Enable PayPal Express Checkout', 'subscriptio'),
                            'type' => 'checkbox',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false,
                            ),
                            'hint' => '<p></p>',
                        ),
                    ),
                ),
            ),
        ),
    );
}
}
