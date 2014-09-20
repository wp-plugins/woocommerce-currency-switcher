jQuery(function() {

    jQuery.fn.life = function(types, data, fn) {
        jQuery(this.context).on(types, this.selector, data, fn);
        return this;
    };

    jQuery('body').append('<div id="woocs_buffer" style="display: none;"></div>');


    jQuery('.woocs_del_currency').life('click', function() {
        jQuery(this).parents('li').hide(220, function() {
            jQuery(this).remove();
        });
        return false;
    });
/*
    jQuery('.woocs_is_etalon').life('click', function() {
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
            jQuery.post(ajaxurl, data, function(request) {
                try {                    
                    woocs_hide_stat_info_popup();
                    woocs_show_info_popup('Set currencies rates by hand and Save changes please!', 5000);
                } catch (e) {
                    woocs_hide_stat_info_popup();
                    alert('Request error! Try later or another agregator!');
                }
            });
        }

        return true;
    });
*/
});


function woocs_insert_html_in_buffer(html) {
    jQuery('#woocs_buffer').html(html);
}
function woocs_get_html_from_buffer() {
    return jQuery('#woocs_buffer').html();
}

function woocs_show_info_popup(text, delay) {
    jQuery(".info_popup").text(text);
    window.setTimeout(function() {
        jQuery(".info_popup").fadeOut(400);
    }, delay);
}

function woocs_show_stat_info_popup(text) {
    jQuery(".info_popup").text(text);
    jQuery(".info_popup").fadeTo(400, 0.9);
}


function woocs_hide_stat_info_popup() {
    window.setTimeout(function() {
        jQuery(".info_popup").fadeOut(400);
    }, 500);
}


