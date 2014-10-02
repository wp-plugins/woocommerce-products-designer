<?php
/*
Plugin Name: Woocommerce Products Customizer
Plugin URI: http://orionorigin.com
Description: A super simple woocommerce extension which helps the customer to design his perfect product (shirts, shoes, business cards...) by adding text, images, shapes before order.
Version: 1.0
Author: ORION
Author URI: http://orionorigin.com
*/
define( 'WPC_URL', plugins_url('/', __FILE__) );
define( 'WPC_DIR', dirname(__FILE__) );

require_once( WPC_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'cliparts.php' );
require_once( WPC_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'functions.plugin.php' );
require_once( WPC_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'functions.tpl.php' );
require_once( WPC_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'hybridauth' . DIRECTORY_SEPARATOR . 'Hybrid' . DIRECTORY_SEPARATOR . 'Auth.php' );
require_once( WPC_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php' );

// Activation, uninstall
register_activation_hook( __FILE__, 'wpc_install' );
register_deactivation_hook ( __FILE__, 'wpc_uninstall' );

function wpc_Init() {
}

add_action( 'init', 'wpc_load_translations' );
function wpc_load_translations() {
    load_plugin_textdomain ( 'wpc', false, basename(rtrim(dirname(__FILE__), '/')) . '/languages/' );
 
}

add_action('init', 'init_sessions',1);
function init_sessions() {
    if (!session_id()) {
        session_start();
    }
    
    if(!isset($_SESSION["wpc_generated_data"]))
        $_SESSION["wpc_generated_data"]=array();
}
    
?>