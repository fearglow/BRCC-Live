<?php
/**
 * Custom Alerts for My custom sensor.
 *
 * @since   latest
 *
 * @package wsal
 * @subpackage wsal-my-custom-alerts
 */

declare(strict_types=1);

namespace WSAL\Custom_Alerts;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\Custom_Alerts\MFM_Alerts' ) ) {
	/**
	 * Custom sensor for Yoast plugin.
	 *
	 * @since latest
	 */
	class MFM_Alerts {

		/**
		 * Returns the structure of the alerts for extension.
		 *
		 * @return array
		 *
		 * @since latest
		 */
		public static function get_custom_alerts(): array {
			return array(
				esc_html__( 'Melapress File Manager', 'website-file-changes-monitor' ) => array(
					esc_html__( 'Melapress File Manager Content', 'website-file-changes-monitor' ) => array(

						array(
							6030,
							WSAL_HIGH,
							esc_html__( 'Melapress File Monitor file / directory change detected', 'website-file-changes-monitor' ),
							__( 'Melapress File Monitor file / directory change detected.', 'website-file-changes-monitor' ),
							array(
								esc_html__( 'Change type(s)', 'website-file-changes-monitor' ) => '%event_type%',
							),
							wsaldefaults_build_links( array( 'RevisionLink' ) ),
							'file',
							'modified',
						),
						array(
							6032,
							WSAL_HIGH,
							esc_html__( 'Melapress File Monitor core file change detected', 'website-file-changes-monitor' ),
							__( 'Melapress File Monitor detected a change within a WordPress core file', 'website-file-changes-monitor' ),
							array(
								//esc_html__( 'Change type(s)', 'website-file-changes-monitor' ) => '%event_type%',
							),
							wsaldefaults_build_links( array( 'RevisionLink' ) ),
							'file',
							'modified',
						),

						array(
							6031,
							WSAL_HIGH,
							esc_html__( 'Melapress File Monitor file skipped due to size', 'website-file-changes-monitor' ),
							__( 'File not scanned for changes because they are bigger than the maximum file size limit.', 'website-file-changes-monitor' ),
							array(
								esc_html__( 'File path', 'website-file-changes-monitor' ) => '%file_path%',
							),
							wsaldefaults_build_links( array( 'RevisionLink' ) ),
							'file',
							'modified',
						),

						array(
							6033,
							WSAL_HIGH,
							esc_html__( 'Melapress File Monitor scan started / finished', 'website-file-changes-monitor' ),
							 /* Translators: %EventType% - Type variable */
							__( 'Melapress File Monitor scan %EventType%.', 'website-file-changes-monitor' ),
							array(
								//esc_html__( 'Change type(s)', 'website-file-changes-monitor' ) => '%event_type%',
							),
							'',
							'system',
							'started',
						),
					),
				),
			);
		}
	}
}