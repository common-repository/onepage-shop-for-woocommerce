<?php if(!defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
Plugin Name: WooCommerce OnePage Shop
Plugin URI: http://www.upscalethought.com
Description: This is a WooCommerce plugin that list all the products on one page and allow users to quickly order products.
Version: 1.0
Author: UpScaleThought
Author URI: http://www.upscalethought.com
*/

define("GEN_WOPS_BASE_URL", WP_PLUGIN_URL.'/'.plugin_basename(dirname(__FILE__)));

function gen_wops_pro_version(){
	include_once('includes/usts-wops-onepage-shop-pro-version.php');
}

include ('includes/gen-wops-admin.php');
include ('includes/gen-wops-view.php');
include ('includes/gen-wops-init.php');



function gen_wops_init(){
  wp_enqueue_style('wops-css',GEN_WOPS_BASE_URL.'/css/gen_wops.css');
  wp_enqueue_style('colorbox-css',GEN_WOPS_BASE_URL.'/css/colorbox.css'); 
  
  wp_enqueue_script('jquery');
  wp_enqueue_script('wcp-jscolor', plugins_url( '/js/colorpicker/jscolor.js', __FILE__ ));
  wp_enqueue_script('wops-tooltip', plugins_url( '/js/wops_tooltip.js', __FILE__ ));    
  wp_enqueue_script('jquery.colorbox', plugins_url( '/js/jquery.colorbox.js', __FILE__ ));
}

add_action('init','gen_wops_init');
register_activation_hook( __FILE__, 'gen_wops_install');
register_deactivation_hook( __FILE__, 'gen_wops_uninstall');