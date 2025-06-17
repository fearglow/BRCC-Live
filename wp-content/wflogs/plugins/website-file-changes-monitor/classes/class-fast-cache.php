<?php
/**
 * Caching class file.
 *
 * @package mfm
 */

namespace MFM;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\CacheItemInterface;
use \MFM\DB_Handler; // phpcs:ignore

/**
 * Class for caching during a run.
 */
class MFM_Fast_Cache {

	/**
	 * Default directory.
	 *
	 * @var string
	 */
	public static $caching_directory = '';

	/**
	 * Set the caching directory location
	 *
	 * @return void
	 */
	public static function setup_cache_path() {
		$path                    = wp_upload_dir();
		$path                    = $path['path'];
		self::$caching_directory = $path . '/tmp';

		CacheManager::setDefaultConfig(
			new ConfigurationOption(
				array(
					'path' => self::$caching_directory,
				)
			)
		);
	}

	/**
	 * Add data to cache.
	 *
	 * @param string $data_string - Incoming data.
	 * @param string $target_cache - Where is it going.
	 * @return void
	 */
	public static function add_to_cache( $data_string, $target_cache = 'directory_runner_cache' ) {
		$obj_files_cache   = CacheManager::getInstance( 'files' );
		$number_of_seconds = 60;
		$current_cache     = $obj_files_cache->getItem( $target_cache );
		$data_string       = $data_string . ',';
		$current_cache->append( $data_string );
		$obj_files_cache->save( $current_cache );

		if ( $current_cache->getLength() >= 50000 ) {
			self::dump_into_db( 'directory_runner_cache' );
		}
	}

	/**
	 * Empty cache content into the DB.
	 *
	 * @param string $target_cache - Where is it.
	 * @return void
	 */
	public static function dump_into_db( $target_cache = 'directory_runner_cache' ) {
		$obj_files_cache = CacheManager::getInstance( 'files' );
		$current_cache   = $obj_files_cache->getItem( $target_cache );
		$data            = $current_cache->get();

		if ( ! is_null( $data ) ) {
			$data = rtrim( $data, ',' ) . ';';

			global $wpdb;
			$table_name = $wpdb->prefix . \MFM\DB_Handler::$scanned_directories_table_name;

			$setting_save = $wpdb->query(  // phpcs:ignore
				"
                INSERT INTO $table_name
                ( path, time )
                VALUES " . $data  // phpcs:ignore
			);

			$setting_save = $wpdb->query( // phpcs:ignore
				"
                DELETE t1 FROM $table_name t1, $table_name t2 
                WHERE t1.id < t2.id 
                AND t1.path = t2.path
            "
			);

			$obj_files_cache->clear();
		}
	}
}
