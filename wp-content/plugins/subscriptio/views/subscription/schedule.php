<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subscription scheduled actions meta box view
 *
 * @var $subscription RP_SUB_Subscription
 */

?>

<div class="rp-sub-subscription-billing-cycle">

    <label for="rp-sub-subscription-settings-billing-cycle-length"><?php esc_html_e('Billing cycle', 'subscriptio'); ?></label>

    <?php RightPress_Forms::number(array(
        'id'            => 'rp-sub-subscription-settings-billing-cycle-length',
        'name'          => 'rp_sub_subscription_settings_billing_cycle_length',
        'class'         => 'input-text rp-sub-subscription-time-length',
        'placeholder'   => esc_html__('e.g. 14', 'subscriptio'),
        'required'      => 'required',
        'min'           => '1',
        'step'          => '1',
        'value'         => $billing_cycle_length,
    )); ?>

    <?php RightPress_Forms::select(array(
        'id'        => 'rp-sub-subscription-settings-billing-cycle-period',
        'name'      => 'rp_sub_subscription_settings_billing_cycle_period',
        'class'     => 'select rp-sub-subscription-time-period',
        'options'   => $time_periods,
        'value'     => $billing_cycle_period,
    )); ?>

    <div style="clear: both;"></div>

</div>

<div class="rp-sub-subscription-lifespan">

    <label for="rp-sub-subscription-settings-lifespan-length"><?php esc_html_e('Lifespan', 'subscriptio'); ?></label>

    <?php RightPress_Forms::number(array(
        'id'            => 'rp-sub-subscription-settings-lifespan-length',
        'name'          => 'rp_sub_subscription_settings_lifespan_length',
        'class'         => 'input-text rp-sub-subscription-time-length',
        'placeholder'   => esc_html__('Infinite', 'subscriptio'),
        'min'           => '1',
        'step'          => '1',
        'value'         => $lifespan_length,
    )); ?>

    <?php RightPress_Forms::select(array(
        'id'        => 'rp-sub-subscription-settings-lifespan-period',
        'name'      => 'rp_sub_subscription_settings_lifespan_period',
        'class'     => 'select rp-sub-subscription-time-period',
        'options'   => $time_periods,
        'value'     => $lifespan_period,
    )); ?>

    <div style="clear: both;"></div>

</div>

<?php if (!empty($scheduled_actions)): ?>

    <ul class="rp-sub-schedule-list">

        <?php foreach($scheduled_actions as $scheduled_action => $scheduled_action_datetime): ?>

            <li rel="<?php echo $scheduled_action; ?>" class="rp-sub-schedule-list-item rp-sub-schedule-list-item-<?php echo str_replace('_', '-', $scheduled_action); ?>">

                <div class="rp-sub-schedule-list-item-content">

                    <div class="rp-sub-schedule-list-item-heading">
                        <strong><?php echo $all_actions[$scheduled_action]['label']; ?></strong>
                    </div>

                    <?php echo $scheduled_action_datetime->format_datetime(); ?>

                </div>

            </li>

        <?php endforeach; ?>

    </ul>

<?php else: ?>

    <p class="rp-sub-meta-box-empty"><?php esc_html_e('There are no actions scheduled.', 'subscriptio'); ?></p>

<?php endif; ?>
