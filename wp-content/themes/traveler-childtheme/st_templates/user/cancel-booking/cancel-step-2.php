<?php
/**
 * @since 1.2.8
 * Cancel booking step 1 - Get order information and confirm refund
 **/
if ( ! isset( $order_id ) ) : ?>
    <div class="text-danger" style="font-size:16px; padding:10px;">
        <?php echo __( 'Unable to retrieve your booking details. Please try again.', 'traveler' ); ?>
    </div>
<?php else :

// If $cancel_data is not set, recalculate the cancellation details (logic copied from step 1)
if ( ! isset( $cancel_data ) ) {
    $item_id     = (int) get_post_meta( $order_id, 'st_booking_id', true );
    $total_price = (float) get_post_meta( $order_id, 'total_price', true );
    $currency    = STUser_f::_get_currency_book_history( $order_id );
    $percent     = (int) get_post_meta( $item_id, 'st_cancel_percent', true );
    $refunded    = $total_price - ( $total_price * $percent / 100 );
    $detail      = isset($_POST['detail']) ? sanitize_text_field($_POST['detail']) : '';
    
    $cancel_data = array(
        'order_id' => $order_id,
        'refunded' => $refunded,
        'currency' => $currency,
        'detail'   => $detail,
    );
}
?>

<div class="alert alert-info mt20" role="alert" style="padding:15px; border-radius:5px; box-shadow:0 2px 5px rgba(0,0,0,0.05); transition: background 0.3s;">
    <div style="font-size:18px; font-weight:bold;"><?php echo __( 'Confirm Your Refund Request', 'traveler' ); ?></div>
    <div style="font-size:13px; color:#a94442; margin-bottom:10px;">
        <em>(<?php echo __( 'By confirming, you acknowledge that your refund request is final and cannot be undone.', 'traveler' ); ?>)</em>
    </div>
    <form action="#" class="form mt10" method="post">
        <!-- Hidden radio input for JS detection -->
        <input type="radio" name="select_account" value="your_stripe" checked style="display:none;">
        
        <!-- Visible confirmation checkbox with modern styling -->
        <div class="form-group" style="margin-bottom:15px;">
            <label style="font-size:14px; cursor:pointer;">
                <input type="checkbox" id="confirm_refund_checkbox" class="required" style="margin-right:8px;">
                <span><?php echo __( 'I confirm that I want to request a refund.', 'traveler' ); ?></span>
            </label>
        </div>
        
        <div class="form-get-account-inner" style="transition: all 0.3s ease-in-out;">
            <!-- This block corresponds to the hidden radio value -->
            <div data-value="your_stripe">
                <div class="form-group">
                    <p style="font-size:14px; color:#555; margin:0;"><?php echo __( 'Your refund will be automatically processed to your credit card within 3-5 business days.', 'traveler' ); ?></p>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="refund-summary mt20" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #fafafa; transition: all 0.3s ease-in-out;">
    <div class="clearfix" style="margin-bottom: 8px;">
        <span style="display:inline-block; width: 45%; font-weight:bold;"><?php echo __('Order ID:', 'traveler'); ?></span>
        <span style="display:inline-block;"><?php echo isset($cancel_data['order_id']) ? esc_html($cancel_data['order_id']) : ''; ?></span>
    </div>
    <div class="clearfix" style="margin-bottom: 8px;">
        <span style="display:inline-block; width: 45%; font-weight:bold;"><?php echo __('Amount:', 'traveler'); ?></span>
        <span style="display:inline-block;"><?php echo TravelHelper::format_money( $total_price ); ?></span>
    </div>
    <div class="clearfix" style="margin-bottom: 8px;">
        <span style="display:inline-block; width: 45%; font-weight:bold;"><?php echo __('Cancellation Fee:', 'traveler'); ?></span>
        <span style="display:inline-block;"><?php echo esc_html($percent) . '%'; ?></span>
    </div>
    <div class="clearfix" style="margin-bottom: 8px;">
        <span style="display:inline-block; width: 45%; font-weight:bold;"><?php echo __('Amount Refunded:', 'traveler'); ?></span>
        <span style="display:inline-block;" class="text-danger">
            <strong><?php echo isset($cancel_data['refunded']) ? TravelHelper::format_money_raw($cancel_data['refunded'], $cancel_data['currency']) : ''; ?></strong>
        </span>
    </div>
    <div class="clearfix" style="margin-top:10px;">
        <span style="display:inline-block; width: 45%; font-weight:bold;"><?php echo __('Refund Reason:', 'traveler'); ?></span>
        <span style="display:inline-block;"><?php echo isset($cancel_data['detail']) ? esc_html($cancel_data['detail']) : __( 'No reason provided', 'traveler' ); ?></span>
    </div>
</div>
<?php endif; ?>

<script type="text/javascript">
jQuery(function($) {
    // When the confirmation checkbox is toggled, trigger a change event on the hidden select_account input.
    $('#confirm_refund_checkbox').on('change', function() {
        if ($(this).is(':checked')) {
            $('input[name="select_account"]').prop('checked', true).trigger('change');
            // Animate the Next button into view (assuming it is in .modal-footer button.next)
            $('.modal-footer button.next').fadeIn(300).removeClass('hidden');
        } else {
            $('input[name="select_account"]').prop('checked', false).trigger('change');
            // Animate the Next button out of view
            $('.modal-footer button.next').fadeOut(300).addClass('hidden');
        }
    });
});
</script>
