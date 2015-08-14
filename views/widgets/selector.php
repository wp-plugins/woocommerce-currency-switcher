<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<div class="widget widget-woocommerce-currency-switcher">
    <?php if (!empty($instance['title'])): ?>
        <h3><?php _e($instance['title']) ?></h3>
    <?php endif; ?>
    <?php
    $show_flags = $instance['show_flags'];
    if ($show_flags === 'true')
    {
        $show_flags = 1;
    } else
    {
        $show_flags = 0;
    }
    //+++
    $txt_type = 'code';
    if (isset($instance['txt_type']))
    {
        $txt_type = $instance['txt_type'];
    }
    echo do_shortcode("[woocs txt_type='{$txt_type}' show_flags={$show_flags} width='{$instance['width']}' flag_position='{$instance['flag_position']}']");
    ?>
</div>

