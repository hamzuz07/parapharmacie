<?php

/**
 * Subscriptio Stripe Credit Card Form
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<?php if (!empty($description) || $is_debug): ?>
    <div class="subscriptio_stripe_description">
       <?php echo $is_debug ? '<strong style="color: red;">' . esc_html__('TEST MODE', 'subscriptio-stripe') . '</strong><br>' : ''; ?>
       <?php echo $is_debug ? sprintf(esc_html__('Click %s for a list of test card numbers.', 'subscriptio-stripe'), ('<a href="http://url.rightpress.net/stripe-testing">' . esc_html__('here', 'subscriptio-stripe') . '</a>')) . '<br><br>' : ''; ?>
       <?php echo !empty($description) ? $description : ''; ?>
    </div>
<?php endif; ?>

<?php if (empty($cards)): ?>
    <input type="hidden" id="subscriptio_stripe_card_id" name="subscriptio_stripe_card_id" value="none" />
<?php else: ?>
    <div class="subscriptio_stripe_existing_cards">
        <?php foreach ($cards as $card_id => $card): ?>
            <input type="radio" id="subscriptio_stripe_card_id" name="subscriptio_stripe_card_id" value="<?php echo $card_id; ?>" <?php echo $card_id == $default_card ? 'checked="checked"' : ''; ?>/>
            <label for="subscriptio_stripe_card_id" style="display:inline;"><?php echo $card; ?></label><br>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (isset($checkout_amount)): ?>
    <input class="subscriptio_stripe_card_amount" id="subscriptio_stripe_card_amount" type="hidden" value="<?php echo esc_attr($checkout_amount); ?>">
<?php endif; ?>

<?php if ($is_inline): ?>
    <fieldset id="<?php echo $id; ?>-cc-form" class="subscriptio_stripe_credit_card_form" <?php echo ($default_card == 'none' ? '' : 'style="display:none;"'); ?>>

        <?php do_action('subscriptio_stripe_before_credit_card_form'); ?>

        <p class="form-row form-row-wide">
            <label for="<?php echo esc_attr($id); ?>-card-number"><?php esc_html_e('Card Number', 'subscriptio-stripe'); ?> <span class="required">*</span></label>
            <input type="text" id="<?php echo esc_attr($id); ?>-card-number" class="input-text wc-credit-card-form-card-number" name="" placeholder="<?php esc_attr_e('•••• •••• •••• ••••', 'subscriptio-stripe'); ?>" autocomplete="off" maxlength="20" />
        </p>

        <p class="form-row form-row-first">
            <label for="<?php echo esc_attr($id); ?>-card-expiry-month"><?php esc_html_e('Expires', 'subscriptio-stripe'); ?><span class="required">*</span></label>
            <select id="<?php echo esc_attr($id); ?>-card-expiry-month" class="subscriptio_stripe_field_month">
                <option value=""><?php esc_html_e('Month', 'subscriptio-stripe'); ?></option>
                <?php foreach (Subscriptio_Stripe::get_months() as $month_key => $month): ?>
                    <option value="<?php echo $month_key; ?>"><?php echo $month; ?></option>
                <?php endforeach; ?>
            </select>
            <select id="<?php echo esc_attr($id); ?>-card-expiry-year" class="subscriptio_stripe_field_year">
                <option value=""><?php esc_html_e('Year', 'subscriptio-stripe'); ?></option>
                <?php foreach (Subscriptio_Stripe::get_years() as $year_key => $year): ?>
                    <option value="<?php echo $year_key; ?>"><?php echo $year; ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p class="form-row form-row-last">
            <label for="<?php echo esc_attr($id); ?>-card-cvc"><?php esc_html_e('Card Code', 'subscriptio-stripe'); ?> <span class="required">*</span></label>
            <input type="text" id="<?php echo esc_attr($id); ?>-card-cvc" class="input-text wc-credit-card-form-card-cvc" name="" placeholder="<?php esc_attr_e('CVC', 'subscriptio-stripe'); ?>" autocomplete="off" />
        </p>

        <div class="clear"></div>

        <?php do_action('subscriptio_stripe_after_credit_card_form'); ?>
    </fieldset>
<?php endif; ?>
