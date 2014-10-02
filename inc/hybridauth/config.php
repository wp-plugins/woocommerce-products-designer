<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/

// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------

return 
	array(
		"base_url" => WPC_URL.'/inc/hybridauth/', 

		"providers" => array ( 
			// openid providers
			"OpenID" => array (
				"enabled" => false
			),

			"Yahoo" => array ( 
				"enabled" => true,
				"keys"    => array ( "key" => get_option('wpc-yahoo-app-id'), "id" => get_option('wpc-yahoo-app-id'), "secret" =>get_option('wpc-yahoo-app-secret') ),
			),

			"AOL"  => array ( 
				"enabled" => false 
			),

			"Google" => array ( 
				"enabled" => true,
				"keys"    => array ( "id" => get_option('wpc-google-app-id'), "secret" => get_option('wpc-google-app-secret') ), 
			),

			"Facebook" => array ( 
				"enabled" => true,
				"keys"    => array ( "id" => get_option('wpc-facebook-app-id'), "secret" => get_option('wpc-facebook-app-secret') ), 
			),

			"Twitter" => array ( 
				"enabled" => true,
				"keys"    => array ( "key" => get_option('wpc-twitter-key'), "secret" => get_option('wpc-twitter-secret') ) 
			),

			// windows live
			"Live" => array ( 
				"enabled" => true,
				"keys"    => array ( "id" => get_option('wpc-live-app-id'), "secret" => get_option('wpc-live-app-secret') ) 
			),

			"MySpace" => array ( 
				"enabled" => false,
				"keys"    => array ( "key" => "", "secret" => "" ) 
			),

			"LinkedIn" => array ( 
				"enabled" => false,
				"keys"    => array ( "key" => "", "secret" => "" ) 
			),

			"Foursquare" => array (
				"enabled" => false,
				"keys"    => array ( "id" => "", "secret" => "" ) 
			),
                        "Instagram" => array(
                            "enabled" => true,
                            "keys"    => array ( "id" => get_option('wpc-instagram-app-id'), "secret" => get_option('wpc-instagram-app-secret') ), 
                        )
		),

		// if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
		"debug_mode" => false,

		"debug_file" => "",
	);
