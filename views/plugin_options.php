<?php if(!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="subsubsub_section">
    <br class="clear" />

    <?php
    $welcome_curr_options = array();
    if(!empty($currencies) AND is_array($currencies)) {
        foreach($currencies as $key=> $currency) {
            $welcome_curr_options[$currency['name']] = $currency['name'];
        }
    }
    //+++
    $options = array(
        array(
            'name'=>__('Currency Switcher Options', 'woocommerce-currency-switcher') . ' ' . $this->the_plugin_version,
            'type'=>'title',
            'desc'=>'',
            'id'=>'woocs_general_settings'
        ),        
        
        array(
            'name'=>__('Is multiple allowed', 'woocommerce-currency-switcher'),
            'desc'=>__('Customer will pay with selected currency or with default currency. Not compatible for 100% with all wp themes and plugin combinations!!', 'woocommerce-currency-switcher'),
            'id'=>'woocs_is_multiple_allowed',
            'type'=>'select',
            'class'=>'chosen_select',
            'css'=>'min-width:300px;',
            'options'=>array(
                0=>__('No', 'woocommerce-currency-switcher'),
                1=>__('Yes', 'woocommerce-currency-switcher')
            ),
            'desc_tip'=>true
        ),       
             
        array('type'=>'sectionend', 'id'=>'woocs_general_settings')
    );
    ?>


    <div class="section">
        <?php woocommerce_admin_fields($options); ?>

        <ul id="woocs_list">
            <?php
            if(!empty($currencies) AND is_array($currencies)) {
                foreach($currencies as $key=> $currency) {
                    woocs_print_currency($this, $currency);
                }
            }
            ?>
        </ul>

    </div>

    <br />


    <div class="info_popup" style="display: none;"></div>

    <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank" class="button-hero"><?php _e("Read wiki about Currency Active codes", 'woocommerce-currency-switcher') ?></a><br />
    <br />
    <a class="button" href="http://woocommerce-currencies-switcher.pluginus.net/documentation/" target="_blank"><?php _e("The plugin Documentation (commercial ver.)", 'woocommerce-currency-switcher') ?></a><br />
    <br />
    <h3><?php _e("Get full version of the plugin from Codecanyon", 'woocommerce-currency-switcher') ?>:</h3>
    <a href="http://codecanyon.net/item/woocommerce-currency-switcher/8085217?ref=realmag777" target="_blank"><img src="<?php echo WOOCS_LINK ?>img/woocs_banner.jpg" alt="<?php _e("full version of the plugin", 'woocommerce-currency-switcher'); ?>" /></a>
</div>

<?php

function woocs_print_currency( $_this, $currency ) {
    ?>
    <li>
        <input disabled="" class="help_tip woocs_is_etalon" data-tip="<?php _e("Set etalon main currency. This should be the currency in which the price of goods exhibited!", 'woocommerce-currency-switcher') ?>" type="radio" <?php checked(1, $currency['is_etalon']) ?> />
        <input type="hidden" name="woocs_is_etalon[]" value="<?php echo $currency['is_etalon'] ?>" />
        <input type="text" value="<?php echo $currency['name'] ?>" name="woocs_name[]" class="woocs-text" placeholder="<?php _e("NAME. Example: USD", 'woocommerce-currency-switcher') ?>" />
        <select class="woocs-drop-down" name="woocs_symbol[]">
            <?php foreach($_this->currency_symbols as $symbol) : ?>
                <option value="<?php echo md5($symbol) ?>" <?php selected(md5($currency['symbol']), md5($symbol)) ?>><?php echo $symbol; ?></option>
            <?php endforeach; ?>
        </select>
        <select class="woocs-drop-down" name="woocs_position[]">
            <option value="0"><?php _e("Select symbol position", 'woocommerce-currency-switcher'); ?></option>
            <?php foreach($_this->currency_positions as $position) : ?>
                <option value="<?php echo $position ?>" <?php selected($currency['position'], $position) ?>><?php echo str_replace('_', ' ', $position); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" value="<?php echo $currency['rate'] ?>" name="woocs_rate[]" class="woocs-text" placeholder="<?php _e("exchange rate", 'woocommerce-currency-switcher') ?>" />
        <input type="text" value="<?php echo $currency['description'] ?>" name="woocs_description[]" class="woocs-text" placeholder="<?php _e("description", 'woocommerce-currency-switcher') ?>" />
       
    </li>
    <?php
}
