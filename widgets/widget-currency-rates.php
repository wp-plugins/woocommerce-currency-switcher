<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php

class WOOCS_RATES extends WP_Widget {

    function __construct() {
        $settings = array('classname' => __CLASS__, 'description' => __('WooCommerce Currency Rates by realmag777', 'woocommerce-currency-switcher'));
        $this->WP_Widget(__CLASS__, __('WooCommerce Currency Rates', 'woocommerce-currency-switcher'), $settings);
    }

    function widget($args, $instance) {
        $args['instance'] = $instance;
        wp_enqueue_script('jquery');
        global $WOOCS;
        echo $WOOCS->render_html(WOOCS_PATH . 'views/widgets/rates.php', $args);
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['exclude'] = $new_instance['exclude'];
        $instance['precision'] = $new_instance['precision'];
        return $instance;
    }

    function form($instance) {
        $defaults = array(
            'title' => __('WooCommerce Currency Rates', 'woocommerce-currency-switcher'),
            'exclude' => '',
            'precision' => 4
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $args = array();
        $args['instance'] = $instance;
        $args['widget'] = $this;
        global $WOOCS;
        echo $WOOCS->render_html(WOOCS_PATH . 'views/widgets/rates_form.php', $args);
    }

}
