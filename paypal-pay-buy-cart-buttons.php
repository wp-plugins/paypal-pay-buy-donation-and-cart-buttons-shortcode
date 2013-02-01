<?php
/*
Plugin Name: Paypal Pay, Buy, Donation and Cart Buttons Shortcode
Plugin URI: http://mohsinrasool.wordpress.com/2013/01/11/wordpress-shortcode-for-paypal-pay-buy-donation-and-cart-buttons/
Description: Add a "paypal_button" shortcode to display pay now, buy now, donation and add to cart PayPal buttons with facility to customize they paypal checkout page.
Version: 1.2
Author: Mohsin Rasool
Author URI: http://mohsinrasool.wordpress.com
License: GPL2
    Copyright 2013  Mohsin Rasool  (email : mohsin.rasool@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
include 'admin-settings.php';

function wpdev_paypal_button($atts, $content = null) {  
    extract(shortcode_atts(array(  
    'type'=>  get_option('wpdev_paypal_button_type'),
    'email' => get_option('wpdev_paypal_button_email'),
    'name' => get_option('wpdev_paypal_button_name'),
    'id' => '',
    'amount' => '',
    'quantity' => '1',
    'echo_link' => false,
    'quantity_txt_postfix' => '',
    'field_sep'=>'',
    'currency' => get_option('wpdev_paypal_button_currency'),
    'tax_rate' => get_option('wpdev_paypal_button_tax_rate'),
    'shipping_charges' => get_option('wpdev_paypal_button_shipping_charges'),
    'btn_size' => get_option('wpdev_paypal_button_size'),
    'btn_display_cc' => get_option('wpdev_paypal_button_display_cc'),
    'btn_text' => '',
    'add_note'=>get_option('wpdev_paypal_button_add_note'),
    'thankyou_page_url'=> get_option('wpdev_paypal_button_thankyou_url'),
    'checkout_logo_url' => get_option('wpdev_paypal_button_logo_url'),
    'checkout_header_border_color' => get_option('wpdev_paypal_button_checkout_header_border_color'),
    'checkout_header_bg_color' => get_option('wpdev_paypal_button_checkout_header_bg_color'),
    'checkout_bg_color' => get_option('wpdev_paypal_button_checkout_bg_color'),
    'button_subtype' => '',
    ), $atts));  
    
    global $post;
    $paypal_values = array();
    
    if(empty($email))        $email = get_bloginfo ('admin_email');
    
    if(empty($name))
        $name = (!empty($post)) ? $post->post_title : get_bloginfo ('admin_email');
    
    if(empty($id))
        $id = (!empty($post)) ? $post->ID : rand(100,1000);
    
    if(strpos(strtolower($type),'pay') !== false){
        $cmd = '_xclick';
        $button_subtype = 'service';
        $btn_text = 'PayNow';    
        $btn = 'btn_paynow';
        $quantity_field = 'quantity';
    }
    elseif(strpos(strtolower($type),'buy') !== false){
        $cmd = '_xclick';
        $button_subtype = 'service';
        $btn_text = 'BuyNow';
        $btn = 'btn_buynow';
        $quantity_field = 'quantity';
    }
    elseif($type=='donation' || $type=='donate'){
        $cmd = '_donations';
        $button_subtype = '';
        $btn_text = 'Donations';
        $btn = 'btn_donate';
        $quantity_field = 'quantity';
    }
    elseif($type=='cart'){
        $cmd = '_cart';
        $button_subtype = 'product';
        $btn_text = 'ShopCart';
        $btn = 'btn_cart';
        $quantity_field = 'quantity';
        $paypal_values['add'] = 1;
    }

    if($btn_size=='large' && $btn!='btn_cart')
        $btn .= ( (strtolower($btn_display_cc)=='no' || empty($btn_display_cc)  ) ) ? '': 'CC';
    else
        $btn .= '';
    
    $btn .= ($btn_size=='large') ? '_LG': '_SM';
    $bn = 'PP-'.$btn_text.'BF:'.$btn.'.gif:NonHostedGuest';
    $btn_src = 'https://www.paypalobjects.com/en_US/i/btn/'.$btn.'.gif';
    
    $paypal_values['cmd'] = $cmd;
    $paypal_values['item_number'] = $id;
    $paypal_values['business'] = $email;
    $paypal_values['lc'] = 'US';
    $paypal_values['item_name'] = $name;
    $paypal_values['currency_code'] = $currency;
    $paypal_values['no_note'] = (($add_note=='yes') ? 0 :1);
    $paypal_values['bn'] = $bn;
    
    if(!empty($button_subtype))
        $paypal_values['button_subtype'] = $button_subtype;
    
    if(!empty($amount))
        $paypal_values['amount'] = $amount;

    if(!empty($tax_rate))
        $paypal_values['tax_rate'] = $tax_rate;

    if(!empty($shipping_charges))
        $paypal_values['shipping'] = $shipping_charges;

    if(!empty($checkout_logo_url))
        $paypal_values['cpp_header_image'] = $checkout_logo_url;
    
    if(!empty($checkout_header_bg_color))
        $paypal_values['cpp_headerback_color'] = $checkout_header_bg_color;

    if(!empty($checkout_header_border_color))
        $paypal_values['cpp_headerborder_color'] = $checkout_header_border_color;

    if(!empty($checkout_bg_color))
        $paypal_values['cpp_payflow_color'] = $checkout_bg_color;

    if(!empty($thankyou_page_url))
        $paypal_values['return'] = $thankyou_page_url;

    if($echo_link) {
        
        if(!empty($quantity) && is_numeric($quantity))
            $paypal_values[$quantity_field] = $quantity;
        else
            $paypal_values[$quantity_field] = 1;
        
        $output = 'https://www.paypal.com/cgi-bin/webscr?'.http_build_query($paypal_values);
    }
    else {
        $output = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">';
        foreach($paypal_values as $name =>$val){
            switch($name){
                case 'amount':
                    $output .= '<input type="hidden" class="paypal_amount" name="'.$name.'" value="'.$val.'">';
                    $output .= '<input type="hidden" class="temp_amount" name="temp_'.$name.'" value="'.$val.'">';
                    break;
                default:
                    $output .= '<input type="hidden" name="'.$name.'" value="'.$val.'">';
            }
        }
        if(is_numeric($quantity))
             $output .= '<input type="hidden" name="'.$quantity_field.'" value="'.$quantity.'">';
        else if(strpos($quantity, '-')!==false){

            $quantity = explode('-',$quantity);
            if(is_numeric($quantity[0]) && is_numeric($quantity[1]) && $quantity[0]<$quantity[1]) {
                $output .= '<select name="'.$quantity_field.'" class="paypal_quantity">';
                for($i=$quantity[0]; $i<=$quantity[1]; $i++)
                    $output .= '<option value="'.$i.'"> '.$i.$quantity_txt_postfix.' </option>';
                $output .= '</select>'.html_entity_decode($field_sep);
            }
            else
                $output .= '<input type="hidden" name="'.$quantity_field.'" value="1">';
        }
        else if(strpos($quantity, ',')){
            $quantity = explode(',',$quantity);
            if(count($quantity)>0) {
                $output .= '<select name="'.$quantity_field.'" class="paypal_quantity">';
                for($i=0; $i<count($quantity); $i++){
                    if(is_numeric($quantity[$i]))
                        $output .= '<option value="'.$quantity[$i].'"> '.$quantity[$i].$quantity_txt_postfix.' </option>';
                }
                $output .= '</select>'.html_entity_decode($field_sep);
            }
            else
                $output .= '<input type="hidden" name="'.$quantity_field.'" value="1">';
        }
        else if($quantity=="")
            $output .= '<input type="text" name="'.$quantity_field.'" value="1" class="paypal_quantity">';
        else
            $output .= '<input type="hidden" name="'.$quantity_field.'" value="1">';

        $rand = rand(111,999);
        $output .= '
        <input type="image" src="'.$btn_src.'" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" 
            onclick="return adjustPayPalQuantity'.$rand.'(this);">
        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
        ';
        if(!empty($quantity) && ($type=='donation' || $type=='donate') ){
            $output .= '
            <script type="text/javascript">
                function adjustPayPalQuantity'.$rand.'(elem){
                    if(jQuery(elem).siblings(".paypal_quantity")) {
                        var qty = jQuery(elem).siblings(".paypal_quantity").val();
                        var amount = jQuery(elem).siblings(".temp_amount").val();
                        jQuery(elem).siblings(".paypal_amount").val(qty * amount);
                        return true;
                    }
                }
            </script>';
        }
		
    }
    
    return $output;
}  

add_shortcode("paypal_button", "wpdev_paypal_button");

// Plugin Activation Hook
function wpdev_paypal_button_activate(){
    // Check if its a first install
    $alreadyInstalled =get_option('wpdev_paypal_button_type');
    if(empty($alreadyInstalled)){
        add_option( 'wpdev_paypal_button_type', 'paynow' );
        add_option( 'wpdev_paypal_button_currency', 'USD' );
        add_option( 'wpdev_paypal_button_add_note', 'no' );
        add_option( 'wpdev_paypal_button_display_cc', 'yes' );
        add_option( 'wpdev_paypal_button_size', 'large' );
    }
}
register_activation_hook( __FILE__, 'wpdev_paypal_button_activate' );