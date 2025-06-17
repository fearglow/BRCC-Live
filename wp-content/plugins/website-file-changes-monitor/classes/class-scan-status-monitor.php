<?php
/**
 * Handle monitoring ongoing scans.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace MFM;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class to update and return current scan status.
 *
 * @since 2.0.0
 */
class Scan_Status_Monitor {

	/**
	 * Add rest route.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function setup_rest_route() {
		register_rest_route(
			'mfm-scan-status',
			'get-status',
			array(
				'methods'             => 'GET',
				'callback'            => function () {
					return self::get_status();
				},
				'permission_callback' => function () {
					return true;
				},
			)
		);
	}

	/**
	 * Update current status.
	 *
	 * @param  array $new_status - Incoming.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function update_status( $new_status ) {
		$default = array(
			'status'                => '',
			'start_time'            => '',
			'current_step'          => '',
			'starting_events_count' => '',
			'current_events_count'  => '',
		);
		$final   = wp_parse_args( $new_status, $default );

		update_site_option( MFM_PREFIX . 'monitor_status', $final );
	}

	/**
	 * Get current status.
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public static function get_status() {
		$return = get_site_option( MFM_PREFIX . 'monitor_status' );
		// All good, pat on the back.
		return new \WP_REST_Response( $return, 200 );
	}

	/**
	 * Check if can get status.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	public static function get_status_permissions_check() {
		return true;
	}
}
