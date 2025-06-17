<?php
/**
 * AJAX_Tasks
 *
 * @package MFM
 */

namespace MFM\Admin;

use \MFM\DB_Handler; // phpcs:ignore
use \MFM\Helpers\Settings_Helper; // phpcs:ignore
use \MFM\Admin\Admin_Manager; // phpcs:ignore

/**
 * Utility file and directory functions.
 */
class AJAX_Tasks {

	/**
	 * Purge all present data.
	 *
	 * @param boolean $skip_install - Skip install after.
	 * @return void
	 */
	public static function purge_data( $skip_install = true ) {

		$is_nonce_set   = isset( $_POST['nonce'] );
		$is_valid_nonce = false;

		if ( ! $skip_install ) {
			if ( $is_nonce_set ) {
				$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], 'mfm_purge_data_nonce' ); // phpcs:ignore
			}

			if ( ! $is_valid_nonce ) {
				$return = array(
					'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
				);
				wp_send_json_error( $return );
				return;
			}
		}

		global $wpdb;
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . DB_Handler::$stored_directories_table_name ) ) === $wpdb->prefix . DB_Handler::$stored_directories_table_name ) {
			$store  = $wpdb->query( $wpdb->prepare( 'DROP TABLE %1s', $wpdb->prefix . DB_Handler::$stored_directories_table_name ) );   // phpcs:ignore
		}
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . DB_Handler::$stored_files_table_name ) ) === $wpdb->prefix . DB_Handler::$stored_files_table_name ) {
			$store  = $wpdb->query( $wpdb->prepare( 'DROP TABLE %1s', $wpdb->prefix . DB_Handler::$stored_files_table_name ) );  // phpcs:ignore
		}
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . DB_Handler::$scanned_directories_table_name ) ) === $wpdb->prefix . DB_Handler::$scanned_directories_table_name ) {
			$store  = $wpdb->query( $wpdb->prepare( 'DROP TABLE %1s', $wpdb->prefix . DB_Handler::$scanned_directories_table_name ) );  // phpcs:ignore
		}
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . DB_Handler::$scanned_files_table_name ) ) === $wpdb->prefix . DB_Handler::$scanned_files_table_name ) {
			$store  = $wpdb->query( $wpdb->prepare( 'DROP TABLE %1s', $wpdb->prefix . DB_Handler::$scanned_files_table_name ) );  // phpcs:ignore
		}
		$store  = $wpdb->query( $wpdb->prepare( 'DROP TABLE %1s', $wpdb->prefix . DB_Handler::$events_table_name ) );  // phpcs:ignore
		$store  = $wpdb->query( $wpdb->prepare( 'DROP TABLE %1s', $wpdb->prefix . DB_Handler::$events_meta_table_name ) );  // phpcs:ignore
		$prefix = MFM_PREFIX . '%';

		$plugin_options = $wpdb->get_results( $wpdb->prepare( 'SELECT option_name FROM %1s WHERE option_name LIKE %s', $wpdb->options, $prefix ) );  // phpcs:ignore

		foreach ( $plugin_options as $option ) {
			delete_option( $option->option_name );
		}

		if ( $skip_install ) {
			return;
		}

		DB_Handler::install();

		$return = array(
			'message' => __( 'Data sucessfully purged, you will now to taken to the inital setup wizard.', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Update a setting to a new provided value.
	 *
	 * @return array - Response message.
	 */
	public static function update_setting() {
		$is_nonce_set   = isset( $_POST['nonce'] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], 'mfm_inline_settings_update' ); // phpcs:ignore
		}

		if ( ! $is_valid_nonce ) {
			$return = array(
				'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		$event_type            = '';
		$event_target          = '';
		$already_found         = false;
		$event_message_context = __( 'excluded directories', 'website-file-changes-monitor' );

		if ( ! isset( $_POST['event_type'] ) || ! isset( $_POST['event_target'] ) ) {
			$return = array(
				'message' => __( 'Failed', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
		} else {
			$event_type       = sanitize_textarea_field( wp_unslash( $_POST['event_type'] ) );
			$event_target     = sanitize_textarea_field( wp_unslash( $_POST['event_target'] ) );
			$current_settings = Settings_Helper::get_mfm_settings();
		}

		if ( 'exclude-directory' === $event_type ) {
			$current = $current_settings['excluded_directories'];
			if ( in_array( $event_target, $current, true ) ) {
				$already_found = true;
			} else {
				array_push( $current, $event_target );
				Settings_Helper::save_setting( 'excluded_directories', $current );
			}
		} elseif ( 'exclude-file' === $event_type ) {
			$event_message_context = __( 'excluded files', 'website-file-changes-monitor' );
			$current               = $current_settings['excluded_files'];
			if ( in_array( $event_target, $current, true ) ) {
				$already_found = true;
			} else {
				array_push( $current, $event_target );
				Settings_Helper::save_setting( 'excluded_files', $current );
			}
		} else {
			Settings_Helper::save_setting( $event_type, $event_target );
		}

		if ( $already_found ) {
			$return = array(
				'message' => __( 'already added to', 'website-file-changes-monitor' ) . ' ' . $event_message_context,
			);
			wp_send_json_error( $return );
		} else {
			$return = array(
				'message' => __( 'saved to ', 'website-file-changes-monitor' ) . ' ' . $event_message_context,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Validate an input.
	 *
	 * @return array - Response message.
	 */
	public static function validate_setting() {

		$is_nonce_set   = isset( $_POST['nonce'] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], 'mfm_validate_setting_nonce' );  // phpcs:ignore
		}

		if ( ! $is_valid_nonce ) {
			$return = array(
				'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		$event_type            = '';
		$event_target          = '';
		$already_found         = false;
		$event_message_context = __( 'excluded directories', 'website-file-changes-monitor' );

		if ( ! isset( $_POST['event_type'] ) ) {
			$return = array(
				'message' => __( 'Failed', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
		}

		$return = array(
			'message' => __( 'saved to ', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Reset a setting to default value.
	 *
	 * @return array - Response message.
	 */
	public static function reset_setting() {
		$is_nonce_set   = isset( $_POST['nonce'] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], 'mfm_reset_setting_nonce' ); // phpcs:ignore
		}

		if ( ! $is_valid_nonce ) {
			$return = array(
				'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		if ( ! isset( $_POST['target'] ) ) {
			$return = array(
				'message' => __( 'Setting not provided', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		$settings = Settings_Helper::get_mfm_settings();
		$target   = sanitize_textarea_field( wp_unslash( $_POST['target'] ) );

		if ( ! in_array( $target, array_keys( $settings ), true ) ) {
			$return = array(
				'message' => __( 'Setting not found ', 'website-file-changes-monitor' ) . $target,
			);
			wp_send_json_error( $return );
			return;
		}

		$save = Settings_Helper::save_setting( $target, Settings_Helper::get_settings_default_value( $target ) );

		$return = array(
			'message' => $target . ' ' . __( 'reset to default', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Mark an item as read and remove it (and all metadata) from the DB
	 *
	 * @return array - Response message.
	 */
	public static function mark_as_read() {
		$is_nonce_set   = isset( $_POST['nonce'] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], 'mfm_inline_settings_update' ); // phpcs:ignore
		}

		if ( ! $is_valid_nonce ) {
			$return = array(
				'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		if ( ! isset( $_POST['target'] ) ) {
			$return = array(
				'message' => __( 'No target given', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		global $wpdb;
		$events_table_name   = $wpdb->prefix . DB_Handler::$events_table_name;
		$metadata_table_name = $wpdb->prefix . DB_Handler::$events_meta_table_name;
		$target              = sanitize_textarea_field( wp_unslash( $_POST['target'] ) );

		if ( 'all' === $target ) {
			$plugin_options = $wpdb->get_results( $wpdb->prepare( 'TRUNCATE TABLE %1s', $events_table_name ) ); // phpcs:ignore
			$plugin_options = $wpdb->get_results( $wpdb->prepare( 'TRUNCATE TABLE %1s', $metadata_table_name ) ); // phpcs:ignore
		} else {
			$plugin_options = $wpdb->get_results( "DELETE FROM $events_table_name WHERE id IN($target)" ); ; // phpcs:ignore
			$plugin_options = $wpdb->get_results( "DELETE FROM $metadata_table_name WHERE event_id IN($target)" ); ; // phpcs:ignore
		}

		$return = array(
			'message' => __( 'All done', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Finish setup wizard and update settings.
	 *
	 * @return array - Response message.
	 */
	public static function finish_setup_wizard() {
		$is_nonce_set   = isset( $_POST['nonce'] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], 'mfm_finish_setup_wizard' ); ; // phpcs:ignore
		}

		if ( ! $is_valid_nonce || ! isset( $_POST['form_data'] ) ) {
			$return = array(
				'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}
		
		$excluded_exts = array();

		foreach ( wp_unslash( $_POST['form_data'] ) as $item ) { // phpcs:ignore
			preg_match_all( '/\[([^\]]*)\]/', $item['name'], $matches );
			
			if ( isset( $matches[1][0] ) ) {
				if ( 'excluded_file_extensions' == $matches[1][0] ) {
					$excluded_exts[] = $item['value'];
				} else {
					Settings_Helper::save_setting( $matches[1][0], $item['value'] );
				}
			}
		}

		Settings_Helper::save_setting(  'excluded_file_extensions', $excluded_exts );

		if ( isset( $_POST['remove_old_data'] ) && wp_unslash( $_POST['remove_old_data'] ) ) {  // phpcs:ignore
			DB_Handler::purge_wfcm_data();
		}

		update_site_option( MFM_PREFIX . 'initial_setup_needed', false );

		$return = array(
			'message' => __( 'All done', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Dismiss new events notice from admin.
	 *
	 * @return void
	 */
	public static function dismiss_events_notice() {
		$is_nonce_set   = isset( $_POST['nonce'] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], MFM_PREFIX . 'dismiss_notice_nonce' );  // phpcs:ignore
		}

		if ( ! $is_valid_nonce ) {
			$return = array(
				'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		update_site_option( MFM_PREFIX . 'event_notification_dismissed', true );

		$return = array(
			'message' => __( 'All done', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Abort active scan check.
	 *
	 * @return void
	 */
	public static function abort_scan() {
		$return = array(
			'message' => get_site_option( MFM_PREFIX . 'monitor_status' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Looking event by string.
	 *
	 * @return void
	 */
	public static function event_lookup() {

		$is_nonce_set   = isset( $_POST['nonce'] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], MFM_PREFIX . 'event_lookup_nonce' ); // phpcs:ignore
		}

		if ( ! $is_valid_nonce || ! isset( $_POST['lookup_target'] ) ) {
			$return = array(
				'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . DB_Handler::$events_table_name;
		$lookup     = sanitize_textarea_field( wp_unslash( $_POST['lookup_target'] ) );

		$found = $wpdb->get_results( 'SELECT * FROM ' . $table_name . " WHERE path LIKE '%" . $lookup . "%' OR data LIKE '%" . $lookup . "%'", ARRAY_A ); // phpcs:ignore

		if ( isset( $found[0] ) ) {
			$markup = Admin_Manager::create_events_list_markup( $found );
			$return = array(
				'message'    => __( 'found, displaying below', 'website-file-changes-monitor' ),
				'event_data' => $markup,
			);
			wp_send_json_success( $return );
		} else {
			$return = array(
				'message' => __( 'not found', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
		}
	}

	/**
	 * Cancel scan in progress.
	 *
	 * @param boolean $skip_install - Skip install after.
	 * @return void
	 */
	public static function cancel_scan( $skip_install = true ) {

		$is_nonce_set   = isset( $_POST['nonce'] );
		$is_valid_nonce = false;

		if ( ! $skip_install ) {
			if ( $is_nonce_set ) {
				$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], 'mfm_cancel_scan_nonce' ); // phpcs:ignore
			}

			if ( ! $is_valid_nonce ) {
				$return = array(
					'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
				);
				wp_send_json_error( $return );
				return;
			}
		}

		DB_Handler::cancel_current_scan();

		$return = array(
			'message' => __( 'Scan cancelled', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Finish setup wizard and update settings.
	 *
	 * @return array - Response message.
	 */
	public static function canceL_setup_wizard() {
		$is_nonce_set   = isset( $_POST['nonce'] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], 'mfm_cancel_setup_wizard' ); ; // phpcs:ignore
		}

		if ( ! $is_valid_nonce ) {
			$return = array(
				'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}
		
		update_site_option( MFM_PREFIX . 'initial_setup_needed', false );

		$return = array(
			'message' => __( 'All done', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}
}
