<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rp-sub-email.class.php';

/**
 * Subscription Email
 *
 * @class RP_SUB_Subscription_Email
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Subscription_Email extends RP_SUB_Email
{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Define placeholders
        $this->placeholders = array(
            '{subscription_number}' => '',
        );

        // Add site title placeholder
        if (!RightPress_Help::wc_version_gte('3.7')) {
            $this->placeholders['{site_title}'] = $this->get_blogname();
        }

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Trigger subscription email
     *
     * @access public
     * @param object $subscription
     * @return void
     */
    public function trigger($subscription)
    {

        // Check subscription object
        if (!is_a($subscription, 'RP_SUB_Subscription')) {
            RightPress_Help::doing_it_wrong(__METHOD__, 'Subscription object must be passed to this method.', '3.0');
            return;
        }

        // Set object
        $this->object = $subscription;

        // Set recipient
        $this->recipient = $subscription->get_suborder()->get_billing_email();

        // Set placeholder values
        $this->placeholders['{subscription_number}'] = $subscription->get_subscription_number();

        // Set template variables
        $this->template_variables = array_merge($this->get_template_variables(), array(
            'subscription' => $subscription,
        ));

        // Call parent method
        parent::trigger($subscription);
    }





}
