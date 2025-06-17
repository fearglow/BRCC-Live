<?php
    $order_data = STUser_f::get_booking_meta($order_id);
    $item_id = get_post_meta($order_id, 'item_id', true);
    $post_type = get_post_type($item_id);
    $hide = esc_html__('hidden-class','traveler-export');
    $css = '
    body{
        font-family: "Poppins", sans-serif;
    }
    .hidden-class{
        display : none;
    }
    
    .invoice-booking-detail .booking-detail-title .thumb {
        width: 300px;
        margin-bottom:10px;     
    }
    
    .invoice-booking-detail .booking-detail-title .info-title .title{
        font-size: 20px;
        font-weight : 500;
        margin-bottom:5px;
    }
    .invoice-booking-detail .booking-detail-title .info-title .website,.email,.phone-number,.fax{
        margin-bottom:5px;
    }
    .booking-client-info{
        margin-top:20px;
    }
    .booking-client-info .client-info-heading{
        text-align : center;
        font-size : 24px;
        font-weight: bold;
        padding-bottom:20px;
    }
    .client-info-detail{
        border-collapse: collapse;
    }
    .client-info-detail .client-info-code{
        text-align: center;
        padding: 10px;
        font-weight: bold;
    }
    .client-info-detail .client-info-code .booking-code{
        background-color: #ccffcc;
        color: #339933;
        padding: 3px 10px;
    }
    .client-info-detail .client-info-status{
        text-align: center;
        padding: 10px;
        font-weight: bold;
    }
    .client-info-detail .client-info-status .booking-status{
        background-color: #ccffcc;
        color: #339933;
        padding: 3px 10px;
        text-transform: capitalize;
    }
    .client-info-detail tr td{
        border: 1px solid #dddddd;
    }
    .client-info-detail .client-title-info{
        padding : 10px 20px;
    }
    .client-info-detail .client-info{
        color: #cc3333;
    }

    .booking-info{
        margin-top:20px;
    }
    .booking-infor-title{
        text-align : center;
        font-size : 24px;
        font-weight: bold;
        padding-bottom : 20px;
    }
    
    .booking-info .title{
        width : 40%;
        text-align: left;
        padding : 10px
    }

    .booking-info .value{
        text-align: right;
        padding : 10px;
    }
    .booking-info .pay-amount{
        font-weight:bold;
    }
    ';
?>

<?php  ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Booking Detail','traveler-export') ?></title>
    
