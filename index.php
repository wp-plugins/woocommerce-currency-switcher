<?php
/*
  Plugin Name: WooCommerce Currency Switcher
  Plugin URI: http://woocommerce-currencies-switcher.pluginus.net/
  Description: Currency Switcher for WooCommerce Free version
  Author: realmag777
  Version: 1.0.1
  Author URI: http://www.pluginus.net/
 */
define('WOOCS_PATH', plugin_dir_path(__FILE__));
define('WOOCS_LINK', plugin_dir_url(__FILE__));
define('WOOCS_PLUGIN_NAME', plugin_basename(__FILE__));

//11-02-2015
final class WOOCS {

    //http://docs.woothemes.com/wc-apidocs/class-WC_Order.html
    public $the_plugin_version = '1.0.1';
    public $settings = array();
    public $default_currency = 'USD';
    public $current_currency = 'USD';
    public $currency_positions = array();
    public $currency_symbols = array();
    public $is_multiple_allowed = false; //from options
    public $decimal_sep = '.';
    public $thousands_sep = ',';
    public $rate_auto_update = ''; //from options    

    public function __construct() {
        if (!session_id()) {
            session_start();
        }
        if (!isset($_SESSION['woocs_first_unique_visit'])) {
            $_SESSION['woocs_first_unique_visit'] = 1;
        }
        $this->is_multiple_allowed = get_option('woocs_is_multiple_allowed');
        $this->rate_auto_update = 'no';
        $this->decimal_sep = wp_specialchars_decode(stripslashes(get_option('woocommerce_price_decimal_sep')), ENT_QUOTES);
        $this->thousands_sep = wp_specialchars_decode(stripslashes(get_option('woocommerce_price_thousand_sep')), ENT_QUOTES);
        //+++
        $this->currency_positions = array('left', 'right', 'left_space', 'right_space');
        $this->init_currency_symbols();

        //+++
        $is_first_activation = (int) get_option('woocs_first_activation');
        if (!$is_first_activation) {
            update_option('woocs_first_activation', 1);
            update_option('woocs_drop_down_view', 'ddslick');
            update_option('woocs_currencies_aggregator', 'yahoo');
            update_option('woocs_welcome_currency', $this->default_currency);
            update_option('woocs_is_multiple_allowed', 0);
            update_option('woocs_show_flags', 1);
            update_option('woocs_show_money_signs', 1);
            update_option('woocs_customer_signs', '');
            update_option('woocs_currencies_rate_auto_update', 'no');
        }
        //+++
        $currencies = $this->get_currencies();
        if (!empty($currencies) AND is_array($currencies)) {
            foreach ($currencies as $key => $currency) {
                if ($currency['is_etalon']) {
                    $this->default_currency = $key;
                    break;
                }
            }
        }

        //simple checkout itercept
        if (isset($_REQUEST['action']) AND $_REQUEST['action'] == 'woocommerce_checkout') {
            $_SESSION['woocs_first_unique_visit'] = 0;
            $_REQUEST['woocommerce-currency-switcher'] = $_SESSION['woocs_current_currency'];
            $this->current_currency = $_SESSION['woocs_current_currency'];
            $_REQUEST['woocs_in_order_currency'] = $_SESSION['woocs_current_currency'];
        }

        //paypal query itercept
        if (isset($_REQUEST['mc_currency']) AND ! empty($_REQUEST['mc_currency'])) {
            if (array_key_exists($_REQUEST['mc_currency'], $currencies)) {
                $_SESSION['woocs_first_unique_visit'] = 0;
                $_REQUEST['woocommerce-currency-switcher'] = $_REQUEST['mc_currency'];
            }
        }


        //WELCOME USER CURRENCY ACTIVATION
        if ($_SESSION['woocs_first_unique_visit'] == 1) {
            $_SESSION['woocs_first_unique_visit'] = 0;
            //$_SESSION['woocs_current_currency'] = $this->get_welcome_currency();
        }

        //+++
        if (isset($_REQUEST['woocommerce-currency-switcher'])) {
            if (array_key_exists($_REQUEST['woocommerce-currency-switcher'], $currencies)) {
                $_SESSION['woocs_current_currency'] = $_REQUEST['woocommerce-currency-switcher'];
            } else {
                $_SESSION['woocs_current_currency'] = $this->default_currency;
            }
        }
        //+++
        //*** check currency in browser address
        if (isset($_GET['currency']) AND ! empty($_GET['currency'])) {
            if (array_key_exists(strtoupper($_GET['currency']), $currencies)) {
                $_SESSION['woocs_current_currency'] = strtoupper($_GET['currency']);
            }
        }
        //+++
        if (isset($_SESSION['woocs_current_currency'])) {
            $this->current_currency = $_SESSION['woocs_current_currency'];
        } else {
            $this->current_currency = $this->default_currency;
        }
        $_SESSION['woocs_default_currency'] = $this->default_currency;
        //+++
        //IF we want to be paid in the basic currency
        if (isset($_REQUEST['action']) AND ! $this->is_multiple_allowed) {
            if ($_REQUEST['action'] == 'woocommerce_update_order_review') {
                $_SESSION['woocs_current_currency'] = $this->default_currency;
                $this->current_currency = $this->default_currency;
            }
        }


        //+++
        //add_action('woocommerce_init', array($this, 'woocommerce_init'), 999);
        // SHEDULER
       
        //+++
    }

