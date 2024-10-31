<?php if(!defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function gen_wops_admin_menue(){
  $wops_icon_url= GEN_WOPS_BASE_URL . '/images/logo.jpg';
  add_object_page('Woo-OnePage Shop', 'Woo-OnePage Shop', 'edit_theme_options', __FILE__, 'gen_wops_setting',$wops_icon_url);
  add_submenu_page( __FILE__, 'OnePage Shop','OnePage Shop', 'edit_theme_options', __FILE__,'gen_wops_setting');
  add_submenu_page( __FILE__, 'PRO Version','PRO Version', 'edit_theme_options', 'pro-version','gen_wops_pro_version');  
}


function gen_wops_install(){
  
}
function gen_wops_uninstall(){}

add_action('admin_menu', 'gen_wops_admin_menue');