</head>
<body>
    <div class="invoice-booking-detail" >
        <div class="booking-detail-title">
            <div class="thumb" style="width:100px;">
            <?php
                if(has_post_thumbnail($item_id)){
                    echo get_the_post_thumbnail( $item_id,array(100, 150), array('alt' => TravelHelper::get_alt_image(), 'class' => 'img-responsive'));
                }else{
                    echo '<img src="'. get_template_directory_uri() . '/img/no-image.png' .'" alt="Default Thumbnail" class="img-responsive" />';
                }
            ?>
            </div>
            <div class="info-title">
                <div class="title">
                    <?php echo esc_html(get_the_title($item_id)) ?>
                </div>

                <div class="address">
                    <?php
                        $address = get_post_meta($item_id,'address',true);
                    
                        if ($post_type == 'st_cars') {
                            $address = get_post_meta($item_id, 'cars_address', true);
                        }
                        if(!empty($address)){
                            echo '<strong>' . esc_html__('Address: ','traveler-export') . '</strong>' . esc_html($address);
                        }
                    ?>
                </div>
                
                <div class="website">
                    <?php
                        $website = get_post_meta($item_id, 'website', true);
                        if ($post_type == 'st_cars') {
                            $website = get_post_meta($item_id, 'cars_website', true);
                        }
                        if ($post_type == 'st_activity') {
                            $website = get_post_meta($item_id, 'contact_website', true);
                        }
                        if ($post_type == 'st_hotel') {
                            $theme_option = st()->get_option('partner_show_contact_info');
                            $metabox = get_post_meta($item_id, 'show_agent_contact_info', true);
                            $use_agent_info = FALSE;
                            if ($theme_option == 'on'){
                                $use_agent_info = true;
                            }
                            if ($metabox == 'user_agent_info'){
                                $use_agent_info = true;
                            } 
                            if ($metabox == 'user_item_info'){
                                $use_agent_info = FALSE;
                            }
                            $obj_hotel = get_post($item_id);
                            $user_id = $obj_hotel->post_author;
                            if ($use_agent_info) {
                                $website = get_the_author_meta('user_url', $user_id);
                            } else {
                                $website = get_post_meta($item_id, 'website', true);
                            }
                        }
                        if(!empty($website)){
                            
                            echo '<strong>' . esc_html__('Website: ','traveler-export') .'</strong>' . esc_url($website);
                        }
                    ?>
                </div>

                <div class="email">
                    <?php
                        $email = get_post_meta($item_id, 'email', true);
                        if ($post_type == 'st_cars') {
                            $email = get_post_meta($item_id, 'cars_email', true);
                        }
                        if ($post_type == 'st_activity' or $post_type == 'st_tours') {
                            $email = get_post_meta($item_id, 'contact_email', true);
                        }
                        if ($post_type == 'st_hotel') {
                            $theme_option = st()->get_option('partner_show_contact_info');
                            $metabox = get_post_meta($item_id, 'show_agent_contact_info', true);
                            $use_agent_info = FALSE;
                            if ($theme_option == 'on'){
                                $use_agent_info = true;
                            }
                            if ($metabox == 'user_agent_info'){
                                $use_agent_info = true;
                            }
                            if ($metabox == 'user_item_info'){
                                $use_agent_info = FALSE;
                            }
                            $obj_hotel = get_post($item_id);
                            $user_id = $obj_hotel->post_author;
                            if ($use_agent_info) {
                                $email = get_the_author_meta('user_email', $user_id);
                            } else {
                                $email = get_post_meta($item_id, 'email', true);
                            }
                        }
                        if(!empty($email)){
                            echo '<strong>' . esc_html__('Email: ','traveler-export') . '</strong>' . esc_html($email);
                        }
                    ?>
                </div>

                <div class="phone-number">
                    <?php
                        $phone = get_post_meta($item_id, 'phone', true);
                        if ($post_type == 'st_cars') {
                            $phone = get_post_meta($item_id, 'cars_phone', true);
                        }
                        if ($post_type == 'st_activity') {
                            $phone = get_post_meta($item_id, 'contact_phone', true);
                        }
                        if ($post_type == 'st_hotel') {
                            $theme_option = st()->get_option('partner_show_contact_info');
                            $metabox = get_post_meta($item_id, 'show_agent_contact_info', true);
                            $use_agent_info = FALSE;
                            if ($theme_option == 'on'){
                                $use_agent_info = true;
                            }
                            if ($metabox == 'user_agent_info'){
                                $use_agent_info = true;
                            }
                            if ($metabox == 'user_item_info'){
                                $use_agent_info = FALSE;
                            }
                            $obj_hotel = get_post($item_id);
                            $user_id = $obj_hotel->post_author;
                            if ($use_agent_info) {
                                $phone = get_user_meta($user_id, 'st_phone', true);
                            } else {
                                $phone = get_post_meta($item_id, 'phone', true);
                            }
                        }
                        if(!empty($phone)){
                            echo '<strong>' . esc_html__('Phone: ','traveler-export') . '</strong>' . esc_html($phone);
                        }
                    ?>
                </div>

                <div class="fax">
                    <?php
                        $fax = get_post_meta($item_id, 'fax', true);
                        if ($post_type == 'st_cars') {
                            $fax = get_post_meta($item_id, 'cars_fax', true);
                        }
                        if ($post_type == 'st_activity') {
                            $fax = get_post_meta($item_id, 'contact_fax', true);
                        }
                        if(!empty($fax)){
                            echo '<strong>' . esc_html__('Fax: ','traveler-export') . '</strong>' . esc_html($fax);
                        }
                    ?>
                </div>
            </div>
        </div>

        <table class="booking-client-info" width="100%" >
            <tbody>
                <tr>
                    <td class = "client-info-heading"><?php echo esc_html__('Client Informations','traveler-export') ?></td>
                </tr>
                <tr>
                    <td>
                        <table class="client-info-detail" width="100%">
                            <tbody>
                                <tr>
                                    <td class = "client-info-code" colspan="2" width="50%">
                                        <?php echo esc_html__('Booking Code: ','traveler-export') ?>
                                        <span class="booking-code"><?php echo esc_html($order_id); ?></span>
                                    </td>
                                    <td class = "client-info-status" colspan="2" width="50%">
                                        <?php echo esc_html__('Status: ','traveler-export') ?>
                                        <?php
                                            if ($order_id) {
                                                $html = get_post_meta($order_id, 'status', true);
                                                switch ($html) {
                                                    case 'incomplete':
                                                    default:
                                                        $html = esc_html__('incomplete', 'traveler-export');
                                                        break;
                                                    case 'complete':
                                                        $html = esc_html__('completed', 'traveler-export');
                                                        break;
                                    
                                                    case 'canceled':
                                                        $html = esc_html__('canceled', 'traveler-export');
                                                        break;
                                    
                                                    case 'pending':
                                                        $html = esc_html__('pending', 'traveler-export');
                                                        break;
                                                }
                                    
                                                
                                    
                                            }
                                        ?>
                                        <span class = "booking-status"><?php echo esc_html($html); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="15%" class = "client-title-info">
                                        <strong><?php echo esc_html__('First Name: ','traveler-export') ?></strong>
                                    </td>
                                    <td class ="client-title-info client-info">
                                        <?php
                                            $first_name = get_post_meta($order_id, 'st_first_name', true);
                                            echo esc_html($first_name);
                                        ?>
                                    </td>
                                    <td class = "client-title-info" width="20%">
                                        <strong><?php echo esc_html__('Last Name: ','traveler-export') ?></strong>
                                    </td>
                                    <td class ="client-title-info client-info">
                                        <?php
                                            $last_name = get_post_meta($order_id, 'st_last_name', true);
                                            echo esc_html($last_name);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class = "client-title-info" width="20%">
                                        <strong><?php echo esc_html__('Phone: ','traveler-export') ?></strong>
                                    </td>
                                    <td class ="client-title-info ">
                                        <?php
                                            $country_code = get_post_meta($order_id, 'st_country_code', true);

                                            if(!empty($country_code)){
                                                $st_phone = $country_code . get_post_meta($order_id, 'st_phone', true);
                                            }else{
                                                $st_phone = get_post_meta($order_id, 'st_phone', true);
                                            }
                                            echo esc_html($st_phone);
                                        ?>
                                    </td>
                                    <td class = "client-title-info" width="20%">
                                        <strong><?php echo esc_html__('Country: ','traveler-export') ?></strong>
                                    </td>
                                    <td class ="client-title-info ">
                                        <?php
                                            
                                            $st_country = get_post_meta($order_id, 'st_country', true);
                                            echo esc_html($st_country);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class = "client-title-info" width="20%">
                                        <strong><?php echo esc_html__('Date: ','traveler-export') ?></strong>
                                    </td>
                                    <td class ="client-title-info ">
                                        <?php
                                            
                                            $st_date = get_the_time(TravelHelper::getDateFormat(), $order_id);
                                            echo esc_html($st_date);
                                        ?>
                                    </td>
                                    <td class = "client-title-info" width="20%">
                                        <strong><?php echo esc_html__('City: ','traveler-export') ?></strong>
                                    </td>
                                    <td class ="client-title-info ">
                                        <?php
                                            
                                            $st_city = get_post_meta($order_id, 'st_city', true);
                                            echo esc_html($st_city);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class = "client-title-info" width="20%">
                                        <strong><?php echo esc_html__('Email: ','traveler-export') ?></strong>
                                    </td>
                                    <td class ="client-title-info" colspan = "3">
                                        <?php
                                            
                                            $st_email = get_post_meta($order_id, 'st_email', true);
                                            echo esc_html($st_email);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class = "client-title-info" width="20%">
                                        <strong><?php echo esc_html__('Address Line 1: ','traveler-export') ?></strong>
                                    </td>
                                    <td class ="client-title-info" colspan = "3">
                                        <?php
                                            
                                            $st_address = get_post_meta($order_id, 'st_address', true);
                                            echo esc_html($st_address);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class = "client-title-info" width="20%">
                                        <strong><?php echo esc_html__('Requirements: ','traveler-export') ?></strong>
                                    </td>
                                    <td class ="client-title-info" colspan = "3">
                                        <?php
                                            
                                            $st_note = get_post_meta($order_id, 'st_note', true);
                                            echo esc_html($st_note);
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="booking-info" width="100%">
            <tbody>
                <tr>
                    <td style="text-align : center" colspan="2" class="booking-infor-title" ><?php echo esc_html__('Booking Detail') ?></td>
                </tr>
                <?php
                    $name_html = get_post_meta($order_id, 'payment_method_name', true);
                if($name_html){ ?>
                    <tr>
                        <td class="payment-method title" > 
                            <?php
                                echo esc_html__('Payment Method: ','traveler-export');
                                
                            ?>    
                        </td>
                        <td class="value">
                            <?php
                            
                                echo '<span style="text-transform: uppercase;">' . esc_html($name_html) . '</span>';
                            ?>
                        </td>
                    </tr>
                <?php }
                ?>
                
                
                <?php if($post_type == 'st_hotel' or $post_type == 'hotel_room'){ ?>
                    <tr>
                        <td class="room-name title" ">
                            <?php echo esc_html__('Room Name: ','traveler-export') ?>
                        </td>
                        <td class="value">
                            <?php
                                $room_id = get_post_meta( $order_id, 'room_id', true );
                                echo '<span style="text-align:left">' . get_the_title($room_id) . '</span>'; 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="title">
                            <?php echo esc_html__('Number of Item: ','traveler-export') ?>
                        </td>
                        <td class="value">
                            <?php
                                $post_id = trim(get_post_meta($order_id, 'item_id', true));
                                $number = intval(get_post_meta($order_id, 'item_number', true));
                                

                                    echo '<span>' . esc_html($number) . ' ' . esc_html__('item(s)', 'traveler-export') . '</span>';
                                    
                            ?>
                        </td>
                    </tr>
                <?php } ?>

                <?php  if ($post_type == 'st_tours' || $post_type == 'st_activity' || $post_type == "st_hotel" || $post_type == 'hotel_room') { ?>
                    <?php
                        $post_id = trim(get_post_meta($order_id, 'item_id', true));
                        $adult = intval(get_post_meta($order_id, 'adult_number', true));
                        if($adult >0){
                    ?>
                        <tr>
                        <td class="title">
                            <?php echo esc_html__('No. Adult(s): ','traveler-export') ?>
                        </td>
                        <td class="value">
                            <?php
                                
                                $currency = get_post_meta($order_id, 'currency', true);
                                $adult_price = floatval(get_post_meta($order_id, 'adult_price', true));
                                
                                $data_price = get_post_meta($order_id, 'data_price', true);
                                if (!empty($data_price['adult_price'])) {

                                    $adult_price = $data_price['adult_price'] / $adult;
                                
                                }
                                $discount = get_post_meta($post_id, 'discount', true);
                                if ($discount > 0){
                                    $adult_price = $adult_price - ($adult_price * ($discount / 100));
                                } 
                                $adult_price_html = esc_html__(' x ','traveler-export') . TravelHelper::format_money_from_db($adult_price, $currency);
                                if ($post_type == "st_hotel" || $post_type == 'hotel_room'){
                                    $adult_price_html = "";
                                } 
                                echo '<span>' . esc_html($adult) . ' ' . esc_html__('adult(s)', 'traveler-export') . esc_html($adult_price_html) . '</span>';
                                    
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
                    
                    <?php
                    $post_id = trim(get_post_meta($order_id, 'item_id', true));
                    $children = intval(get_post_meta($order_id, 'child_number', true));
                    if($children > 0){
                    ?>
                        <tr>
                            
                            <td class="title">
                                <?php echo esc_html__('No. Child(s): ','traveler-export') ?>
                            </td>
                            <td class="value">
                                <?php
                                    
                                    $currency = get_post_meta($order_id, 'currency', true);
                                    $child_price = floatval(get_post_meta($order_id, 'child_price', true));
                                    $data_price = get_post_meta($order_id, 'data_price', true);
                                    if (!empty($data_price['child_price'])) {

                                        $child_price = $data_price['child_price'] / $children;
                    
                                    }
                                    $discount = get_post_meta($post_id, 'discount', true);
                                    if ($discount > 0){
                                        $child_price = $child_price - ($child_price * ($discount / 100));
                                    } 
                                    $child_price_html = esc_html__(' x ','traveler-export') . TravelHelper::format_money_from_db($child_price, $currency);
                                    if ($post_type == "st_hotel" || $post_type == 'hotel_room'){
                                        $child_price_html = "";
                                    } 
                                    echo '<span>' . esc_html($children) . ' ' . esc_html__('children', 'traveler-export') . esc_html($child_price_html) . '</span>';
                                        
                                ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php
                    $post_id = trim(get_post_meta($order_id, 'item_id', true));
                    $infant = intval(get_post_meta($order_id, 'infant_number', true));
                    if($infant > 0){
                    ?>
                        <tr>
                            
                            <td class="title">
                                <?php echo esc_html__('No. Infant: ','traveler-export') ?>
                            </td>
                            <td class="value">
                                <?php
                                    
                                    $currency = get_post_meta($order_id, 'currency', true);
                                    $infant_price = floatval(get_post_meta($order_id, 'infant_price', true));
                                    $data_price = get_post_meta($order_id, 'data_price', true);
                                    if (!empty($data_price['infant_price'])) {

                                        $infant_price = $data_price['infant_price'] / $infant;
                    
                                    }
                                    $discount = get_post_meta($post_id, 'discount', true);
                                    if ($discount > 0){
                                        $infant_price = $infant_price - ($infant_price * ($discount / 100));
                                    } 
                                    $infant_price_html = esc_html__(' x ','traveler-export') . TravelHelper::format_money_from_db($infant_price, $currency);
                                    if ($post_type == "st_hotel" || $post_type == 'hotel_room'){
                                        $infant_price_html = "";
                                    } 
                                    echo '<span>' . esc_html($infant) . ' ' . esc_html__('infant', 'traveler-export') . esc_html($infant_price_html) . '</span>';
                                        
                                ?>
                            </td>
                        </tr>
                    <?php } ?>

                <?php } ?>

                <tr>
                    <td class="title">
                        <?php
                            $post_id = trim(get_post_meta($order_id, 'item_id', true));
                            $tour_price_type = get_post_meta($order_id, 'price_type', true);
                            $post_type = get_post_type($post_id);
                            $title = '';
                            if ($post_type == 'st_hotel' or $post_type == 'st_rental'){
                                $title =  esc_html__("Check in - out: ", 'traveler-export');
                            }
                            if ($post_type == 'st_cars'){
                                $title =  esc_html__("Pick-up from - Drop-off to: ", 'traveler-export');
                            }
                            if ($post_type == 'st_tours') {

                                if ($tour_price_type == 'fixed_depart') {
                                    $title = esc_html__('Fixed Departure', 'traveler-export');
                                } else {
                                    $tour_type = get_post_meta($order_id, 'type_tour', true);
                                    if (!empty($tour_type) and $tour_type == 'daily_tour') {
                                        $title =  esc_html__("Departure date: ", 'traveler-export');
                                    }
                                    $title = esc_html__("Departure date - Return date: ", 'traveler-export');
                                }
                            }  
                            if ($post_type == 'st_activity') {
                                $activity_type = get_post_meta($order_id, 'type_activity', true);

                                if (!empty($activity_type) and $activity_type == 'daily_activity') {
                                    $title = esc_html__("From: ", 'traveler-export');
                                }
                                $title = esc_html__("From - To: ", 'traveler-export');
                            }

                            echo esc_html($title);
                        ?>
                    </td>
                    <td class = "value">
                        <?php
                            $post_id = trim(get_post_meta($order_id, 'item_id', true));
                            $post_type = get_post_type($post_id);
                            $value = "";
                            if ($post_type == 'st_tours') {
                                $tour_price_type = get_post_meta($order_id, 'price_type', true);
                                if ($tour_price_type == 'fixed_depart') {
                                    $value .= esc_html__('Start date', 'traveler-export') . ': ';
                                    $value .= TourHelper::getDayFromNumber(date('N', strtotime(get_post_meta($order_id, 'check_in', true)))) . ' ' . date(TravelHelper::getDateFormat(), strtotime(get_post_meta($order_id, 'check_in', true))) . '<br />';
                                    $value .= esc_html__('End date', 'traveler-export') . ': ';
                                    $value .= TourHelper::getDayFromNumber(date('N', strtotime(get_post_meta($order_id, 'check_out', true)))) . ' ' . date(TravelHelper::getDateFormat(), strtotime(get_post_meta($order_id, 'check_out', true)));
                                } else {
                                    $tour_type = get_post_meta($order_id, 'type_tour', true);        
                                    if ($tour_type == 'daily_tour') {        
                                        $value .= date(TravelHelper::getDateFormat(), strtotime(get_post_meta($order_id, 'check_in', true)));        
                                        $value .= "<br/>";       
                                        $duration = get_post_meta($order_id, 'duration', true);
                                        $value .= esc_html__("Duration: ", 'traveler-export') . $duration;
                                    } else {
                                        $value .= date(TravelHelper::getDateFormat(), strtotime(get_post_meta($order_id, 'check_in', true)));
                                        $value .= " - ";
                                        $value .= date(TravelHelper::getDateFormat(), strtotime(get_post_meta($order_id, 'check_out', true)));
                                    }
                                }
                            }
                            if ($post_type == 'st_activity') {
                                $activity_type = get_post_meta($order_id, 'type_activity', true);
                                if ($activity_type == 'daily_activity') {
                                    $value .= date(TravelHelper::getDateFormat(), strtotime(get_post_meta($order_id, 'check_in', true)));
                                    $value .= "<br/>";
                                    $duration = get_post_meta($order_id, 'duration', true);
                                    $value .= esc_html__("Duration: ", 'traveler-export') . $duration;
                                } else {
                                    $value .= date(TravelHelper::getDateFormat(), strtotime(get_post_meta($order_id, 'check_in', true)));
                                    $value .= " - ";
                                    $value .= date(TravelHelper::getDateFormat(), strtotime(get_post_meta($order_id, 'check_out', true)));
                                }
                            }
                            if ($post_type == 'st_hotel' or $post_type == 'hotel_room' or $post_type == 'hotel_room' or $post_type == 'st_rental') {
                                $day = '';
                                $check_in = get_post_meta($order_id, 'check_in', true);
                                $check_out = get_post_meta($order_id, 'check_out', true);
                                $diff = STDate::dateDiff($check_in, $check_out);    
                                $day .= ($diff >= 2) ? ' (' . esc_html($diff) . ' ' . esc_html__("days", 'traveler-export') . ')' : '(' . esc_html($diff) . ' ' . esc_html__("day", 'traveler-export') . ')';
                                $value .= date(TravelHelper::getDateFormat(), strtotime($check_in)) . ' - ' . date(TravelHelper::getDateFormat(), strtotime($check_out)) . esc_html($day);
                            }
                            if ($post_type == 'st_cars') {
                                $unit = st()->get_option('cars_price_unit');
                                $start = explode(" ", get_post_meta($order_id, 'check_in', true));
                                $start = $start[0];
                                $start = strtotime($start . ' ' . get_post_meta($order_id, 'check_in_time', true));
                                $end = explode(" ", get_post_meta($order_id, 'check_out', true));
                                $end = $end[0];
                                $end = strtotime($end . ' ' . get_post_meta($order_id, 'check_out_time', true));
                                $time = STCars::get_date_diff($start, $end);
                                $label = '';
                                if ($unit == 'hour') {
                                    if ($time > 1) {
                                        $label = esc_html__('hours', 'traveler-export');
                                    } else {
                                        $label = esc_html__('hour', 'traveler-export');
                                    }
                                } elseif ($unit == 'day') {
                                    if ($time > 1) {
                                        $label = esc_html__('days', 'traveler-export');
                                    } else {
                                        $label = esc_html__('day', 'traveler-export');
                                    }
                                } elseif ($unit == 'distance' and $post_type == 'st_cars') {
                                    $time = get_post_meta($order_id, 'distance', true);
                                    if ($time > 1) {
                                        $label = STCars::get_price_unit_by_unit_id($unit, 'plural');
                                    } else {
                                        $label = STCars::get_price_unit_by_unit_id($unit, 'label');
                                    }
                                }  
                                $value .= date(TravelHelper::getDateFormat(), strtotime(get_post_meta($order_id, 'check_in', true))) . " " . get_post_meta($order_id, 'check_in_time', true);
                                $value .= " - ";
                                if ($unit == 'distance' and $post_type == 'st_cars') {
                                    $pick_up = get_post_meta($order_id, 'pick_up', true);
                                    $drop_off = get_post_meta($order_id, 'drop_off', true);
                                    $value .= date(TravelHelper::getDateFormat(), strtotime(get_post_meta($order_id, 'check_out', true))) . " " . get_post_meta($order_id, 'check_out_time', true) . '';
                                    $value .= "<br> " . esc_html($pick_up) . " - " . esc_html($drop_off) . " ( {$time} {$label} )";
                                } else {
                                    $value .= date(TravelHelper::getDateFormat(), strtotime(get_post_meta($order_id, 'check_out', true))) . " " . get_post_meta($order_id, 'check_out_time', true);
                                    if (!empty($time) and !empty($label)) {
                                        $value .= ' (' . esc_html($time) . ' ' . esc_html($label) . ')';
                                    }
                                }
                            }

                            echo balanceTags($value);
                        ?>
                    </td>
                </tr>

                <?php
                    $extra_price = get_post_meta( $order_id, 'extra_price', true );
                    $extras      = get_post_meta( $order_id, 'extras', true );
                ?>
                <tr class=" <?php if(empty($extra_price)) echo esc_attr($hide); ?>">
                    <td class="title" > 
                        <?php
                            echo esc_html__('Extra Price: ','traveler-export');
                            
                        ?>    
                    </td>
                    <td class="value">
                        <?php
                            echo esc_html(TravelHelper::format_money_from_db($extra_price ,$currency));  
                        ?>
                    </td>
                </tr>

                <?php if($post_type == "st_tours"){
                    $hotel_packages = get_post_meta( $order_id, 'package_hotel', true );
                    $hotel_package_price = get_post_meta( $order_id, 'package_hotel_price', true );
                    $activity_packages = get_post_meta( $order_id, 'package_activity', true );
                    $activity_package_price = get_post_meta( $order_id, 'package_activity_price', true );
                    $car_packages = get_post_meta( $order_id, 'package_car', true );
                    $car_package_price = get_post_meta( $order_id, 'package_car_price', true );
                    $flight_packages = get_post_meta( $order_id, 'package_flight', true );
                    $flight_package_price = get_post_meta( $order_id, 'package_flight_price', true );
                ?>
                    <tr class=" <?php if(empty($hotel_package_price)) echo esc_attr($hide); ?>">
                        
                        <td class="title"><?php echo esc_html__('Hotel Package:','traveler-export'); ?></td>
                        <td class="value"><?php echo esc_html(TravelHelper::format_money_from_db($hotel_package_price ,$currency)); ?></td>
                    </tr>
                    <tr class=" <?php if(empty($activity_package_price)) echo esc_attr($hide); ?>">
                        
                        <td class="title"><?php echo esc_html__('Activity Package:','traveler-export'); ?></td>
                        <td class="value"><?php echo esc_html(TravelHelper::format_money_from_db($activity_package_price ,$currency)); ?></td>
                    </tr>
                    <tr class=" <?php if(empty($car_package_price)) echo esc_attr($hide); ?>">
                        
                        <td class="title"><?php echo esc_html__('Car Package:','traveler-export'); ?></td>
                        <td class="value"><?php echo esc_html(TravelHelper::format_money_from_db($car_package_price ,$currency)); ?></td>
                    </tr>
                    <tr class=" <?php if(empty($flight_package_price)) echo esc_attr($hide); ?>">
                        
                        <td class="title"><?php echo esc_html__('Flight Package:','traveler-export'); ?></td>
                        <td class="value"><?php echo esc_html(TravelHelper::format_money_from_db($flight_package_price ,$currency)); ?></td>
                    </tr>
                <?php } ?>

                <?php
                    $currency = get_post_meta( $order_id, 'currency', true );
                    $data_price = get_post_meta( $order_id , 'data_prices' , true);
                    $discount_rate = get_post_meta($order_id, 'discount_rate', true);
                    $discount_type = get_post_meta($order_id, 'discount_type', true);
                    $pay_amount = isset($data_price['total_price']) ? $data_price['total_price'] : 0;
                    $deposit_status = get_post_meta($order_id, 'deposit_money', true);
                    $deposit_price = isset($data_price['deposit_price']) ? $data_price['deposit_price'] : 0;
                    $booking_fee_price = isset($data_price['booking_fee_price']) ? $data_price['booking_fee_price'] : 0;
                    $total_order = $order_data['total_order'];
                    $subtotal = get_post_meta($order_id, 'ori_price', true);
                    $tax = intval(get_post_meta($order_id, 'st_tax_percent', true));
                    if(!empty($booking_fee_price)){
                        $total_order =$total_order-$booking_fee_price;
                    }
                    $price_total_with_tax = STPrice::getTotalPriceWithTaxInOrder($total_order,$order_id);
                ?>       
                <tr>
                    <td class="title"><?php echo esc_html__("Subtotal: ",'traveler-export') ?></td>
                    <td class="value"><?php echo esc_html(TravelHelper::format_money_from_db($subtotal ,$currency)); ?></td>
                </tr>
                <tr>
                    <td class="title"><?php echo esc_html__("Tax: ",'traveler-export') ?></td>
                    <td class="value">
                    <?php
                        if (!empty($tax)) {
                            echo esc_html($tax." %");
                        }else{
                            echo esc_html($tax);
                        }
                    ?>
                    </td>
                </tr>

                <?php if(is_array($deposit_status) && !empty($deposit_status['type']) && floatval($deposit_status['amount']) > 0){ ?>
                    <?php if(!empty($price_total_with_tax)){ ?>
                        <tr>
                            <td class="title "><?php echo esc_html__("Total: ",'traveler-export') ?></td>
                            <td class="value"><?php echo TravelHelper::format_money_from_db($price_total_with_tax ,$currency); ?></td>
                        </tr>
                    <?php } ?>
                    <?php $coupon_price = isset($data_price['coupon_price']) ? $data_price['coupon_price'] : 0; ?>
                    <tr class="<?php if(empty($coupon_price)) echo esc_html($hide); ?>">
                        <td class="title"><?php echo esc_html__("Coupon: ",'traveler-export') ?></td>
                        <td class="value"> - <?php echo esc_html(TravelHelper::format_money_from_db($coupon_price ,$currency)); ?></td>
                    </tr>
                    <tr>
                        <td class="title"><?php echo esc_html__("Deposit: ",'traveler-export') ?></td>
                        <td class="value"><?php echo esc_html(TravelHelper::format_money_from_db($deposit_price ,$currency)); ?></td>
                    </tr>
                    <?php
                    if(!empty($booking_fee_price)){
                        ?>
                        <tr>
                            <td class="title"><?php echo esc_html__('Fee: ','traveler-export') ; ?></td>
                            <td class="value"><?php echo esc_html(TravelHelper::format_money_from_db($booking_fee_price ,$currency)); ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td class="title"><?php echo esc_html__('Pay Amount: ','traveler-export') ; ?></td>
                        <td class="value pay-amount"><?php echo esc_html(TravelHelper::format_money_from_db($pay_amount ,$currency)); ?></td>
                    </tr>

                <?php }else{ ?>
                    <?php $coupon_price = isset($data_price['coupon_price']) ? $data_price['coupon_price'] : 0; ?>
                    <tr class="<?php if(empty($coupon_price)) echo esc_html($hide); ?>">
                        <td class="title"><?php echo esc_html__("Coupon: ",'traveler-export') ?></td>
                        <td class="value"> - <?php echo esc_html(TravelHelper::format_money_from_db($coupon_price ,$currency)); ?></td>
                    </tr>

                    <tr>
                        <td class="title"><?php echo esc_html__("Total: ",'traveler-export') ?></td>
                        <td class="value"><?php echo TravelHelper::format_money_from_db($price_total_with_tax ,$currency); ?></td>
                    </tr>
                    <?php
                    if(!empty($booking_fee_price)){
                        ?>
                        <tr>
                            <td class="title"><?php echo esc_html__('Fee: ','traveler-export') ; ?></td>
                            <td class="value"><?php echo esc_html(TravelHelper::format_money_from_db($booking_fee_price ,$currency)); ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td class="title"><?php echo esc_html__('Pay Amount: ','traveler-export') ; ?></td>
                        <td class="value pay-amount"><?php echo esc_html(TravelHelper::format_money_from_db($pay_amount ,$currency)); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    
    </div>
</body>
</html>

<?php $html = ob_get_clean(); ?>
<?php
$emogrifier = new STT_Emogrifier();
$emogrifier->setHtml($html);
$emogrifier->setCss($css);
$mergedHtml = $emogrifier->emogrify();
unset($emogrifier);
echo balanceTags($mergedHtml);