    private function init_currency_symbols() {
        //includes/wc-core-functions.php #217
        $this->currency_symbols = array(
            '&#36;', '&euro;', '&yen;', '&#1088;&#1091;&#1073;.', '&#1075;&#1088;&#1085;.', '&#8361;',
            '&#84;&#76;', 'د.إ', '&#2547;', '&#82;&#36;', '&#1083;&#1074;.',
            '&#107;&#114;', '&#82;', '&#75;&#269;', '&#82;&#77;', 'kr.', '&#70;&#116;',
            'Rp', 'Rs.', 'Kr.', '&#8362;', '&#8369;', '&#122;&#322;', '&#107;&#114;',
            '&#67;&#72;&#70;', '&#78;&#84;&#36;', '&#3647;', '&pound;', 'lei', '&#8363;',
            '&#8358;', 'Kn', '-----'
        );

        $this->currency_symbols = array_merge($this->currency_symbols, $this->get_customer_signs());
    }

    //for auto rate update sheduler
    public function rate_auto_update() {
        
    }

    public function woocommerce_init() {
        add_action('wp_ajax_woocommerce_update_shipping_method', array($this, 'woocommerce_order_amount_total_shipping'), 999);
        add_action('wp_ajax_nopriv_woocommerce_update_shipping_method', array($this, 'woocommerce_order_amount_total_shipping'), 999);
    }

    public function init() {
        if (!class_exists('WooCommerce')) {
            return;
        }

        wp_enqueue_script('jquery');

        //+++
        load_plugin_textdomain('woocommerce-currency-switcher', false, dirname(plugin_basename(__FILE__)) . '/languages');
        //+++
        add_filter('woocommerce_paypal_supported_currencies', array($this, 'enable_custom_currency'), 999);
        add_filter('woocommerce_currency_symbol', array($this, 'woocommerce_currency_symbol'), 999);
        add_filter('woocommerce_currency', array($this, 'get_woocommerce_currency'), 999);
        add_filter('raw_woocommerce_price', array($this, 'raw_woocommerce_price'), 999);
        add_filter('woocommerce_order_amount_item_subtotal', array($this, 'order_amount_item_subtotal'), 999);
        //add_filter('woocommerce_order_amount_line_subtotal', array($this, 'order_amount_item_subtotal'), 999);
        add_filter('woocommerce_price_format', array($this, 'woocommerce_price_format'), 999);
        add_filter('woocommerce_thankyou_order_id', array($this, 'woocommerce_thankyou_order_id'), 999);
        add_filter('woocommerce_before_resend_order_emails', array($this, 'woocommerce_before_resend_order_emails'), 999);
        //add_action('woocommerce_payment_complete', array($this, 'woocommerce_payment_complete'), 999);
        add_action('woocommerce_order_status_completed', array($this, 'woocommerce_order_status_completed'), 1);
        //add_action('woocommerce_shipping_init', array($this, 'woocommerce_shipping_init'), 999);
        //add_filter('woocommerce_shipping_methods', array($this, 'woocommerce_shipping_methods'), 999);
        //add_filter('woocommerce_checkout_get_value', array($this, 'woocommerce_checkout_get_value'), 999);
        add_filter('woocommerce_order_amount_total_shipping', array($this, 'woocommerce_order_amount_total_shipping'), 999);
        //woocommerce\includes\class-wc-order.php #523 get_total_tax
        //woocommerce\includes\gateways\paypal\class-wc-gateway-paypal.php #420 $args['tax_cart'] = $order->get_total_tax();
        add_filter('woocommerce_order_amount_total_tax', array($this, 'woocommerce_order_amount_total_tax'), 999);
        //add_filter ('woocommerce_order_amount_shipping_tax', array( $this, 'woocommerce_order_amount_total_shipping' ), 999);
        //add_filter ('woocommerce_order_amount_cart_tax', array( $this, 'woocommerce_order_amount_total_shipping' ), 999);
        add_filter('woocommerce_order_amount_total_discount', array($this, 'woocommerce_order_amount_total_shipping'), 999);
        add_filter('woocommerce_order_amount_cart_discount', array($this, 'woocommerce_order_amount_total_shipping'), 999);
        add_filter('woocommerce_order_amount_order_discount', array($this, 'woocommerce_order_amount_total_shipping'), 999);
        add_filter('woocommerce_update_shipping_method', array($this, 'woocommerce_order_amount_total_shipping'), 999);
        //add_filter('woocommerce_before_mini_cart', array($this, 'woocommerce_before_mini_cart'), 1);
        //add_filter('woocommerce_cart_item_price', array($this, 'woocommerce_cart_item_price'), 1);
        //+++
        //need to fixing customers problems only with cart in header
        add_filter('woocommerce_cart_contents_total', array($this, 'woocommerce_cart_contents_total'), 999);

        //add_filter('woocommerce_get_refreshed_fragments', array($this, 'woocommerce_get_refreshed_fragments'), 999);
        //add_filter('woocommerce_cart_subtotal', array($this, 'woocommerce_cart_subtotal'), 999);
        add_filter('formatted_woocommerce_price', array($this, 'formatted_woocommerce_price'), 999);
        //+++
        //UNCOMMENT PLEASE THIS NEXT LINE OF CODE IF YOUR PAYPAL DOESN UNDERSTAND SELECTED CURRENCY AMOUNT
        add_filter('woocommerce_order_amount_total', array($this, 'woocommerce_order_amount_total'), 999);
        //+++
        add_action('wp_ajax_woocs_save_etalon', array($this, 'save_etalon'));
        add_action('wp_ajax_woocs_get_rate', array($this, 'get_rate'));

        add_action('woocommerce_settings_tabs_array', array($this, 'woocommerce_settings_tabs_array'), 999);
        add_action('woocommerce_settings_tabs_woocs', array($this, 'print_plugin_options'), 999);
        add_action('woocommerce_get_price_html', array($this, 'woocommerce_get_price_html'), 999);
        //add_action('woocommerce_payment_successful_result', array($this, 'woocommerce_payment_successful_result'), 999);
        //+++
        add_action('widgets_init', array($this, 'widgets_init'));
        add_action('wp_head', array($this, 'wp_head'), 1);
        add_action('wp_footer', array($this, 'wp_footer'), 999);
        //overide woocs slider js
        wp_enqueue_script('wc-price-slider', WOOCS_LINK . 'js/price-slider.js', array('jquery', 'jquery-ui-slider'));
        //***
        add_action('save_post', array($this, 'save_post'), 1);
        add_action('admin_head', array($this, 'admin_head'), 1);
        add_action('admin_init', array($this, 'admin_init'), 1);
        add_shortcode('woocs', array($this, 'woocs_shortcode'));
        add_shortcode('woocs_get_sign_rate', array($this, 'get_sign_rate'));
        if ($this->is_multiple_allowed) {
            add_action('the_post', array($this, 'the_post'), 1);
            add_action('load-post.php', array($this, 'admin_action_post'), 1);
        }
        //+++
        add_filter('plugin_action_links_' . WOOCS_PLUGIN_NAME, array($this, 'plugin_action_links'));
    }

