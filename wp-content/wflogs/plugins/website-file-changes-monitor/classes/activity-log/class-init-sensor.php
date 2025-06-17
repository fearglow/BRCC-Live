<?php
/**
 * Helepr class for file and directory tasks.
 *
 * @package mfm
 */

namespace MFM\WSAL;

use \MFM\Helpers\Settings_Helper; // phpcs:ignore

/**
 * Utility file and directory functions.
 */
class Init_Sensor {

	public static function init() {

		if ( class_exists( '\WSAL\Helpers\Classes_Helper' ) ) {
			add_action(
				'wsal_sensors_manager_add',
				/**
				* Adds sensors classes to the Class Helper
				*
				* @return void
				*
				* @since latest
				*/
				function () {
					\WSAL\Helpers\Classes_Helper::add_to_class_map(
						array(
							'WSAL\\Plugin_Sensors\\MFM_Sensor' => __DIR__ . '/class-mfm-sensor.php',
						)
					);
				}
			);

			add_action(
				'wsal_custom_alerts_register',
				/**
				* Adds sensors classes to the Class Helper
				*
				* @return void
				*
				* @since latest
				*/
				function () {
					\WSAL\Helpers\Classes_Helper::add_to_class_map(
						array(
							'WSAL\\Custom_Alerts\\MFM_Alerts' => __DIR__ . '/class-mfm-alerts.php',
						)
					);
				}
			);

			add_filter(
				'wsal_event_type_data',
				function ( $types ) {
					$new_types = array(
						'finished'   => esc_html__( 'Finished', 'website-file-changes-monitor' ),
					);
		
					// combine the two arrays.
					$types = array_merge( $types, $new_types );
					return $types;
				}
			);

			add_filter(
				'wsal_togglealerts_obsolete_events',
				function ( $obsolete_events ) {
					$new_events      = array(
						6028,
						6029,
					);
					$obsolete_events = array_merge( $obsolete_events, $new_events );
					return $obsolete_events;
				}
			);
			\WSAL\Plugin_Sensors\MFM_Sensor::init();
		}
	} 

}
