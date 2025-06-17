<?php
/**
 * Handle and help with settings..
 *
 * @package mfm
 */

namespace MFM\Helpers;

use \MFM\DB_Handler; // phpcs:ignore

/**
 * Utility file and directory functions.
 */
class Settings_Helper {


	/**
	 * Array of settings.
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Site Content.
	 *
	 * Site content setting keeps track of plugins, themes, and
	 * other necessary information required during file changes
	 * monitoring scan.
	 *
	 * @var string
	 */
	public static $site_content = 'site-content';

	/**
	 * Return plugin setting.
	 *
	 * @param string $setting - Setting name.
	 * @param mixed  $default_value - Default setting value.
	 * @return mixed
	 */
	public static function get_setting( $setting, $default_value = false ) {
		if ( ! isset( self::$settings[ $setting ] ) ) {
			self::$settings[ $setting ] = get_site_option( MFM_PREFIX . $setting, $default_value );
		}

		if ( 'debug-logging-enabled' === $setting && defined( 'MFM_DEV_MODE' ) ) {
			return 'yes';
		}

		return self::$settings[ $setting ];
	}

	/**
	 * Save plugin setting.
	 *
	 * @param string $setting - Setting name.
	 * @param mixed  $value   - Setting value.
	 */
	public static function save_setting( $setting, $value ) {

		if ( 'logging-enabled' === $setting && 'no' === $value ) {
			if ( 'yes' === get_site_option( MFM_PREFIX . $setting ) ) {
				DB_Handler::cancel_current_scan();
			}
		}

		update_site_option( MFM_PREFIX . $setting, $value, false );
		self::$settings[ $setting ] = $value;
		delete_transient( MFM_PREFIX . 'options' );
	}

	/**
	 * Remove plugin setting.
	 *
	 * @param string $setting - Setting name.
	 */
	public static function delete_setting( $setting ) {
		delete_site_option( MFM_PREFIX . $setting );
		unset( self::$settings[ $setting ] );
	}

	/**
	 * Get default value for a given setting,
	 *
	 * @param string $setting_key - Lookiup key.
	 * @return mixed - Value
	 */
	public static function get_settings_default_value( $setting_key ) {
		$content_dir                 = trailingslashit( WP_CONTENT_DIR );
		$default_excluded_dirs       = array( $content_dir . 'cache', $content_dir . 'upgrade' );
		$default_excluded_extensions = array( 'pdf', 'jpg', 'jpeg', 'png', 'bmp', 'txt', 'log', 'mo', 'po', 'mp3', 'wav', 'gif', 'ico', 'jpe', 'psd', 'raw', 'svg', 'tif', 'tiff', 'aif', 'flac', 'm4a', 'oga', 'ogg', 'ra', 'wma', 'asf', 'avi', 'mkv', 'mov', 'mp4', 'mpe', 'mpeg', 'mpg', 'ogv', 'qt', 'rm', 'vob', 'webm', 'wm', 'wmv', 'json', 'DS_Store' );
		$default_excluded_files      = array( 'wp-config-sample.php' );
		$base_paths_to_scan          = Directory_And_File_Helpers::get_directories_from_path( ABSPATH );
		$base_paths_to_scan          = array_push( $base_paths_to_scan, ABSPATH );

		$defaults = array(
			'logging-enabled'              => 'yes',
			'scan-frequency'               => 'daily',
			'scan-hour'                    => '02',
			'scan-day'                     => '1',
			'scan-date'                    => '01',

			'base_paths_to_scan'           => $base_paths_to_scan,
			'excluded_file_extensions'     => $default_excluded_extensions,
			'excluded_directories'         => $default_excluded_dirs,
			'excluded_files'               => $default_excluded_files,
			'allowed-in-core-dirs'         => array(),
			'allowed-in-core-files'        => array(
				'wp-config.php',
				'.htaccess',
				'bing.xml',
				'sitemap.xml',
				'readme.html',
				'.ftpquota',
				'robots.txt',
				'license.txt',
			),
			'enabled-notifications'        => array(
				'added',
				'deleted',
				'modified',
			),
			'email_notice_type'            => 'admin',
			'custom_email_address'         => '',
			'email-changes-limit'          => 10,
			'send-email-upon-changes'      => 'yes',
			'empty-email-allowed'          => 'no',
			'debug-logging-enabled'        => 'no',
			'delete-data-enabled'          => 'no',
			'core-scan-enabled'            => 'yes',
			'scan-files-with-no-extension' => 'yes',
			'max-file-size'                => 5,
			'purge-length'                 => 1,
		);

		return ( 'all' === $setting_key ) ? $defaults : $defaults[ $setting_key ];
	}

