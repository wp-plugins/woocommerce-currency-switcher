<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
$currencies = $this->get_currencies();
$prev = $this->current_currency;
$this->current_currency = $curr;
$price = str_replace(',', '.', wc_price(0));
$rate = $currencies[$curr]['rate'];
if (isset($add)) {
    $rate = number_format($rate + (floatval($add) * $rate), 4, '.', '');
}
$string = str_replace('0.00', $rate, $price);
$this->current_currency = $prev;
?>

<span class="woocs_currency"><?php echo $string ?></span>
