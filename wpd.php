<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://orionorigin.com
 * @since             3.0
 * @package           Wpd
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Products Designer
 * Plugin URI:        http://www.orionorigin.com/woocommerce-product-designer/
 * Description:       The Interactive Way to Customize & Sell Your Products Online!
 * Version:           2.0
 * Author:            ORION
 * Author URI:        http://orionorigin.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpd
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPD_VERSION', '2.0' );
define( 'WPD_URL', plugins_url('/', __FILE__) );
define( 'WPD_DIR', dirname(__FILE__) );
define( 'WPD_MAIN_FILE', 'woocommerce-product-designer/wpd.php' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpd-activator.php
 */
function activate_wpd() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpd-activator.php';
	Wpd_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpd-deactivator.php
 */
function deactivate_wpd() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpd-deactivator.php';
	Wpd_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpd' );
register_deactivation_hook( __FILE__, 'deactivate_wpd' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpd.php';

require_once( WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wpd-clipart.php' );
require_once( WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wpd-editor.php' );
require_once( WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wpd-design.php' );
require_once( WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wpd-retarded-actions.php' );
//if(!class_exists("Hybrid_Auth"))
//require_once( WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'hybridauth' . DIRECTORY_SEPARATOR . 'Hybrid' . DIRECTORY_SEPARATOR . 'Auth.php' );
if(!class_exists("TCPDF"))
require_once( WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    3.0
 */
function run_wpd() {

	$plugin = new Wpd();
	$plugin->run();

}

run_wpd();