    /**
     * Show action links on the plugin screen
     */
    public function plugin_action_links($links) {
        return array_merge(array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=woocs') . '">' . __('Settings', 'woocommerce-currency-switcher') . '</a>',
            '<a target="_blank" href="' . esc_url('http://woocommerce-currencies-switcher.pluginus.net/documentation/') . '">' . __('Documentation', 'woocommerce-currency-switcher') . '</a>'
                ), $links);

        return $links;
    }

    public function widgets_init() {
        require_once WOOCS_PATH . 'widget-woocs-selector.php';
        register_widget('WOOCS_SELECTOR');
    }

    public function admin_head() {
        //wp_enqueue_scripts('jquery');
        wp_enqueue_script('woocommerce-currency-switcher-admin', WOOCS_LINK . 'js/admin.js', array('jquery'));
    }

    public function admin_init() {
        if ($this->is_multiple_allowed) {
            add_meta_box('woocs_order_metabox', __('WOOCS Order Info', 'woocommerce-currency-switcher'), array($this, 'woocs_order_metabox'), 'shop_order', 'side', 'default');
        }
    }

    //for orders hook
    public function the_post($post) {
        if (is_object($post) AND $post->post_type == 'shop_order') {
            $currency = get_post_meta($post->ID, '_woocs_order_currency', true);
            $_REQUEST['woocs_in_order_currency'] = $currency;
            $this->default_currency = $currency;
        }

        return $post;
    }

    public function save_post($order_id) {
        if (!empty($_POST)) {
            global $post;
            if (is_object($post)) {
                if ($post->post_type == 'shop_order' AND isset($_POST['woocs_order_currency'])) {
                    $currencies = $this->get_currencies();
                    $currencies_keys = array_keys($currencies);
                    $currency = $_POST['woocs_order_currency'];
                    if (in_array($currency, $currencies_keys)) {
                        update_post_meta($order_id, '_woocs_order_currency', $currency);
                        wc_add_order_item_meta($order_id, '_woocs_order_currency', $currency, true);

                        update_post_meta($order_id, '_woocs_order_rate', $currencies[$currency]['rate']);
                        wc_add_order_item_meta($order_id, '_woocs_order_rate', $currencies[$currency]['rate'], true);

                        update_post_meta($order_id, '_woocs_order_base_currency', $this->default_currency);
                        wc_add_order_item_meta($order_id, '_woocs_order_base_currency', $this->default_currency, true);

                        update_post_meta($order_id, '_woocs_order_currency_changed_mannualy', time());
                        wc_add_order_item_meta($order_id, '_woocs_order_currency_changed_mannualy', time(), true);
                    }
                }
            }
        }
    }

    //for orders hook
    public function admin_action_post() {
        if (isset($_GET['post'])) {
            $post_id = $_GET['post'];
            $post = get_post($post_id);
            if (is_object($post) AND $post->post_type == 'shop_order') {
                $currency = get_post_meta($post->ID, '_woocs_order_currency', true);
                $_REQUEST['woocs_in_order_currency'] = $currency;
                $this->default_currency = $currency;
            }
        }
    }

    public function woocs_order_metabox($post) {
        $data = array();
        $data['post'] = $post;
        $data['order'] = new WC_Order($post->ID);
        echo $this->render_html(WOOCS_PATH . 'views/woocs_order_metabox.php', $data);
    }

    public function wp_head() {
        wp_enqueue_script('jquery');
        $currencies = $this->get_currencies();
        ?>
        <script type="text/javascript">
            var woocs_drop_down_view = "<?php echo $this->get_drop_down_view(); ?>";
            var woocs_current_currency = <?php echo json_encode($currencies[$this->current_currency]) ?>;
            var woocs_default_currency = <?php echo json_encode($currencies[$this->default_currency]) ?>;
        </script>
        <?php
        if ($this->get_drop_down_view() == 'ddslick') {
            wp_enqueue_script('jquery.ddslick.min', WOOCS_LINK . 'js/jquery.ddslick.min.js', array('jquery'));
        }

        if ($this->get_drop_down_view() == 'chosen') {
            wp_enqueue_script('chosen-drop-down', WOOCS_LINK . 'js/chosen/chosen.jquery.min.js', array('jquery'));
            wp_enqueue_style('chosen-drop-down', WOOCS_LINK . 'js/chosen/chosen.min.css');
        }
        //+++
        wp_enqueue_style('woocommerce-currency-switcher', WOOCS_LINK . 'css/front.css');
        wp_enqueue_script('woocommerce-currency-switcher', WOOCS_LINK . 'js/front.js', array('jquery'));
    }

    public function woocommerce_settings_tabs_array($tabs) {
        $tabs['woocs'] = __('Currency', 'woocommerce-currency-switcher');
        return $tabs;
    }

    public function print_plugin_options() {
        if (isset($_POST['woocs_name']) AND ! empty($_POST['woocs_name'])) {
            $result = array();
            update_option('woocs_drop_down_view', $_POST['woocs_drop_down_view']);
            update_option('woocs_currencies_aggregator', $_POST['woocs_currencies_aggregator']);
            update_option('woocs_welcome_currency', $this->default_currency);
            update_option('woocs_is_multiple_allowed', $_POST['woocs_is_multiple_allowed']);
            update_option('woocs_customer_signs', "");
            update_option('woocs_currencies_rate_auto_update', 'no');
            update_option('woocs_show_flags', $_POST['woocs_show_flags']);
            update_option('woocs_show_money_signs', $_POST['woocs_show_money_signs']);
            //***
            foreach ($_POST['woocs_name'] as $key => $name) {
                if (!empty($name)) {
                    $symbol = $_POST['woocs_symbol'][$key]; //md5 encoded

                    foreach ($this->currency_symbols as $s) {
                        if (md5($s) == $symbol) {
                            $symbol = $s;
                            break;
                        }
                    }

                    $result[strtoupper($name)] = array(
                        'name' => $name,
                        'rate' => floatval($_POST['woocs_rate'][$key]),
                        'symbol' => $symbol,
                        'position' => (in_array($_POST['woocs_position'][$key], $this->currency_positions) ? $_POST['woocs_position'][$key] : $this->currency_positions[0]),
                        'is_etalon' => (int) $_POST['woocs_is_etalon'][$key],
                        'description' => $_POST['woocs_description'][$key],
                        'flag' => $_POST['woocs_flag'][$key],
                    );
                }
            }

            update_option('woocs', $result);
            $this->init_currency_symbols();
        }
        //+++
        wp_enqueue_script('media-upload');
        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('woocommerce-currency-switcher-options', WOOCS_LINK . 'js/options.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'));
        wp_enqueue_style('woocommerce-currency-switcher-options', WOOCS_LINK . 'css/options.css');
        $args = array();
        $args['currencies'] = $this->get_currencies();
        echo $this->render_html(WOOCS_PATH . 'views/plugin_options.php', $args);
    }

    public function get_drop_down_view() {
        $res = get_option('woocs_drop_down_view');
        if (empty($res)) {
            $res = 'ddslick';
        }
        return $res;
    }

    public function get_currencies() {
        $default = array(
            'USD' => array(
                'name' => 'USD',
                'rate' => 1,
                'symbol' => '&#36;',
                'position' => 'right',
                'is_etalon' => 1,
                'description' => 'USA dollar',
                'flag' => '',
            ),
            'EUR' => array(
                'name' => 'EUR',
                'rate' => 0.74,
                'symbol' => '&euro;',
                'position' => 'left_space',
                'is_etalon' => 0,
                'description' => 'Europian Euro',
                'flag' => '',
            )
        );

        $curr = get_option('woocs', $default);
        if (count($curr) != 2) {
            $curr = $default;
        }
        return $curr;
    }

    //need for paypal currencies supporting
    public function enable_custom_currency($currency_array) {
        //https://developer.paypal.com/docs/classic/api/currency_codes/
        //includes\gateways\paypal\class-wc-gateway-paypal.php => woo func
        //function is_valid_for_use() =>Check if this gateway is enabled and available in the user's country
        $currency_array[] = 'usd';
        $currency_array[] = 'aud';
        $currency_array[] = 'brl';
        $currency_array[] = 'cad';
        $currency_array[] = 'czk';
        $currency_array[] = 'dkk';
        $currency_array[] = 'eur';
        $currency_array[] = 'hkd';
        $currency_array[] = 'huf';
        $currency_array[] = 'ils';
        $currency_array[] = 'jpy';
        $currency_array[] = 'myr';
        $currency_array[] = 'mxn';
        $currency_array[] = 'nok';
        $currency_array[] = 'nzd';
        $currency_array[] = 'php';
        $currency_array[] = 'pln';
        $currency_array[] = 'gbp';
        $currency_array[] = 'rub';
        $currency_array[] = 'sgd';
        $currency_array[] = 'sek';
        $currency_array[] = 'chf';
        $currency_array[] = 'twd';
        $currency_array[] = 'thb';
        $currency_array[] = 'try';
        $currency_array = array_map('strtoupper', $currency_array);
        return $currency_array;
    }

    public function woocommerce_currency_symbol($currency) {

        $currencies = $this->get_currencies();
        $symbol = '&#36;';

        if ($this->is_conditions_allowed()) {
            if (empty($this->default_currency)) {
                //THIS can be after the plugin activation in not on new shop
                foreach ($currencies as $key => $currency) {
                    if ($currency['is_etalon']) {
                        $this->default_currency = $key;
                        break;
                    }
                }
            }
            if (isset($_REQUEST['action']) AND $_REQUEST['action'] == 'woocommerce_get_refreshed_fragments') {
                return $currencies[$this->current_currency]['symbol']; //shopping cart woo widget fix
            } else {
                return $currencies[$this->default_currency]['symbol'];
            }
        }

        if (isset($currencies[$this->current_currency])) {
            $symbol = $currencies[$this->current_currency]['symbol'];
        }
        return $symbol;
    }

    public function get_woocommerce_currency() {
        if ($this->is_conditions_allowed()) {
            return $this->default_currency;
        }
        return $this->current_currency;
    }

    public function woocommerce_get_price_html($price_html) {
        if ($this->current_currency == 'JPY') {
            if ($this->decimal_sep == '.') {
                $price_html = preg_replace('/.00/', '', $price_html);
            }

            if ($this->decimal_sep == ',') {
                $price_html = preg_replace('/,00/', '', $price_html);
            }
        }

        return $price_html;
    }

    public function raw_woocommerce_price($price) {
        $currencies = $this->get_currencies();

        if (isset($_REQUEST['woocs_in_order_currency']) AND $this->is_multiple_allowed) {
            //if we are inside an order, set currency of current order
            if (isset($currencies[$_REQUEST['woocs_in_order_currency']])) {
                global $post;
                if (is_object($post)) {
                    $rate = get_post_meta($post->ID, '_woocs_order_rate', TRUE);
                    if (isset($_GET['post'])) {
                        if (!empty($rate)) {
                            $price = $price * $rate;
                        } else {
                            $price = $price * $currencies[$_REQUEST['woocs_in_order_currency']]['rate'];
                        }
                    } else {
                        $order = new WC_Order($post->ID);
                        $price = number_format($order->get_total() * $rate, 2);
                    }
                }
            }
        } else {
            if ($this->current_currency != $this->default_currency) {

                if ($this->is_conditions_allowed()) {
                    return $price;
                }

                //+++
                if (is_ajax()) {
                    if (isset($_REQUEST['action'])) {
                        if ($_REQUEST['action'] == 'woocommerce_checkout' OR $_REQUEST['action'] == 'woocommerce_update_order_review' OR $_REQUEST['action'] == 'woocommerce_get_refreshed_fragments') {
                            if ($this->is_multiple_allowed) {
                                $price = $price * $currencies[$this->current_currency]['rate'];
                            } else {
                                $this->current_currency = $this->default_currency;
                            }
                        }
                    }
                } else {
                    if (isset($currencies[$this->current_currency])) {
                        $price = $price * $currencies[$this->current_currency]['rate'];
                    }
                }
            }
        }

        //***
        if ($this->current_currency == 'JPY') {
            $price = number_format($price, 0, '.', '');
        } else {
            $price = number_format($price, 2, '.', '');
        }

        return $price;
        //return number_format ($price, 2, $this->decimal_sep, $this->thousands_sep);
    }

    public function get_welcome_currency() {
        return get_option('woocs_welcome_currency');
    }

    public function get_customer_signs() {
        $signs = array();
        $data = get_option('woocs_customer_signs');
        if (!empty($data)) {
            $data = explode(',', $data);
            if (!empty($data) AND is_array($data)) {
                $signs = $data;
            }
        }
        return $signs;
    }

    public function get_checkout_page_id() {
        return (int) get_option('woocommerce_checkout_page_id');
    }

    public function order_amount_item_subtotal($price) {
        return $this->raw_woocommerce_price($price);
    }

    public function woocommerce_price_format() {
        $currencies = $this->get_currencies();
        $currency_pos = 'left';
        if (isset($currencies[$this->current_currency])) {
            $currency_pos = $currencies[$this->current_currency]['position'];
        }
        switch ($currency_pos) {
            case 'left' :
                $format = '%1$s%2$s';
                break;
            case 'right' :
                $format = '%2$s%1$s';
                break;
            case 'left_space' :
                $format = '%1$s&nbsp;%2$s';
                break;
            case 'right_space' :
                $format = '%2$s&nbsp;%1$s';
                break;
        }

        return $format;
    }

    //[woocs]
    public function woocs_shortcode($args) {
        return $this->render_html(WOOCS_PATH . 'views/shortcodes/woocs.php', $args);
    }

    //[woocs_currency curr='BRL' add='0.1']
    public function woocs_currency($args) {
        return $this->render_html(WOOCS_PATH . 'views/shortcodes/woocs_currency.php', $args);
    }

    //ajax
    public function get_rate() {
        $is_ajax = true;
        if (isset($_REQUEST['no_ajax'])) {
            $is_ajax = false;
        }
        //***
        //http://en.wikipedia.org/wiki/ISO_4217
        $mode = get_option('woocs_currencies_aggregator');
        $request = "";
        switch ($mode) {
            case 'yahoo':
                $res = file_get_contents('http://finance.yahoo.com/d/quotes.txt?e=.txt&f=c4l1&s=' . $this->default_currency . $_REQUEST['currency_name'] . '=X');
                $res = explode(',', $res);
                if (isset($res[1])) {
                    $request = floatval($res[1]);
                } else {
                    $request = sprintf(__("no data for %s", 'woocommerce-currency-switcher'), $_REQUEST['currency_name']);
                }
                break;

            case 'google':
                $amount = urlencode(1);
                $from_Currency = urlencode($this->default_currency);
                $to_Currency = urlencode($_REQUEST['currency_name']);
                $url = "http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
                $html = file_get_contents($url);
                preg_match_all('/<span class=bld>(.*?)<\/span>/s', $html, $matches);
                if (isset($matches[1][0])) {
                    $request = floatval($matches[1][0]);
                } else {
                    $request = sprintf(__("no data for %s", 'woocommerce-currency-switcher'), $_REQUEST['currency_name']);
                }

                /*
                  $ch = curl_init();
                  $timeout = 0;
                  curl_setopt($ch, CURLOPT_URL, $url);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
                  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                  $rawdata = curl_exec($ch);
                  curl_close($ch);
                  $data = explode('bld>', $rawdata);
                  $data = explode($to_Currency, $data[1]);

                  echo floatval($data[0]);
                 */
                break;

            case 'appspot':
                $res = file_get_contents('http://rate-exchange.appspot.com/currency?from=' . $this->default_currency . '&to=' . $_REQUEST['currency_name']);
                $res = json_decode($res);
                if (isset($res->rate)) {
                    $request = floatval($res->rate);
                } else {
                    $request = sprintf(__("no data for %s", 'woocommerce-currency-switcher'), $_REQUEST['currency_name']);
                }
                break;

            default:
                break;
        }

        //custom work Francisco
        if ($_REQUEST['currency_name'] == 'COP') {
            $content = file_get_contents("http://dolar.wilkinsonpc.com.co/dolar.html");
            $content = explode('<span class="baja">', $content);
            $content = explode(' ', $content[1]);
            $content = explode('</span>', $content[0]);
            $rate = $content[0];
            $rate = substr($rate, 1);
            $rate = str_replace(',', '', $rate);
            $rate1 = (float) $rate;
            $request = $rate;
        }

        //***
        if ($is_ajax) {
            echo $request;
            exit;
        } else {
            return $request;
        }
    }

    //ajax
    public function save_etalon() {
        $currencies = $this->get_currencies();
        $currency_name = $_REQUEST['currency_name'];
        foreach ($currencies as $key => $currency) {
            if ($currency['name'] == $currency_name) {
                $currencies[$key]['is_etalon'] = 1;
            } else {
                $currencies[$key]['is_etalon'] = 0;
            }
        }
        update_option('woocs', $currencies);
        //+++ get curr updated values back
        $request = array();
        $this->default_currency = strtoupper($_REQUEST['currency_name']);
        $_REQUEST['no_ajax'] = TRUE;
        foreach ($currencies as $key => $currency) {
            if ($currency_name != $currency['name']) {
                $_REQUEST['currency_name'] = $currency['name'];
                $request[$key] = $this->get_rate();
            } else {
                $request[$key] = 1;
            }
        }

        echo json_encode($request);
        exit;
    }

    private function is_conditions_allowed() {

        $is_admin = (bool) substr_count($_SERVER['REQUEST_URI'], 'wp-admin');

        //loading of checkout page
        if (isset($_REQUEST['action']) AND is_ajax()) {
            if ($_REQUEST['action'] == 'woocommerce_update_order_review') {
                if ($this->is_multiple_allowed) {
                    $is_admin = false;
                } else {
                    $is_admin = true;
                }
            }
        }

        //loading paypal data
        if (isset($_REQUEST['payment_method']) AND is_ajax()) {
            //if($_REQUEST['payment_method'] == 'paypal') {
            $is_admin = false;
            //}
        }

        if ($is_admin) {
            return is_admin();
        }

        //***
        if ($this->is_multiple_allowed) {
            return false;
        }


        return false;
    }

    //order data registration
    public function woocommerce_thankyou_order_id($order_id) {
        update_post_meta($order_id, '_woocs_order_currency', $this->current_currency);
        wc_add_order_item_meta($order_id, '_woocs_order_currency', $this->current_currency, true);

        $currencies = $this->get_currencies();
        update_post_meta($order_id, '_woocs_order_rate', $currencies[$this->current_currency]['rate']);
        wc_add_order_item_meta($order_id, '_woocs_order_rate', $currencies[$this->current_currency]['rate'], true);

        update_post_meta($order_id, '_woocs_order_base_currency', $this->default_currency);
        wc_add_order_item_meta($order_id, '_woocs_order_base_currency', $this->default_currency, true);

        update_post_meta($order_id, '_woocs_order_currency_changed_mannualy', 0);
        wc_add_order_item_meta($order_id, '_woocs_order_currency_changed_mannualy', 0, true);

        return $order_id;
    }

    //when admin complete order
    public function woocommerce_order_status_completed($order_id) {
        if ($this->is_multiple_allowed) {
            $currency = get_post_meta($order_id, '_woocs_order_currency', true);
            if (!empty($currency)) {
                $_REQUEST['woocs_in_order_currency'] = $currency;
                $this->default_currency = $currency;
            }
        }
    }

    public function woocommerce_order_amount_total_shipping($order_shipping) {
        //woocommerce\includes\gateways\paypal\class-wc-gateway-paypal.php #429
        //woocommerce\includes\class-wc-order.php #523 get_total_tax - maybe the same will need
        if ($this->is_multiple_allowed) {
            $currencies = $this->get_currencies();
            $order_shipping = $order_shipping * $currencies[$this->current_currency]['rate'];
        }

        $order_shipping = number_format($order_shipping, 2, $this->decimal_sep, $this->thousands_sep);
        return $order_shipping;
    }

    //for paypal only
    public function woocommerce_order_amount_total_tax($order_shipping) {
        //woocommerce\includes\gateways\paypal\class-wc-gateway-paypal.php #429
        //woocommerce\includes\class-wc-order.php #523 get_total_tax - maybe the same will need
        static $done = 0;
        if ($this->is_multiple_allowed AND ! $done AND is_ajax() AND isset($_REQUEST['payment_method'])) {
            if (!isset($_REQUEST['no_woocs_order_amount_total_tax'])) {
                $currencies = $this->get_currencies();
                $order_shipping = $order_shipping * $currencies[$this->current_currency]['rate'];
                $done = 1;
                //$order_shipping+=0.01; //round correction for tax
                $order_shipping = number_format($order_shipping, 2, $this->decimal_sep, $this->thousands_sep);
            }
        }

        return $order_shipping;
    }

    public function woocommerce_get_refreshed_fragments() {
        if ($this->is_multiple_allowed) {
            //$this->current_currency = 'EUR';
            //$this->default_currency = 'EUR';
        }
    }

    //shopping cart woo widget fix plugins\woocommerce\includes\class-wc-ajax.php#88
    public function formatted_woocommerce_price($price) {
        //if($this->is_multiple_allowed) {
        if ($this->default_currency != $this->current_currency) {
            if (isset($_REQUEST['action']) AND $_REQUEST['action'] == 'woocommerce_get_refreshed_fragments') {
                $currencies = $this->get_currencies();
                $price = str_replace($this->thousands_sep, '', $price);
                $price = floatval($price) * $currencies[$this->current_currency]['rate'];
                $price = number_format($price, 2, $this->decimal_sep, $this->thousands_sep);
            }
        }
        //}

        return $price;
    }

    //some themes need this for Paypal
    public function woocommerce_order_amount_total($order_total) {
        //class-wc-gateway-paypal#369 amount_1
        //woocommerce\includes\class-wc-order.php#545
        static $done = 0;
        //if( $this->is_multiple_allowed  AND is_ajax ()  AND ! $done AND ( isset ($_REQUEST['action']) AND $_REQUEST['action'] == 'woocommerce_checkout' AND $_REQUEST['payment_method'] == 'paypal') ) {
        if ($this->is_multiple_allowed AND ! $done AND ! isset($_REQUEST['no_woocs_order_amount_total'])) {
            $currencies = $this->get_currencies();
            $order_total = $order_total * $currencies[$this->current_currency]['rate'];
            $done = 1;
            $order_total = number_format($order_total, 2, $this->decimal_sep, $this->thousands_sep);
        }
        //}
        return $order_total;
    }

    public function woocommerce_before_resend_order_emails($order) {
        if ($this->is_multiple_allowed) {
            $_REQUEST['no_woocs_order_amount_total_tax'] = 1;
            $currency = get_post_meta($order->id, '_woocs_order_currency', true);
            if (!empty($currency)) {
                $_REQUEST['woocs_in_order_currency'] = $currency;
                $this->default_currency = $currency;
            }
        }
    }

    //********************************************************************************
    //need to fixing customers problems only with cart in header
    public function woocommerce_cart_contents_total($cart_contents_total) {
        return $cart_contents_total; //return it for releases - it is custom fix
        //+++
        if ($this->default_currency != $this->current_currency) {
            if ($this->current_currency != 'USD') {
                $val = floatval(substr(trim(strip_tags($cart_contents_total)), 6));
            } else {
                $val = floatval(strip_tags($cart_contents_total));
            }

            $currencies = $this->get_currencies();
            $cart_contents_total = $currencies[$this->current_currency]['symbol'] . number_format($val * $currencies[$this->current_currency]['rate'], 2, '.', '');
        }
        return $cart_contents_total;
    }

    public function wp_footer() {
        //return; //return it for releases
        ?>
        <script type="text/javascript">
            try {
                jQuery(function () {
                    jQuery.cookie('woocommerce_cart_hash', null, {path: '/'});
                });
            } catch (e) {

            }
        </script>
        <?php
    }

    //********************************************************************************
    //for pdf invoice plugin
    public static function normalize_order_data($order_id, $amount) {

        $rate = get_post_meta($order_id, '_woocs_order_rate', TRUE);
        $currency = get_post_meta($order_id, '_woocs_order_currency', TRUE);

        if (!empty($currency) AND ! empty($rate)) {
            $amount = $amount * $rate;
        }

        return $amount;
    }

    public function render_html($pagepath, $data = array()) {
        @extract($data);
        ob_start();
        include($pagepath);
        return ob_get_clean();
    }

    public function get_sign_rate($atts) {
        $sign = strtoupper($atts['sign']);
        $currencies = $this->get_currencies();
        $rate = 0;
        if (isset($currencies[$sign])) {
            $rate = $currencies[$sign]['rate'];
        }

        return $rate;
    }

    //for mini card
    public function woocommerce_before_mini_cart() {
        $this->default_currency = $_SESSION['woocs_current_currency'];
    }

    //for mini card
    public function woocommerce_cart_item_price($price_html) {
        $currencies = $this->get_currencies();
        $price_html = strip_tags($price_html);
        preg_match_all('!\d+!', $price_html, $matches);
        $price = floatval($matches[0][0] . '.' . $matches[0][1]);
        echo ($price);
        exit;
        $price = $price * $currencies[$this->current_currency]['rate'];
        return $price;
    }

}

//+++
$WOOCS = new WOOCS();
$GLOBALS['WOOCS'] = $WOOCS;
add_action('init', array($WOOCS, 'init'), 1);



//includes/wc-core-functions.php #156
//includes/wc-formatting-functions.php #297
//includes/admin/post-types/meta-boxes/class-wc-meta-box-order-totals.php


