<?php
/*
Plugin Name: Paypal Pay, Buy, Donation and Cart Buttons Shortcode
Plugin URI: http://mohsinrasool.wordpress.com/paypal-pay-buy-donation-cart-button-wordpress-shortcode
Description: Add a "paypal_button" shortcode to display pay now, buy now, donation and add to cart PayPal buttons with facility to customize they paypal checkout page.
Version: 1.0
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
    }
    elseif(strpos(strtolower($type),'buy') !== false){
        $cmd = '_xclick';
        $button_subtype = 'service';
        $btn_text = 'BuyNow';
        $btn = 'btn_buynow';
    }
    elseif($type=='donation' || $type=='donate'){
        $cmd = '_donations';
        $button_subtype = '';
        $btn_text = 'Donations';
        $btn = 'btn_donate';
    }
    elseif($type=='cart'){
        $cmd = '_cart';
        $add = '1';
        $button_subtype = 'product';
        $btn_text = 'ShopCart';
        $btn = 'btn_cart';
    }

    if($btn_size=='large' && $btn!='btn_cart') {
        $btn .= ( (strtolower($btn_display_cc)=='no' || empty($btn_display_cc)  ) ) ? '': 'CC';
    }
    else
        $btn .= '';
    
    $btn .= ($btn_size=='large') ? '_LG': '_SM';
    $bn = 'PP-'.$btn_text.'BF:'.$btn.'.gif:NonHostedGuest';
    $btn_src = 'https://www.paypalobjects.com/en_US/i/btn/'.$btn.'.gif';
    
    $output = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="'.$cmd.'">
<input type="hidden" name="business" value="'.$email.'">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="item_name" value="'.$name.'">
<input type="hidden" name="item_number" value="'.$id.'">
<input type="hidden" name="currency_code" value="'.$currency.'">
<input type="hidden" name="no_note" value="'.(($add_note=='yes') ? 0 :1).'">';
    
    if(!empty($button_subtype))
        $output .= '<input type="hidden" name="button_subtype" value="'.$button_subtype.'">';
    if(!empty($amount))
        $output .= '<input type="hidden" name="amount" value="'.$amount.'">';
    if(!empty($tax_rate))
        $output .= '<input type="hidden" name="tax_rate" value="'.$tax_rate.'">';
    if(!empty($shipping_charges))
        $output .= '<input type="hidden" name="shipping" value="'.$shipping_charges.'">';
    if(!empty($add))
        $output .= '<input type="hidden" name="add" value="'.$add.'">';
    if(!empty($checkout_logo_url))
        $output .= '<input type="hidden" name="cpp_header_image" value="'.$checkout_logo_url.'">';
    if(!empty($checkout_header_bg_color))
        $output .= '<input type="hidden" name="cpp_headerback_color" value="'.$checkout_header_bg_color.'">';
    if(!empty($checkout_header_border_color))
        $output .= '<input type="hidden" name="cpp_headerborder_color" value="'.$checkout_header_border_color.'">';
    if(!empty($checkout_bg_color))
        $output .= '<input type="hidden" name="cpp_payflow_color" value="'.$checkout_bg_color.'">';
       
    if(!empty($thankyou_page_url))
        $output .= '<input type="hidden" name="return" value="'.$thankyou_page_url.'">';
    
$output .= '<input type="hidden" name="bn" value="'.$bn.'">
<input type="image" src="'.$btn_src.'" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
';
		
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