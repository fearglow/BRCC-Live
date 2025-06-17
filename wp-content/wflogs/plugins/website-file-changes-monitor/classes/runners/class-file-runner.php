<?php
/**
 * Handles gathering of file data..
 *
 * @package mfm
 */

namespace MFM\Runners;

use \MFM\Helpers\Directory_And_File_Helpers;  // phpcs:ignore
use \MFM\DB_Handler;  // phpcs:ignore

/**
 * Main file discovery.
 */
class File_Runner extends \WP_Background_Process {

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
	protected $action = 'file_runner';

	/**
	 * Undocumented function
	 *
	 * @param array $item - Incoming.
	 * @return bool
	 */
	protected function task( $item ) {
		$path = $item['path'];

		if ( is_dir( $path ) ) {

			$files = Directory_And_File_Helpers::scan_and_store_files( $path );

			if ( isset( $files['paths'] ) ) {
				$data = array(
					'path'            => $path,
					'file_paths'      => maybe_serialize( $files['paths'] ),
					'file_hashes'     => maybe_serialize( $files['hashs'] ),
					'file_timestamps' => maybe_serialize( $files['timestamps'] ),
					'data_hash'       => md5( maybe_serialize( $files ) ),
				);
				DB_Handler::insert_data( DB_Handler::$scanned_files_table_name, $data );
			}
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
