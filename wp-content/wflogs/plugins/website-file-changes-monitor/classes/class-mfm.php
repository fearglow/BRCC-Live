<?php
/**
 * Main class file for plugin, handles loading all other bits.
 *
 * @package mfm
 */

use \MFM\Helpers\Directory_And_File_Helpers; // phpcs:ignore
use \MFM\Helpers\Settings_Helper; // phpcs:ignore
use \MFM\Helpers\Logger; // phpcs:ignore
use \MFM\Helpers\Emailer; // phpcs:ignore
use \MFM\DB_Handler; // phpcs:ignore
use \MFM\MFM_Fast_Cache; // phpcs:ignore
use \MFM\Scan_Status_Monitor; // phpcs:ignore
use \MFM\Crons\Cron_Handler; // phpcs:ignore

/**
 * Main MFM Class.
 */
class MFM {

	/**
	 * Background runner for directories.
	 *
	 * @var WP_Background_Process
	 */
	public static $dir_runner;

	/**
	 * Background runner for files.
	 *
	 * @var WP_Background_Process
	 */
	public static $file_runner;

	/**
	 * Background runner for comparisons.
	 *
	 * @var WP_Background_Process
	 */
	public static $file_comparison_runner;

	/**
	 * Background runner for core files.
	 *
	 * @var WP_Background_Process
	 */
	public static $core_runner;

	/**
	 * Hooks init (nothing else) and calls things that need to run right away.
	 */
	public static function on_load() {
		MFM\Admin\Admin_Manager::actions();

		// Runner crons.
		add_filter( 'cron_schedules', array( '\MFM\Crons\Cron_Handler', 'cron_schedules' ) ); // phpcs:ignore

		// Ajax.
		add_action( 'wp_ajax_mfm_start_directory_runner', array( __CLASS__, 'start_directory_runner' ) );
		add_action( 'wp_ajax_mfm_send_test_email', array( '\MFM\Helpers\Emailer', 'send_test_email' ), 10, 1 );
		add_action( 'wp_ajax_mfm_load_extra_metadata', array( '\MFM\DB_Handler', 'get_event_metadata' ), 10, 1 );
		add_action( 'wp_ajax_monitor_mfm_scan_status', array( '\MFM\Scan_Status_Monitor', 'get_status' ), 10, 2 );

		// User AJAX.
		add_action( 'wp_ajax_mfm_purge_data', array( '\MFM\Admin\AJAX_Tasks', 'purge_data' ) );
		add_action( 'wp_ajax_mfm_update_setting', array( '\MFM\Admin\AJAX_Tasks', 'update_setting' ), 10, 1 );
		add_action( 'wp_ajax_mfm_validate_setting', array( '\MFM\Admin\AJAX_Tasks', 'validate_setting' ), 10, 1 );
		add_action( 'wp_ajax_mfm_reset_setting', array( '\MFM\Admin\AJAX_Tasks', 'reset_setting' ), 10, 1 );
		add_action( 'wp_ajax_mfm_mark_as_read', array( '\MFM\Admin\AJAX_Tasks', 'mark_as_read' ), 10, 1 );
		add_action( 'wp_ajax_mfm_finish_setup_wizard', array( '\MFM\Admin\AJAX_Tasks', 'finish_setup_wizard' ), 10, 1 );
		add_action( 'wp_ajax_mfm_dismiss_events_notice', array( '\MFM\Admin\AJAX_Tasks', 'dismiss_events_notice' ), 10, 1 );
		add_action( 'wp_ajax_mfm_abort_scan', array( '\MFM\Admin\AJAX_Tasks', 'abort_scan' ), 10, 1 );
		add_action( 'wp_ajax_mfm_event_lookup', array( '\MFM\Admin\AJAX_Tasks', 'event_lookup' ), 10, 1 );
		add_action( 'wp_ajax_mfm_cancel_scan', array( '\MFM\Admin\AJAX_Tasks', 'cancel_scan' ) );
		add_action( 'wp_ajax_mfm_cancel_setup_wizard', array( '\MFM\Admin\AJAX_Tasks', 'cancel_setup_wizard' ), 10, 1 );

		// Rest API.
		add_action( 'rest_api_init', array( '\MFM\Scan_Status_Monitor', 'setup_rest_route' ) );

		// File runner.
		add_action( 'mfm_directory_runner_completed', array( __CLASS__, 'directory_run_completed' ) );
		add_action( 'mfm_file_runner_completed', array( __CLASS__, 'file_run_completed' ) );
		add_action( 'mfm_file_comparison_runner_completed', array( __CLASS__, 'file_comparison_run_completed' ) );
		add_action( 'init', array( '\MFM\Plugins_And_Themes_Monitor', 'init' ) );

		// WP Activity Log.
		add_action( 'init', array( '\MFM\WSAL\Init_Sensor', 'init' ) );

		// Fire up runner classes.
		self::$dir_runner             = new \MFM\Runners\Directory_Runner();
		self::$file_runner            = new \MFM\Runners\File_Runner();
		self::$file_comparison_runner = new \MFM\Runners\File_Comparison_Runner();
		self::$core_runner            = new \MFM\Runners\Core_File_Runner();

		// Caching.
		MFM_Fast_Cache::setup_cache_path();

		// MFM crons.
		Cron_Handler::load_crons_handler();
	}

