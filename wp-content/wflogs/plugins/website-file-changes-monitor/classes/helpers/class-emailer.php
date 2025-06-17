<?php
/**
 * Handle emails.
 *
 * @package mfm
 */

namespace MFM\Helpers;

use \MFM\Helpers\Settings_Helper; // phpcs:ignore
use \MFM\Helpers\Logger; // phpcs:ignore
use \MFM\DB_Handler; // phpcs:ignore
use \MFM\Helpers\Events_Helper; // phpcs:ignore

/**
 * Utility file and directory functions.
 */
class Emailer {

	/**
	 * Send a summary of any changes of last scan.
	 *
	 * @param integer $scan_run_id - Scan ID.
	 * @return void
	 */
	public static function send_scan_summary( $scan_run_id = 0 ) {
		
		if ( 'yes' === Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
			$msg  = Logger::mfm_get_log_timestamp() . ' EMAIL SUMMARY SEND' . " \n";
			$msg .= Logger::mfm_get_log_timestamp() . ' ' . Settings_Helper::get_notification_email() . " \n";
			$msg .= Logger::mfm_get_log_timestamp() . ' ' . $scan_run_id . " \n";
			Logger::mfm_write_to_log( $msg );
		}

		if ( 0 == $scan_run_id ) { // phpcs:ignore
			$scan_run_id = get_site_option( MFM_PREFIX . 'active_scan_id' );
		}

		$enabled_notifications = Settings_Helper::get_setting( 'enabled-notifications' );
		$events                = DB_Handler::get_events_for_specific_run( $scan_run_id, $enabled_notifications );
		
		$to       = Settings_Helper::get_notification_email();
		$home_url = home_url();
		$safe_url = str_replace( array( 'http://', 'https://' ), '', $home_url );
		$subject  = 'File changes from file integrity scan';

		$limit      = (int) Settings_Helper::get_setting( 'email-changes-limit', 10 );
		$send_empty = ( 'yes' === Settings_Helper::get_setting( 'empty-email-allowed' ) ) ? true : false;

		if ( empty( $events ) && ! $send_empty ) {
			return;
		}

		if ( ! empty( $events ) ) {
			$message = '<p>The below is a list file change events from your most recent scan</p>' . " \n";

			foreach ( $events as $event ) {
				$message .= " \n";
				$message .= '<p><strong>' . Events_Helper::create_event_type_label( $event['event_type'], 'all', true ) . '</strong></p>' . " \n";

				$items  = explode( ',', $event['data'] );
				$prefix = 'added';
				if ( strpos( $event['event_type'], 'removed' ) !== false ) {
					$prefix = 'removed';
				} elseif ( strpos( $event['event_type'], 'modified' ) !== false ) {
					$prefix = 'modified';
				}

				$message .= '<p><strong>Directory: </strong>' . $event['path'] . '</p>' . " \n";

				foreach ( $items as $item => $value ) {
					$is_arr = maybe_unserialize( $value );

					if ( ! is_array( $is_arr ) ) {
						if ( Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) {
							$msg  = Logger::mfm_get_log_timestamp() . ' Email item skipped, not array' . " \n";
							$msg .= Logger::mfm_get_log_timestamp() . $is_arr . " \n";
							Logger::mfm_write_to_log( $msg );
						}
						continue;
					}
					$val_i = 0;
					foreach ( $is_arr as $type => $item ) {
						if ( $val_i < $limit ) {
							if ( is_array( $item ) ) {
								$i = 0;
								foreach ( $item as $file ) {
									if ( $i < $limit ) {
										$message .= '<p>File ' . ucfirst( $type ) . ': ' . $file . '</p>' . " \n";
									}

									++$i;

								}
							} else {
								$message .= '<p>File ' . ucfirst( $type ) . ': ' . str_replace( ABSPATH, '', $item ) . '</p>' . " \n";
							}
						}
						++$val_i;
					}
				}
			}
		} else {
			$message = '<p>There were no file changes detected during the last file integrity scan</p>' . " \n";
		}

		$message .= " \n" . '<p>' . __( 'Visit the File Monitor in the WordPress dashboard to check the file changes.', 'website-file-changes-monitor' ) . '</p>' . " \n";

		/* Translators: %s: Plugin WP Hyperlink */
		$message .= '<p>' . sprintf( __( 'This file integrity scan was done with the %s.', 'website-file-changes-monitor' ), '<a href="https://wordpress.org/plugins/website-file-changes-monitor/" target="_blank">' . __( 'Melapress File Monitor', 'website-file-changes-monitor' ) . '</a>' ) . '</p>';

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		add_filter( 'wp_mail_content_type', array( __CLASS__, 'set_html_content_type' ) );

		$result = wp_mail( $to, $subject, $message );

		remove_filter( 'wp_mail_content_type', array( __CLASS__, 'set_html_content_type' ) );
		return $result;
	}

	/**
	 * Filter the mail content type.
	 */
	public static function set_html_content_type() {
		return 'text/html';
	}

	/**
	 * Send an email test to provided address.
	 *
	 * @return array - Response.
	 */
	public static function send_test_email() {

		$is_nonce_set   = isset( $_POST['nonce'] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST['nonce'], 'mfm_test_email_nonce' ); // phpcs:ignore
		}

		if ( ! $is_valid_nonce ) {
			$return = array(
				'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		$to = isset( $_POST['email_address'] ) ? sanitize_email( wp_unslash( $_POST['email_address'] ) ) : false;

		if ( ! $to ) {
			$return = array(
				'message' => __( 'No email supplied', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
		}

		$subject = esc_html__( 'MFM Email Test', 'website-file-changes-monitor' );
		$message = esc_html__( 'This is a test message, sent from the Melapress File Monitor plugin', 'website-file-changes-monitor' );
		$result  = wp_mail( $to, $subject, $message );

		if ( ! $result ) {
			$return = array(
				'message' => __( 'Test failed', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
		}

		$return = array(
			'message' => __( 'Test email successfully sent', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}
}
