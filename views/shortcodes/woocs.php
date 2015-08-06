<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
//*** hide if there is checkout page
global $post;
if (is_object($post))
{
    if ($this->get_checkout_page_id() == $post->ID)
    {
        //return "";
    }
}
$drop_down_view = $this->get_drop_down_view();

//***
if ($drop_down_view == 'flags')
{
    foreach ($this->get_currencies() as $key => $currency)
    {
        if (!empty($currency['flag']))
        {
            ?>
            <a href="#" class="woocs_flag_view_item <?php if ($this->current_currency == $key): ?>woocs_flag_view_item_current<?php endif; ?>" data-currency="<?php echo $currency['name'] ?>" title="<?php echo $currency['name'] . ', ' . $currency['symbol'] . ' ' . $currency['description'] ?>"><img src="<?php echo $currency['flag'] ?>" alt="<?php echo $currency['name'] . ', ' . $currency['symbol'] ?>" /></a>
            <?php
        }
    }
} else
{
    $empty_flag = WOOCS_LINK . 'img/no_flag.png';
    $show_money_signs = get_option('woocs_show_money_signs');
//***
    if (!isset($show_flags))
    {
        $show_flags = get_option('woocs_show_flags');
    }



    if (!isset($width))
    {
        $width = '100%';
    }

    if (!isset($flag_position))
    {
        $flag_position = 'right';
    }
    ?>
    <form method="post" action="" class="woocommerce-currency-switcher-form <?php if ($show_flags): ?>woocs_show_flags<?php endif; ?>">
        <input type="hidden" name="woocommerce-currency-switcher" value="<?php echo $this->current_currency ?>" />
        <select name="woocommerce-currency-switcher" style="width: <?php echo $width ?>;" data-width="<?php echo $width ?>" data-flag-position="<?php echo $flag_position ?>" class="woocommerce-currency-switcher" onchange="woocs_redirect(this.value); void(0);">
            <?php foreach ($this->get_currencies() as $key => $currency) : ?>
                <option <?php if ($show_flags) : ?>style="background: url('<?php echo(!empty($currency['flag']) ? $currency['flag'] : $empty_flag); ?>') no-repeat 99% 0; background-size: 30px 20px;"<?php endif; ?> value="<?php echo $key ?>" <?php selected($this->current_currency, $key) ?> data-imagesrc="<?php if ($show_flags) echo(!empty($currency['flag']) ? $currency['flag'] : $empty_flag); ?>" data-description="<?php echo $currency['description'] ?>"><?php echo $currency['name'] ?> <?php if ($show_money_signs) echo ', ' . $currency['symbol'] ?></option>
            <?php endforeach; ?>
        </select>
        <div style="display: none;">WOOCS <?php echo $this->the_plugin_version ?></div>
    </form>
    <?php
}
