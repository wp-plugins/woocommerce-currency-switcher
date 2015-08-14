<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php
$currencies = $this->get_currencies();
$rate = get_post_meta($post->ID, '_woocs_order_rate', TRUE);
$currency = get_post_meta($post->ID, '_order_currency', TRUE);
$base_currency = get_post_meta($post->ID, '_woocs_order_base_currency', TRUE);
$changed_mannualy = get_post_meta($post->ID, '_woocs_order_currency_changed_mannualy', TRUE);
if (empty($base_currency)) {
    $base_currency = $this->default_currency;
}
?>

<div id="woocs_order_metabox">
    <strong><?php _e('Order currency', 'woocommerce-currency-switcher') ?></strong>: 
    <span class="woocs_order_currency">
        <i><?php echo $currency ?></i>
        <select name="woocs_order_currency2" style="width: 80%; display: none;">
            <?php foreach ($currencies as $key => $curr) : ?>
                <option value="<?php echo $key ?>"><?php echo $curr['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </span>&nbsp;<span class="tips" data-tip="<?php _e('Currency in which the customer paid.', 'woocommerce-currency-switcher') ?><?php if ($changed_mannualy > 0): ?> <?php printf(__('THIS order currency is changed manually %s!', 'woocommerce-currency-switcher'), date('d-m-Y', $changed_mannualy)) ?><?php endif; ?>">[?]</span><br />
    <strong><?php _e('Base currency', 'woocommerce-currency-switcher') ?></strong>: <?php echo $base_currency ?><br />
    <strong><?php _e('Order currency rate', 'woocommerce-currency-switcher') ?></strong>: <?php echo $rate ?>&nbsp;<span class="tips" data-tip="<?php _e('Currency rate when the customer paid ', 'woocommerce-currency-switcher') ?>">[?]</span><br />
    <strong><?php _e('Total amount', 'woocommerce-currency-switcher') ?></strong>: 
    <?php
    $_REQUEST['no_woocs_order_amount_total'] = 1;
    echo trim(number_format($order->get_total(), 2) . ' ' . $currency);
    ?><br />
    <hr />
    <strong><?php _e('For new manual order ONLY!!', 'woocommerce-currency-switcher') ?></strong>:<br />
    <a href="javascript:woocs_change_order_data();void(0);" class="button woocs_change_order_curr_button"><?php _e('change order currency', 'woocommerce-currency-switcher') ?></a>
    <a href="javascript:woocs_cancel_order_data();void(0);" style="display: none;" class="button woocs_cancel_order_curr_button"><?php _e('cancel', 'woocommerce-currency-switcher') ?></a>
</div>

<script type="text/javascript">
    var woocs_old_currency = null;
    function woocs_change_order_data() {
        woocs_old_currency = jQuery('#woocs_order_metabox .woocs_order_currency i').html();
        jQuery('#woocs_order_metabox .woocs_order_currency select').show();
        jQuery('#woocs_order_metabox .woocs_order_currency select').attr('name', 'woocs_order_currency');
        jQuery('#woocs_order_metabox .woocs_order_currency select').val(woocs_old_currency);
        jQuery('.woocs_change_order_curr_button').hide();
        jQuery('.woocs_cancel_order_curr_button').show();
    }

    function woocs_cancel_order_data() {
        jQuery('#woocs_order_metabox .woocs_order_currency select').hide();
        jQuery('#woocs_order_metabox .woocs_order_currency select').attr('name', 'woocs_order_currency2');
        jQuery('.woocs_change_order_curr_button').show();
        jQuery('.woocs_cancel_order_curr_button').hide();
    }
</script>

