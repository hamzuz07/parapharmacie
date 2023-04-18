<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rp-sub-renewal-order-email.class.php';

// We are including these files so need to check if class has not been defined yet
if (!class_exists('RP_SUB_Email_Customer_Processing_Renewal_Order', false)) {

/**
 * Customer Processing Renewal Order Email
 *
 * @class RP_SUB_Email_Customer_Processing_Renewal_Order
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Email_Customer_Processing_Renewal_Order extends RP_SUB_Renewal_Order_Email
{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        $this->id               = 'customer_processing_renewal_order';
        $this->customer_email   = true;
        $this->title            = esc_html__('Processing renewal order', 'subscriptio');
        $this->description      = esc_html__('This is a renewal order notification sent to customers containing renewal order details after payment.', 'subscriptio');

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Get email subject
     *
     * @access public
     * @return string
     */
    public function get_default_subject()
    {

        return esc_html__('Payment received for your subscription renewal order', 'subscriptio');
    }

    /**
     * Get email heading
     *
     * @access public
     * @return string
     */
    public function get_default_heading()
    {

        return esc_html__('Thank you for your payment', 'subscriptio');
    }

    /**
     * Default content to show below main email content
     *
     * @access public
     * @return string
     */
    public function get_default_additional_content()
    {

        return esc_html__('Thank you for choosing us.', 'subscriptio');
    }





}
}

return new RP_SUB_Email_Customer_Processing_Renewal_Order();
