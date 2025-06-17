<?php
$format = TravelHelper::getDateFormat();
$start = STInput::get('start');
$end = STInput::get('end');
$current_user = wp_get_current_user();
$user_email = $current_user->data->user_email;

$css = '
body{
    font-family: "Poppins", sans-serif;
}
.list-booking-history{
    border-collapse: collapse;
}
.booking_history_title{
    text-align:center;
    margin-bottom : 50px;
}
.list-booking-history th,td {
    border: 1px solid #ddd;
    text-align:left;
    padding :  10px;

}
.list-booking-history .status{
    text-transform: capitalize;
}
.booking-history-info-pdf{
    margin-bottom:30px;
}
.booking-history-info-pdf .date{
   text-align:left;
   border : none;
   padding : 0px;
}
.booking-history-info-pdf .email{
    text-align: right;
    border : none;
    padding : 0px;
}

';
?>
<?php ob_start();?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo esc_html__('Booking History','traveler-export') ?></title>
</head>
<body>
    <h2 class="booking_history_title"><?php echo esc_html__('Booking History','traveler-export') ?></h2>
    <table class="booking-history-info-pdf" width="100%">
        <tr>
            <td  class="date">
                <?php if(!empty($start) && !empty($end)){ ?>
                    <span><?php echo esc_html__('From - To: ','traveler-export') ?><?php echo esc_html($start) ?> - <?php echo esc_html($end) ?></span>
                <?php } ?>
            </td>
            <td class="email">
                <span ><?php echo esc_html__('Creator Email: ','traveler-export'); echo esc_html($user_email) ?></span>   
            </td>
        </tr>
    </table>
                    
    <table class="list-booking-history"  width="100%">
        <thead>
            <tr>
                <th width="10%"><?php echo esc_html__('ID Order','traveler-export') ?></th>
                <th><?php echo esc_html__('Customer Email','traveler-export') ?></th>
                <th width = "15%"><?php echo esc_html__('Price','traveler-export') ?></th>
                <th><?php echo esc_html__('Status','traveler-export') ?></th>
                <th><?php echo esc_html__('Created Date','traveler-export') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($list_data as $data ){
                $wc_order_id = $data->wc_order_id;
                $order_id = $data->order_item_id;
                $total_order = $data->total_order;
                $status = $data->status;
                if (st()->get_option('use_woocommerce_for_booking') === 'on' && class_exists('Woocommerce')) {
                    $email = get_post_meta($wc_order_id,'_billing_email',true);
                    $id_order = $wc_order_id;
                    
                } else {
                    $email = get_post_meta($wc_order_id,'st_email',true);
                    $id_order = $order_id;
                }
                $currency = TravelHelper::_get_currency_book_history($order_id);
                
                $created = $data->created;
            ?>
                <tr>
                    <td><?php echo esc_html($id_order)?></td>
                    <td><?php echo esc_html($email)?></td>
                    <td><?php 
                        if ($data->type == "normal_booking") {
                            $total_price = get_post_meta($wc_order_id, 'total_price', true);
                        } else {
                            $total_price = get_post_meta($wc_order_id, '_order_total', true);
                        }
                        $currency = TravelHelper::_get_currency_book_history($wc_order_id);
                        echo TravelHelper::format_money_raw($total_price, $currency);
                        ?></td>
                    <td class="status"><?php echo esc_html($status)?></td>
                    <td><?php echo date_i18n($format, strtotime($created))?></td>
                </tr>
            <?php } ?>
            
        </tbody>
    </table>
</body>
</html>

<?php
$html = ob_get_clean();
$emogrifier = new STT_Emogrifier();
$emogrifier->setHtml($html);
$emogrifier->setCss($css);
$mergedHtml = $emogrifier->emogrify();
unset($emogrifier);
echo balanceTags($mergedHtml);
