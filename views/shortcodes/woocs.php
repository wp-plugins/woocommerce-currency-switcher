<?php if( ! defined ('ABSPATH') ) die ('No direct access allowed'); ?>
<?php
//*** hide if there is checkout page
global $post;
if( is_object ($post) ) {
    if( $this->get_checkout_page_id () == $post->ID ) {
        return "";
    }
}
//***

if( ! isset ($width) ) {
    $width = '100%';
}
?>
<form method="post" action="" class="woocommerce-currency-switcher-form">
    <input type="hidden" name="woocommerce-currency-switcher" value="<?php echo $this->current_currency ?>" />
    <select name="woocommerce-currency-switcher" style="width: <?php echo $width ?>;" data-width="<?php echo $width ?>" class="woocommerce-currency-switcher" onchange="return jQuery(this).closest('form').submit();">
        <?php foreach ( $this->get_currencies () as $key=> $currency ) : ?>
            <option value="<?php echo $key ?>" <?php selected ($this->current_currency, $key) ?>><?php echo $currency['name'] ?>, <?php echo $currency['symbol'] ?></option>
        <?php endforeach; ?>
    </select>
    <div style="display: none;">WOOCS <?php echo $this->the_plugin_version ?></div>
</form>
