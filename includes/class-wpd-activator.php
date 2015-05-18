<?php

/**
 * Fired during plugin activation
 *
 * @link       http://orionorigin.com
 * @since      3.0
 *
 * @package    Wpd
 * @subpackage Wpd/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      3.0
 * @package    Wpd
 * @subpackage Wpd/includes
 * @author     ORION <support@orionorigin.com>
 */
class Wpd_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    3.0
	 */
	public static function activate() {
            GLOBAL $wp_rewrite;
            add_option('wpc_do_activation_redirect', true);            
            $wp_rewrite->flush_rules();
	}

}
