<?php
/**
 * Handle logging during scans.
 *
 * @package mfm
 */

namespace MFM\Helpers;

use \MFM\Helpers\Settings_Helper; // phpcs:ignore
use \MFM\Helpers\Logger; // phpcs:ignore

/**
 * Utility file and directory functions.
 */
class Events_Helper {

	/**
	 * Create and output neat label for use in events view.
	 *
	 * @param string $types - Lookup type.
	 * @param string $current_view - View.
	 * @return void.
	 */
	public static function create_event_type_label( $types, $current_view = 'all', $return_instead_of_echo = false ) {

		$event_labels = array(
			'file-scan-started'        => __( 'File scan started ', 'website-file-changes-monitor' ),
			'core-file-modified'       => __( 'Core file modified ', 'website-file-changes-monitor' ),
			'core-file-renamed'        => __( 'Core file renamed ', 'website-file-changes-monitor' ),
			'core-file-added'          => __( 'Core file added ', 'website-file-changes-monitor' ),
			'core-file-removed'        => __( 'Core file removed ', 'website-file-changes-monitor' ),
			'core-directory-added'     => __( 'Core Directory added ', 'website-file-changes-monitor' ),
			'core-directory-modified'  => __( 'Core Directory modified ', 'website-file-changes-monitor' ),
			'core-directory-removed'   => __( 'Core Directory removed ', 'website-file-changes-monitor' ),
			'file-scan-complete'       => __( 'File scan complete ', 'website-file-changes-monitor' ),
			'file-scan-aborted'        => __( 'File scan aborted ', 'website-file-changes-monitor' ),
			'other-file-added'         => __( 'File(s) added ', 'website-file-changes-monitor' ),
			'other-file-modified'      => __( 'File(s) modified ', 'website-file-changes-monitor' ),
			'other-file-removed'       => __( 'File(s) removed ', 'website-file-changes-monitor' ),
			'other-file-renamed'       => __( 'File(s) renamed ', 'website-file-changes-monitor' ),
			'other-directory-added'    => __( 'Directory added ', 'website-file-changes-monitor' ),
			'other-directory-modified' => __( 'Directory modified ', 'website-file-changes-monitor' ),
			'other-directory-removed'  => __( 'Directory removed ', 'website-file-changes-monitor' ),
			'plugin-file-added'        => __( 'Plugin file(s) added ', 'website-file-changes-monitor' ),
			'plugin-file-modified'     => __( 'Plugin file(s) modified ', 'website-file-changes-monitor' ),
			'plugin-file-renamed'      => __( 'Plugin file(s) renamed ', 'website-file-changes-monitor' ),
			'plugin-directory-removed' => __( 'Plugin removed ', 'website-file-changes-monitor' ),
			'plugin-directory-added'   => __( 'Plugin added ', 'website-file-changes-monitor' ),
			'plugin-updated'           => __( 'Plugin updated ', 'website-file-changes-monitor' ),
			'theme-file-modified'      => __( 'Theme file(s) modified ', 'website-file-changes-monitor' ),
			'theme-file-renamed'       => __( 'Theme file(s) renamed ', 'website-file-changes-monitor' ),
			'theme-directory-removed'  => __( 'Theme removed ', 'website-file-changes-monitor' ),
			'theme-directory-added'    => __( 'Theme added ', 'website-file-changes-monitor' ),
			'theme-updated'            => __( 'Theme updated ', 'website-file-changes-monitor' ),
		);

		$label = $types;
		if ( strpos( $types, ',' ) !== false ) {
			$label     = '';
			$types_arr = explode( ',', $types );
			if ( 'all' !== $current_view ) {
				$label = ( isset( $event_labels[ $types_arr[0] ] ) ) ? $event_labels[ $types_arr[0] ] : $types;
				$label = preg_replace( '/\W\w+\s*(\W*)$/', '$1', $label ) . ' ' . $current_view;
			} else {
				foreach ( $types_arr as $key => $value ) {
					if ( isset( $event_labels[ $value ] ) ) {
						$label = ( '' === $label || ! $label ) ? $event_labels[ $value ] : $label . ' & ' . $event_labels[ $value ];
					} else {
						$label = ( '' === $label || ! $label ) ? $value : $label . ' & ' . $value;
					}
				}
			}
		} else {
			if ( isset( $event_labels[ $types ] ) ) {
				$label = $event_labels[ $types ];
			}
		}

		if ( $return_instead_of_echo ) {
			return $label;
		} else {
			echo '<strong>' . esc_html__( 'Event Type:', 'website-file-changes-monitor' ) . '</strong> ' . $label . '<br>'; // phpcs:ignore
		}   }

