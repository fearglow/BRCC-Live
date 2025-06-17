<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 21/08/2015
 * Time: 9:45 SA
 */

add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles', 20 );

function enqueue_parent_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'child-style', get_stylesheet_uri() );
}

function register_metabox( $custom_metabox ) {
	/**
	 * Register our meta boxes using the
	 * ot_register_meta_box() function.
	 */
	if ( function_exists( 'ot_register_meta_box' ) ) {
		if ( ! empty( $custom_metabox ) ) {
			foreach ( $custom_metabox as $value ) {
				ot_register_meta_box( $value );
			}
		}
	}
}

function loadFrontInit() {
	require __DIR__ . '/front/class.admin.rental.php';
	require __DIR__ . '/front/rental.helper.php';

	require __DIR__ . '/front/class.rental.php';
	require __DIR__ . '/front/booking-rental.php';
}
add_action( 'init', 'loadFrontInit' );
