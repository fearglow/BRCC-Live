<?php
/**
 * Handle plugin and theme updates.
 *
 * @package mfm
 */

namespace MFM;

use \MFM\Helpers\Directory_And_File_Helpers; // phpcs:ignore

/**
 * Plugin and theme monitoring.
 */
class Plugins_And_Themes_Monitor {

	/**
	 * Undocumented variable
	 *
	 * @var array
	 */
	private static $old_themes = array();

	/**
	 * Undocumented variable
	 *
	 * @var array
	 */
	private static $old_plugins = array();

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public static function init() {
		$has_permission = ( current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins' ) || current_user_can( 'delete_plugins' ) || current_user_can( 'update_plugins' ) || current_user_can( 'install_themes' ) );
		add_action( 'admin_init', array( __CLASS__, 'event_admin_init' ) );
		if ( $has_permission ) {
			add_action( 'shutdown', array( __CLASS__, 'event_admin_shutdown' ) );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public static function event_admin_init() {
		self::$old_themes  = wp_get_themes();
		self::$old_plugins = get_plugins();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public static function event_admin_shutdown() {

		// Filter global arrays for security.
		$post_array  = filter_input_array( INPUT_POST );
		$get_array   = filter_input_array( INPUT_GET );
		$script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) : false;

		$action = '';
		if ( isset( $get_array['action'] ) && '-1' !== $get_array['action'] ) {
			$action = $get_array['action'];
		} elseif ( isset( $post_array['action'] ) && '-1' !== $post_array['action'] ) {
			$action = $post_array['action'];
		}

		if ( isset( $get_array['action2'] ) && '-1' !== $get_array['action2'] ) {
			$action = $get_array['action2'];
		} elseif ( isset( $post_array['action2'] ) && '-1' !== $post_array['action2'] ) {
			$action = $post_array['action2'];
		}

		$actype = '';
		if ( ! empty( $script_name ) ) {
			$actype = basename( $script_name, '.php' );
		}

		$is_plugins = 'plugins' === $actype;

		// Install plugin.
		if ( in_array( $action, array( 'install-plugin', 'upload-plugin', 'wsal_run_addon_install' ), true ) && current_user_can( 'install_plugins' ) ) {
			$plugin = array_merge( array_diff( array_keys( get_plugins() ), array_keys( self::$old_plugins ) ), array_diff( array_keys( self::$old_plugins ), array_keys( get_plugins() ) ) );

			if ( ! count( $plugin ) ) {
				/**
				 * No changed plugins - there is nothing we suppose to log.
				 */
				return;
			}

			self::update_plugins_and_themes_list();
		}

		// Uninstall plugin.
		if ( $is_plugins && in_array( $action, array( 'delete-selected' ), true ) && current_user_can( 'delete_plugins' ) ) {
            if ( ! isset( $post_array['verify-delete'] ) ) { // phpcs:ignore
				// First step, before user approves deletion
				// TODO store plugin data in session here.
			} else {
				// second step, after deletion approval
				// TODO use plugin data from session.
				// self::update_plugins_and_themes_list(); .
			}
		}

		// Upgrade plugin.
		if ( in_array( $action, array( 'upgrade-plugin', 'update-plugin', 'update-selected' ), true ) && current_user_can( 'update_plugins' ) ) {
			$plugins = array();

			// Check $_GET array cases.
			if ( isset( $get_array['plugins'] ) ) {
				$plugins = explode( ',', $get_array['plugins'] );
			} elseif ( isset( $get_array['plugin'] ) ) {
				$plugins[] = $get_array['plugin'];
			}

			// Check $_POST array cases.
			if ( isset( $post_array['plugins'] ) ) {
				$plugins = explode( ',', $post_array['plugins'] );
			} elseif ( isset( $post_array['plugin'] ) ) {
				$plugins[] = $post_array['plugin'];
			}
			if ( isset( $plugins ) ) {
				foreach ( $plugins as $plugin_file ) {
					self::add_to_recent_update_list( 'plugin', $plugin_file );
				}
			}
		}

		// Update theme.
		if ( in_array( $action, array( 'upgrade-theme', 'update-theme', 'update-selected-themes' ), true ) && current_user_can( 'install_themes' ) ) {
			// Themes.
			$themes = array();

			// Check $_GET array cases.
			if ( isset( $get_array['slug'] ) || isset( $get_array['theme'] ) ) {
				$themes[] = isset( $get_array['slug'] ) ? $get_array['slug'] : $get_array['theme'];
			} elseif ( isset( $get_array['themes'] ) ) {
				$themes = explode( ',', $get_array['themes'] );
			}

			// Check $_POST array cases.
			if ( isset( $post_array['slug'] ) || isset( $post_array['theme'] ) ) {
				$themes[] = isset( $post_array['slug'] ) ? $post_array['slug'] : $post_array['theme'];
			} elseif ( isset( $post_array['themes'] ) ) {
				$themes = explode( ',', $post_array['themes'] );
			}
			if ( isset( $themes ) ) {
				foreach ( $themes as $theme_name ) {
					self::add_to_recent_update_list( 'theme', $theme_name );
				}
			}
		}

		// Install theme.
		if ( in_array( $action, array( 'install-theme', 'upload-theme' ), true ) && current_user_can( 'install_themes' ) ) {
			self::update_plugins_and_themes_list();
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param string $plugin - Plugin path.
	 * @return string
	 */
	public static function get_plugin_dir( $plugin ) {
		$position = strpos( $plugin, '/' );
		if ( false !== $position ) {
			$plugin = substr_replace( $plugin, '', $position );
		}
		return $plugin;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public static function update_plugins_and_themes_list() {
		$plugin_list  = Directory_And_File_Helpers::create_plugin_keys();
		$theme_dir    = dirname( get_template_directory() );
		$theme_paths  = Directory_And_File_Helpers::get_directories_from_path( $theme_dir );
		$current_list = get_site_option( MFM_PREFIX . 'plugins_and_themes_history', array() );

		foreach ( $plugin_list as $plugin_path ) {
			if ( ! in_array( $plugin_path, $current_list, true ) ) {
				$current_list[] = $plugin_path;
			}
		}

		foreach ( $theme_paths as $theme_path ) {
			if ( ! in_array( $theme_path, $current_list, true ) ) {
				$current_list[] = $theme_path;
			}
		}

		update_site_option( MFM_PREFIX . 'plugins_and_themes_history', $current_list );
	}

	/**
	 * Add item to tracked list.
	 *
	 * @param string $type - Item type.
	 * @param string $item - Item type.
	 * @return void
	 */
	public static function add_to_recent_update_list( $type, $item ) {
		$current_list = get_site_option( MFM_PREFIX . 'plugins_and_themes_recent_updates', array() );

		if ( 'plugin' === $type ) {
			if ( ! in_array( dirname( $item ), $current_list, true ) ) {
				$current_list[] = dirname( $item );
			}
		} elseif ( ! in_array( $item, $current_list, true ) ) {
			$current_list[] = $item;
		}

		update_site_option( MFM_PREFIX . 'plugins_and_themes_recent_updates', $current_list );
	}
}
