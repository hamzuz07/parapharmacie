<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription activity log meta box view
 */

?>

<?php if (!empty($log_entries)): ?>

    <ul>

        <?php foreach($log_entries as $log_entry): ?>

            <li rel="<?php echo $log_entry->get_id(); ?>" class="rp-sub-activity-log-entry-<?php echo $log_entry->get_status('edit'); ?>">

                <div class="rp-sub-activity-log-entry-content">

                    <div class="rp-sub-activity-log-entry-heading">
                        <strong><?php echo $log_entry->get_event_type_label(); ?></strong> &ndash; <?php echo $log_entry->get_status_label(); ?>
                    </div>

                    <?php if ($log_entry->get_notes()): ?>
                        <?php echo RightPress_Help::array_to_html_list($log_entry->get_notes(), false, 'rp-sub-activity-log-entry-notes'); ?>
                    <?php endif; ?>

                </div>

                <p class="rp-sub-activity-log-entry-meta">

                    <a href="<?php echo esc_url(add_query_arg('rp_sub_log_entry_id', $log_entry->get_id(), admin_url('edit.php?post_type=rp_sub_log_entry'))); ?>" title="<?php esc_html_e('More details', 'subscriptio'); ?>"><?php echo $log_entry->get_created()->format_datetime(); ?></a>

                    <?php if ($log_entry->get_actor_id()): ?>
                        <?php echo sprintf(' ' . esc_html__('by %s', 'subscriptio'), $log_entry->get_formatted_actor_name()); ?>
                    <?php endif; ?>

                </p>

            </li>

        <?php endforeach; ?>

    </ul>

    <p class="rp-sub-activity-log-view-all">
        <a href="<?php echo esc_url(add_query_arg('subscription_id', $subscription->get_id(), admin_url('edit.php?post_type=rp_sub_log_entry'))); ?>"><?php esc_html_e('View all entries', 'subscriptio'); ?> &rarr;</a>
    </p>

<?php else: ?>

    <p class="rp-sub-meta-box-empty"><?php esc_html_e('There are no log entries yet.', 'subscriptio'); ?></p>

<?php endif; ?>