	/**
	 * Install the plugins tables etc.
	 *
	 * @return void
	 */
	public function install() {
		DB_Handler::install();
	}

	/**
	 * Add directory runner item to queue.
	 *
	 * @param  string $path - Path to add.
	 * @return void
	 */
	public static function push_item_to_list( $path ) {
		self::$dir_runner->push_to_queue( $path );
		self::$dir_runner->save()->dispatch();
	}

	/**
	 * Start the scan process off as a whole.
	 *
	 * @return void
	 */
	public static function start_directory_runner() {
		// Update Monitoring.
		$details = array(
			'status'                => 'started',
			'start_time'            => current_time( 'timestamp' ), // phpcs:ignore
			'current_step'          => 'Initialising',
			'starting_events_count' => DB_Handler::get_events( true ),
		);
		Scan_Status_Monitor::update_status( $details );

		if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
			$msg = Logger::mfm_get_log_timestamp() . ' SCAN STEP 1 - SCAN STARTED' . " \n";
			Logger::mfm_write_to_log( $msg );
		}

		$post_array = filter_input_array( INPUT_POST );
		$query_args = array();

		set_site_transient( MFM_PREFIX . 'dir_runner_started', current_time( 'timestamp' ), 1 ); // phpcs:ignore

		$current_id   = get_site_option( MFM_PREFIX . 'active_scan_id', 0 );
		$this_scan_id = $current_id + 1;
		update_site_option( MFM_PREFIX . 'active_scan_id', $current_id + 1 );
		update_site_option( MFM_PREFIX . 'scanner_running', true );

		$data = array(
			'path'        => '',
			'event_type'  => 'file-scan-started',
			'time'        => current_time( 'timestamp' ), // phpcs:ignore
			'is_read'     => 'no',
			'data'        => false,
			'scan_run_id' => get_site_option( MFM_PREFIX . 'active_scan_id', 0 ),
		);
		DB_Handler::add_event( $data );

		\MFM\Plugins_And_Themes_Monitor::update_plugins_and_themes_list();

		// Clear old data.
		DB_Handler::pre_scan_dump();

		$base = Directory_And_File_Helpers::get_directories_from_path( ABSPATH );
		array_push( $base, ABSPATH );

		if ( 'yes' === Settings_Helper::get_setting( 'core-scan-enabled', 'yes' ) ) {
			$core_files = Directory_And_File_Helpers::create_core_file_keys();
			foreach ( $core_files as $item ) {
				self::$core_runner->push_to_queue( $item );
				self::$core_runner->save();
			}

			self::$core_runner->dispatch();
		}

		$base = apply_filters( MFM_PREFIX . 'append_dir_to_scan', $base );

		foreach ( $base as $base_item ) {
			$data = array(
				'path' => $base_item,
				'time' => current_time( 'timestamp' ), // phpcs:ignore
			);
			DB_Handler::insert_data( DB_Handler::$scanned_directories_table_name, $data );
			$items = Directory_And_File_Helpers::get_directories_from_path( $base_item );
			foreach ( $items as $item ) {
				self::$dir_runner->push_to_queue( $item );
				self::$dir_runner->save();
			}
		}

		self::$dir_runner->dispatch();

