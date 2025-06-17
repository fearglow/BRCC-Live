<?php
/**
 * @since 1.2.8
 * Cancel booking step 1 - Get order information and confirm
 **/
if( !isset( $order_id ) ):
?>
    <div class="text-danger" style="font-size:16px; padding:15px; border:1px solid #f2dede; background:#f9e2e2; border-radius:4px;">
        <?php echo __('Can not get order information. Please try again.', 'traveler'); ?>
    </div>
<?php
else:
    // Calculate cancellation details (unchanged logic)
    $item_id     = (int) get_post_meta( $order_id, 'st_booking_id', true );
    $post_type   = get_post_meta( $order_id, 'st_booking_post_type', true );
    if( $post_type == 'st_hotel' ){
        $room_id = (int) get_post_meta($order_id, 'room_id', true);
    }
    $total_price = (float) get_post_meta( $order_id, 'total_price', true );
    $currency    = STUser_f::_get_currency_book_history($order_id);
    $percent     = (int) get_post_meta( $item_id, 'st_cancel_percent', true );
    if( $post_type == 'st_hotel' && isset( $room_id ) ){
        $percent = (int) get_post_meta( $room_id, 'st_cancel_percent', true );
    }
    $refunded    = $total_price - ( $total_price * $percent / 100 );
    $status      = get_post_meta( $order_id, 'status', true );
    if( $status != 'complete' ){
        $refunded = 0;
    }
    if($status === 'complete'){
        $status_string = __('Complete','traveler');
    } elseif($status === 'incomplete'){
        $status_string = __('Incomplete','traveler');
    } elseif($status === 'pending'){
        $status_string = __('Pending','traveler');
    } else {
        $status_string = $status;
    }
    $st_is_woocommerce_checkout = apply_filters( 'st_is_woocommerce_checkout', false );
    if($st_is_woocommerce_checkout){
        $st_get_meta_orderby_id = st_get_order_by_order_item_id($order_id);
        $status =  ($st_get_meta_orderby_id['status']);
        if($status === 'wc-on-hold'){
            $status_string = __('On hold','traveler');
        } elseif($status === 'wc-pending'){
            $status_string = __('Pending','traveler');
        } elseif($status === 'wc-cancelled'){
            $status_string = __('Cancelled','traveler');
        } elseif($status === 'wc-completed'){
            $status_string = __('Complete','traveler');
        } elseif($status === 'wc-processing'){
            $status_string = __('Processing','traveler');
        }
        $item_id = !empty($st_get_meta_orderby_id['st_booking_id']) ? $st_get_meta_orderby_id['st_booking_id'] : 0;
        $wc_order_id = !empty($st_get_meta_orderby_id['wc_order_id']) ? $st_get_meta_orderby_id['wc_order_id'] : 0;
        $total_price = (float) get_post_meta( $wc_order_id, '_order_total', true);
        if ( empty( $total_price ) ) {
            global $wpdb;
            $querystr = "SELECT total_amount FROM " . $wpdb->prefix . "wc_orders WHERE id = '{$wc_order_id}'";
            $total_price = $wpdb->get_row( $querystr, OBJECT )->total_amount;
        }
        $percent = (int) get_post_meta( $item_id, 'st_cancel_percent', true );
        if( $post_type == 'st_hotel' && isset( $room_id ) ){
            $percent = (int) get_post_meta( $room_id, 'st_cancel_percent', true );
        }
        $refunded = $total_price - ( $total_price * $percent / 100 );
    }
    $check_in  = strtotime( get_post_meta( $order_id, 'check_in', true));
    $check_out = strtotime(get_post_meta( $order_id, 'check_out', true));
    $format    = TravelHelper::getDateFormat();
    if($check_in and $check_out) {
        $date = date_i18n( $format, $check_in ) . ' <i class="fa fa-long-arrow-right" style="color:#888;"></i> ' . date_i18n( $format, $check_out );
    }
    if( $post_type == 'st_tours') {
        $type_tour = get_post_meta( $item_id, 'type_tour', true );
        if($type_tour == 'daily_tour') {
            $duration = get_post_meta( $item_id, 'duration_day', true );
            if ($date){
                $date  = __( "Check in: ", 'traveler' ) . date_i18n( $format, $check_in ) . "<br>";
                $date .= __( "Duration: ", 'traveler' ) . esc_html($duration);
            }
        }
    }
