<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="subsubsub_section">
    <br class="clear" />

    <?php
    $welcome_curr_options = array();
    if (!empty($currencies) AND is_array($currencies)) {
        foreach ($currencies as $key => $currency) {
            $welcome_curr_options[$currency['name']] = $currency['name'];
        }
    }
    //+++
    $options = array(
        array(
            'name' => __('Currency Switcher Options (free version)', 'woocommerce-currency-switcher') . ' ' . $this->the_plugin_version,
            'type' => 'title',
            'desc' => '',
            'id' => 'woocs_general_settings'
        ),
        array(
            'name' => __('Drop-down view', 'woocommerce-currency-switcher'),
            'desc' => __('How to display currency switcher drop-down on the front of your site', 'woocommerce-currency-switcher'),
            'id' => 'woocs_drop_down_view',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                'ddslick' => __('ddslick', 'woocommerce-currency-switcher'),
                'chosen' => __('chosen', 'woocommerce-currency-switcher'),
                'no' => __('simple drop-down', 'woocommerce-currency-switcher'),
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Show flags by default', 'woocommerce-currency-switcher'),
            'desc' => __('Show/hide flags on the front drop-down', 'woocommerce-currency-switcher'),
            'id' => 'woocs_show_flags',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                0 => __('No', 'woocommerce-currency-switcher'),
                1 => __('Yes', 'woocommerce-currency-switcher')
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Show money signs', 'woocommerce-currency-switcher'),
            'desc' => __('Show/hide money signs on the front drop-down', 'woocommerce-currency-switcher'),
            'id' => 'woocs_show_money_signs',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                0 => __('No', 'woocommerce-currency-switcher'),
                1 => __('Yes', 'woocommerce-currency-switcher')
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Is multiple allowed', 'woocommerce-currency-switcher'),
            'desc' => __('Customer will pay with selected currency or with default currency.', 'woocommerce-currency-switcher'),
            'id' => 'woocs_is_multiple_allowed',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                0 => __('No', 'woocommerce-currency-switcher'),
                1 => __('Yes', 'woocommerce-currency-switcher')
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Welcome currency', 'woocommerce-currency-switcher'),
            'desc' => __('In wich currency show prices for first visit of your customer on your site. GOOD WHEN SITUATION IN YOUR COUNTRY WITH PRICES IS NOT STABLE. In the premium version only!', 'woocommerce-currency-switcher'),
            'id' => 'woocs_welcome_currency',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array('in the premium version only'),
            'desc_tip' => true
        ),
        array(
            'name' => __('Currency aggregator', 'woocommerce-currency-switcher'),
            'desc' => __('Currency aggregators', 'woocommerce-currency-switcher'),
            'id' => 'woocs_currencies_aggregator',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                'yahoo' => __('http://finance.yahoo.com', 'woocommerce-currency-switcher'),
                'google' => __('http://google.com/finance', 'woocommerce-currency-switcher'),
                'appspot' => __('http://rate-exchange.appspot.com', 'woocommerce-currency-switcher'),
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Currencies rate auto update', 'woocommerce-currency-switcher'),
            'desc' => __('Currencies rate auto update by wp cron. Use it for your own risk! In the premium version only!', 'woocommerce-currency-switcher'),
            'id' => 'woocs_currencies_rate_auto_update',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                'no' => __('no auto update', 'woocommerce-currency-switcher'),
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Custom money signs. Premium only.', 'woocommerce-currency-switcher'),
            'desc' => __('Add your money symbols in your shop. Example: $USD,AAA,AUD$,DDD - separated by commas. In the premium version only!', 'woocommerce-currency-switcher'),
            'id' => 'woocs_customer_signs',
            'type' => 'textarea',
            'std' => '', // WooCommerce < 2.0
            'default' => '', // WooCommerce >= 2.0
            'css' => 'min-width:500px;',
            'desc_tip' => true
        ),
        array('type' => 'sectionend', 'id' => 'woocs_general_settings')
    );
    ?>


    <div class="section">
        <?php woocommerce_admin_fields($options); ?>


        <div style="display: none;">
            <div id="woocs_item_tpl"><?php
                $empty = array(
                    'name' => '',
                    'rate' => 0,
                    'symbol' => '',
                    'position' => '',
                    'is_etalon' => 0,
                    'description' => ''
                );
                woocs_print_currency($this, $empty);
                ?>
            </div>
        </div>

        <ul id="woocs_list">
            <?php
            if (!empty($currencies) AND is_array($currencies)) {
                foreach ($currencies as $key => $currency) {
                    woocs_print_currency($this, $currency);
                }
            }
            ?>
        </ul>

    </div>
    <br />

    <a href="http://woocommerce-currencies-switcher.pluginus.net/category/faq/" target="_blank" class="button"><?php _e("FAQ", 'woocommerce-currency-switcher') ?></a>&nbsp;
    <a href="http://woocommerce-currencies-switcher.pluginus.net/i-can-add-flags-what-to-do/" target="_blank" class="button"><?php _e("I cant add flags! What to do?", 'woocommerce-currency-switcher') ?></a><br />


    <br />

    <?php _e('<b>Note:</b> To update all currencies rates by one click - press radio button of default currency and then press Save changes!', 'woocommerce-currency-switcher'); ?><br />


    <div class="info_popup" style="display: none;"></div>

    <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank" class="button-hero"><?php _e("Read wiki about Currency Active codes", 'woocommerce-currency-switcher') ?></a><br />
    <br />
    <a class="button" href="http://woocommerce-currencies-switcher.pluginus.net/documentation/" target="_blank"><?php _e("The plugin Documentation", 'woocommerce-currency-switcher') ?></a><br />

    <hr />

    <i>In the free version of the plugin you can operate only with 2 ANY currencies. If you want more currencies and features you are need make upgrade to the premium version of the plugin</i><br />

    <h3><?php _e("Get the full version of the plugin from Codecanyon", 'woocommerce-currency-switcher') ?>:</h3>
    <a href="http://codecanyon.net/item/woocommerce-currency-switcher/8085217?ref=realmag777" target="_blank"><img src="<?php echo WOOCS_LINK ?>img/woocs_banner.jpg" alt="<?php _e("full version of the plugin", 'woocommerce-currency-switcher'); ?>" /></a>