		// All good, pat on the back.
		wp_send_json_success( array( 'message' => 'Started' ) );
	}

	/**
	 * Directory run done, so fire off the file runner.
	 *
	 * @return void
	 */
	public static function directory_run_completed() {
		$status = get_site_option( MFM_PREFIX . 'monitor_status' );
		MFM_Fast_Cache::dump_into_db( 'directory_runner_cache' );

		if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
			$msg = Logger::mfm_get_log_timestamp() . ' SCAN STEP 2 - DIRECTORIES SCANNED, START FILE RUNNER' . " \n";
			Logger::mfm_write_to_log( $msg );
		}

		// Update Monitoring.
		$details = array(
			'status'               => 'directory_scan_complete',
			'start_time'           => current_time( 'timestamp' ), // phpcs:ignore
			'current_step'         => 'Directory Scan Complete',
			'current_events_count' => DB_Handler::get_events( true ),
		);
		Scan_Status_Monitor::update_status( $details );

		self::start_file_runner();
	}

	/**
	 * Deep scan directories and record files within.
	 *
	 * @return void
	 */
	public static function start_file_runner() {
		if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
			$msg = Logger::mfm_get_log_timestamp() . ' SCAN STEP 3 - START FILE RUNNER' . " \n";
			Logger::mfm_write_to_log( $msg );
		}

		// Update Monitoring.
		$details = array(
			'status'               => 'file_runner_started',
			'start_time'           => current_time( 'timestamp' ), // phpcs:ignore
			'current_step'         => 'File Runner Started',
			'current_events_count' => DB_Handler::get_events( true ),
		);
		Scan_Status_Monitor::update_status( $details );

		$dirs = DB_Handler::get_directory_runner_results( false );

		foreach ( $dirs as $item ) {
			self::$file_runner->push_to_queue( $item );
			self::$file_runner->save();
		}
		self::$file_runner->dispatch();
	}

	/**
	 *  File run completed, store or compare.
	 *
	 * @return void
	 */
	public static function file_run_completed() {
		if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
			$msg = Logger::mfm_get_log_timestamp() . ' SCAN STEP 4 - FILE RUN COMPLETED' . " \n";
			Logger::mfm_write_to_log( $msg );
		}

		// Check if this is the 1st run.
		$needed = DB_Handler::get_directory_runner_results( true, 0, true );

		// Update Monitoring.
		$details = array(
			'status'               => 'file_runner_complete',
			'start_time'           => current_time( 'timestamp' ), // phpcs:ignore
			'current_step'         => ( $needed > 0 ) ? 'File Runner Complete, Starting Comparison' : 'Initial File Scan Complete',
			'current_events_count' => DB_Handler::get_events( true ),
		);
		Scan_Status_Monitor::update_status( $details );

		if ( $needed > 0 ) {
			if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
				$msg = Logger::mfm_get_log_timestamp() . ' SCAN STEP 5 - COMPARE DIRECTORY CHANGES' . " \n";
				Logger::mfm_write_to_log( $msg );
			}

			DB_Handler::compare_and_report_directory_changes();
			delete_site_option( MFM_PREFIX . 'scanner_running' );
		} else {
			if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
				$msg = Logger::mfm_get_log_timestamp() . ' SCAN STEP 5 - INITIAL FILE COMPARISON RUN COMPLETE' . " \n";
				Logger::mfm_write_to_log( $msg );
			}

			update_site_option( MFM_PREFIX . 'scanner_running', false );

			// Update Monitoring.
			$details = array(
				'status'               => 'scan_complete',
				'start_time'           => current_time( 'timestamp' ), // phpcs:ignore
				'current_step'         => 'Initial File Scan Complete',
				'current_events_count' => DB_Handler::get_events( true ),
			);
			Scan_Status_Monitor::update_status( $details );

			// Is 1st run, so just store it.
			DB_Handler::store_scanned_data();

			$current_id = get_site_option( MFM_PREFIX . 'active_scan_id' );
			$data       = array(
				'path'        => '',
				'event_type'  => 'file-scan-complete',
				'time'        => current_time( 'timestamp' ), // phpcs:ignore
				'is_read'     => 'no',
				'data'        => '',
				'scan_run_id' => $current_id,
			);

			DB_Handler::add_event( $data );

			update_site_option( MFM_PREFIX . 'last_scan_time', current_time( 'timestamp' ) ); // phpcs:ignore
			delete_site_option( MFM_PREFIX . 'scanner_running' );

		}
	}

	/**
	 * File run completed, store or compare.
	 *
	 * @return void
	 */
	public static function start_file_comparison_runner() {
		if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
			$msg = Logger::mfm_get_log_timestamp() . ' SCAN STEP 6 - COMPARE FILE CHANGES' . " \n";
			Logger::mfm_write_to_log( $msg );
		}

		$file_result = DB_Handler::get_file_runner_results( false );
		foreach ( $file_result as $item ) {
			self::$file_comparison_runner->push_to_queue( $item );
			self::$file_comparison_runner->save();
		}
		self::$file_comparison_runner->dispatch();
	}

	/**
	 * Start file comparison step.
	 *
	 * @return void
	 */
	public static function file_comparison_run_completed() {
		if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
			$msg = Logger::mfm_get_log_timestamp() . ' SCAN STEP 7 - FILE COMPARISON RUN COMPLETE' . " \n";
			Logger::mfm_write_to_log( $msg );
		}

		// Store it all up.
		DB_Handler::store_scanned_data();

		update_site_option( MFM_PREFIX . 'event_notification_dismissed', false );

		// Update Monitoring.
		$details = array(
			'status'               => 'scan_complete',
			'start_time'           => current_time( 'timestamp' ), // phpcs:ignore
			'current_step'         => 'All Done',
			'current_events_count' => DB_Handler::get_events( true ),
		);
		Scan_Status_Monitor::update_status( $details );

		$current_id = get_site_option( MFM_PREFIX . 'active_scan_id' );
		$data       = array(
			'path'        => '',
			'event_type'  => 'file-scan-complete',
			'time'        => current_time( 'timestamp' ), // phpcs:ignore
			'is_read'     => 'no',
			'data'        => '',
			'scan_run_id' => $current_id,
		);

		DB_Handler::add_event( $data );

		update_site_option( MFM_PREFIX . 'last_scan_time', current_time( 'timestamp' ) ); // phpcs:ignore

		Emailer::send_scan_summary( $current_id );
		update_site_option( MFM_PREFIX . 'scanner_running', false );
	}
}
