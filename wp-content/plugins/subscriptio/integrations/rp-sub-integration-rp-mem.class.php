<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * RP_SUB integration with RP_MEM
 *
 * @class RP_SUB_Integration_RP_MEM
 * @package Subscriptio
 * @author RightPress
 */
class RP_SUB_Integration_RP_MEM
{

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // RP_MEM version 3.0+ present
        if (defined('RP_MEM_VERSION')) {

            // TODO
        }
        // Old WooCommerce Membership present, print notice
        else if (defined('RPWCM_VERSION')) {
            add_action('admin_notices', array($this, 'print_incompatible_version_notice'));
        }
    }

    /**
     * Old WooCommerce Membership active on site, print notice
     *
     * @access public
     * @return void
     */
    public function print_incompatible_version_notice()
    {

        // Notice dismissed earlier
        if (get_option('rp_sub_rp_mem_incompatible_version_notice_dismissed', false)) {
            return;
        }

        // Notice dismissed now
        if (!empty($_REQUEST['rp_sub_rp_mem_incompatible_version_notice_dismissed'])) {
            update_option('rp_sub_rp_mem_incompatible_version_notice_dismissed', '1', false);
            return;
        }

        // Notice Format notice
        $notice = '<p>';
        $notice .= esc_html__('Subscriptio version 3.0 and up is only compatible with WooCommerce Membership version 3.0 and up.', 'subscriptio');
        $notice .= '</p><p>';
        $notice .= esc_html__('Plugins will work but they will not communicate with each other, e.g. membership will not be cancelled when subscription is cancelled.', 'subscriptio');
        $notice .= '</p><p>';
        $notice .= sprintf(esc_html__('Please read our %s for more information.', 'subscriptio'), ('<a href="http://url.rightpress.net/subscriptio-3-0-rp-mem-integration">' . esc_html__('upgrade guide', 'subscriptio') . '</a>'));
        $notice .= '</p><p><small>';
        $notice .= '<a href="' . add_query_arg(array('rp_sub_rp_mem_incompatible_version_notice_dismissed' => '1')) . '">' . esc_html__('Hide this notice', 'subscriptio') . '</a>';
        $notice .= '</small></p>';

        // Print notice
        echo '<div id="rp_sub_rp_mem_incompatible_version_notice" class="error" style="padding-bottom: 7px;"><h3>' . esc_html__('Warning!', 'subscriptio') . '</h3>' . $notice . '</div>';
    }





}

RP_SUB_Integration_RP_MEM::get_instance();
