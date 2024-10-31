<?php
//Get RTL (right to left)
if(isset($_GET['rtl'])) { $rtl = $_GET['rtl']; } else { $rtl = esc_attr(get_option('ns_core_rtl')); } 

//Get currency options
$admin_obj = new PropertyShift_Admin();
$settings = $admin_obj->load_settings();

$dynamic_script = '';

//OUTPUT VARIABLES FOR USE IN propertyshift.js
$dynamic_script .= "var rtl = '{$rtl}';";
$dynamic_script .= "var currency_symbol = '{$settings['ps_currency_symbol']}';";
$dynamic_script .= "var currency_symbol_position = '{$settings['ps_currency_symbol_position']}';";
$dynamic_script .= "var currency_thousand = '{$settings['ps_thousand_separator']}';";
$dynamic_script .= "var currency_decimal = '{$settings['ps_decimal_separator']}';";
$dynamic_script .= "var currency_decimal_num = '{$settings['ps_num_decimal']}';";

wp_add_inline_script( 'propertyshift', $dynamic_script);

?>