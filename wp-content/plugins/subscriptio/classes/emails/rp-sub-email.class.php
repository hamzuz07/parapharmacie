<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Email
 *
 * @class RP_SUB_Email
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Email extends WC_Email
{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Get template name
        $template_name = str_replace('_', '-', $this->id);

        // Set template paths
        $this->template_html    = 'emails/' . $template_name . '.php';
        $this->template_plain   = 'emails/plain/' . $template_name . '.php';

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Initialize settings form fields
     *
     * @access public
     * @return void
     */
    public function init_form_fields()
    {

        // Get available placeholders for this email
        $placeholder_text = sprintf(esc_html__('Available placeholders: %s', 'subscriptio'), '<code>' . esc_html(implode('</code>, <code>', array_keys($this->placeholders))) . '</code>');

        // Define form fields
        $this->form_fields = array(
            'enabled' => array(
                'title'     => esc_html__('Enable/Disable', 'subscriptio'),
                'type'      => 'checkbox',
                'label'     => esc_html__('Enable this email notification', 'subscriptio'),
                'default'   => 'yes',
            ),
            'send_to_admin' => array(
                'title'     => esc_html__('Send to admin', 'subscriptio'),
                'type'      => 'checkbox',
                'label'     => esc_html__('Send BCC copy to admin', 'subscriptio'),
                'default'   => 'no',
            ),
            'subject' => array(
                'title'         => esc_html__('Subject', 'subscriptio'),
                'type'          => 'text',
                'desc_tip'      => true,
                'description'   => $placeholder_text,
                'placeholder'   => $this->get_default_subject(),
                'default'       => '',
            ),
            'heading' => array(
                'title'         => esc_html__('Email heading', 'subscriptio'),
                'type'          => 'text',
                'desc_tip'      => true,
                'description'   => $placeholder_text,
                'placeholder'   => $this->get_default_heading(),
                'default'       => '',
            ),
            'additional_content' => array(
                'title'         => esc_html__('Additional content', 'subscriptio'),
                'description'   => esc_html__( 'Text to appear to appear below the main email content.', 'subscriptio' ) . ' ' . $placeholder_text,
                'css'           => 'width: 400px; height: 75px;',
                'placeholder'   => esc_html__('N/A', 'subscriptio'),
                'type'          => 'textarea',
                'default'       => $this->get_default_additional_content(),
                'desc_tip'      => true,
            ),
            'email_type' => array(
                'title'         => esc_html__('Email type', 'subscriptio'),
                'type'          => 'select',
                'description'   => esc_html__('Choose which format of email to send.', 'subscriptio'),
                'default'       => 'html',
                'class'         => 'email_type wc-enhanced-select',
                'options'       => $this->get_email_type_options(),
                'desc_tip'      => true,
            ),
        );
    }

    /**
     * Trigger a notification
     *
     * Note: Child classes are supposed to do some bootstrapping before calling this parent method
     *
     * @access public
     * @param object $object
     * @return void
     */
    public function trigger($object)
    {

        // Check if this email type is enabled, recipient is set and we are not on a development website
        if ($this->is_enabled() && $this->get_recipient() && RP_SUB_Main_Site_Controller::is_main_site()) {

            // Maybe switch locale
            $this->setup_locale();

            // Send email
            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());

            // Restore locale
            $this->restore_locale();
        }
    }

    /**
     * Return content from the additional_content field
     *
     * Displayed above the footer
     *
     * @access public
     * @return string
     */
    public function get_additional_content()
    {

        return apply_filters('subscriptio_email_additional_content_' . $this->id, $this->format_string($this->get_option('additional_content', '')), $this->object);
    }

    /**
     * Get subject
     *
     * @access public
     * @return string
     */
    public function get_subject()
    {

        return apply_filters('subscriptio_email_subject_' . $this->id, $this->format_string($this->get_option('subject', $this->get_default_subject())), $this->object);
    }

    /**
     * Get heading
     *
     * @access public
     * @return string
     */
    public function get_heading()
    {

        return apply_filters('subscriptio_email_heading_' . $this->id, $this->format_string($this->get_option('heading', $this->get_default_heading())), $this->object);
    }

    /**
     * Get recipient
     *
     * @access public
     * @return string
     */
    public function get_recipient()
    {

        $recipient = apply_filters('subscriptio_email_recipient_' . $this->id, $this->recipient, $this->object);
        $recipients = array_map('trim', explode(',', $recipient));
        $recipients = array_filter($recipients, 'is_email');
        return implode(', ', $recipients);
    }

    /**
     * Get email headers
     *
     * @access public
     * @return string
     */
    public function get_headers()
    {

        // Content type
        $header = 'Content-Type: ' . $this->get_content_type() . "\r\n";

        // Set customer's email as reply to email when email is sent to admin
        // Note: This does not work for BCC copies of customer emails sent to admin
        if (!$this->is_customer_email()) {
            $order_object = is_a($this->object, 'RP_SUB_Subscription') ? $this->object->get_suborder() : $this->object;
            $header .= 'Reply-to: ' . $order_object->get_billing_first_name() . ' ' . $order_object->get_billing_last_name() . ' <' . $order_object->get_billing_email() . ">\r\n";
        }

        // Maybe send BCC copy of customer email to admin
        if ($this->is_customer_email() && $this->get_option('send_to_admin') === 'yes') {

            // Add BCC header
            $header .= "Bcc: " . get_option('admin_email') . "\r\n";
        }

        // Allow developers to override
        return apply_filters('subscriptio_email_headers', $header, $this->id, $this->object);
    }

    /**
     * Get email attachments
     *
     * @access public
     * @return array
     */
    public function get_attachments()
    {

        return apply_filters('subscriptio_email_attachments', array(), $this->id, $this->object);
    }

    /**
     * Get HTML email content
     *
     * @access public
     * @return string
     */
    public function get_content_html()
    {

        ob_start();
        RP_SUB_Help::include_template($this->template_html, array_merge($this->template_variables, array('plain_text' => false)));
        return ob_get_clean();
    }

    /**
     * Get plain text email content
     *
     * @access public
     * @return string
     */
    public function get_content_plain()
    {

        ob_start();
        RP_SUB_Help::include_template($this->template_plain, array_merge($this->template_variables, array('plain_text' => true)));
        return ob_get_clean();
    }

    /**
     * Get template variables
     *
     * @access public
     * @return array
     */
    public function get_template_variables()
    {

        return array(
            'email'                 => $this,
            'email_heading'         => $this->get_heading(),
            'additional_content'    => $this->get_additional_content(),
            'sent_to_admin'         => !$this->is_customer_email(),
        );
    }





}
