<?php
if ( ! class_exists( 'STAdminRentalNew' ) ) {
	class STAdminRentalNew {
		public function __construct() {
			add_action( 'wp_loaded', [ $this, 'after_wp_is_loaded' ] );
		}
		public function after_wp_is_loaded() {
			add_action( 'current_screen', [ $this, 'init_metabox_new' ] );
			add_filter( 'body_class', [ $this, 'custom_class_date_calendar' ] );
		}

		public function custom_class_date_calendar( $classes ) {
			if ( is_singular( 'st_rental' ) ) {
				$allow_full_day = get_post_meta( get_the_ID(), 'allow_full_day', true );
				if ( $allow_full_day !== 'on' ) {
					$classes[] = 'st-no-fullday-booking';
				}
			} elseif ( is_front_page() ) {
				$classes[] = 'st-no-fullday-booking';
			}
			return $classes;
		}

		public function init_metabox_new() {
			$screen = get_current_screen();
			if ( $screen->id != 'st_rental' ) {
				return false;
			}
			$metabox[] = [
				'id'       => 'rental_metabox_custom',
				'title'    => __( 'Rental Custom details', 'traveler' ),
				'desc'     => '',
				'pages'    => [ 'st_rental' ],
				'context'  => 'normal',
				'priority' => 'high',
				'fields'   => [
					[
						'label' => __( 'Allowed full day booking ', 'traveler' ),
						'id'    => 'allow_full_day',
						'type'  => 'on-off',
						'std'   => 'on',
						'desc'  => __( 'You can book room with full day<br/>Eg: booking from 22 -23, then all days 22 and 23 are full, other people cannot book', 'traveler' ),
					],
				],
			];

			register_metabox( $metabox );
		}
	}
	new STAdminRentalNew;
}
