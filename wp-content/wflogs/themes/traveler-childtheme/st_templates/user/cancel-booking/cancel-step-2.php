<?php
/**
 * @since 1.2.8
 *   Cancel booking step 1 - Get order infomation and confirm
 **/
if ( ! isset( $order_id ) ) : ?>
<div class="text-danger">
	<?php echo __( 'Can not get data. Try again!', 'traveler' ); ?>
</div>
<?php else : ?>
<div class="alert alert-info mt20" role="alert">
	<div class=""><strong><?php echo __( 'Select your account that admin can refund for you:', 'traveler' ); ?></strong></div>
	<div class="text-danger" style="font-size: 12px;"><em>(<?php echo __( 'You need to enter all fields required', 'traveler' ); ?>)</em></div>
	<form action="#" class="form mt10" method="post">
		<div class="form-group">
			<label for="">
				<input type="radio" name="select_account" value="your_bank" class="required">
				<span><?php echo __( 'Cash', 'traveler' ); ?></span>
			</label>
		</div>

		<?php
		$st_is_woocommerce_checkout = apply_filters( 'st_is_woocommerce_checkout', false );
		if ( ! $st_is_woocommerce_checkout ) :
			?>
			<div class="form-group">
				<label for="">
					<input type="radio" name="select_account" value="your_stripe" class="required">
					<span><?php echo __( 'Your stripe transaction ID', 'traveler' ); ?></span>
				</label>
			</div>
		<?php endif; ?>

		<div class="form-get-account-inner">
		</div>
	</form>
</div>
<div class="hidden form-get-account">
    <div data-value="your_bank">
        <div class="form-group">
            <label for=""><?php echo __( 'Cash Back Amount: ', 'traveler' ) ?><span class="text-danger">(*)</span></label>
            <input type="currency" class="form-control required" name="account_name" value="<?php echo isset($cancel_data['your_bank']['account_name']) ? esc_attr($cancel_data['your_bank']['account_name']) : ''; ?>" required="required">
        </div>
    </div>
</div>

	<?php
	$st_is_woocommerce_checkout = apply_filters( 'st_is_woocommerce_checkout', false );
	if ( ! $st_is_woocommerce_checkout ) :
		?>
		<div data-value="your_paypal">
			<div class="form-group">
				<label for=""><?php echo __( 'Paypal email: ', 'traveler' ) ?><span class="text-danger">(*)</span></label>
				<input type="text" class="form-control required" name="paypal_email" value="" required="required">
				<i><?php esc_html_e( 'We will pay you in your purchase account.', STP_TEXTDOMAIN ) ?></i>
			</div>
		</div>
		<div data-value="your_stripe">
			<div class="form-group">
				<label for=""><?php echo __( 'Transaction ID: ', 'traveler' ) ?><span class="text-danger">(*)</span></label>
				<input type="text" class="form-control required" name="transaction_id" value="" required="required">
			</div>
		</div>
	<?php endif; ?>
</div>
<?php endif; ?>
