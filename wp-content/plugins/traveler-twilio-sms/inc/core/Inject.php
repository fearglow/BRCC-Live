<?php
 use Twilio\Rest\Client;
if(!class_exists('STTInjects')){
    class STTInjects{
        protected static $_inst;
        
    
        public function __construct(){   
        add_filter('st_user_settings_phone_field', [$this,'changeUserSettingPhoneField'],10,2);
        add_action('st_save_user_setting_field',[$this,'updateUserSettingPhoneField'],10);
        add_filter('st_checkout_form_text_field',[$this,'changeCheckoutField'],10,3);
        add_action('st_save_order_other_table',[$this,'updateCheckoutFiled'],10);
        add_action('st_booking_change_status', [$this,'sendSMS'],10,3);
       
        
        }
        public function changeUserSettingPhoneField($html,$data){
            $countryCode = get_user_meta($data->ID,'st_country_code',true);
                             
            ob_start();
           ?>
           <div class="form-group   ">
                    <label for="st_phone"><?php st_the_language('user_phone_number') ?></label>
                    <div class="stt-country-phone">
                        <select class="form-control stt-dropdown" name="st_country_code">
                            <?php 
                             
                            
                            $countryArray = stt_get_country_code();

                            foreach($countryArray as $key => $country){
                               ?>
                               <option value="<?php echo esc_attr($country['code']) ?>"<?php selected($country['code'],$countryCode)?> data-icon="<?php echo esc_attr('<img src = '. esc_url( STTTwilioSMS::inst()->pluginUrl . 'asset/img/flag/' .strtolower($key).'.svg') .' width="20px">') ?>"> <?php echo esc_html($country['name']) ?>  <?php echo esc_html($country['code']) ?>  </option>

                            <?php } ?>
                       </select>
                       <input name="st_phone" class="form-control" value="<?php echo esc_attr(get_user_meta($data->ID , 'st_phone' , true)) ?>" type="text" />
                    </div>
                    
                </div>
           <?php
            $html= ob_get_clean();
            return $html;

        }
        public function updateUserSettingPhoneField($id_user){
            $countryCode = STInput::post('st_country_code');
           
            update_user_meta($id_user,'st_country_code', $countryCode);
        }

        public function changeCheckoutField($html,$field_name,$field){
                      
            if($field_name == 'st_phone'){
                extract($field);

                if(!$placeholder) $placeholder=$label;
        
                $required=false;
                if(strpos($validate,'required')!==FALSE)
                {
                    $class[]='required';
                    $required='<span class="require">*</span>';
                }
        
                if($icon)
                {
                    $icon="<i class='".st_handle_icon_class($icon)." input-icon'></i>";
                }
        
        
                ob_start();
                ?>
                <div class="col-sm-<?php echo esc_attr($size) ?>">
        
                    <div class="form-group <?php if($icon){ echo 'form-group-icon-left';} ?>">                
                        <label for="field-<?php echo esc_attr($field_name) ?>"><?php echo balanceTags($label) ?> <?php echo balanceTags($required) ?> </label>
                        <?php echo balanceTags($icon)?>
                        <div class="stt-country-phone">
                        <?php
                            $countryCodeIp = '';

                            $ip = stt_get_client_ip();
                            
                            $dataArray = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
                            if(is_object($dataArray) && isset($dataArray->geoplugin_countryCode)){
                                $countryCodeIp = $dataArray->geoplugin_countryCode;
                            }
                            
                        ?>
                        <select class="form-control stt-dropdown" name="st_country_code">
                            <?php 
                            $countryArray = stt_get_country_code();
                            
                            foreach($countryArray as $key => $country){
                                
                               ?>
                               <option value="<?php echo esc_attr($country['code']) ?>" <?php  selected($key,$countryCodeIp)?>  data-icon="<?php echo esc_attr('<img src = '. esc_url( STTTwilioSMS::inst()->pluginUrl . 'asset/img/flag/' .strtolower($key).'.svg') .' width="20px">') ?>"> <?php echo esc_html($country['name']) ?>  <?php echo esc_html($country['code']) ?>  </option>

                            <?php } ?>
                       </select>
                       <input class="<?php echo implode(' ',$class) ?>" id="field-<?php echo esc_attr($field_name) ?>" value="<?php echo esc_attr($value) ?>" name="<?php echo esc_attr($field_name) ?>" placeholder="<?php echo esc_attr($placeholder) ?>" type="text">
                        </div>
                       
                    </div>
        
                </div>
                <?php
                
                
                 $html=ob_get_clean();
            }          
            return $html;
        }
        public function updateCheckoutFiled($insert_post){
                $countryCode = STInput::post('st_country_code');
                update_post_meta($insert_post, 'st_country_code', $countryCode);
                      
        }

        public function sendSMS($status, $order_id, $gateway){
            
           $serviceID = get_post_meta($order_id,'item_id',true);
           $authorID = get_post_field('post_author',$serviceID);
           $authCountryCode = get_user_meta($authorID,'st_country_code',true);
           $authorPhone = $authCountryCode . get_user_meta($authorID,'st_phone',true);
           $adminID = stt_get_admin_data()->ID;
           $adCountryCode = get_user_meta($adminID,'st_country_code',true);
           $adPhone = $adCountryCode . get_user_meta($adminID,'st_phone',true);
           $customerPhone = get_post_meta($order_id, 'st_phone', true);
           $cusCountryCode = get_post_meta($order_id, 'st_country_code', true);
           $customerPhone =  $cusCountryCode . $customerPhone;
           $firstName = get_post_meta($order_id, 'st_first_name', true);
           $lastName = get_post_meta($order_id, 'st_last_name', true);
           $customerName = $firstName . ' ' .$lastName;
           $stEmail = get_post_meta($order_id, 'st_email', true);
           $stCountry = get_post_meta($order_id,'st_country',true);
           $valueCartInfo = get_post_meta($order_id,'st_cart_info',true);
           $cartData = $valueCartInfo[$serviceID];
           $checkIn = date( TravelHelper::getDateFormat(), strtotime(  $cartData['data']['check_in'] ) );
           $checkOut = date( TravelHelper::getDateFormat(), strtotime(  $cartData['data']['check_out'] ) );
           $createDate = date( TravelHelper::getDateFormat(), time() );
           $totalPrice = get_post_meta($order_id,'total_price',true);
           $totalPrice = TravelHelper::format_money($totalPrice);
           $stCheck = get_option('stt_checked');
           
           $stMessCustomer = get_option('stt_message_content');
           $stMessCustomer = str_replace('[booking_id]',$order_id,$stMessCustomer);
           $stMessCustomer = str_replace('[customer_name]',$customerName,$stMessCustomer);
           $stMessCustomer = str_replace('[customer_email]',$stEmail,$stMessCustomer);
           $stMessCustomer = str_replace('[st_country]',$stCountry,$stMessCustomer);
           $stMessCustomer = str_replace('[check_in]',$checkIn,$stMessCustomer);
           $stMessCustomer = str_replace('[check_out]',$checkOut,$stMessCustomer);
           $stMessCustomer = str_replace('[customer_phone]',$customerPhone,$stMessCustomer);
           $stMessCustomer = str_replace('[total_price]',$totalPrice,$stMessCustomer);
           $stMessCustomer = str_replace('[created_date]',$createDate,$stMessCustomer);
           $cusMess =  do_shortcode($stMessCustomer);
           

           $stMessAdmin = get_option('stt_message_admin');
           $stMessAdmin = str_replace('[booking_id]',$order_id,$stMessAdmin);
           $stMessAdmin = str_replace('[customer_name]',$customerName,$stMessAdmin);
           $stMessAdmin = str_replace('[customer_email]',$stEmail,$stMessAdmin);
           $stMessAdmin = str_replace('[st_country]',$stCountry,$stMessAdmin);
           $stMessAdmin = str_replace('[check_in]',$checkIn,$stMessAdmin);
           $stMessAdmin = str_replace('[check_out]',$checkOut,$stMessAdmin);
           $stMessAdmin = str_replace('[customer_phone]',$customerPhone,$stMessAdmin);
           $stMessAdmin = str_replace('[total_price]',$totalPrice,$stMessAdmin);
           $stMessAdmin = str_replace('[created_date]',$createDate,$stMessAdmin);
           $adMess =  do_shortcode($stMessAdmin);
           
           $stMessAuthor = get_option('stt_message_author');
           $stMessAuthor = str_replace('[booking_id]',$order_id,$stMessAuthor);
           $stMessAuthor = str_replace('[customer_name]',$customerName,$stMessAuthor);
           $stMessAuthor = str_replace('[customer_email]',$stEmail,$stMessAuthor);
           $stMessAuthor = str_replace('[st_country]',$stCountry,$stMessAuthor);
           $stMessAuthor = str_replace('[check_in]',$checkIn,$stMessAuthor);
           $stMessAuthor = str_replace('[check_out]',$checkOut,$stMessAuthor);
           $stMessAuthor = str_replace('[customer_phone]',$customerPhone,$stMessAuthor);
           $stMessAuthor = str_replace('[total_price]',$totalPrice,$stMessAuthor);
           $stMessAuthor = str_replace('[created_date]',$createDate,$stMessAuthor);
           $auMess =  do_shortcode($stMessAuthor);

           if($stCheck == '1'){
                $sid    = get_option('stt_account_id');
                $token  = get_option('stt_auth_token');
                $phone_number = get_option('stt_phone_number');
                $twilio = new Client($sid, $token);
                try{
                    $cusMessage = $twilio->messages
                    ->create( esc_html($customerPhone),
                             array(
                                 "body" => esc_html($cusMess),
                                 "from" => esc_html($phone_number)
                             )
                    ); 
                }catch(Exception $e){
                    wp_json_encode(array('error' => $e));
                }
                if(!empty($authorPhone) &&  !empty(get_user_meta($authorID,'st_phone',true))){
                    try{
                        $authMessages = $twilio->messages
                        ->create( esc_html($authorPhone),
                                 array(
                                     "body" => esc_html($auMess),
                                     "from" => esc_html($phone_number)
                                 )
                        );
                    }catch(Exception $e){
                        wp_json_encode(array('error' => $e));
                    }
                }
                if(!empty($adCountryCode) &&  !empty(get_user_meta($adminID,'st_phone',true))){
                    try{
                        if($adminID != $authorID){
                            $adMessages = $twilio->messages
                            ->create( esc_html($adPhone),
                                    array(
                                        "body" => esc_html($adMess),
                                        "from" => esc_html($phone_number)
                                    )
                            );
                        }
    
                    }catch(Exception $e){
                        wp_json_encode(array('error' => $e));
                    }
                }
           }
            
        }

        public function sendDepartureSMS($order_id) {
            $log_path = WP_CONTENT_DIR . '/debug-departure-sms.log';
            $timestamp = current_time('mysql');
        
            $serviceID = get_post_meta($order_id, 'item_id', true);
            $authorID = get_post_field('post_author', $serviceID);
            $authCountryCode = get_user_meta($authorID, 'st_country_code', true);
            $authorPhone = $authCountryCode . get_user_meta($authorID, 'st_phone', true);
            $adminID = stt_get_admin_data()->ID;
            $adCountryCode = get_user_meta($adminID, 'st_country_code', true);
            $adPhone = $adCountryCode . get_user_meta($adminID, 'st_phone', true);
            $customerPhone = get_post_meta($order_id, 'st_phone', true);
            $cusCountryCode = get_post_meta($order_id, 'st_country_code', true);
            $customerPhone = $cusCountryCode . $customerPhone;
            $firstName = get_post_meta($order_id, 'st_first_name', true);
            $lastName = get_post_meta($order_id, 'st_last_name', true);
            $customerName = $firstName . ' ' . $lastName;
            $valueCartInfo = get_post_meta($order_id, 'st_cart_info', true);
            $cartData = $valueCartInfo[$serviceID];
            $checkIn = date(TravelHelper::getDateFormat(), strtotime($cartData['data']['check_in']));
            $checkOut = date( TravelHelper::getDateFormat(), strtotime(  $cartData['data']['check_out'] ) );

            $custom_message = "Hi $firstName,\nThis is Amy from Betsie River Canoes and Campground. Just a reminder of your reservation on {$checkIn}. Check in is at 1pm and checkout is at 12pm on $checkOut. Please remember to put our address in your navigation if using it. 13598 Lindy Rd Thompsonville MI 49683. If you could reply with your name and an ETA for your arrival then I can make sure to have someone available to get you checked in. Thank you";
        
            $stCheck = get_option('stt_checked');
        
            if ($stCheck == '1') {
                $sid = get_option('stt_account_id');
                $token = get_option('stt_auth_token');
                $phone_number = get_option('stt_phone_number');
                $twilio = new Client($sid, $token);
                try {
                    $cusMessage = $twilio->messages->create(esc_html($customerPhone), [
                        "body" => esc_html($custom_message),
                        "from" => esc_html($phone_number)
                    ]);
                    file_put_contents($log_path, "[$timestamp] SMS sent to $customerPhone for order ID $order_id\n", FILE_APPEND);
                } catch (Exception $e) {
                    file_put_contents($log_path, "[$timestamp] Error sending SMS to $customerPhone for order ID $order_id: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }
        }
        
        
        public static function inst()
        {
            if(!self::$_inst)
            {
                self::$_inst=new self();
            }

            return self::$_inst;
        }
    }
    STTInjects::inst();
}