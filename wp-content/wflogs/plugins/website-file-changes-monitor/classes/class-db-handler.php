<?php
/**
 * Handle and setup the plugins DB.
 *
 * @package mfm
 */

namespace MFM;

use \MFM\Helpers\Directory_And_File_Helpers; // phpcs:ignore
use \MFM\Helpers\Settings_Helper; // phpcs:ignore
use \MFM\Admin\AJAX_Tasks; // phpcs:ignore
use \MFM\Scan_Status_Monitor; // phpcs:ignore
use \MFM\Helpers\Logger; // phpcs:ignore

class DB_Handler { // phpcs:ignore

	/**
	 * Name for stored dir table.
	 *
	 * @var string
	 */
	public static $stored_directories_table_name = MFM_PREFIX . 'stored_directories';

	/**
	 * Name for stored scanned dir table.
	 *
	 * @var string
	 */
	public static $scanned_directories_table_name = MFM_PREFIX . 'scanned_directories';

	/**
	 * Name for stored files table.
	 *
	 * @var string
	 */
	public static $stored_files_table_name = MFM_PREFIX . 'stored_files';

	/**
	 * Name for stored scanned files table.
	 *
	 * @var string
	 */
	public static $scanned_files_table_name = MFM_PREFIX . 'scanned_files';

	/**
	 * Name for stored events table.
	 *
	 * @var string
	 */
	public static $events_table_name = MFM_PREFIX . 'events';

	/**
	 * Name for stored events meta table.
	 *
	 * @var string
	 */
	public static $events_meta_table_name = MFM_PREFIX . 'events_metadata';

