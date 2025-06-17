<?php

class ST_Booking {

	public function __construct() {
		// booking edit
		add_action( 'st_admin_edit_booking_before_update_status', [ $this, 'update' ], 10, 2 );
		add_action( 'vina_stripe_before_update_status', [ $this, 'update_stripe' ], 10, 2 );
	}

	public function update( $status, $order_id ) {
		$post_type = get_post_meta( $order_id, 'item_post_type', true );
		if ( $post_type != 'st_rental' ) {
			return;
		}

		// Update priority rental
		if ( empty( st_get_order_by_order_item_id( $order_id ) ) ) {
			return;
		}
		global $wpdb;
		$check_in_timestamp   = st_get_order_by_order_item_id( $order_id )['check_in_timestamp'];
		$check_out_timestamp  = st_get_order_by_order_item_id( $order_id )['check_out_timestamp'];
		$status_order         = st_get_order_by_order_item_id( $order_id )['status'];
		$post_id              = st_get_order_by_order_item_id( $order_id )['st_booking_id'];
		$table_st_rental_avai = $wpdb->prefix . 'st_rental_availability';

		if ( $status_order != 'complete' && $_POST['status'] == 'complete' ) {
			for ( $i = $check_in_timestamp; $i <= $check_out_timestamp; $i = strtotime( '+1 day', $i ) ) {
				$where_avai = [
					'post_id'  => $post_id,
					'check_in' => $i,
				];

				if ( $i == $check_in_timestamp ) {
					$data_avai = [
						'priority' => 1,
					];
				} elseif ( $i == $check_out_timestamp ) {
					$data_avai = [
						'priority' => 2,
					];
				} else {
					$data_avai = [
						'priority' => 0,
					];
				}

				$wpdb->update( $table_st_rental_avai, $data_avai, $where_avai );
			}
		}
	}
	
	public function update_stripe( $status, $order_id ) {
		$post_type = get_post_meta( $order_id, 'item_post_type', true );
		if ( $post_type != 'st_rental' ) {
			return;
		}

		// Update priority rental
		if ( empty( st_get_order_by_order_item_id( $order_id ) ) ) {
			return;
		}
		global $wpdb;
		$check_in_timestamp   = st_get_order_by_order_item_id( $order_id )['check_in_timestamp'];
		$check_out_timestamp  = st_get_order_by_order_item_id( $order_id )['check_out_timestamp'];
		$status_order         = st_get_order_by_order_item_id( $order_id )['status'];
		$post_id              = st_get_order_by_order_item_id( $order_id )['st_booking_id'];
		$table_st_rental_avai = $wpdb->prefix . 'st_rental_availability';

		for ( $i = $check_in_timestamp; $i <= $check_out_timestamp; $i = strtotime( '+1 day', $i ) ) {
			$where_avai = [
				'post_id'  => $post_id,
				'check_in' => $i,
			];

			if ( $i == $check_in_timestamp ) {
				$data_avai = [
					'priority' => 1,
				];
			} elseif ( $i == $check_out_timestamp ) {
				$data_avai = [
					'priority' => 2,
				];
			} else {
				$data_avai = [
					'priority' => 0,
				];
			}

			$wpdb->update( $table_st_rental_avai, $data_avai, $where_avai );
		}
	}
}

new ST_Booking;
