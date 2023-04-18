<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * View for site URL mismatch notification
 * Displayed on development/staging websites or when user changes main website URL
 */

?>

<div id="message" class="error subscriptio_url_mismatch">

    <h2><?php esc_html_e('Subscriptio URL mismatch', 'subscriptio'); ?></h2>

    <p><?php esc_html_e('Your website URL has changed. Automatic payments and customer emails are disabled to prevent live transactions originating from development or staging servers.', 'subscriptio'); ?></p>

    <p><?php esc_html_e('If you moved this website permanently and would like to re-enable these features, click on the link below to make current URL primary.', 'subscriptio'); ?></p>

    <p style="padding-bottom: 10px;">
        <a href="<?php echo add_query_arg('subscriptio_url_mismatch_action', 'change') ?>">Make current URL primary</a>
        &nbsp;&nbsp;
        <a href="<?php echo add_query_arg('subscriptio_url_mismatch_action', 'ignore') ?>">Do not remind me again</a>
    </p>

</div>
