<?php
/**
 * Handles checking and reporting of core file changes.
 *
 * @package mfm
 */

namespace MFM\Runners;

use \MFM\Helpers\Directory_And_File_Helpers; // phpcs:ignore
use \MFM\DB_Handler;  // phpcs:ignore

/**
 * Main runner for each file.
 */
class Core_File_Runner extends \WP_Background_Process {

	/**
	 * Runner prefix.
	 *
	 * @var string
	 */
	protected $prefix = 'mfm';

	/**
	 * Runner action name.
	 *
	 * @var string
	 */
	protected $action = 'core_file_runner';

	/**
	 * Main task logic.
	 *
	 * @param array $item - Input.
	 * @return bool
	 */
	protected function task( $item ) {

		$path          = explode( '|', $item )[0];
		$expected_hash = explode( '|', $item )[1];

		if ( ! file_exists( $path ) ) {
			return false;
		}
		$current_hash = md5_file( $path );

		$context = Directory_And_File_Helpers::determine_directory_context( dirname( $path ), false );

		if ( 'other' !== $context ) {
			return false;
		}

		$new_data = array(
			'modified' => array( $path ),
		);

		if ( $expected_hash !== $current_hash ) {

			$is_same_as_last_scan = Directory_And_File_Helpers::check_stored_file_hash( $path, $current_hash );

			// No change since last scan, dont alert.
			if ( $is_same_as_last_scan ) {
				return false;
			}

			$current_id = get_site_option( MFM_PREFIX . 'active_scan_id' );
			$data       = array(
				'path'        => dirname( $path ),
				'event_type'  => 'core-file-modified',
				'time'        => current_time( 'timestamp' ), // phpcs:ignore
				'is_read'     => 'no',
				'data'        => maybe_serialize( $new_data ),
				'scan_run_id' => $current_id,
			);

			DB_Handler::add_event( $data );
		}

		return false;
	}

	/**
	 * Unlock.
	 *
	 * @return $this
	 */
	protected function unlock_process() {
		delete_site_transient( $this->identifier . '_process_lock' );
		return $this;
	}
}
