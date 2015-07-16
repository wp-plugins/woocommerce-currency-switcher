var wocs_loading_first_time = true;//simply flag var
jQuery(function () {

    jQuery.fn.life = function (types, data, fn) {
        jQuery(this.context).on(types, this.selector, data, fn);
        return this;
    };

    //keeps data of $_GET array
    woocs_array_of_get = jQuery.parseJSON(woocs_array_of_get);
    if (woocs_array_of_get.length == 0) {
        woocs_array_of_get = {};
    }

    //+++

    if (woocs_drop_down_view == 'chosen') {
        try {
            if (jQuery("select.woocommerce-currency-switcher").length) {
                jQuery("select.woocommerce-currency-switcher").chosen({
                    disable_search_threshold: 10
                });

                jQuery.each(jQuery('.woocommerce-currency-switcher-form .chosen-container'), function (index, obj) {
                    jQuery(obj).css({'width': jQuery(this).prev('select').data('width')});
                });
            }
        } catch (e) {
            console.log(e);
        }
    }

    if (woocs_drop_down_view == 'ddslick') {
        try {
            jQuery.each(jQuery('select.woocommerce-currency-switcher'), function (index, obj) {
                var width = jQuery(obj).data('width');
                var flag_position = jQuery(obj).data('flag-position');
                jQuery(obj).ddslick({
                    //data: ddData,
                    width: width,
                    imagePosition: flag_position,
                    selectText: "Select currency",
                    //background:'#ff0000',
                    onSelected: function (data) {
                        if (!wocs_loading_first_time) {
                            var form = jQuery(data.selectedItem).closest('form.woocommerce-currency-switcher-form');
                            jQuery(form).find('input[name="woocommerce-currency-switcher"]').eq(0).val(data.selectedData.value);

                            if (woocs_array_of_get.length == 0) {
                                jQuery(form).submit();
                            } else {

                                woocs_redirect(data.selectedData.value);

                            }


                        }
                    }
                });
            });

        } catch (e) {
            console.log(e);
        }
    }

    //for flags view instead of drop-down
    jQuery('.woocs_flag_view_item').click(function () {
        if (jQuery(this).hasClass('woocs_flag_view_item_current')) {
            return false;
        }
        //***

        if (woocs_array_of_get.length == 0) {
            window.location = window.location.href + '?currency=' + jQuery(this).data('currency');
        } else {

            woocs_redirect(jQuery(this).data('currency'));

        }

        return false;
    });

    //for converter
    if (jQuery('.woocs_converter_shortcode').length) {
        jQuery('.woocs_converter_shortcode_button').click(function () {
            var amount = jQuery(this).parent('.woocs_converter_shortcode').find('.woocs_converter_shortcode_amount').eq(0).val();
            var from = jQuery(this).parent('.woocs_converter_shortcode').find('.woocs_converter_shortcode_from').eq(0).val();
            var to = jQuery(this).parent('.woocs_converter_shortcode').find('.woocs_converter_shortcode_to').eq(0).val();
            var precision = jQuery(this).parent('.woocs_converter_shortcode').find('.woocs_converter_shortcode_precision').eq(0).val();
            var results_obj = jQuery(this).parent('.woocs_converter_shortcode').find('.woocs_converter_shortcode_results').eq(0);
            jQuery(results_obj).val(woocs_lang_loading + ' ...');
            var data = {
                action: "woocs_convert_currency",
                amount: amount,
                from: from,
                to: to,
                precision: precision
            };

            jQuery.post(woocs_ajaxurl, data, function (value) {
                jQuery(results_obj).val(value);
            });

            return false;

        });
    }

    //for rates
    if (jQuery('.woocs_rates_shortcode').length) {
        jQuery('.woocs_rates_current_currency').life('change', function () {
            var _this = this;
            var data = {
                action: "woocs_rates_current_currency",
                current_currency: jQuery(this).val(),
                precision: jQuery(this).data('precision'),
                exclude: jQuery(this).data('exclude')
            };

            jQuery.post(woocs_ajaxurl, data, function (html) {
                jQuery(_this).parent('.woocs_rates_shortcode').html(html);
            });

            return false;

        });
    }

    wocs_loading_first_time = false;
});


function woocs_redirect(currency) {
    var l = window.location.href;
    l = l.split('?');
    l = l[0];
    var string_of_get = '?';
    woocs_array_of_get.currency = currency;
    /*
     l = l.replace(/(\?currency=[a-zA-Z]+)/g, '?');
     l = l.replace(/(&currency=[a-zA-Z]+)/g, '');
     */

    if (Object.keys(woocs_array_of_get).length > 0) {
        jQuery.each(woocs_array_of_get, function (index, value) {
            string_of_get = string_of_get + "&" + index + "=" + value;
        });
    }
    window.location = l + string_of_get;
}


function woocs_submit(_this) {
    if (woocs_array_of_get.length == 0) {
        jQuery(_this).closest('form').submit();
    } else {
        woocs_redirect(jQuery(_this).val());
    }

    return true;
}
