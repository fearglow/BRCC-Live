<?php
/* ======================================================
 # Login as User for WordPress - v1.6.0 (free version)
 # -------------------------------------------------------
 # Author: Web357
 # Copyright © 2014-2024 Web357. All rights reserved.
 # License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
 # Website: https://www.web357.com/login-as-user-wordpress-plugin
 # Demo: https://login-as-user-wordpress-demo.web357.com/wp-admin/
 # Support: https://www.web357.com/support
 # Last modified: Wednesday 02 April 2025, 11:37:26 PM
 ========================================================= */
 
/**
 * Define the internationalization functionality
 */
class LoginAsUser_settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
		$this->fields = new LoginAsUser_fields();
	}

	/**
	 * Adds the option in WordPress Admin menu
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function options_page() 
	{
		add_options_page( 
			esc_html__( 'Login as User settings', 'login-as-user'),
			'Login as User',
			'manage_options', 
			'login-as-user',
			array($this, 'options_page_content') 
		);
	}

	/**
	 * Adds the admin page content
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function options_page_content() 
	{
		include_once(plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings-view.php');
	}

	/**
	 * Function that will validate all fields.
	 */
	public function validateSettings( $fields ) 
	{ 
		$options = get_option( 'login_as_user_options' );
		$valid_fields = array();
		$message = null;
		$type = null;

		// Validate "redirect_to" Field
		$redirect_to = trim( $fields['redirect_to'] );
		$redirect_to = strip_tags( stripslashes( $redirect_to ) );
		$redirect_to_frontend_url = esc_url_raw ( home_url('/') . $redirect_to );

		if (wp_http_validate_url($redirect_to_frontend_url) && substr($redirect_to, 0, 1) != '/' && substr($redirect_to, 0, 1) != '\\' && substr($redirect_to, 0, 8) != 'wp-admin' && substr($redirect_to, 0, 8) != 'wp-login')
		{
			$message = __('Option saved successfully!', 'login-as-user');
			$type = 'updated';
			$valid_fields['redirect_to'] = $redirect_to;
		}
		else 
		{
			$valid_fields['redirect_to'] = '';
			$message  = __('Error. The URL is not valid: ' . home_url('/') . $fields['redirect_to'] . '.', 'login-as-user');
			$message .= (substr($redirect_to, 0, 1) == '/' || substr($redirect_to, 0, 1) == '\\') ? __('<br>Please remove the slash in front of URL.', 'login-as-user') : '';
			$message .= (substr($redirect_to, 0, 8) == 'wp-admin') ? __('<br>You can\'t redirect the user to the admin page.', 'login-as-user') : '';
			$message .= (substr($redirect_to, 0, 8) == 'wp-login') ? __('<br>You can\'t redirect the user to the login page.', 'login-as-user') : '';
			$type = 'error';
		}

		// add_settings_error( $setting, $code, $message, $type )
		add_settings_error('my_option_notice', 'my_option_notice', $message, $type);

		// Validate "login_as_type" Field
		$login_as_type = trim( $fields['login_as_type'] );
		$login_as_type = strip_tags( stripslashes( $login_as_type ) );
		$valid_fields['login_as_type'] = $login_as_type;

		// Validate "login_as_type_characters_limit" Field
		$login_as_type_characters_limit = trim( $fields['login_as_type_characters_limit'] );
		$login_as_type_characters_limit = strip_tags( stripslashes( $login_as_type_characters_limit ) );
		$valid_fields['login_as_type_characters_limit'] = $login_as_type_characters_limit;

		// Validate "message_display_position" Field
		$message_display_position = trim( $fields['message_display_position'] );
		$message_display_position = strip_tags( stripslashes( $message_display_position ) );
		$valid_fields['message_display_position'] = $message_display_position;

		// Validate "show_admin_link_in_topbar" Field
		$show_admin_link_in_topbar = trim( $fields['show_admin_link_in_topbar'] );
		$show_admin_link_in_topbar = strip_tags( stripslashes( $show_admin_link_in_topbar ) );
		$valid_fields['show_admin_link_in_topbar'] = $show_admin_link_in_topbar;

		// Validate "enable_ping_animation" Field
		$enable_ping_animation = trim( $fields['enable_ping_animation'] );
		$enable_ping_animation = strip_tags( stripslashes( $enable_ping_animation ) );
		$valid_fields['enable_ping_animation'] = $enable_ping_animation;

		// Validate "license_key" Field
		$license_key = trim( $fields['license_key'] );
		$license_key = strip_tags( stripslashes( $license_key ) );
		$valid_fields['license_key'] = $license_key;

		// Validate "role_management_assignments" Field
		if (isset($fields['role_management_assignments'])) {
			$role_management_assignments = $fields['role_management_assignments'];
			$sanitized_assignments = array();

			if (is_array($role_management_assignments)) {
				foreach ($role_management_assignments as $manager_role => $managed_roles) {
					$sanitized_manager_role = sanitize_text_field($manager_role);
					if (is_array($managed_roles)) {
						// Check if "None" (value "none") is selected
						if (in_array('none', $managed_roles, true)) {
							// If "none" is selected, ensure it's the only option saved
							$sanitized_assignments[$sanitized_manager_role] = array('none');
						} else {
							// If "none" is not selected, sanitize and filter the roles normally
							$sanitized_managed_roles = array_map('sanitize_text_field', $managed_roles);
							$sanitized_managed_roles = array_filter($sanitized_managed_roles, function($role) {
								global $wp_roles;
								return isset($wp_roles->roles[$role]); // Ensure the role is valid
							});

							// Only add the roles if there are valid selections
							if (!empty($sanitized_managed_roles)) {
								$sanitized_assignments[$sanitized_manager_role] = $sanitized_managed_roles;
							}
						}
					}
				}
			}

			$valid_fields['role_management_assignments'] = $sanitized_assignments;
		} else {
			$valid_fields['role_management_assignments'] = array();
		}

		return apply_filters( 'validateSettings', $valid_fields, $fields);
	}

	/**
	 * Initialize the settings link
	 *
	 * @access   public
	 */
	public function settings_link($links) 
	{
		$link = 'options-general.php?page=' . 'login-as-user';
		$settings_link = '<a href="'.esc_url($link).'">'.esc_html__( 'Settings', 'login-as-user' ).'</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	/**
	 * Initialize the settings page
	 *
	 * @since    3.2.0
	 * @access   public
	 */
	public function settings_init() 
	{
		/**
		 * REGISTER SETTINGS
		 */
		register_setting( 'login-as-user', 'login_as_user_options', array($this, 'validateSettings'));

		/**
		 * SECTIONS
		 */
		add_settings_section(
			'base_settings_section', 
			'', 
			'',
			'login-as-user'
		);

		/**
		 * Define Vars
		 */
		// Type of the Toolbar Content
		$options = get_option( 'login_as_user_options' );

		/**
		 * FIELDS
		 */		
		// Link to redirect after login as user
		add_settings_field( 
			'redirect_to', 
			__( 'URL Redirection', 'login-as-user' ),
			array($this->fields, 'textField'),
			'login-as-user', 
			'base_settings_section',
			[
				'label-for' => 'redirect_to',
				'name' => 'redirect_to',
				'class' => 'license_key',
				'default_value' => '',
				'size' => 60,
				'maxlength' => 250,
				'placeholder' => __('example: my-account/orders', 'login-as-user'),
				'desc' => __('This is the URL path that you (Admin) will be redirected after logging in as a User.<br> For example: maybe you want to be redirected in user\'s orders page to see if an order has been completed successfully or cancelled, or just editing his profile details.<br>Leave it blank if you would like to be redirected to the home page (default)', 'login-as-user'),
				'prefix' => '<span class="lac-input-prefix">'.home_url('/').'</span>'
			]
		);

		// Login as... button
		add_settings_field( 
			'login_as_type', 
			esc_html__( '"Login as...«option»" button', 'login-as-user' ), 
			array($this->fields, 'selectField'),
			'login-as-user', 
			'base_settings_section',
			[
				'id' => 'login_as_type',
				'default_value' => 'user_login',
				'options' => [
					['id' => '0', 'label' => esc_html__( 'Nickname (username)', 'login-as-user' ), 'value' => 'user_login'],
					['id' => '1', 'label' => esc_html__( 'First name', 'login-as-user' ), 'value' => 'user_firstname'],
					['id' => '2', 'label' => esc_html__( 'Last name', 'login-as-user' ), 'value' => 'user_lastname'],
					['id' => '3', 'label' => esc_html__( 'Full name (first & last)', 'login-as-user' ), 'value' => 'user_fullname'],
					['id' => '4', 'label' => esc_html__( 'None (display only the user icon)', 'login-as-user' ), 'value' => 'only_icon'],
				],
				'desc' => __('Choose which string will be displayed on the "Login as User" button.<br>For example Login as «Yiannis», or Login as «Christodoulou», or Login as «Johnathan99», or Login as «Yiannis Christodoulou».', 'login-as-user'),
			]
		);

		// Characters limit of login as name
		add_settings_field( 
			'login_as_type_characters_limit', 
			esc_html__( 'Show only the first X characters on the "Login as...«option»" button', 'login-as-user' ), 
			array($this->fields, 'selectField'),
			'login-as-user', 
			'base_settings_section',
			[
				'id' => 'login_as_type_characters_limit',
				'default_value' => '0',
				'options' => [
					['id' => '0', 'label' => esc_html__('All characters (default)', 'login-as-user'), 'value' => '0'],
					['id' => '1', 'label' => '1', 'value' => '1'],
					['id' => '2', 'label' => '2', 'value' => '2'],
					['id' => '3', 'label' => '3', 'value' => '3'],
					['id' => '4', 'label' => '4', 'value' => '4'],
					['id' => '5', 'label' => '5', 'value' => '5'],
					['id' => '6', 'label' => '6', 'value' => '6'],
					['id' => '7', 'label' => '7', 'value' => '7'],
					['id' => '8', 'label' => '8', 'value' => '8'],
					['id' => '9', 'label' => '9', 'value' => '9'],
					['id' => '10', 'label' => '10', 'value' => '10'],
					['id' => '11', 'label' => '11', 'value' => '11'],
					['id' => '12', 'label' => '12', 'value' => '12'],
					['id' => '13', 'label' => '13', 'value' => '13'],
					['id' => '14', 'label' => '14', 'value' => '14'],
					['id' => '15', 'label' => '15', 'value' => '15'],
				],
				'desc' => __('Show only the first X characters of the username, or first/last name, or full name, on the "Login as...«option»" button. <br>For example, if you choose 5, the button will be displayed as Login as «Yiann...», or Login as «Chris...», or Login as «Johna...», or Login as «Yiann...».', 'login-as-user'),
			]
		);

		// Message Display Position
		add_settings_field( 
			'message_display_position', 
			esc_html__( 'Message Display Position', 'login-as-user' ), 
			array($this->fields, 'selectField'),
			'login-as-user', 
			'base_settings_section',
			[
				'id' => 'message_display_position',
				'default_value' => 'bottom',
				'options' => [
					['id' => 'top', 'label' => esc_html__('Top', 'login-as-user'), 'value' => 'top'],
					['id' => 'bottom', 'label' => esc_html__('Bottom', 'login-as-user'), 'value' => 'bottom'],
					['id' => 'none', 'label' => esc_html__('None', 'login-as-user'), 'value' => 'none']
				],
				'desc' => __('Choose where the login message should appear on the frontend. Only Admins and Managers with "login-as-a-user" privileges can see this message.', 'login-as-user'),
			]
		);

		// Show Admin Link in Topbar
		add_settings_field( 
			'show_admin_link_in_topbar', 
			esc_html__( 'Show Admin Link in Topbar', 'login-as-user' ), 
			array($this->fields, 'selectField'),
			'login-as-user', 
			'base_settings_section',
			[
				'id' => 'show_admin_link_in_topbar',
				'default_value' => '1',
				'options' => [
					['id' => 'yes', 'label' => esc_html__('Yes', 'login-as-user'), 'value' => 'yes'],
					['id' => 'no', 'label' => esc_html__('No', 'login-as-user'), 'value' => 'no'],
				],
				'desc' => __('Display a link in the WordPress topbar to navigate back to dashboard as Admin.', 'login-as-user'),
			]
		);

		add_settings_field( 
			'enable_ping_animation', 
			esc_html__( 'Enable Attention Animation', 'login-as-user' ), 
			array($this->fields, 'selectField'),
			'login-as-user', 
			'base_settings_section',
			[
				'id' => 'enable_ping_animation',
				'default_value' => 'no',
				'options' => [
					['id' => 'yes', 'label' => esc_html__('Yes', 'login-as-user'), 'value' => 'yes'],
					['id' => 'no', 'label' => esc_html__('No', 'login-as-user'), 'value' => 'no'],
				],
				'desc' => __('Show ping animation on the admin button after logging in as a user.', 'login-as-user'),
			]
		);

		// License Key
		 
		add_settings_field( 
			'license_key', 
			esc_html__( 'License Key', 'login-as-user' ),
			array($this->fields, 'hiddenField'),
			'login-as-user', 
			'base_settings_section',
			[
				'label-for' => 'license_key',
				'name' => 'license_key',
				'class' => 'license_key hidden',
				'default_value' => '',
				'size' => 60,
				'maxlength' => 60,
				'placeholder' => __('Enter your license key from web357.com', 'login-as-user'),
				'desc' => __('In order to update commercial Web357 plugins, you have to enter the Web357 License Key.<br>You can find the License Key in your account settings at Web357.com, in the <a href="//www.web357.com/my-account/web357-license-manager" target="_blank"><strong>Web357 License Key Manager</strong></a> section.', 'login-as-user')
			]
		);
		 

		

		
	}
}