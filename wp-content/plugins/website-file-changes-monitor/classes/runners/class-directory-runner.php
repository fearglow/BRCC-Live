<?php
/**
 * Handles recording directory data.
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
use MFM\Helpers\Settings_Helper;
use MFM\MFM_Fast_Cache;

/**
 * Background runner for dirs.
 *
 * @since 2.0.0
 */
class Directory_Runner extends \WP_Background_Process {

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
	protected $action = 'directory_runner';

	/**
	 * Main task logic.
	 *
	 * @param string $incoming_item - Incoming.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	protected function task( $incoming_item ) {
		if ( is_dir( $incoming_item ) ) {

			$items = Directory_And_File_Helpers::get_directories_from_path( $incoming_item );

			if ( ! empty( $items ) ) {

				$ignore_dirs = Settings_Helper::get_setting_cached( 'excluded_directories' );

				foreach ( $items as $item ) {
					if ( ! in_array( $item, $ignore_dirs, true ) ) {
						\MFM::push_item_to_list( $item );
					}
				}
			}
		}

		$data = array(
			'time' => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			'path' => $incoming_item,
		);

		if ( $incoming_item ) {
			MFM_Fast_Cache::add_to_cache( "('" . $incoming_item . "', '" . current_time( 'timestamp' ) . "', '" . substr( sprintf( "%o", fileperms( $incoming_item ) ), -4 ) . "')" ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
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
