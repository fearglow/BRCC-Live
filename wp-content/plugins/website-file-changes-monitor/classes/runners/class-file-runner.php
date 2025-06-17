<?php
/**
 * Handles gathering of file data..
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace MFM\Runners;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MFM\Helpers\Directory_And_File_Helpers;
use MFM\DB_Handler;

/**
 * Main file discovery.
 *
 * @since 2.0.0
 */
class File_Runner extends \WP_Background_Process {

	/**
	 * Runner prefix.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	protected $prefix = 'mfm';

	/**
	 * Runner action name.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	protected $action = 'file_runner';

	/**
	 * Undocumented function
	 *
	 * @param array $item - Incoming.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	protected function task( $item ) {
		$path = $item['path'];

		if ( is_dir( $path ) ) {

			$files = Directory_And_File_Helpers::scan_and_store_files( $path );

			if ( isset( $files['paths'] ) ) {
				$data = array(
					'path'             => $path,
					'file_paths'       => maybe_serialize( $files['paths'] ),
					'file_hashes'      => maybe_serialize( $files['hashs'] ),
					'file_timestamps'  => maybe_serialize( $files['timestamps'] ),
					'data_hash'        => md5( maybe_serialize( $files ) ),
					'file_permissions' => maybe_serialize( $files['permissions'] ),
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
	 *
	 * @since 2.0.0
	 */
	protected function unlock_process() {
		delete_site_transient( $this->identifier . '_process_lock' );
		return $this;
	}

	/**
	 * Should the process exit with wp_die?
	 *
	 * @param mixed $should_return What to return if filter says don't die, default is null.
	 *
	 * @return void|mixed
	 *
	 * @since 2.0.0
	 */
	protected function maybe_wp_die( $should_return = null ) {
		/**
		 * Should wp_die be used?
		 *
		 * @return bool
		 *
		 * @since 2.0.0
		 */
		if ( apply_filters( $this->identifier . '_wp_die', true ) ) {
			wp_die();
		}

		return $should_return;
	}
}
