<?php

/**
 * View for site URL mismatch notification
 * Displayed on development/staging websites or when user changes main website URL
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div id="message" class="error subscriptio_url_mismatch">
    <p>
        <strong><?php esc_html_e('Subscriptio URL mismatch', 'subscriptio'); ?></strong>
    </p>
    <p>
        <?php esc_html_e('Your website URL has recently been changed. Automatic payments and customer emails have been disabled to prevent live transactions originating from development or staging servers.', 'subscriptio'); ?><br />
        <?php esc_html_e('If you have moved this website permanently and would like to re-enable these features, select appropriate action below.', 'subscriptio'); ?>
    </p>
    <form action="" method="post">
        <button class="button-primary" name="subscriptio_url_mismatch_action" value="ignore"><?php esc_html_e('Hide this warning', 'subscriptio'); ?></button>
        <button class="button" name="subscriptio_url_mismatch_action" value="change"><?php esc_html_e('Make current URL primary', 'subscriptio'); ?></button>
    </form>
</div>
