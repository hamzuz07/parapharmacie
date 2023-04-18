<?php

/**
 * View for Subscription Edit page Subscription Actions block
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="subscription_actions">
    <select name="subscriptio_subscription_actions">

        <option value=""><?php esc_html_e('Actions', 'subscriptio'); ?></option>

        <?php if ($subscription->can_be_paused()): ?>
            <option value="pause"><?php esc_html_e('Pause Subscription', 'subscriptio'); ?></option>
        <?php endif; ?>

        <?php if ($subscription->can_be_resumed()): ?>
            <option value="resume"><?php esc_html_e('Resume Subscription', 'subscriptio'); ?></option>
        <?php endif; ?>

        <?php if ($subscription->can_be_cancelled()): ?>
            <option value="cancel"><?php esc_html_e('Cancel Subscription', 'subscriptio'); ?></option>
        <?php endif; ?>

        <?php if (in_array($subscription->status, array('cancelled', 'expired', 'failed'))): ?>
            <option value="" disabled="disabled"><?php esc_html_e('- No actions available -', 'subscriptio'); ?></option>
        <?php endif; ?>

    </select>
</div>
<div class="subscription_actions_footer submitbox">
    <div class="subscriptio_subscription_delete">
        <?php if (current_user_can('delete_post', $subscription->id)): ?>
            <a class="submitdelete deletion" href="<?php echo esc_url(get_delete_post_link($subscription->id)); ?>"><?php echo (!EMPTY_TRASH_DAYS ? esc_html__('Delete Permanently', 'subscriptio') : esc_html__('Move to Trash', 'subscriptio')); ?></a>
        <?php endif; ?>
    </div>

    <button type="submit" class="button button-primary" title="<?php esc_attr_e('Process', 'subscriptio'); ?>" name="subscriptio_subscription_button" value="actions"><?php esc_html_e('Process', 'subscriptio'); ?></button>
</div>
<div style="clear: both;"></div>