	/**
	 * Get all the MFM settings.
	 *
	 * @return array - Current settings.
	 */
	public static function get_mfm_settings() {
		return array(
			'logging-enabled'              => self::get_setting( 'logging-enabled', self::get_settings_default_value( 'logging-enabled' ) ),
			'scan-frequency'               => self::get_setting( 'scan-frequency', self::get_settings_default_value( 'scan-frequency' ) ),
			'scan-hour'                    => self::get_setting( 'scan-hour', self::get_settings_default_value( 'scan-hour' ) ),
			'scan-day'                     => self::get_setting( 'scan-day', self::get_settings_default_value( 'scan-day' ) ),
			'scan-date'                    => self::get_setting( 'scan-date', self::get_settings_default_value( 'scan-date' ) ),
			'base_paths_to_scan'           => self::get_setting( 'base_paths_to_scan', self::get_settings_default_value( 'base_paths_to_scan' ) ),
			'excluded_file_extensions'     => self::get_setting( 'excluded_file_extensions', self::get_settings_default_value( 'excluded_file_extensions' ) ),
			'excluded_directories'         => self::get_setting( 'excluded_directories', self::get_settings_default_value( 'excluded_directories' ) ),
			'excluded_files'               => self::get_setting( 'excluded_files', self::get_settings_default_value( 'excluded_files' ) ),
			'allowed-in-core-dirs'         => self::get_setting( 'allowed-in-core-dirs', self::get_settings_default_value( 'allowed-in-core-dirs' ) ),
			'allowed-in-core-files'        => self::get_setting( 'allowed-in-core-files', self::get_settings_default_value( 'allowed-in-core-files' ) ),
			'enabled-notifications'        => self::get_setting( 'enabled-notifications', self::get_settings_default_value( 'enabled-notifications' ) ),
			'email_notice_type'            => self::get_setting( 'email_notice_type', self::get_settings_default_value( 'scan-frequency' ) ),
			'custom_email_address'         => self::get_setting( 'custom_email_address', self::get_settings_default_value( 'custom_email_address' ) ),
			'email-changes-limit'          => self::get_setting( 'email-changes-limit', self::get_settings_default_value( 'email-changes-limit' ) ),
			'send-email-upon-changes'      => self::get_setting( 'send-email-upon-changes', self::get_settings_default_value( 'send-email-upon-changes' ) ),
			'empty-email-allowed'          => self::get_setting( 'empty-email-allowed', self::get_settings_default_value( 'empty-email-allowed' ) ),
			'debug-logging-enabled'        => self::get_setting( 'debug-logging-enabled', self::get_settings_default_value( 'debug-logging-enabled' ) ),
			'delete-data-enabled'          => self::get_setting( 'delete-data-enabled', self::get_settings_default_value( 'delete-data-enabled' ) ),
			'core-scan-enabled'            => self::get_setting( 'core-scan-enabled', self::get_settings_default_value( 'core-scan-enabled' ) ),
			'max-file-size'                => self::get_setting( 'max-file-size', self::get_settings_default_value( 'max-file-size' ) ),
			'scan-files-with-no-extension' => self::get_setting( 'scan-files-with-no-extension', self::get_settings_default_value( 'scan-files-with-no-extension' ) ),
			'purge-length'                 => self::get_setting( 'purge-length', self::get_settings_default_value( 'purge-length' ) ),

		);
	}

	/**
	 * Is AM or PM?
	 *
	 * @return bool True is current WordPress time format is an AM/PM
	 */
	public static function is_time_format_am_pm() {
		return ( 1 === preg_match( '/[aA]$/', get_option( 'time_format' ) ) );
	}

