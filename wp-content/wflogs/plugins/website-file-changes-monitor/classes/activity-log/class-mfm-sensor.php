<?php
/**
 * Sensor: My custom sensor
 *
 * My custom sensor file.
 *
 * @package Wsal
 * @since latest
 */

declare(strict_types=1);

namespace WSAL\Plugin_Sensors;

use WSAL\Controllers\Alert_Manager;
use \MFM\Helpers\Events_Helper; // phpcs:ignore

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\Plugin_Sensors\MFM_Sensor' ) ) {

	class MFM_Sensor {

		/**
		 * Listening to events using hooks.
		 * Here you can code your own custom sensors for triggering your custom events.
		 */
		public static function init() {
			add_action( 'mfm_file_change_event_created', array( __CLASS__, 'file_change_event' ) );
			add_action( 'mfm_file_exceeded_size_event_created', array( __CLASS__, 'file_beyond_max_size' ) );
		}

		/**
		 * Trigger a WSAL event.
		 *
		 * @param array $data - Event data.
		 * @return void
		 */
		public static function file_change_event( $data ) {
			if ( isset( $data['event_type'] ) ) {
				$editor_link = esc_url( admin_url( 'admin.php?page=file-monitor-admin' ) );
				$event_id    = 6030;

				$variables = array(
					'event_type'   => Events_Helper::create_event_type_label( $data['event_type'], 'all', true ),
					'RevisionLink' => $editor_link,
				);

				if ( 'file-scan-started' === $data['event_type'] || 'file-scan-complete' === $data['event_type'] || 'file-scan-aborted' === $data['event_type'] ) {
					$event_id = 6033;
				} elseif ( 'core-file-modified' === $data['event_type'] ) {
					$event_id = 6032;
				}

				if ( 'file-scan-complete' === $data['event_type'] ) {
					$variables['EventType'] = 'finished';
				}

				if ( 'file-scan-aborted' === $data['event_type'] ) {
					$variables['EventType'] = 'stopped';
				}

				Alert_Manager::trigger_event( $event_id, $variables );
			}
		}

		/**
		 * Trigger WSAL event when a file exceeds max size.
		 *
		 * @param  string $path - File path.
		 * @return void
		 */
		public static function file_beyond_max_size( $path ) {
			if ( isset( $data['event_type'] ) ) {
				$editor_link = esc_url( admin_url( 'admin.php?page=file-monitor-admin' ) );
				$event_id    = 6031;
				$variables   = array(
					'file_path'    => $path,
					'RevisionLink' => $editor_link,
				);
				Alert_Manager::trigger_event( $event_id, $variables );
			}
		}
	}
}