</div>

<script type="text/javascript">
    jQuery(function () {

        jQuery.fn.life = function (types, data, fn) {
            jQuery(this.context).on(types, this.selector, data, fn);
            return this;
        };

        jQuery('body').append('<div id="woocs_buffer" style="display: none;"></div>');

        jQuery("#woocs_list").sortable();


        jQuery('.woocs_del_currency').life('click', function () {
            jQuery(this).parents('li').hide(220, function () {
                jQuery(this).remove();
            });
            return false;
        });

        jQuery('.woocs_is_etalon').life('click', function () {
            jQuery('.woocs_is_etalon').next('input[type=hidden]').val(0);
            jQuery('.woocs_is_etalon').prop('checked', 0);
            jQuery(this).next('input[type=hidden]').val(1);
            jQuery(this).prop('checked', 1);
            //instant save
            var currency_name = jQuery(this).parent().find('input[name="woocs_name[]"]').val();
            if (currency_name.length) {
                woocs_show_stat_info_popup('Loading ...');
                var data = {
                    action: "woocs_save_etalon",
                    currency_name: currency_name
                };
                jQuery.post(ajaxurl, data, function (request) {
                    try {
                        request = jQuery.parseJSON(request);
                        jQuery.each(request, function (index, value) {
                            var elem = jQuery('input[name="woocs_name[]"]').filter(function () {
                                return this.value.toUpperCase() == index;
                            });

                            if (elem) {
                                jQuery(elem).parent().find('input[name="woocs_rate[]"]').val(value);
                                jQuery(elem).parent().find('input[name="woocs_rate[]"]').text(value);
                            }
                        });

                        woocs_hide_stat_info_popup();
                        woocs_show_info_popup('Save changes please!', 1999);
                    } catch (e) {
                        woocs_hide_stat_info_popup();
                        alert('Request error! Try later or another agregator!');
                    }
                });
            }

            return true;
        });


        jQuery('.woocs_flag_input').life('change', function ()
        {
            jQuery(this).next('a.woocs_flag').find('img').attr('src', jQuery(this).val());
        });

        jQuery('.woocs_flag').life('click', function ()
        {
            var input_object = jQuery(this).prev('input[type=hidden]');
            window.send_to_editor = function (html)
            {
                woocs_insert_html_in_buffer(html);
                var imgurl = jQuery('#woocs_buffer').find('a').eq(0).attr('href');
                woocs_insert_html_in_buffer("");
                jQuery(input_object).val(imgurl);
                jQuery(input_object).trigger('change');
                tb_remove();
            };
            tb_show('', 'media-upload.php?post_id=0&type=image&TB_iframe=true');

            return false;
        });

        jQuery('.woocs_finance_yahoo').life('click', function () {
            var currency_name = jQuery(this).parent().find('input[name="woocs_name[]"]').val();
            var _this = this;
            jQuery(_this).parent().find('input[name="woocs_rate[]"]').val('loading ...');
            var data = {
                action: "woocs_get_rate",
                currency_name: currency_name
            };
            jQuery.post(ajaxurl, data, function (value) {
                jQuery(_this).parent().find('input[name="woocs_rate[]"]').val(value);
            });

            return false;
        });

    });


    function woocs_insert_html_in_buffer(html) {
        jQuery('#woocs_buffer').html(html);
    }
    function woocs_get_html_from_buffer() {
        return jQuery('#woocs_buffer').html();
    }

    function woocs_show_info_popup(text, delay) {
        jQuery(".info_popup").text(text);
        jQuery(".info_popup").fadeTo(400, 0.9);
        window.setTimeout(function () {
            jQuery(".info_popup").fadeOut(400);
        }, delay);
    }

    function woocs_show_stat_info_popup(text) {
        jQuery(".info_popup").text(text);
        jQuery(".info_popup").fadeTo(400, 0.9);
    }


    function woocs_hide_stat_info_popup() {
        window.setTimeout(function () {
            jQuery(".info_popup").fadeOut(400);
        }, 500);
    }