	/**
	 * Get desired email address for use in the notification.
	 *
	 * @return string - email to use.
	 */
	public static function get_notification_email() {
		if ( 'admin' !== self::get_setting( 'email_notice_type', 'admin' ) && ! empty( self::get_setting( 'custom_email_address', '' ) ) ) {
			return self::get_setting( 'custom_email_address' );
		} else {
			return get_bloginfo( 'admin_email' );
		}
	}

	/**
	 * Get system info.
	 *
	 * NOTE: This report contains info that is intentionally not translated. The
	 * report strings should always be as statically defined here.
	 *
	 * @return string
	 */
	public static function get_system_info() {
		// System info.
		global $wpdb;

		$sysinfo = '### System Info → Begin ###' . "\n\n";

		// Start with the basics.
		$sysinfo .= '-- Site Info --' . "\n\n";
		$sysinfo .= 'Site URL (WP Address):    ' . site_url() . "\n";
		$sysinfo .= 'Home URL (Site Address):  ' . home_url() . "\n";
		$sysinfo .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

		// Get theme info.
		$theme_data   = wp_get_theme();
		$theme        = $theme_data->name . ' ' . $theme_data->version;
		$parent_theme = $theme_data->template;
		if ( ! empty( $parent_theme ) ) {
			$parent_theme_data = wp_get_theme( $parent_theme );
			$parent_theme      = $parent_theme_data->name . ' ' . $parent_theme_data->version;
		}

		// Language information.
		$locale = get_locale();

		// WordPress configuration.
		$sysinfo .= "\n" . '-- WordPress Configuration --' . "\n\n";
		$sysinfo .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
		$sysinfo .= 'Language:                 ' . ( ! empty( $locale ) ? $locale : 'en_US' ) . "\n";
		$sysinfo .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
		$sysinfo .= 'Active Theme:             ' . $theme . "\n";
		if ( $parent_theme !== $theme ) {
			$sysinfo .= 'Parent Theme:             ' . $parent_theme . "\n";
		}
		$sysinfo .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

		// Only show page specs if frontpage is set to 'page'.
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$front_page_id = (int) get_option( 'page_on_front' );
			$blog_page_id  = (int) get_option( 'page_for_posts' );

			$sysinfo .= 'Page On Front:            ' . ( 0 !== $front_page_id ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
			$sysinfo .= 'Page For Posts:           ' . ( 0 !== $blog_page_id ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
		}

		$sysinfo .= 'ABSPATH:                  ' . ABSPATH . "\n";
		$sysinfo .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
		$sysinfo .= 'WP Memory Limit:          ' . WP_MEMORY_LIMIT . "\n";

		// Get plugins that have an update.
		$updates = get_plugin_updates();

		// Must-use plugins.
		// NOTE: MU plugins can't show updates!
		$muplugins = get_mu_plugins();
		if ( count( $muplugins ) > 0 ) {
			$sysinfo .= "\n" . '-- Must-Use Plugins --' . "\n\n";

			foreach ( $muplugins as $plugin => $plugin_data ) {
				$sysinfo .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
			}
		}

		// WordPress active plugins.
		$sysinfo .= "\n" . '-- WordPress Active Plugins --' . "\n\n";

		$plugins        = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( ! in_array( $plugin_path, $active_plugins ) ) { // phpcs:ignore
				continue;
			}

			$update   = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$sysinfo .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		// WordPress inactive plugins.
		$sysinfo .= "\n" . '-- WordPress Inactive Plugins --' . "\n\n";

		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( in_array( $plugin_path, $active_plugins ) ) { // phpcs:ignore
				continue;
			}

			$update   = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$sysinfo .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		if ( is_multisite() ) {
			// WordPress Multisite active plugins.
			$sysinfo .= "\n" . '-- Network Active Plugins --' . "\n\n";

			$plugins        = wp_get_active_network_plugins();
			$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

			foreach ( $plugins as $plugin_path ) {
				$plugin_base = plugin_basename( $plugin_path );

				if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
					continue;
				}

				$update   = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
				$plugin   = get_plugin_data( $plugin_path );
				$sysinfo .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
			}
		}

		// Server configuration.
		$sysinfo .= "\n" . '-- Webserver Configuration --' . "\n\n";
		$sysinfo .= 'PHP Version:              ' . PHP_VERSION . "\n";
		$sysinfo .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";

		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : false;

		if ( $server_software ) {
			$sysinfo .= 'Webserver Info:           ' . $server_software . "\n";
		} else {
			$sysinfo .= 'Webserver Info:           Global $_SERVER array is not set.' . "\n";
		}

		// PHP configs.
		$sysinfo .= "\n" . '-- PHP Configuration --' . "\n\n";
		$sysinfo .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
		$sysinfo .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
		$sysinfo .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
		$sysinfo .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
		$sysinfo .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
		$sysinfo .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
		$sysinfo .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

		// MFM options.
		$sysinfo .= "\n" . '-- MFM Options --' . "\n\n";
		$options  = self::get_mfm_settings();

		if ( ! empty( $options ) && is_array( $options ) ) {
			foreach ( $options as $name => $value ) {
				$sysinfo .= 'Option: ' . $name . "\n";
				if ( is_array( $value ) ) {
					$sysinfo .= 'Value:  ' . implode( $value ) . "\n\n";
				} else {
					$sysinfo .= 'Value:  ' . $value . "\n\n";
				}
			}
		}

		$sysinfo .= "\n" . '### System Info → End ###' . "\n\n";

		return $sysinfo;
	}

