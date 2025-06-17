<?php
/* ======================================================
 # Login as User for WordPress - v1.5.2 (free version)
 # -------------------------------------------------------
 # For WordPress
 # Author: Web357
 # Copyright © 2014-2024 Web357. All rights reserved.
 # License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
 # Website: https://www.web357.com/product/login-as-user-wordpress-plugin
 # Demo: https://demo-wordpress.web357.com/try-the-login-as-a-user-wordpress-plugin/
 # Support: https://www.web357.com/support
 # Last modified: Sunday 30 June 2024, 10:34:39 PM
 ========================================================= */
/**
 * Define the internationalization functionality
 */
class LoginAsUser_i18n {

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'login-as-user',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages'
		);

	}
}