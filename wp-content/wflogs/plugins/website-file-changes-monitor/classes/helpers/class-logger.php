<?php
/**
 * Handle logging during scans.
 *
 * @package mfm
 */

namespace MFM\Helpers;

use \MFM\Helpers\Settings_Helper; // phpcs:ignore

/**
 * Utility file and directory functions.
 */
class Logger {

	/**
	 * Write data to log file.
	 *
	 * @param string $data     - Data to write to file.
	 * @param bool   $override - Set to true if overriding the file.
	 * @return bool
	 */
	public static function mfm_write_to_log( $data, $override = false ) {
		if ( ! is_dir( MFM_UPLOADS_DIR . MFM_LOGS_DIR ) ) {
			self::mfm_create_index_file( MFM_LOGS_DIR );
			self::mfm_create_htaccess_file( MFM_LOGS_DIR );
		}

		return self::mfm_write_to_file( trailingslashit( MFM_LOGS_DIR ) . 'mfm-debug.log', $data, $override );
	}

	/**
	 * Create an index.php file, if none exists, in order to
	 * avoid directory listing in the specified directory.
	 *
	 * @param string $dir_path - Directory Path.
	 * @return bool
	 */
	public static function mfm_create_index_file( $dir_path ) {
		return self::mfm_write_to_file( trailingslashit( $dir_path ) . 'index.php', '<?php // Silence is golden' );
	}

	/**
	 * Create an .htaccess file, if none exists, in order to
	 * block access to directory listing in the specified directory.
	 *
	 * @param string $dir_path - Directory Path.
	 * @return bool
	 */
	public static function mfm_create_htaccess_file( $dir_path ) {
		return self::mfm_write_to_file( trailingslashit( $dir_path ) . '.htaccess', 'Deny from all' );
	}

	/**
	 * Returns the timestamp for log files.
	 *
	 * @return string
	 */
	public static function mfm_get_log_timestamp() {
		return '[' . date( 'd-M-Y H:i:s' ) . ' UTC]';
	}

	/**
	 * Write data to log file in the uploads directory.
	 *
	 * @param string $filename - File name.
	 * @param string $content  - Contents of the file.
	 * @param bool   $override - (Optional) True if overriding file contents.
	 * @return bool
	 */
	public static function mfm_write_to_file( $filename, $content, $override = false ) {
		global $wp_filesystem;
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();

		$filepath = MFM_UPLOADS_DIR . $filename;
		$dir_path = dirname( $filepath );
		$result   = false;

		if ( ! is_dir( $dir_path ) ) {
			wp_mkdir_p( $dir_path );
		}

		if ( ! $wp_filesystem->exists( $filepath ) || $override ) {
			$result = $wp_filesystem->put_contents( $filepath, $content );
		} else {
			$existing_content = $wp_filesystem->get_contents( $filepath );
			$result           = $wp_filesystem->put_contents( $filepath, $existing_content . $content );
		}

		return $result;
	}
}