	/**
	 * Create and output distinguishing label markup for use in events list.
	 *
	 * @param string $path - Current path.
	 * @param array  $plugin_list - Current plugin list.
	 * @param array  $core_file_keys - Core files list.
	 * @return void
	 */
	public static function create_list_label( $path, $plugin_list, $core_file_keys ) {
		$event['path'] = $path;
		$theme_dir     = dirname( get_template_directory() );
		if ( in_array( $event['path'], $plugin_list, true ) || strpos( $event['path'], WP_PLUGIN_DIR ) !== false ) {
			echo '<strong style="color: blue;">' . esc_html__( 'PLUGIN', 'website-file-changes-monitor' ) . '</strong><br>';
		} elseif ( strpos( $event['path'], $theme_dir ) !== false ) {
			echo '<strong style="color: orange;">' . esc_html__( 'THEME', 'website-file-changes-monitor' ) . '</strong><br>';
		} elseif ( in_array( $event['path'], $core_file_keys, true ) || trim( trailingslashit( $event['path'] ) ) === ABSPATH || (string) $path == ABSPATH || trailingslashit( (string) $path ) == ABSPATH . 'wp-includes/' || trailingslashit( (string) $path ) == ABSPATH . 'wp-admin/' ) {
			echo '<strong style="color: red;">' . esc_html__( 'CORE', 'website-file-changes-monitor' ) . '</strong><span class="mfm-info-hint hint--right" aria-label="' . esc_html__( 'This event indicates a core file was has been modified from its expected hash', 'website-file-changes-monitor' ) . '"><span class="dashicons dashicons-warning"></span></span><br>';
		} else {
			echo '<strong style="color: grey;">' . esc_html__( 'OTHER', 'website-file-changes-monitor' ) . '</strong><br>';
		}
	}

	/**
	 * Create and output HTML for file list, shown in each event in the events viewer.
	 *
	 * @param array  $items - Event items.
	 * @param string $current_view - Current view.
	 * @param int    $event_id - Event ID.
	 * @return void
	 */
	public static function create_file_list( $items, $current_view, $event_id ) {
		foreach ( $items as $type => $item ) {
			$file_array = maybe_unserialize( $item );

			if ( ! is_array( $file_array ) ) {
				$file_array = maybe_unserialize( str_replace( ABSPATH, '', $item ) );
			}

			if ( is_array( $file_array ) && ! empty( $file_array ) ) {
				$expand_string = '';
				if ( isset( $file_array['modified'] ) ) {
					if ( ! empty( $file_array['modified'] ) ) {
						$count          = ( count( $file_array['modified'] ) > 500 ) ? '500+' : count( $file_array['modified'] );
						$expand_string .= $count . ' ' . esc_html__( 'modified files', 'website-file-changes-monitor' );
					}
				}
				if ( isset( $file_array['added'] ) ) {
					if ( ! empty( $file_array['added'] ) ) {
						$count          = ( count( $file_array['added'] ) > 500 ) ? '500+' : count( $file_array['added'] );
						$expand_string .= ( empty( $expand_string ) ) ? $count . ' ' . esc_html__( 'added files', 'website-file-changes-monitor' ) : ', ' . $count . ' ' . esc_html__( 'added files', 'website-file-changes-monitor' );
					}
				}
				if ( isset( $file_array['removed'] ) ) {
					if ( ! empty( $file_array['removed'] ) ) {
						$count          = ( count( $file_array['removed'] ) > 500 ) ? '500+' : count( $file_array['removed'] );
						$expand_string .= ( empty( $expand_string ) ) ? $count . ' ' . esc_html__( 'removed files', 'website-file-changes-monitor' ) : ', ' . $count . ' ' . esc_html__( 'removed files', 'website-file-changes-monitor' );
					}
				}

				$expander_needed = true;
				$item_class = '';
				foreach ( $file_array as $change_type => $changes ) {
					if ( count( $changes ) < 3 ) {
						$expander_needed = false;
						$item_class = 'expanded';
					}
				}

				if ( $expander_needed ) {
					echo '<span data-expand-list-wrapper><a href="#" data-expand-list class="hint--right" aria-label="' . esc_html__( 'Click to view changes', 'website-file-changes-monitor' ) . '"><span class="dashicons dashicons-arrow-right-alt"></span></a>' . esc_attr( $expand_string ) . '</span>';
				}		

				foreach ( $file_array as $change_type => $changes ) {
					if ( ! is_array( $changes ) ) {
								continue;
					}

					foreach ( $changes as $file ) {
						if ( 'additional_external_data' === $file ) {
							echo '<span><span data-additional-prefix-holder>' . esc_html__( 'Additional changes found:', 'website-file-changes-monitor' ) . '</span> <a href="#" data-mfm-load-further-changes="' . esc_attr( $event_id ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'mfm_load_extra_metadata' ) ) . '">' . esc_html__( 'Load additional changes', 'website-file-changes-monitor' ) . '</a> <span class="mfm-action-spinner"><div class="icon-spin"><span class="dashicons dashicons-admin-generic"></span></div></span></span>';
						} else {
							if ( 'all' !== $current_view ) {
								if ( $change_type !== $current_view ) {
									continue;
								}
							}
							echo '<span class="mfm-list-item ' . esc_attr( $item_class ) . '">File ' . esc_attr( ucfirst( $change_type ) ) . ': ' . esc_attr( str_replace( ABSPATH, '', $file ) ) . ' <div class="mfm_file_actions_panel"><a href="#" data-mfm-update-setting data-exclude-file="' . esc_attr( $file ) . '" class="hint--left" aria-label="Exclude file from future  scans"><span class="dashicons dashicons-insert"></span></a></div></span>';
						}
					}
				}
			} else {
				if ( is_array( $file_array ) && empty( $file_array ) ) {
					echo '<span data-empty-dir-wrapper>' . esc_html__( 'Empty directory', 'website-file-changes-monitor' ) . '</span>';
				} else {
					echo wp_kses_post( $file_array );
				}
			}
		}
	}
}