</script>

<?php

function woocs_print_currency($_this, $currency) {
    ?>
    <li>
        <input class="help_tip woocs_is_etalon" data-tip="<?php _e("Set etalon main currency. This should be the currency in which the price of goods exhibited!", 'woocommerce-currency-switcher') ?>" type="radio" <?php checked(1, $currency['is_etalon']) ?> />
        <input type="hidden" name="woocs_is_etalon[]" value="<?php echo $currency['is_etalon'] ?>" />
        <input type="text" value="<?php echo $currency['name'] ?>" name="woocs_name[]" class="woocs-text" placeholder="<?php _e("NAME. Example: USD", 'woocommerce-currency-switcher') ?>" />
        <select class="woocs-drop-down" name="woocs_symbol[]">
            <?php foreach ($_this->currency_symbols as $symbol) : ?>
                <option value="<?php echo md5($symbol) ?>" <?php selected(md5($currency['symbol']), md5($symbol)) ?>><?php echo $symbol; ?></option>
            <?php endforeach; ?>
        </select>
        <select class="woocs-drop-down" name="woocs_position[]">
            <option value="0"><?php _e("Select symbol position", 'woocommerce-currency-switcher'); ?></option>
            <?php foreach ($_this->currency_positions as $position) : ?>
                <option value="<?php echo $position ?>" <?php selected($currency['position'], $position) ?>><?php echo str_replace('_', ' ', $position); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" value="<?php echo $currency['rate'] ?>" name="woocs_rate[]" class="woocs-text" placeholder="<?php _e("exchange rate", 'woocommerce-currency-switcher') ?>" />
        <button class="button woocs_finance_yahoo help_tip" data-tip="<?php _e("Press this button if you want get currency rate from the selected aggregator above!", 'woocommerce-currency-switcher') ?>"><?php _e("finance", 'woocommerce-currency-switcher'); ?>.<?php echo get_option('woocs_currencies_aggregator') ?></button>
        <input type="text" value="<?php echo $currency['description'] ?>" name="woocs_description[]" class="woocs-text" placeholder="<?php _e("description", 'woocommerce-currency-switcher') ?>" />
        <?php
        $flag = WOOCS_LINK . 'img/no_flag.png';
        if (isset($currency['flag']) AND ! empty($currency['flag'])) {
            $flag = $currency['flag'];
        }
        ?>
        <input type="hidden" value="<?php echo $flag ?>" class="woocs_flag_input" name="woocs_flag[]" />
        <a href="#" class="woocs_flag help_tip" data-tip="<?php _e("Click to select the flag", 'woocommerce-currency-switcher'); ?>"><img src="<?php echo $flag ?>" style="vertical-align: middle; width: 37px;" alt="<?php _e("Flag", 'woocommerce-currency-switcher'); ?>" /></a>
        &nbsp;<a href="#" class="help_tip" data-tip="<?php _e("drag and drope", 'woocommerce-currency-switcher'); ?>"><img style="width: 22px; vertical-align: middle;" src="<?php echo WOOCS_LINK ?>img/move.png" alt="<?php _e("move", 'woocommerce-currency-switcher'); ?>" /></a>
    </li>
    <?php
}