?>
<div class="info">
    <div style="font-size:20px; font-weight:bold; margin-bottom:10px;"><?php echo __('Booking Details', 'traveler'); ?></div>
    <div style="margin-bottom:4px; font-size:14px;">
        <strong style="display:inline-block; width:40%;"><?php echo __('Booking:', 'traveler'); ?></strong>
        <em><?php echo get_the_title( $item_id ); ?></em>
    </div>
    <?php if( isset( $room_id ) && !empty( $room_id ) ): ?>
    <div style="margin-bottom:4px; font-size:14px;">
        <strong style="display:inline-block; width:40%;"><?php echo __('Room:', 'traveler'); ?></strong>
        <em><?php echo get_the_title( $room_id ); ?></em>
    </div>
    <?php endif; ?>
    <div style="margin-bottom:4px; font-size:14px;">
        <strong style="display:inline-block; width:40%;"><?php echo __('Booked Dates:', 'traveler'); ?></strong>
        <em><?php echo wp_kses($date, ['i' => ['style' => []]]); ?></em>
    </div>
    <div style="margin-bottom:10px;">
        <button class="btn btn-primary btn-sm text-capitalize" style="font-size:14px; padding:6px 12px; transition: background 0.3s;"><?php echo esc_html($status_string); ?></button>
    </div>
    <div class="clearfix" style="margin-bottom:4px; font-size:14px;">
        <strong style="display:inline-block; width:45%;"><?php echo __('Amount:', 'traveler'); ?></strong>
        <div class="pull-right" style="font-weight:bold;"><?php echo TravelHelper::format_money( $total_price ); ?></div>
    </div>
    <div class="clearfix" style="margin-bottom:4px; font-size:14px;">
        <strong style="display:inline-block; width:45%;"><?php echo __('Cancellation Fee:', 'traveler'); ?></strong>
        <div class="pull-right" style="font-weight:bold;"><?php echo esc_html($percent) . '%'; ?></div>
    </div>
    <div class="line clearfix" style="margin:8px 0; border-bottom:1px solid #ddd;"></div>
    <div class="clearfix" style="margin-bottom:4px; font-size:14px;">
        <strong style="display:inline-block; width:45%;"><?php echo __('Amount Refunded:', 'traveler'); ?></strong>
        <div class="pull-right" style="font-weight:bold; color:#d9534f;"><?php echo TravelHelper::format_money( $refunded ); ?></div>
    </div>
    <div class="alert alert-warning mt20" role="alert" style="padding:10px; border:1px solid #ffeeba; background:#fff3cd; border-radius:4px;">
        <div style="font-size:16px; font-weight:bold; margin-bottom:8px;"><?php echo __('Why do you want to cancel this order?', 'traveler'); ?></div>
        <form action="#" class="form mt10" method="post">
            <div class="form-group" style="font-size:14px; margin-bottom:4px;">
                <label style="display:block; margin-bottom:4px;">
                    <input type="radio" name="why_cancel" value="booked_wrong_itinerary" data-text="<?php echo __('Booked wrong itinerary', 'traveler'); ?>" style="margin-right:6px;">
                    <span><?php echo __('Booked wrong itinerary', 'traveler'); ?></span>
                </label>
                <label style="display:block; margin-bottom:4px;">
                    <input type="radio" name="why_cancel" value="booked_wrong_dates" data-text="<?php echo __('Booked wrong Dates', 'traveler'); ?>" style="margin-right:6px;">
                    <span><?php echo __('Booked wrong Dates', 'traveler'); ?></span>
                </label>
                <label style="display:block; margin-bottom:4px;">
                    <input type="radio" name="why_cancel" value="found_better_itinerary" data-text="<?php echo __('Found better itinerary', 'traveler'); ?>" style="margin-right:6px;">
                    <span><?php echo __('Found better itinerary', 'traveler'); ?></span>
                </label>
                <label style="display:block; margin-bottom:4px;">
                    <input type="radio" name="why_cancel" value="found_better_price" data-text="<?php echo __('Found better price', 'traveler'); ?>" style="margin-right:6px;">
                    <span><?php echo __('Found better price', 'traveler'); ?></span>
                </label>
                <label style="display:block;">
                    <input type="radio" name="why_cancel" value="other" style="margin-right:6px;">
                    <span><?php echo __('Other', 'traveler'); ?></span>
                </label>
            </div>
            <div class="form-group" style="margin-top:10px;">
                <textarea name="detail" id="" class="form-control hide" style="font-size:14px; padding:8px; border:1px solid #ccc; border-radius:4px;" placeholder="<?php echo __('Please provide additional details if any...', 'traveler'); ?>"></textarea>
            </div>
        </form>
    </div>
</div>
<?php
endif;
?>
