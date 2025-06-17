<?php
/**
 * Handles recording directory data.
 *
 * @package mfm
 */

namespace MFM\Runners;

use \MFM\Helpers\Directory_And_File_Helpers; // phpcs:ignore
use \MFM\Helpers\Settings_Helper; // phpcs:ignore
use \MFM\MFM_Fast_Cache; // phpcs:ignore

/**
 * Backround runnder for dirs.
 */
class Directory_Runner extends \WP_Background_Process {

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
	protected $action = 'directory_runner';

	/**
	 * Main task logic.
	 *
	 * @param string $incoming_item - Incoming.
	 * @return bool
	 */
	protected function task( $incoming_item ) {

		if ( is_dir( $incoming_item ) ) {

			$items = Directory_And_File_Helpers::get_directories_from_path( $incoming_item );

			if ( ! empty( $items ) ) {

				$ignore_dirs = Settings_Helper::get_setting( 'excluded_directories' );

				foreach ( $items as $item ) {
					if ( ! in_array( $item, $ignore_dirs, true ) ) {
						\MFM::push_item_to_list( $item );
					}
				}
			}
		}

		$data = array(
			'time' => current_time( 'timestamp' ),  // phpcs:ignore
			'path' => $incoming_item,
		);

		if ( $incoming_item ) {
			MFM_Fast_Cache::add_to_cache( "('" . $incoming_item . "', '" . current_time( 'timestamp' ) . "')" );  // phpcs:ignore
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
