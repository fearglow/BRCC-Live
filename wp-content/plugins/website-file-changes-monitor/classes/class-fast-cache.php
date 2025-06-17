<?php
/**
 * Caching class file.
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

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use MFM\DB_Handler;

/**
 * Class for caching during a run.
 *
 * @since 2.0.0
 */
class MFM_Fast_Cache {

	/**
	 * Default directory.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	public static $caching_directory = '';

	/**
	 * Set the caching directory location
	 *
	 * @return void
	 *
	 * @since 2.0.0
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
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function add_to_cache( $data_string, $target_cache = 'directory_runner_cache' ) {
		$obj_files_cache = CacheManager::getInstance( 'files' );
		$current_cache   = $obj_files_cache->getItem( $target_cache );
		$data_string     = $data_string . ',';
		$current_cache->append( $data_string );
		$obj_files_cache->save( $current_cache );

		if ( $current_cache->getLength() >= 50000 ) {
			DB_Handler::dump_into_db( 'directory_runner_cache' );
		}
	}
}