			/**
		 * Date format based on WordPress date settings. It can be optionally sanitized to get format compatible with
		 * JavaScript date and time picker widgets.
		 *
		 * Note: This function must not be used to display actual date and time values anywhere. For that use function GetDateTimeFormat.
		 *
		 * @param bool $sanitized If true, the format is sanitized for use with JavaScript date and time picker widgets.
		 *
		 * @return string
		 */
		public static function get_date_format( $sanitized = false ) {
			if ( $sanitized ) {
				return 'Y-m-d';
			}

			return get_option( 'date_format' );
		}

		/**
		 * Time format based on WordPress date settings. It can be optionally sanitized to get format compatible with
		 * JavaScript date and time picker widgets.
		 *
		 * Note: This function must not be used to display actual date and time values anywhere. For that use function GetDateTimeFormat.
		 *
		 * @param bool $sanitize If true, the format is sanitized for use with JavaScript date and time picker widgets.
		 *
		 * @return string
		 */
		public static function get_time_format( $sanitize = false ) {
			$result = get_option( 'time_format' );
			if ( $sanitize ) {
				$search  = array( 'a', 'A', 'T', ' ' );
				$replace = array( '', '', '', '' );
				$result  = str_replace( $search, $replace, $result );
			}

			return $result;
		}

		/**
		 * Determines datetime format to be displayed in any UI in the plugin (logs in administration, emails, reports,
		 * notifications etc.).
		 *
		 * Note: Format returned by this function is not compatible with JavaScript date and time picker widgets. Use
		 * functions GetTimeFormat and GetDateFormat for those.
		 *
		 * @param bool $line_break             - True if line break otherwise false.
		 * @param bool $use_nb_space_for_am_pm - True if non-breakable space should be placed before the AM/PM chars.
		 *
		 * @return string
		 */
		public static function get_datetime_format( $line_break = true, $use_nb_space_for_am_pm = true ) {
			$result = self::get_date_format();

			$result .= $line_break ? '<\b\r>' : ' ';

			$time_format    = self::get_time_format();
			$has_am_pm      = false;
			$am_pm_fraction = false;
			$am_pm_pattern  = '/(?i)(\s+A)/';
			if ( preg_match( $am_pm_pattern, $time_format, $am_pm_matches ) ) {
				$has_am_pm      = true;
				$am_pm_fraction = $am_pm_matches[0];
				$time_format    = preg_replace( $am_pm_pattern, '', $time_format );
			}

			// Check if the time format does not have seconds.
			if ( false === stripos( $time_format, 's' ) ) {
				$time_format .= ':s'; // Add seconds to time format.
			}

			if ( $has_am_pm ) {
				$time_format .= preg_replace( '/\s/', $use_nb_space_for_am_pm ? '&\n\b\s\p;' : ' ', $am_pm_fraction );
			}

			$result .= $time_format;

			return $result;
		}
}
