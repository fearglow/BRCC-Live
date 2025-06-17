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
class LoginAsUser_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	private $plugin_name_clean = 'login-as-user-pro';

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * This fields
	 *
	 * @var [class]
	 */
	public $fields;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() 
	{
		wp_enqueue_style( $this->plugin_name_clean, plugin_dir_url( __FILE__ ) . 'css/admin.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() 
	{
		wp_enqueue_script( $this->plugin_name_clean, plugin_dir_url( __FILE__ ) . 'js/admin.min.js', array( 'jquery', ), $this->version, true );
		wp_localize_script( $this->plugin_name_clean, 'loginasuserAjax', array( 'loginasuser_ajaxurl' => admin_url( 'admin-ajax.php' )));        
	}

	
}