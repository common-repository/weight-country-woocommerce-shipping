<?php
/*
	Plugin Name: WooCommerce Shipping Pro with Table Rate (BASIC)
    Plugin URI: https://www.xadapter.com/product/woocommerce-table-rate-shipping-pro-plugin/
    Description: User friendly Weight and Country based WooCommerce Shipping plug-in. Upgrade to Shipping Pro No.1 WooCommerce Shipping Plugin. Configure your shipping with the help of our experts!. 30 Day no question asked refund. 
    Version: 2.0.7
    Author: PluginHive
    Author URI: https://www.pluginhive.com/
    Copyright: 2014-2018 PluginHive.
    License: GPLv2 or later
    License URI: http://www.gnu.org/licenses/gpl-2.0.html
    WC requires at least: 2.6.0
    WC tested up to: 3.4

*/
    
function wf_shipping_pro_basic_activatoin_check(){
    
    //check if basic version is there
    if ( is_plugin_active('woocommerce-shipping-pro/woocommerce-shipping-pro.php') ){
        deactivate_plugins( basename( __FILE__ ) );
        wp_die(__("Is everything fine? You already have the Premium version installed in your website. For any issues, kindly raise a ticket via <a target='_blank' href='//support.xadapter.com/'>support.xadapter.com</a>", "wf_estimated_delivery"), "", array('back_link' => 1 ));
    }
}
register_activation_hook( __FILE__, 'wf_shipping_pro_basic_activatoin_check' );

function wf_weight_country_plugin_action_links( $links ) {
    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wf_woocommerce_shipping_pro' ) . '">' . __( 'Settings', 'wf_country_weight_shipping' ) . '</a>',
        '<a href="https://wordpress.org/support/plugin/weight-country-woocommerce-shipping" target="_blank">' . __( 'Support', 'wf_country_weight_shipping' ) . '</a>',
        '<a href="https://www.xadapter.com/product/woocommerce-table-rate-shipping-pro-plugin/"  target="_blank">' . __( 'Premium Upgrade', 'woocommerce-shipment-tracking' ) . '</a>',
    );
    return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wf_weight_country_plugin_action_links' );


load_plugin_textdomain( 'wf_woocommerce_shipping_pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {	

    include( 'wf-shipping-pro-common.php' );
   
    if (!function_exists('wf_plugin_configuration')){
       function wf_plugin_configuration(){
            return array(
                'id' => 'wf_woocommerce_shipping_pro',
                'method_title' => __('Shipping Pro', 'wf_woocommerce_shipping_pro' ),
                'method_description' => __('Intuitive Rule Based Shipping Plug-in for WooCommerce. Set shipping rates based on rules based by Country, State, Post Code, Product Category,Shipping Class and Weight.', 'wf_woocommerce_shipping_pro' ));		
        }
    }

}

register_activation_hook( __FILE__, 'wf_plugin_activate' );

