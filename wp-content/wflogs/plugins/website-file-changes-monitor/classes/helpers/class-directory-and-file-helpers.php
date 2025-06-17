<?php
/**
 * Helepr class for file and directory tasks.
 *
 * @package mfm
 */

namespace MFM\Helpers;

use \MFM\DB_Handler; // phpcs:ignore
use \MFM\Helpers\Settings_Helper; // phpcs:ignore
use \MFM\Helpers\Logger; // phpcs:ignore

/**
 * Utility file and directory functions.
 */
class Directory_And_File_Helpers {

	/**
	 * Gather directory info.
	 *
	 * @param  string  $path - Lookup path.
	 * @param  boolean $recursive - Is recursive.
	 * @param  array   $filtered - Skip items.
	 * @return array - Results.
	 * @throws RuntimeException - Error.
	 */
	public static function get_directories_from_path( $path, $recursive = false, array $filtered = array() ) {
		if ( ! is_dir( $path ) ) {
			throw new RuntimeException( "$path does not exist." ); // phpcs:ignore
		}

		$filtered += array( '.', '..' );

		$dirs = array();
		$d    = dir( $path );
		while ( ( $entry = $d->read() ) !== false ) { // phpcs:ignore
			if ( ! in_array( $entry, $filtered, true ) ) {
				if ( is_dir( "$path/$entry" ) ) {

					$depth = explode('/', "$path/$entry" );
					$depth = sizeof( $depth )-1;
					if ( MFM_MAX_DEPTH < $depth ) {
						if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
							$msg = Logger::mfm_get_log_timestamp() . ' PATH BEYOND DEPTH LIMIT' . " \n";
							$msg = Logger::mfm_get_log_timestamp() . ' ' . $path . " \n";
							Logger::mfm_write_to_log( $msg );
						}
						continue;
					}

					$path_name = realpath( $path . DIRECTORY_SEPARATOR . $entry );
					$dirs[]    = $path_name;

					if ( $recursive ) {
						$new_dirs = self::get_directories_from_path( "$path/$entry" );
						foreach ( $new_dirs as $new_dir ) {
							$dirs[] = "$entry/$new_dir";
						}
					}
				}
			}
		}

