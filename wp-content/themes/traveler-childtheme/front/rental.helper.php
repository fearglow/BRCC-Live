<?php
if ( ! class_exists( 'STRentalHelperNew' ) ) {
	class STRentalHelperNew {
		public function __construct() {
			add_action( 'wp_loaded', [ $this, 'after_wp_is_loaded' ] );
		}
		public function after_wp_is_loaded() {
			remove_all_actions( 'wp_ajax_st_get_availability_rental_single' );
			remove_all_actions( 'wp_ajax_nopriv_st_get_availability_rental_single' );

			add_action( 'wp_ajax_st_get_availability_rental_single', [ $this, '_get_availability_rental_single' ] );
			add_action( 'wp_ajax_nopriv_st_get_availability_rental_single', [ $this, '_get_availability_rental_single' ] );
		}

		public function _get_availability_rental_single() {
			$rental_id     = STInput::request( 'post_id', '' );
			$rental_origin = TravelHelper::post_origin( $rental_id, 'st_rental' );
			if ( STRental::is_groupday( $rental_origin ) ) {
				RentalHelper::_get_availability_rental_single_groupday();
			} else {
				self::_get_availability_rental_single_each_day();
			}
		}
		static function _get_availability_rental_single_each_day() {
			$list_date_booked = [];
			$list_date        = [];
			$rental_id        = STInput::request( 'post_id', '' );
			$rental_origin    = TravelHelper::post_origin( $rental_id, 'st_rental' );
			$check_in         = STInput::request( 'start', '' );
			$check_out        = STInput::request( 'end', '' );
			$discount_type    = get_post_meta( $rental_id, 'discount_type_no_day', true );
			$discount         = get_post_meta( $rental_id, 'discount_rate', true );
			$is_sale_schedule = get_post_meta( $rental_id, 'is_sale_schedule', true );
			$sale_price_from  = get_post_meta( $rental_id, 'sale_price_from', true );
			$sale_price_to    = get_post_meta( $rental_id, 'sale_price_to', true );
			$allow_full_day   = get_post_meta( $rental_origin, 'allow_full_day', true );
			if ( ! $allow_full_day || $allow_full_day == '' ) {
				$allow_full_day = 'on';
			}
			if ( ! is_numeric( $check_in ) ) {
				$check_in = strtotime( $check_in );
			}
			if ( ! is_numeric( $check_out ) ) {
				$check_out = strtotime( $check_out );
			}
			$year = date( 'Y', $check_in );
			if ( empty( $year ) ) {
				$year = date( 'Y' );
			}
			$year2 = date( 'Y', $check_out );
			if ( empty( $year2 ) ) {
				$year2 = date( 'Y' );
			}
			$month = date( 'm', $check_in );
			if ( empty( $month ) ) {
				$month = date( 'm' );
			}
			$month2 = date( 'm', $check_out );
			if ( empty( $month2 ) ) {
				$month2 = date( 'm' );
			}
			$result                  = RentalHelper::_get_full_ordered_new( $rental_origin, $check_in, $check_out );
			$number_rental           = intval( get_post_meta( $rental_origin, 'rental_number', true ) );
			$min_max                 = RentalHelper::_get_min_max_date_ordered_new( $rental_origin, $check_in, $check_out );
			$list_date_fist_half_day = [];
			$list_date_last_half_day = [];
			$array_fist_half_day     = [];
			$array_last_half_day     = [];
			if ( is_array( $min_max ) && count( $min_max ) && is_array( $result ) && count( $result ) ) {
				$disable = [];
				for ( $i = $check_in; $i <= $check_out; $i = strtotime( '+1 day', $i ) ) {
					$num_rental                = 0;
					$num_rental_first_half_day = 0;
					$num_rental_last_half_day  = 0;
					foreach ( $result as $key => $date ) {
						if ( $allow_full_day == 'on' ) {
							if ( $i >= intval( $date['check_in_timestamp'] ) && $i <= intval( $date['check_out_timestamp'] ) ) {
								$num_rental += 1;
							}
						} else {
							if ( $i > intval( $date['check_in_timestamp'] ) && $i < intval( $date['check_out_timestamp'] ) ) {
								$num_rental += 1;
							}
							if ( $i == intval( $date['check_in_timestamp'] ) ) {
								$num_rental_first_half_day += 1;
							}
							if ( $i == intval( $date['check_out_timestamp'] ) ) {
								$num_rental_last_half_day += 1;
							}
						}
					}
					$disable[ $i ]             = $num_rental;
					$array_fist_half_day[ $i ] = $num_rental_first_half_day;
					$array_last_half_day[ $i ] = $num_rental_last_half_day;
				}
				if ( count( $disable ) ) {
					foreach ( $disable as $key => $num_room ) {
						if ( intval( $num_room ) >= $number_rental ) {
							$list_date[] = date( TravelHelper::getDateFormat(), $key );
						}
					}
				}
				if ( count( $array_fist_half_day ) ) {
					foreach ( $array_fist_half_day as $key => $num_rental ) {
						if ( intval( $num_rental ) >= $number_rental ) {
							$list_date_fist_half_day[] = date( TravelHelper::getDateFormat(), $key );
						}
					}
				}
				if ( count( $array_last_half_day ) ) {
					foreach ( $array_last_half_day as $key => $num_rental ) {
						if ( intval( $num_rental ) >= $number_rental ) {
							$list_date_last_half_day[] = date( TravelHelper::getDateFormat(), $key );
						}
					}
				}
			}

			$list_date_2    = AvailabilityHelper::_getDisableCustomDateRental( $rental_origin, $month, $month2, $year, $year2 );
			$date1          = strtotime( $year . '-' . $month . '-01' );
			$date2          = strtotime( $year2 . '-' . $month2 . '-01' );
			$date2          = strtotime( date( 'Y-m-t', $date2 ) );
			$today          = strtotime( date( 'Y-m-d' ) );
			$return         = [];
			$booking_period = intval( get_post_meta( $rental_origin, 'rentals_booking_period', true ) );

			$rental_booked = ST_Rental_Availability::inst()
				->where( 'check_in >=', $check_in )
				->where( 'check_out <=', $check_out )
				->where( 'post_id', $rental_origin )
				->where( 'number_booked >=', $number_rental )
				->get()->result();

			if ( isset( $rental_booked ) ) {
				foreach ( $rental_booked as $kk => $vv ) {
					$list_date_booked[] = date( TravelHelper::getDateFormat(), $vv['check_in'] );
				}
			}
			for ( $i = $date1; $i <= $date2; $i = strtotime( '+1 day', $i ) ) {
				$period = STDate::dateDiff( date( 'Y-m-d', $today ), date( 'Y-m-d', $i ) );
				$d      = date( TravelHelper::getDateFormat(), $i );
				if ( in_array( $d, $list_date ) || ( in_array( $d, $list_date_fist_half_day ) && in_array( $d, $list_date_last_half_day ) ) ) {
					// Booked
					$return['events'][] = [
						'start'  => date( 'Y-m-d', $i ),
						'date'   => date( 'Y-m-d', $i ),
						'day'    => date( 'd', $i ),
						'event'  => esc_attr__( 'Booked', 'traveler' ),
						'price'  => esc_attr__( 'Booked', 'traveler' ),
						'status' => 'not_available',
					];
				} elseif ( $i < $today ) {
						// past
						$return['events'][] = [
							'start'  => date( 'Y-m-d', $i ),
							'date'   => date( 'Y-m-d', $i ),
							'day'    => date( 'd', $i ),
							'event'  => esc_attr__( 'Unavailable', 'traveler' ),
							'price'  => esc_attr__( 'Unavailable', 'traveler' ),
							'status' => 'not_available',
						];
				} else {
					// disabled
					if ( in_array( $d, $list_date_2 ) ) {
						$return['events'][] = [
							'start'  => date( 'Y-m-d', $i ),
							'date'   => date( 'Y-m-d', $i ),
							'day'    => date( 'd', $i ),
							'event'  => esc_attr__( 'Unavailable', 'traveler' ),
							'price'  => esc_attr__( 'Unavailable', 'traveler' ),
							'status' => 'not_available',
						];
					} elseif ( $period < $booking_period ) {
						$return['events'][] = [
							'start'  => date( 'Y-m-d', $i ),
							'date'   => date( 'Y-m-d', $i ),
							'day'    => date( 'd', $i ),
							'event'  => esc_attr__( 'Unavailable', 'traveler' ),
							'price'  => esc_attr__( 'Unavailable', 'traveler' ),
							'status' => 'not_available',
						];
					} elseif ( in_array( $d, $list_date_fist_half_day ) ) {
						$return['events'][] = [
							'start'  => date( 'Y-m-d', $i ),
							'date'   => date( 'Y-m-d', $i ),
							'day'    => date( 'd', $i ),
							'status' => 'available_allow_fist',
							'event'  => TravelHelper::format_money( STPrice::getRentalPriceOnlyCustomPrice( $rental_origin, $i, strtotime( '+1 day', $i ) ) ),
							'price'  => TravelHelper::format_money( STPrice::getRentalPriceOnlyCustomPrice( $rental_origin, $i, strtotime( '+1 day', $i ) ) ),
						];
					} elseif ( in_array( $d, $list_date_last_half_day ) ) {
						$return['events'][] = [
							'start'  => date( 'Y-m-d', $i ),
							'date'   => date( 'Y-m-d', $i ),
							'day'    => date( 'd', $i ),
							'status' => 'available_allow_last',
							'event'  => TravelHelper::format_money( STPrice::getRentalPriceOnlyCustomPrice( $rental_origin, $i, strtotime( '+1 day', $i ) ) ),
							'price'  => TravelHelper::format_money( STPrice::getRentalPriceOnlyCustomPrice( $rental_origin, $i, strtotime( '+1 day', $i ) ) ),
						];
					} else {
						$return['events'][] = [
							'start'  => date( 'Y-m-d', $i ),
							'date'   => date( 'Y-m-d', $i ),
							'day'    => date( 'd', $i ),
							// 'status' => 'available',
							'event'  => TravelHelper::format_money( STPrice::getRentalPriceOnlyCustomPrice( $rental_origin, $i, strtotime( '+1 day', $i ) ) ),
							'price'  => TravelHelper::format_money( STPrice::getRentalPriceOnlyCustomPrice( $rental_origin, $i, strtotime( '+1 day', $i ) ) ),
							'status' => 'available',
						];
					}
				}
			}
			echo json_encode( $return );
			die;
		}
	}
	new STRentalHelperNew;
}
