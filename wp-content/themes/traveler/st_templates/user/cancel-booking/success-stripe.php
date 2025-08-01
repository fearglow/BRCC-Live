<?php
/**
 * @since 1.2.8
 *   success stripe
 **/
?>
<div class="booking-cancel-notice">
	<img src="<?php echo get_template_directory_uri(); ?>/v2/images/ico_success.svg" alt="Booking Cancel Success"/>
	<div class="notice-success">
		<h4><?php echo __( 'Your refund request has been successfully delivered.', 'traveler' ); ?></h4>
		<p><?php echo __( 'Please wait for confirmation from our billing team!', 'traveler' ); ?></p>
	</div>
</div>
<div class="alert alert-info mt20" role="alert">
	<p><strong><?php echo __( 'Refund will return to the same card used for the booking:', 'traveler' ); ?></strong></p>
	<p class="mt10"><strong><?php echo __( 'Amount: ', 'traveler' ) ?></strong> <em><?php echo TravelHelper::format_money_raw( $cancel_data['refunded'], $cancel_data['currency'] ); ?></em></p>
	<!-- <p class="mt20"><strong><?php echo __( 'Description: ', 'traveler' ) ?></strong> <em><?php echo esc_html( $cancel_data['detail'] ); ?></em></p> -->
</div>
