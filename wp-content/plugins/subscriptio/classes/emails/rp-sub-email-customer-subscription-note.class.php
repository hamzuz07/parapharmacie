<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rp-sub-subscription-email.class.php';

// We are including these files so need to check if class has not been defined yet
if (!class_exists('RP_SUB_Email_Customer_Subscription_Note', false)) {

/**
 * Customer Subscription Note Email
 *
 * @class RP_SUB_Email_Customer_Subscription_Note
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Email_Customer_Subscription_Note extends RP_SUB_Subscription_Email
{

    public $customer_note;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        $this->id               = 'customer_subscription_note';
        $this->customer_email   = true;
        $this->title            = esc_html__('Subscription customer note', 'subscriptio');
        $this->description      = esc_html__('Subscription customer note emails are sent when you add a note to customer to a subscription.', 'subscriptio');

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Trigger subscription email
     *
     * @access public
     * @param array $args
     * @return void
     */
    public function trigger($args)
    {

        // Order id or customer note is not set
        if (empty($args) || empty($args['order_id']) || empty($args['customer_note'])) {
            return;
        }
        
        // Get suborder
        $suborder = wc_get_order($args['order_id']);

        // Order is not suborder
        if (!$suborder || !is_a($suborder, 'RP_SUB_Suborder')) {
            return;
        }

        // Set customer note
        $this->customer_note = $args['customer_note'];

        // Get subscription
        $subscription = subscriptio_get_subscription($suborder);

        // Call parent method
        parent::trigger($subscription);
    }

    /**
     * Get email subject
     *
     * @access public
     * @return string
     */
    public function get_default_subject()
    {

        return sprintf(esc_html__('Note added to your %s subscription', 'subscriptio'), '{site_title}');
    }

    /**
     * Get email heading
     *
     * @access public
     * @return string
     */
    public function get_default_heading()
    {

        return esc_html__('A note has been added to your subscription', 'subscriptio');
    }

    /**
     * Default content to show below main email content
     *
     * @access public
     * @return string
     */
    public function get_default_additional_content()
    {

        return esc_html__('Thanks for reading.', 'subscriptio');
    }

    /**
     * Get template variables
     *
     * @access public
     * @return array
     */
    public function get_template_variables()
    {

        // Merge with default variables and return
        return array_merge(parent::get_template_variables(), array(
            'customer_note' => $this->customer_note,
        ));
    }





}
}

return new RP_SUB_Email_Customer_Subscription_Note();