	/**
	 * Setup and install required tables.
	 *
	 * @param boolean $setup_scan_dbs_only - Setup scanning only.
	 * @return void
	 */
	public static function install( $setup_scan_dbs_only = false ) {
		global $wpdb;

		$needed = get_site_option( MFM_PREFIX . 'db_setup_complete' );

		if ( $needed ) {
			return;
		}

		$db_names = array(
			self::$stored_directories_table_name,
			self::$scanned_directories_table_name,
		);

		$file_db_names = array(
			self::$stored_files_table_name,
			self::$scanned_files_table_name,
		);

		if ( $setup_scan_dbs_only ) {
			unset( $db_names[0] );
			unset( $file_db_names[0] );
		}

		foreach ( $db_names as $name ) {
			$table_name      = $wpdb->prefix . $name;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                path TEXT NOT NULL,
                time TEXT NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		foreach ( $file_db_names as $name ) {
			$table_name      = $wpdb->prefix . $name;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                path TEXT NOT NULL,
                file_paths TEXT NOT NULL,
                file_hashes TEXT NOT NULL,
                file_timestamps TEXT NOT NULL,
                data_hash TEXT NOT NULL,
                time TEXT NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		$table_name      = $wpdb->prefix . self::$events_table_name;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            path TEXT NOT NULL,
            data TEXT NOT NULL,
            event_type TEXT NOT NULL,
            is_read TEXT NOT NULL,
            time TEXT NOT NULL,
            scan_run_id TEXT NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$table_name      = $wpdb->prefix . self::$events_meta_table_name;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            event_id mediumint(9),
            data TEXT NOT NULL,
            event_type TEXT NOT NULL,
            scan_run_id TEXT NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_site_option( MFM_PREFIX . 'initial_setup_needed', true );
	}

	/**
	 * Dump temp data into stored tables.
	 *
	 * @return void
	 */
	public static function store_scanned_data() {
		global $wpdb;
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . self::$stored_directories_table_name ) ) === $wpdb->prefix . self::$stored_directories_table_name ) {
			$store = $wpdb->query( $wpdb->prepare( 'DROP TABLE %1s;', $wpdb->prefix . self::$stored_directories_table_name ) ); // phpcs:ignore
		}
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . self::$stored_files_table_name ) ) === $wpdb->prefix . self::$stored_files_table_name ) {
			$store = $wpdb->query( $wpdb->prepare( 'DROP TABLE %1s;', $wpdb->prefix . self::$stored_files_table_name ) ); // phpcs:ignore
		}	
		$store = $wpdb->query( $wpdb->prepare( 'CREATE TABLE %1s SELECT * FROM %2s', $wpdb->prefix . self::$stored_files_table_name, $wpdb->prefix . self::$scanned_files_table_name ) ); // phpcs:ignore
		$store = $wpdb->query( $wpdb->prepare( 'CREATE TABLE %1s SELECT * FROM %2s', $wpdb->prefix . self::$stored_directories_table_name, $wpdb->prefix . self::$scanned_directories_table_name ) ); // phpcs:ignore
	}

	/**
	 * Insert data into given table.
	 *
	 * @param string $table_to_insert_to - Destination.
	 * @param string $data_to_insert - Data.
	 * @return int - Resulting ID.
	 */
	public static function insert_data( $table_to_insert_to, $data_to_insert ) {
		global $wpdb;
		$path       = $data_to_insert['path'];
		$table_name = $wpdb->prefix . $table_to_insert_to;

		if ( $table_to_insert_to !== self::$events_table_name && $table_to_insert_to !== self::$events_meta_table_name ) {
			$exists = $wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE path LIKE %s', $table_name, $path ) ); // phpcs:ignore
		}

		$wpdb->insert( $table_name, $data_to_insert ); // phpcs:ignore

		return $wpdb->insert_id;
	}

	/**
	 * Gather and report directory changes.
	 *
	 * @return void
	 */
	public static function compare_and_report_directory_changes() {
		global $wpdb;
		$get_stored          = false;
		$stored_directories  = $wpdb->prefix . self::$stored_directories_table_name;
		$scanned_directories = $wpdb->prefix . self::$scanned_directories_table_name;
		$stored_files        = $wpdb->prefix . self::$stored_files_table_name;
		$scanned_files       = $wpdb->prefix . self::$scanned_files_table_name;
		$events_table_name   = $wpdb->prefix . self::$events_table_name;

		$is_known                 = false;
		$is_known_update          = false;
		$final                    = array();
		$known_plugins_and_themes = get_site_option( MFM_PREFIX . 'plugins_and_themes_history' );
		$plugin_list              = Directory_And_File_Helpers::create_plugin_keys();
		$new_event                = 0;
		$is_active_plugin         = false;

		// Compare Dirs.
		$missing_since_last_scan = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$stored_directories} WHERE path NOT IN (SELECT path FROM  %1s );", $scanned_directories ), ARRAY_A ); // phpcs:ignore
		$added_since_last_scan   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$scanned_directories} WHERE path NOT IN (SELECT path FROM  %1s );", $stored_directories ), ARRAY_A ); // phpcs:ignore

		$current_id  = get_site_option( MFM_PREFIX . 'active_scan_id' );

		// Directory has been removed since last scan.
		foreach ( $missing_since_last_scan as $item ) {
			$old_data = $wpdb->get_results( $wpdb->prepare( 'SELECT file_paths FROM %1s WHERE path = %s', $stored_files, $item['path'] ), ARRAY_A ); // phpcs:ignore
			$old_data = isset( $old_data[0]['file_paths'] ) ? maybe_serialize( $old_data[0]['file_paths'] ) : '';

			$ignore_dirs = Settings_Helper::get_setting( 'excluded_directories' );
			// Is path a child of an ignored path?
			$lookup = false;
			foreach ( $ignore_dirs as $ignored ) {
				$lookup = strpos( $item['path'], $ignored );
			}

			if ( $lookup !== false ) {
				continue;
			}

			// Check if is a known theme or plugin.
			foreach ( $known_plugins_and_themes as $known ) {
				if ( str_contains( $item['path'], $known ) ) {

					// Gather results held for this theme or plugin.
					foreach ( $plugin_list as $plugin ) {
						if ( str_contains( $item['path'], $plugin ) ) {
							$is_active_plugin = true;
						}
					}

					if ( $is_active_plugin ) {
						continue;
					}

					$held_data        = $wpdb->get_results( $wpdb->prepare( 'SELECT file_paths FROM %1s WHERE path LIKE %s', $stored_files, '%' . $known . '%' ), ARRAY_A ); // phpcs:ignore
					$final_data_array = array();

					foreach ( $held_data as $data_item => $value ) {
						array_push( $final_data_array, maybe_unserialize( $value['file_paths'] ) );
					}
					$final = array_merge( ...$final_data_array );

					$old_data = ( count( $final ) >= 500 ) ? 'external_metadata' : implode( ',', $final );

					// If large number of files found, send oversplit to metadata.
					if ( count( $final ) > 500 ) {
						$wanted = count( $final ) < 500;
						$part_a = array_slice( $final, 0, 500, true );
						$part_b = array_slice( $final, 500, count( $final ), true );
						array_push( $part_a, 'additional_external_data' );
						$old_data = implode( ',', $part_a );
					}

					$is_known = $known;
				}
			}

			if ( $is_active_plugin ) {
				continue;
			}

			$data = array(
				'path'        => ( $is_known ) ? $is_known : $item['path'],
				'event_type'  => strtolower( Directory_And_File_Helpers::determine_directory_context( $item['path'] ) ) . '-directory-removed',
				'time'        => current_time( 'timestamp' ), // phpcs:ignore
				'is_read'     => 'no',
				'data'        => maybe_serialize( self::format_event_data_string( $old_data, 'removed' ) ),
				'scan_run_id' => $current_id,
			);

			// Fire off an event for this change.
			$check = ( $is_known ) ? $is_known : $item['path'];
			if ( ! self::was_event_reported( $check, $current_id ) ) {
				$new_event = self::add_event( $data );

				// Fire off additional metadata if needed.
				if ( count( $final ) > 500 && $new_event > 0 ) {
					$send_to_exernal_storage = self::insert_event_metadata( $part_b, $new_event, $current_id, 'removed' );
				}
			}
		}

		foreach ( $added_since_last_scan as $item ) {
			$old_data = $wpdb->get_results( $wpdb->prepare( 'SELECT file_paths FROM %1s WHERE path = %s', $scanned_files, $item['path'] ), ARRAY_A ); // phpcs:ignore
			$old_data = isset( $old_data[0]['file_paths'] ) ? maybe_serialize( $old_data[0]['file_paths'] ) : '';

			$ignore_dirs = Settings_Helper::get_setting( 'excluded_directories' );
			// Is path a child of an ignored path?
			$lookup = false;
			foreach ( $ignore_dirs as $ignored ) {
				$lookup = strpos( $item['path'], $ignored );
			}
			
			if ( $lookup !== false ) {
				continue;
			}

			$event_suffix = 'added';

			// Check if is a known theme or plugin.
			foreach ( $known_plugins_and_themes as $known ) {
				if ( str_contains( $item['path'], $known ) ) {

					// Gather results held for this theme or plugin.
					$held_data        = $wpdb->get_results( $wpdb->prepare( 'SELECT file_paths FROM %1s WHERE path LIKE %s', $scanned_files, '%' . $known . '%' ), ARRAY_A ); // phpcs:ignore
					$final_data_array = array();

					foreach ( $held_data as $data_item => $value ) {
						array_push( $final_data_array, maybe_unserialize( $value['file_paths'] ) );
					}
					$final = array_merge( ...$final_data_array );

					$old_data = ( count( $final ) >= 500 ) ? 'external_metadata' : implode( ',', $final );

					// If large number of files found, send oversplit to metadata.
					if ( count( $final ) > 500 ) {
						$part_a = array_slice( $final, 0, 500, true );
						$part_b = array_slice( $final, 500, count( $final ), true );
						array_push( $part_a, 'additional_external_data' );
						$old_data = implode( ',', $part_a );
					}

					$is_known = $known;

					if ( $is_known ) {
						$test = $wpdb->get_results( $wpdb->prepare( 'SELECT file_paths FROM %1s WHERE path LIKE %s ', $stored_files, '%' . $known . '%' ), ARRAY_A ); // phpcs:ignore

						if ( ! empty( $test ) ) {
							$event_suffix    = 'updated';
							$is_known_update = true;
						}
					}
				}
			}

			$data = array(
				'path'        => ( $is_known ) ? $is_known : $item['path'],
				'event_type'  => strtolower( Directory_And_File_Helpers::determine_directory_context( $item['path'] ) ) . '-directory-' . $event_suffix,
				'time'        => current_time( 'timestamp' ), // phpcs:ignore
				'is_read'     => 'no',
				'data'        => maybe_serialize( self::format_event_data_string( $old_data, 'added' ) ),
				'scan_run_id' => $current_id,
			);

			// Fire off an event for this change.
			$check = ( $is_known ) ? $is_known : $item['path'];
			if ( ! self::was_event_reported( $check, $current_id ) ) {
				if ( ! $is_known_update ) {
					$new_event = self::add_event( $data );
				}

				// Fire off additional metadata if needed.
				if ( count( $final ) > 500 && $new_event > 0 ) {
					$send_to_exernal_storage = self::insert_event_metadata( $part_b, $new_event, $current_id, 'added' );
				}
			}
		}

		\MFM::start_file_comparison_runner();
	}

	/**
	 * Compare incoming path and its hashes to what we have stored.
	 *
	 * @param string $path - File path.
	 * @param string $hash - Dir hash.
	 * @param array  $file_paths - Paths.
	 * @param array  $file_hashes - Hashes.
	 * @return array - Found differences.
	 */
	public static function compare_file_changes( $path, $hash, $file_paths, $file_hashes ) {

		if ( is_link( $path ) ) {
			return array();
		}

		$ignore_dirs = Settings_Helper::get_setting( 'excluded_directories' );
		$current_id  = get_site_option( MFM_PREFIX . 'active_scan_id' );

		// Is path a child of an ignored path?
		foreach ( $ignore_dirs as $ignored ) {
			$lookup = strpos( $path, $ignored );
			if ( $lookup !== false ) {
				return array();
			}
		}

		if ( in_array( $path, $ignore_dirs, true ) || self::was_event_reported( $path, $current_id ) ) {
			return array();
		}

		global $wpdb;
		$stored_files  = $wpdb->prefix . self::$stored_files_table_name;
		$scanned_files = $wpdb->prefix . self::$scanned_directories_table_name;
		$diff_a        = array();

		$found = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE path = %s AND data_hash != %s', $stored_files, $path, $hash ), ARRAY_A ); // phpcs:ignore

		if ( isset( $found[0] ) ) {

			$found_file_paths_array  = maybe_unserialize( $found[0]['file_paths'] );
			$file_paths_array        = maybe_unserialize( $file_paths );
			$found_file_hashes_array = maybe_unserialize( $found[0]['file_hashes'] );
			$file_hashes_array       = maybe_unserialize( $file_hashes );

			$core_files               = Directory_And_File_Helpers::create_core_file_keys( true, false );
			$ingore_files             = Settings_Helper::get_setting( 'excluded_files' );
			$ingore_files             = ( is_array( $ingore_files ) ) ? $ingore_files : array();
			$excluded_file_extensions = Settings_Helper::get_setting( 'excluded_file_extensions' );
			$excluded_file_extensions = ( is_array( $excluded_file_extensions ) ) ? $excluded_file_extensions : array();
			$max_size                 = Settings_Helper::get_setting( 'max-file-size', 5 );

			$index = 0;
			foreach ( $found_file_paths_array as $path ) {
				if ( ! in_array( $path, $file_paths_array, true ) ) {

					$max_size        = Settings_Helper::get_setting( 'max-file-size', 5 );
					$file_size_limit = $max_size * 1048576;

					if ( in_array( $path, $ingore_files, true ) ) {
						if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
							$msg  = Logger::mfm_get_log_timestamp() . ' Attempted to check file, but skipped as ignored:' . " \n";
							$msg .= Logger::mfm_get_log_timestamp() . ' ' . $path . " \n";
							Logger::mfm_write_to_log( $msg );
						}
						continue;
					}

					$path_info      = pathinfo( $path );
					$file_extension = substr( strrchr( $path, '.' ), 1 );

					if ( $file_extension && in_array( $file_extension, $excluded_file_extensions, true ) ) {
						if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
							$msg  = Logger::mfm_get_log_timestamp() . ' Attempted to check file, but skipped as ignored extension:' . " \n";
							$msg .= Logger::mfm_get_log_timestamp() . ' ' . $path . " \n";
							Logger::mfm_write_to_log( $msg );
						}
						continue;
					}

					if ( ! $file_extension ) {
						if ( 'yes' !== Settings_Helper::get_setting( 'scan-files-with-no-extension', 'yes' ) ) {
							if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
								$msg  = Logger::mfm_get_log_timestamp() . ' Attempted to check file, but skipped as has no extension:' . " \n";
								$msg .= Logger::mfm_get_log_timestamp() . ' ' . $path . " \n";
								Logger::mfm_write_to_log( $msg );
							}
							continue;
						}
					}

					if ( ! is_file(  ABSPATH . $path ) ) {
						$diff_a['removed'][] = $path;
					} elseif ( in_array( $file_hashes_array[ $index ], $found_file_hashes_array, true ) ) {
						$diff_a['renamed'][]          = $path;
						$diff_a['renamed-old-path'][] = $path;
					}
				}
				++$index;
			}

			$index = 0;
			foreach ( $file_paths_array as $path ) {

				$max_size        = Settings_Helper::get_setting( 'max-file-size', 5 );
				$file_size_limit = $max_size * 1048576;

				if ( ! is_file( ABSPATH . $path ) ) {
					if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
						$msg  = Logger::mfm_get_log_timestamp() . ' Attempted to check file as added, but failed is_file check:' . " \n";
						$msg .= Logger::mfm_get_log_timestamp() . ' ' . $path . " \n";
						Logger::mfm_write_to_log( $msg );
					}
					continue;
				}

				if ( filesize( ABSPATH . $path ) > $file_size_limit ) {
					if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
						$msg  = Logger::mfm_get_log_timestamp() . ' File limit exceeded:' . " \n";
						$msg .= Logger::mfm_get_log_timestamp() . ' ' . $path . " \n";
						Logger::mfm_write_to_log( $msg );
					}
					do_action( 'mfm_file_exceeded_size_event_created', $path );
					continue;
				}

				if ( in_array( ltrim( $path, '/' ), $core_files, true ) ) {
					if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
						$msg  = Logger::mfm_get_log_timestamp() . ' Attempted to check file as added, but ignored as core:' . " \n";
						$msg .= Logger::mfm_get_log_timestamp() . ' ' . $path . " \n";
						Logger::mfm_write_to_log( $msg );
					}
					continue;
				}
				if ( in_array( $path, $ingore_files, true ) ) {
					if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
						$msg  = Logger::mfm_get_log_timestamp() . ' Attempted to check file as added, but ignored:' . " \n";
						$msg .= Logger::mfm_get_log_timestamp() . ' ' . $path . " \n";
						Logger::mfm_write_to_log( $msg );
					}
					continue;
				}

				$path_info      = pathinfo( $path );
				$file_extension = substr( strrchr( $path, '.' ), 1 );

				if ( $file_extension && in_array( $file_extension, $excluded_file_extensions, true ) ) {
					if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
						$msg  = Logger::mfm_get_log_timestamp() . ' Attempted to check file as added, but ignored extension:' . " \n";
						$msg .= Logger::mfm_get_log_timestamp() . ' ' . $path . " \n";
						Logger::mfm_write_to_log( $msg );
					}
					continue;
				}

				if ( ! $file_extension ) {
					if ( 'yes' !== Settings_Helper::get_setting( 'scan-files-with-no-extension', 'yes' ) ) {
						if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
							$msg  = Logger::mfm_get_log_timestamp() . ' Attempted to check file as added, but ignored as no extension:' . " \n";
							$msg .= Logger::mfm_get_log_timestamp() . ' ' . $path . " \n";
							Logger::mfm_write_to_log( $msg );
						}
						continue;
					}
				}

				if ( ! in_array( $path, $found_file_paths_array, true ) ) {
					$diff_a['added'][] = $path;
				} elseif ( isset( $found_file_hashes_array[ $index ] ) && $found_file_hashes_array[ $index ] !== $file_hashes_array[ $index ] ) {
					continue;
				} elseif ( in_array( $file_hashes_array[ $index ], $found_file_hashes_array, true ) ) {
					if ( isset( $diff_a['renamed'] ) && ! empty( $diff_a['renamed'] ) && ! in_array( $path, $diff_a['renamed'], true ) ) {
						if ( ! in_array( $path, $found_file_paths_array, true ) ) {
							$diff_a['renamed'][] = $path;
						}
					}
				}

				++$index;
			}
		}

		return $diff_a;
	}

	/**
	 * Add a new events.
	 *
	 * @param array $data - Incoming data.
	 * @param bool  $update_if_found - Update.
	 * @return $insert_id - Event ID.
	 */
	public static function add_event( $data, $update_if_found = false ) {
		global $wpdb;
		$table_name   = $wpdb->prefix . self::$events_table_name;
		$stored_files = $wpdb->prefix . self::$stored_files_table_name;
		$insert_id    = 0;

		$found = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE path = %s AND scan_run_id = %d', $table_name, $data['path'], $data['scan_run_id'] ), ARRAY_A ); // phpcs:ignore

		if ( isset( $found[0] ) ) {
			$difference = $data['time'] - $found[0]['time'];

			if ( $update_if_found ) {
				$input_data = serialize( unserialize( $found[0]['data'] ) + unserialize( $data['data'] ) ); // phpcs:ignore
				$update = $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET data = %s WHERE id = %d", $input_data, $found[0]['id'] ) );  // phpcs:ignore
			} elseif ( $difference > 10 ) {
				$insert_id = self::insert_data( self::$events_table_name, $data );
			}
		} else {
			$insert_id = self::insert_data( self::$events_table_name, $data );
		}

		if ( $insert_id > 0 ) {
			do_action( 'mfm_file_change_event_created', $data );
		}

		return $insert_id;
	}

	/**
	 * Check if event has handled.
	 *
	 * @param string $path - Path.
	 * @param string $scan_run_id - Current scan ID.
	 * @return bool - Was reported.
	 */
	public static function was_event_reported( $path, $scan_run_id ) {
		global $wpdb;
		$table_name   = $wpdb->prefix . self::$events_table_name;
		$stored_files = $wpdb->prefix . self::$stored_files_table_name;
		$insert_id    = 0;
		$found        = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE path = %s AND scan_run_id = %d', $table_name, $path, $scan_run_id ), ARRAY_A ); // phpcs:ignore

		return isset( $found[0] );
	}

	/**
	 * Insert overspill into table
	 *
	 * @param array  $part_b - Data.
	 * @param bool   $new_event - Is new.
	 * @param int    $current_id - Scan ID.
	 * @param string $event_type - Event type.
	 * @return int - Result ID.
	 */
	public static function insert_event_metadata( $part_b, $new_event, $current_id, $event_type = 'removed' ) {
		global $wpdb;
		$metadata_table_name = $wpdb->prefix . self::$events_meta_table_name;
		$data_string         = '';

		foreach ( $part_b as $item => $val ) {
			$data_string .= '("' . $new_event . '", "' . $event_type . '", "' . $val . '", "' . $current_id . '"),';
		}

		$data_string  = trim( $data_string, ',' ) . ';';
		$setting_save = $wpdb->query( "INSERT INTO $metadata_table_name ( event_id, event_type, data, scan_run_id ) VALUES " . $data_string ); // phpcs:ignore

		return $setting_save;
	}

	/**
	 * Format event data.
	 *
	 * @param mixed  $data - Incoming data.
	 * @param string $event_type - Event type.
	 * @return array - Result.
	 */
	public static function format_event_data_string( $data, $event_type = 'modified' ) {
		$data =  maybe_unserialize( maybe_unserialize( $data ) );

		$incoming  = is_string( $data ) && '' !== $data ? explode( ',', $data ) : $data;
		$formatted = array();

		if ( ! is_array( $incoming ) ) {
			return $formatted;
		}

		foreach ( $incoming as $item ) {
			$formatted[ $event_type ][] = maybe_unserialize( $item );
		}

		return $formatted;
	}

	/**
	 * Retieve additonal event metadata.
	 *
	 * @param integer $limit - Limit.
	 * @param integer $offset - Offset.
	 * @return void
	 */
	public static function get_event_metadata( $limit = 500, $offset = 0 ) {
		$is_nonce_set   = isset( $_POST['nonce'] ); // phpcs:ignore
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], 'mfm_load_extra_metadata' ); // phpcs:ignore
		}

		if ( ! $is_valid_nonce ) {
			$return = array(
				'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		if ( ! isset( $_POST['event_target'] ) ) {
			$return = array(
				'message' => __( 'target ID no provided', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		global $wpdb;
		$metadata_table_name = $wpdb->prefix . self::$events_meta_table_name;
		$lookup_id           = wp_unslash( $_POST['event_target'] ); // phpcs:ignore
		$offset              = isset( $_POST['offset'] ) ? wp_unslash( $_POST['offset'] ) : $offset; // phpcs:ignore
		$total_available     = count( $wpdb->get_results( $wpdb->prepare( 'SELECT id FROM %1s WHERE event_id = %d', $metadata_table_name, $lookup_id ), ARRAY_A ) ); // phpcs:ignore
		$data                = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE event_id = %d LIMIT 500 OFFSET %d', $metadata_table_name, $lookup_id, $offset ), ARRAY_A ); // phpcs:ignore

		$return = array(
			'message'     => __( 'Done! ', 'website-file-changes-monitor' ),
			'event_data'  => $data,
			'remaining'   => ( $total_available > count( $data ) ) ? $total_available - count( $data ) - $offset : 0,
			'next_offset' => $offset + 500,
		);
		wp_send_json_success( $return );
	}

	/**
	 * Dump all scanned data ready for fresh scan.
	 *
	 * @return void
	 */
	public static function pre_scan_dump() {
		// Clear old data.
		global $wpdb;
		$table_name = $wpdb->prefix . self::$scanned_files_table_name;
		$delete     = $wpdb->query( $wpdb->prepare( 'TRUNCATE TABLE %1s', $table_name ) ); // phpcs:ignore
		$table_name = $wpdb->prefix . self::$scanned_directories_table_name;
		$delete     = $wpdb->query( $wpdb->prepare( 'TRUNCATE TABLE %1s', $table_name ) ); // phpcs:ignore
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE option_name LIKE %s', $wpdb->options, '%_directory_transient_%' ) ); // phpcs:ignore
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE option_name LIKE %s', $wpdb->options, '%_directory_runner_%' ) ); // phpcs:ignore
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE option_name LIKE %s', $wpdb->options, '%_file_transient_%' ) ); // phpcs:ignore
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE option_name LIKE %s', $wpdb->options, '%_file_runner_%' ) ); // phpcs:ignore

		$clear_event_data_size = Settings_Helper::get_setting( 'purge-length', 1 );
		$clear_event_data_size = --$clear_event_data_size;
		$current_id            = get_site_option( MFM_PREFIX . 'active_scan_id', 0 );

		$wanted_amount   = $current_id - $clear_event_data_size;
		$table_name      = $wpdb->prefix . self::$events_table_name;
		$meta_table_name = $wpdb->prefix . self::$events_meta_table_name;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE scan_run_id < %s', $table_name, $wanted_amount ) ); // phpcs:ignore
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE scan_run_id < %s', $meta_table_name, $wanted_amount ) ); // phpcs:ignore
	}

	/**
	 * Remove all data on uninstall.
	 *
	 * @return void
	 */
	public static function uninstall() {
		$needed = get_site_option( MFM_PREFIX . 'delete-data-enabled' );

		if ( 'yes' === $needed ) {
			global $wpdb;
			$cron_hook_identifiers = array( 'mfm_monitor_file_changes' );

			foreach ( $cron_hook_identifiers as $cron_hook_identifier ) {
				wp_clear_scheduled_hook( $wpdb->prefix . $cron_hook_identifier );
			}

			AJAX_Tasks::purge_data( true );
		}
	}

	/**
	 * Cancel in-progress scan.
	 *
	 * @return void
	 */
	public static function cancel_current_scan() {

		\MFM::$dir_runner->delete_all();
		\MFM::$file_runner->delete_all();
		\MFM::$file_comparison_runner->delete_all();
		\MFM::$core_runner->delete_all();

		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE option_name LIKE %s', $wpdb->options, '%_runner_%' ) ); // phpcs:ignore

		$data = array(
			'path'        => '',
			'event_type'  => 'file-scan-aborted',
			'time'        => current_time( 'timestamp' ), // phpcs:ignore
			'is_read'     => 'no',
			'data'        => false,
			'scan_run_id' => get_site_option( MFM_PREFIX . 'active_scan_id', 0 ),
		);

		// Update Monitoring.
		$details = array(
			'status'               => 'scan_complete',
			'start_time'           => current_time( 'timestamp' ), // phpcs:ignore
			'current_step'         => 'Scan Aborted',
			'current_events_count' => 0,
		);

		self::add_event( $data );
		update_site_option( MFM_PREFIX . 'scanner_running', false );
		Scan_Status_Monitor::update_status( $details );
	}

	/**
	 * Get results from directory runner.
	 *
	 * @param boolean $return_count - Return just a count.
	 * @param integer $limit - Limit results.
	 * @param boolean $get_stored - Get stored or scanned (temp) results.
	 * @return int|array - Results.
	 */
	public static function get_directory_runner_results( $return_count = false, $limit = 0, $get_stored = false ) {
		global $wpdb;
		$table_name = ( $get_stored ) ? $wpdb->prefix . self::$stored_directories_table_name : $wpdb->prefix . self::$scanned_directories_table_name;

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
			$sql = $wpdb->prepare( 'SELECT * FROM %1s', $table_name );  // phpcs:ignore
			if ( $limit > 0 ) {
				$sql .= $wpdb->prepare( ' LIMIT %d', $limit );
			}
			$bg_jobs = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
			return ( $return_count ) ? count( $bg_jobs ) : $bg_jobs;
		} else {
			return ( $return_count ) ? 0 : array();
		}
	}

	/**
	 * Get results from file runner.
	 *
	 * @param boolean $return_count - Return just a count.
	 * @param integer $limit - Limit results.
	 * @return int|array - Results.
	 */
	public static function get_file_runner_results( $return_count = false, $limit = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$scanned_files_table_name;
		$sql        = $wpdb->prepare( 'SELECT * FROM %1s', $table_name );  // phpcs:ignore

		if ( $limit > 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d', $limit );
		}
		$bg_jobs = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
		return ( $return_count ) ? count( $bg_jobs ) : $bg_jobs;
	}

	/**
	 * Get events from the db.
	 *
	 * @param boolean $return_count - Amount to get.
	 * @param integer $limit - Limit.
	 * @param integer $offset - Offset.
	 * @param string  $events_type - Event type.
	 * @return array - Results.
	 */
	public static function get_events( $return_count = false, $limit = 0, $offset = 0, $events_type = 'all' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$events_table_name;

		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) == $table_name ) { // phpcs:ignore
			$bg_jobs = array();
			self::install();
			return ( $return_count ) ? count( $bg_jobs ) : $bg_jobs;
		}

		$sql = $wpdb->prepare( 'SELECT * FROM %1s', $table_name );  // phpcs:ignore

		if ( 'all' !== $events_type ) {
			$sql .= $wpdb->prepare( ' WHERE event_type LIKE %s', '%' . $events_type . '%' );
		}

		$sql .= ' ORDER BY time DESC';

		if ( $limit > 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $limit, $offset );
		}

		$bg_jobs = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore

		return ( $return_count ) ? count( $bg_jobs ) : $bg_jobs;
	}

	/**
	 * Get a count of events.
	 *
	 * @param boolean $skip_non_file_events - Skip basic events.
	 * @return int - Result.
	 */
	public static function get_events_count( $skip_non_file_events = true ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$events_table_name;

		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) == $table_name ) { // phpcs:ignore
			$bg_jobs = array();
			self::install();
			return 0;
		}

		$sql = $wpdb->prepare( 'SELECT COUNT(*) FROM %1s', $table_name ); // phpcs:ignore

		if ( $skip_non_file_events ) {
			$events_type = 'modified';
			$sql        .= $wpdb->prepare( ' WHERE event_type LIKE %s', '%' . $events_type . '%' );
			$events_type = 'added';
			$sql        .= $wpdb->prepare( ' OR %s', '%' . $events_type . '%' );
			$events_type = 'removed';
			$sql        .= $wpdb->prepare( ' OR %s', '%' . $events_type . '%' );
		}

		$num_rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore

		$count = array_values( $num_rows[0] );
		return (int) $count[0];
	}

	/**
	 * Get events based on a specific run ID.
	 *
	 * @param int $scan_run_id - ID to lookup.
	 * @return array - Results.
	 */
	public static function get_events_for_specific_run( $scan_run_id, $event_types_wanted ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$events_table_name;
		$sql        = $wpdb->prepare( 'SELECT * FROM %1s', $table_name ); // phpcs:ignore
		$sql       .= $wpdb->prepare( ' WHERE scan_run_id = %s', $scan_run_id );
		$sql       .= $wpdb->prepare( ' AND data != %s', '' );

		$event_types_wanted = array_map(
			function( $v ) {
				return '"%' . esc_sql( $v ) . '%"';
			},
			$event_types_wanted
		);

		$event_types_wanted = implode( ' OR event_type LIKE ', $event_types_wanted );
		$sql        .= ' AND event_type LIKE ' . $event_types_wanted . '';  // phpcs:ignore
		$bg_jobs    = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore

		return $bg_jobs;
	}

	/**
	 * Purge old WFCM data.
	 *
	 * @return void
	 */
	public static function purge_wfcm_data() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wfcm_file_events';

		// Delete wfcm options.
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE ( option_name LIKE %s OR option_name LIKE %s )', $wpdb->options, 'wfcm-%', 'wfcm_%' ) ); // phpcs:ignore
		// Delete wfcm transients.
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE ( option_name LIKE %s OR option_name LIKE %s )', $wpdb->options, '_transient_wfcm%', '_transient_timeout_wfcm%' ) ); // phpcs:ignore

		// Delete wfcm_file_event posts + data.
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %1s', $table_name ) ); // phpcs:ignore
	}
}
