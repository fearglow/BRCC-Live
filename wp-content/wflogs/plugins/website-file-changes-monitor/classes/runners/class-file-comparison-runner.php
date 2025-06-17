<?php
/**
 * Handles checking and reporting of file changes.
 *
 * @package mfm
 */

namespace MFM\Runners;

use \MFM\Helpers\Directory_And_File_Helpers; // phpcs:ignore
use \MFM\DB_Handler; // phpcs:ignore

/**
 * Main comparison runner.
 */
class File_Comparison_Runner extends \WP_Background_Process {

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
	protected $action = 'file_comparison_runner';

	/**
	 * Undocumented function
	 *
	 * @param array $item - Incoming.
	 * @return bool
	 */
	protected function task( $item ) {
		$changes = DB_Handler::compare_file_changes( $item['path'], $item['data_hash'], $item['file_paths'], $item['file_hashes'] );

		if ( ! empty( $changes ) ) {
			$is_known         = false;
			$is_active_plugin = false;
			$is_update        = false;

			$known_plugins_and_themes = get_site_option( MFM_PREFIX . 'plugins_and_themes_history' );
			$current_id               = get_site_option( MFM_PREFIX . 'active_scan_id' );

			foreach ( $known_plugins_and_themes as $known ) {
				if ( str_contains( $item['path'], $known ) || $item['path'] === $known ) {
					$is_known     = true;
					$item['path'] = $known;
				}
			}

			if ( $is_known ) {
				$plugin_list = Directory_And_File_Helpers::create_plugin_keys();
				foreach ( $plugin_list as $plugin ) {
					if ( str_contains( $item['path'], $plugin ) ) {
						$is_active_plugin = true;
					}
				}
			}

			if ( $is_active_plugin ) {
				$updates = get_site_option( MFM_PREFIX . 'plugins_and_themes_recent_updates', array() );
				foreach ( $updates as $key => $updated ) {
					if ( str_contains( $item['path'], $updated ) ) {
						$is_update = true;
						unset( $updates[ $key ] );
					}
				}
				if ( $is_update ) {
					$update_opt = update_site_option( MFM_PREFIX . 'plugins_and_themes_recent_updates', $updates );
				}
			}

			$context_of_changes = array();
			if ( isset( $changes['modified'] ) ) {
				array_push( $context_of_changes, 'modified' );
			}
			if ( isset( $changes['renamed'] ) ) {
				array_push( $context_of_changes, 'renamed' );
				foreach ( $changes['renamed'] as $renamed ) {
					$changes['modified'][] = $renamed;
				}				 
				unset( $changes['renamed'] );
			}
			if ( isset( $changes['added'] ) ) {
				array_push( $context_of_changes, 'added' );
			}
			if ( isset( $changes['removed'] ) ) {
				array_push( $context_of_changes, 'removed' );
			}

			if ( ! empty( $changes ) ) {
				$data = array(
					'path'        => $item['path'],
					'event_type'  => ( ! $is_update ) ? strtolower( Directory_And_File_Helpers::determine_directory_context( $item['path'] ) ) . '-file-' . implode( ',', $context_of_changes ) : strtolower( Directory_And_File_Helpers::determine_directory_context( $item['path'] ) ) . '-updated',
					'time'        => current_time( 'timestamp' ), // phpcs:ignore
					'is_read'     => 'no',
					'data'        => maybe_serialize( $changes ),
					'scan_run_id' => $current_id,
				);

				DB_Handler::add_event( $data, $is_known );
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
