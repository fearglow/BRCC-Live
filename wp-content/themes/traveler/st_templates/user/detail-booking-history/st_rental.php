<?php
$currency = get_post_meta( $order_id, 'currency', true );
$order_data = STUser_f::get_booking_meta($order_id);
$date_format = TravelHelper::getDateFormat();

$equipment_type = get_post_meta($order_id, 'equipment_type', true);
$length = get_post_meta($order_id, 'length_ft', true);
$slide_outs = get_post_meta($order_id, 'slide_outs', true);


?>
<div class="st_tab st_tab_order tabbable">
    <ul class="nav nav-tabs tab_order">
        <li class="active">
            <?php
            $post_type = get_post_type( $service_id );
            $obj = get_post_type_object( $post_type ); ?>
            <a data-toggle="tab" href="#tab-booking-detail" aria-expanded="true"> <?php echo sprintf(esc_html__("%s Details",'traveler'),$obj->labels->singular_name) ?></a>
        </li>
        <li class="">
            <a data-toggle="tab" href="#tab-customer-detail" aria-expanded="false"> <?php esc_html_e("Customer Details",'traveler') ?></a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent973">
        <div id="tab-booking-detail" class="tab-pane fade active in">
            <div class="info">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Booking ID",'traveler') ?>:  </strong>
                                #<?php echo esc_html($order_id) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Payment Method: ",'traveler') ?> </strong>
                                <?php echo STPaymentGateways::get_gatewayname(get_post_meta($order_id, 'payment_method', true)); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Order Date",'traveler') ?>:  </strong>
                                <?php echo esc_html(date_i18n($date_format, strtotime($order_data['created']))) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Booking Status",'traveler') ?>:  </strong>
                                <?php
                                $data_status =  STUser_f::_get_all_order_statuses();
                                $status = $order_data['status'];
                                if(!empty($status_string = $data_status[$status])){
                                    //$status_string = $data_status[$status];
    	                            $status_string = $data_status[get_post_meta($order_id, 'status', true)];
                                    if( isset( $order_data['cancel_refund_status'] ) && $order_data['cancel_refund_status'] == 'pending'){
                                        $status_string = __('Cancelling', 'traveler');
                                    }
                                }
                                ?>
                                <span class=""> <?php  echo esc_html($status_string); ?></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Rental Name",'traveler') ?>:  </strong>
                                <a href="<?php echo get_the_permalink($service_id) ?>"><?php echo get_the_title($service_id) ?></a>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Address: ",'traveler') ?>:  </strong>
                                <?php  echo get_post_meta( $service_id, 'address', true); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Check In:",'traveler') ?> </strong>
                                <?php

                                $check_in = get_post_meta( $order_id, 'check_in', true );
                                if ( !empty( $check_in ) ) {
                                    $check_in = date( $date_format , strtotime( $check_in ) );
                                } else {
                                    $check_in = '';
                                }
                                echo esc_html($check_in);
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6 ">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Check Out:",'traveler') ?> </strong>
                                <?php
                                $check_out = get_post_meta( $order_id, 'check_out', true );
                                if ( !empty( $check_out ) ) {
                                    $check_out = date( $date_format, strtotime( $check_out ) );
                                } else {
                                    $check_out = '';
                                }
                                echo esc_html($check_out);
                                ?>
                            </div>
                        </div>
                        <?php if(!empty(st_print_order_item_guest_name(json_decode($order_data['raw_data'],true)))){?>
                        <div class="col-md-12">
                            <div class="item_booking_detail">
                                <?php st_print_order_item_guest_name(json_decode($order_data['raw_data'],true)) ?>
                            </div>
                        </div>
                        <?php }?>
                        <div class="line col-md-12"></div>
                        <div class="col-md-12">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Rental Price:",'traveler') ?> </strong>
                                <?php echo TravelHelper::format_money_from_db( get_post_meta( $order_id, 'item_price', true ), $currency ); ?>
                            </div>
                        </div>
                        <?php if(!empty($discount = get_post_meta($order_id , 'discount_rate' , true))) {?>
                            <div class="col-md-12">
                                <div class="item_booking_detail">
                                    <strong><?php esc_html_e("Discount Rate:",'traveler') ?> </strong>
                                    <?php echo esc_html($discount); ?> %
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-12">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("No. Adults :",'traveler') ?> </strong>
                                <?php echo get_post_meta( $order_id, 'adult_number', true ); ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("No. Children :",'traveler') ?> </strong>
                                <?php echo get_post_meta( $order_id, 'child_number', true ); ?>
                            </div>
                        </div>
						<?php if ($post_type == 'st_rental') : ?>
                        <div class="col-md-12">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Equipment Type:", 'traveler') ?> </strong>
                                <?php echo esc_html($equipment_type); ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Length (ft):", 'traveler') ?> </strong>
                                <?php echo esc_html($length); ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Slide-Outs:", 'traveler') ?> </strong>
                                <?php echo esc_html($slide_outs); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                        <?php
                        $extra_price = get_post_meta( $order_id, 'extra_price', true );
                        $extras      = get_post_meta( $order_id, 'extras', true );
                        $data_extra = [];
                        if ( isset( $extras[ 'value' ] ) && is_array( $extras[ 'value' ] ) && count( $extras[ 'value' ] ) ) {
                            foreach ( $extras[ 'value' ] as $name => $number ) {
                                if(!empty($extras[ 'value' ][ $name ])){
                                    $data_extra[ $name ] = array(
                                        'title'=>$extras[ 'title' ][ $name ],
                                        'price'=>$extras[ 'price' ][ $name ],
                                        'value'=>$extras[ 'value' ][ $name ],
                                    );
                                }
                            }
                        }
                        ?>
                        <div class="col-md-6 <?php if(empty($data_extra)) echo "hide"; ?>">
                            <div class="item_booking_detail">
                                <strong><?php esc_html_e("Extra Price:",'traveler') ?> </strong>
                                <?php echo TravelHelper::format_money_from_db( $extra_price, $currency ); ?>
                                <?php if ( is_array( $data_extra ) && count( $extras ) ){ ?>
                                    <table class="table mt10 mb10" style="table-layout: fixed;" width="200">
                                        <tr>
                                            <td>
                                                <label>
                                                    <strong><?php esc_html_e("Name Extra",'traveler') ?></strong>
                                                </label>
                                            </td>
                                            <td width="40%">
                                                <strong><?php esc_html_e("Price",'traveler') ?></strong>
                                            </td>
                                        </tr>
                                        <?php foreach ( $data_extra as $key => $val ):
                                            $price = intval( $val[ 'value' ]) * floatval($val[ 'price' ]);
                                            ?>
                                            <tr>
                                                <td>
                                                    <label>
                                                        <?php echo esc_html($val[ 'title' ]) . ' x ' . esc_html(intval( $val[ 'value' ])); ?>
                                                    </label>
                                                </td>
                                                <td width="40%">
                                                    <?php echo TravelHelper::format_money_from_db( $price, $currency ); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                <?php }else{ echo 0 ;} ?>
                            </div>
                        </div>
                        <?php echo st()->load_template('user/detail-booking-history/detail-price',false,
                            array(
                                'order_data'=>$order_data,
                                'order_id'=>$order_id,
                                'service_id'=>$service_id,
                            )
                        ) ?>
                    </div>
                </div>
        </div>
        <div id="tab-customer-detail" class="tab-pane fade">
            <div class="container-customer">
                <?php echo apply_filters( 'st_customer_info_booking_history', st()->load_template('user/detail-booking-history/customer',false,array("order_id"=>$order_id)),$order_id ); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <?php do_action("st_after_body_order_information_table",$order_id); ?>
    <button data-dismiss="modal" class="btn btn-default" type="button"><?php esc_html_e("Close",'traveler') ?></button>
</div>