		return $dirs;
	}

	/**
	 * Gather relevant file info for a given path.
	 *
	 * @param  string $root_dir - Gather files and store info.
	 * @param  array  $file_data - Incoming data.
	 * @return array
	 */
	public static function scan_and_store_files( $root_dir, $file_data = array() ) {

		$invisible_file_names = array( '.', '..', '.htaccess', '.htpasswd' );
		// run through content of root directory.
		$dir_content = scandir( $root_dir );

		$ingore_files = Settings_Helper::get_setting( 'excluded_files' );
		$ingore_files = ( is_array( $ingore_files ) ) ? $ingore_files : array();

		foreach ( $dir_content as $key => $content ) {
			// filter all files not accessible.
			$path = $root_dir . '/' . $content;
			if ( ! in_array( $content, $invisible_file_names, true ) ) {

				if ( is_file( $path ) && is_readable( $path ) ) {
					if ( in_array( str_replace( ABSPATH, '', $path ), $ingore_files, true ) ) {
						continue;
					}

					// save file name with path.
					$file_data['paths'][]      = str_replace( ABSPATH, '', $path );
					$file_data['hashs'][]      = md5_file( $path );
					$file_data['timestamps'][] = filemtime( $path );

				}
			}
		}
		return $file_data;
	}

	/**
	 * Gather current WP file hashes for comparison.
	 *
	 * @return array
	 */
	public static function get_core_files_hashes() {
		$version = $GLOBALS['wp_version'];
		$locale  = get_locale();

		// try to load checksum from transient cache.
		$cache_key        = 'mfm_wp_org_checksums_' . $version . '_' . $locale;
		$cached_checksums = get_transient( $cache_key );
		if ( false === $cached_checksums ) {
			$endpoint_url = add_query_arg(
				array(
					'version' => $version,
					'locale'  => $locale,
				),
				'https://api.wordpress.org/core/checksums/1.0/'
			);
			$response     = wp_remote_get( $endpoint_url );
			if ( is_wp_error( $response ) ) {
				return array();
			}

			// plugins/info/1.0/{slug} https://api.wordpress.org/plugins/checksums/1.0/wp-2fa/.

			$body = json_decode( $response['body'], true );
			if ( empty( $body['checksums'] ) || ! is_array( $body['checksums'] ) ) {
				return array();
			}

			$checksums = $body['checksums'];
			set_transient( $cache_key, json_encode( $body['checksums'] ), WEEK_IN_SECONDS ); // phpcs:ignore
		} else {
			// cached value need to be decoded first.
			$checksums = json_decode( $cached_checksums, true );
			if ( ! is_array( $checksums ) ) {
				// empty array is returned if the data is malformed in any way and cannot be decoded as JSON.
				return array();
			}
		}

		return $checksums;
	}

	/**
	 * Create list of paths for all core files.
	 *
	 * @param boolean $return_just_paths - Return paths only.
	 * @param boolean $include_abspath - Include Abspath in result.
	 * @return array - Result.
	 */
	public static function create_core_file_keys( $return_just_paths = false, $include_abspath = true ) {
		$final       = array();
		$plugin_list = self::create_plugin_keys();
		$theme_dir   = dirname( get_template_directory() );

		foreach ( self::get_core_files_hashes() as $core_file => $val ) {
			if ( $include_abspath ) {
				$final[] = ( $return_just_paths ) ? ABSPATH . $core_file : ABSPATH . $core_file . '|' . $val;
			} else {
				$final[] = ( $return_just_paths ) ? $core_file : $core_file . '|' . $val;
			}
		}

		return $final;
	}

	/**
	 * Gather current plugin info.
	 *
	 * @return array $plugins - Result.
	 */
	public static function get_installed_plugin_info() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins = get_plugins();

		return $plugins;
	}

	/**
	 * Create array of paths for current plugin info.
	 *
	 * @param boolean $add_wp_dir - Add WP dir to result.
	 * @return array - Result.
	 */
	public static function create_plugin_keys( $add_wp_dir = true ) {
		$final = array();
		$info  = self::get_installed_plugin_info();

		foreach ( array_keys( $info ) as $plugin ) {
			$final[] = ( $add_wp_dir ) ? dirname( WP_PLUGIN_DIR . '/' . $plugin ) : dirname( $plugin );
		}

		return $final;
	}

	/**
	 * Turn UNIX stamp into a nice string.
	 *
	 * @param int $date - Current time.
	 * @return string time.
	 */
	public static function timeago( $date ) {
		$datetime_format = Settings_Helper::get_datetime_format( false );
		$date = date( $datetime_format, $date ); // phpcs:ignore

		return $date;
	}

	/**
	 * Determine if path is for a plugin or theme etc.
	 *
	 * @param  string $path - Incoming dir.
	 * @param  bool   $return_ucwords - Capitalize.
	 * @return string - Result.
	 */
	public static function determine_directory_context( $path, $return_ucwords = false ) {
		$context   = 'other';
		$theme_dir = dirname( get_template_directory() );

		// Is this something within the plugins directory?
		if ( strpos( $path, WP_PLUGIN_DIR ) !== false ) {
			$context = 'plugin';
		} elseif ( strpos( (string) $path, (string) $theme_dir ) !== false ) {
			$context = 'theme';
		} elseif ( (string) $path == ABSPATH || trailingslashit( (string) $path ) == ABSPATH . 'wp-includes/' || trailingslashit( (string) $path ) == ABSPATH . 'wp-admin/' ) {
			$context = 'core';
		} elseif ( trailingslashit( (string) dirname( $path ) ) == ABSPATH || trailingslashit( (string) dirname( $path ) ) == ABSPATH . 'wp-includes/' || trailingslashit( (string) dirname( $path ) ) == ABSPATH . 'wp-admin/' ) {
			$context = 'core';
		}

		return ( $return_ucwords ) ? ucwords( $context ) : $context;
	}

	/**
	 * Check hash against currently stored hash.
	 *
	 * @param string $file_path - Lookup path.
	 * @param string $incoming_hash - Current has.
	 * @return bool - Result.
	 */
	public static function check_stored_file_hash( $file_path, $incoming_hash ) {
		global $wpdb;
		$stored_files = $wpdb->prefix . DB_Handler::$stored_files_table_name;
		$path         = trailingslashit( dirname( $file_path ) );

		$found = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE path = %s', $stored_files, $path ), ARRAY_A ); // phpcs:ignore

		$file_name = '/' . trim( substr( $file_path, strrpos( $file_path, '/' ) + 1 ) );

		if ( isset( $found[0] ) ) {
			$stored_hash             = false;
			$found_file_paths_array  = maybe_unserialize( $found[0]['file_paths'] );
			$found_file_hashes_array = maybe_unserialize( $found[0]['file_hashes'] );
			$index                   = 0;
			foreach ( $found_file_paths_array as $check_path ) {
				if ( $check_path === $file_name ) {
					$stored_hash = $found_file_hashes_array[ $index ];
				}
				++$index;
			}

			if ( $stored_hash === $incoming_hash ) {
				return true;
			}
		}

		return false;
	}
}
