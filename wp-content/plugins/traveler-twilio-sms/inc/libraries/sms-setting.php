<?php

if (!class_exists('STTSetting')) {
    class STTSetting
    {
        protected static $_inst;
        public function __construct()
        {
            add_action('admin_menu', [$this, 'addSubMenuPage']);
        }
        public function addSubMenuPage()
        {
            add_submenu_page('st_traveler_options',esc_html__('Traveler SMS','traveler-sms'), esc_html__('Traveler SMS','traveler-sms'), 'manage_options','traveler-sms', [$this,'submenuPageOutput']);
        }
        public function submenuPageOutput()
        {
            if(!empty(STInput::post('save-sms-option'))){
               $sttPhoneNumber = STInput::post('stt_phone_number','');
               $sttAccountID = STInput::post('stt_account_id','');
               $sttAuthToken = STInput::post('stt_auth_token','');
               $stCheck = isset($_POST['stt_checked']) ? 1 : 0;
               $sttMessCustomer = STInput::post('stt_message_content','');
               $sttMessAdmin = STInput::post('stt_message_admin','');
               $sttMessAuthor = STInput::post('stt_message_author','');
               update_option('stt_checked', $stCheck);
               update_option('stt_checked', $stCheck);
               update_option('stt_checked', $stCheck);
               update_option('stt_phone_number', $sttPhoneNumber);
               update_option('stt_account_id',$sttAccountID);
               update_option('stt_auth_token',$sttAuthToken);
               update_option('stt_message_content',$sttMessCustomer);
               update_option('stt_message_admin',$sttMessAdmin);
               update_option('stt_message_author',$sttMessAuthor);
            }
            $sttPhoneNumber = get_option('stt_phone_number');
            $sttAccountID = get_option('stt_account_id');
            $sttAuthToken = get_option('stt_auth_token');
            $stCheck = get_option('stt_checked');
            $sttMessCustomer = get_option('stt_message_content');
            $sttMessAdmin = get_option('stt_message_admin');
            $sttMessAuthor = get_option('stt_message_author');
            ?>
            <h2><?php echo esc_html('Settings Traveler SMS') ?></h2>
            <?php settings_errors();?>
            <form  method="post" action="">
                <table class="form-table">
                    <tbody>
                    <tr>

                        <th scope="row"><label><?php echo esc_html__('Turn On Send SMS','traveler-sms') ?></label></th>
                        <td>
                            <div class="onoffswitch">
                                <input type="checkbox" name="stt_checked" class="onoffswitch-checkbox" id="myonoffswitch" value="" <?php checked($stCheck,'1') ?>>
                                <label class="onoffswitch-label" for="myonoffswitch">
                                    <span class="onoffswitch-inner"></span>
                                    <span class="onoffswitch-switch"></span>
                                </label>
                            </div>

                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label ><?php echo esc_html__('Twilio Phone Number', 'traveler-sms') ?></label></th>
                        <td>
                            <input type="text" name="stt_phone_number" size="40" value="<?php echo esc_attr($sttPhoneNumber) ?>" >
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label ><?php echo esc_html__('Twilio Account ID', 'traveler-sms') ?></label></th>
                        <td>
                            <input type="text" name="stt_account_id" size="40" value="<?php echo esc_attr($sttAccountID) ?>" >
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label ><?php echo esc_html__('Auth Token', 'traveler-sms') ?></label></th>
                        <td>
                            <input type="text" name="stt_auth_token" size="40" value="<?php echo esc_attr($sttAuthToken) ?>" autocomplete="off">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label ><?php echo esc_html__('Message To Admin', 'traveler-sms') ?></label></th>
                        <td>
                            <textarea rows="6" cols="40" name="stt_message_admin"><?php echo esc_textarea($sttMessAdmin) ?></textarea>
                        </td>

                    </tr>
                    <tr>
                        <th scope="row"><label ><?php echo esc_html__('Message To Partner', 'traveler-sms') ?></label></th>
                        <td>
                            <textarea rows="6" cols="40" name="stt_message_author"><?php echo esc_textarea($sttMessAuthor) ?></textarea>
                        </td>

                    </tr>
                    <tr>
                        <th scope="row"><label ><?php echo esc_html__('Message To Customer', 'traveler-sms') ?></label></th>
                        <td class="textarea-sms">
                            <textarea class="textarea-sms-content" rows="6" cols="40" name="stt_message_content"><?php echo esc_textarea($sttMessCustomer) ?></textarea>

                            <div class="sms-shortcode">
                            <p><?php echo esc_html__('Note: Message cannot exceed 160 characters','traveler-sms') ?></p>
                            <p><?php echo esc_html__('[booking_id] : Booking number','traveler-sms') ?></p>
                            <p><?php echo esc_html__('[customer_phone] : Customer Phone','traveler-sms') ?></p>
                            <p><?php echo esc_html__('[customer_name] : Customer Name','traveler-sms') ?></p>
                            <p><?php echo esc_html__('[customer_email] : Customer Email','traveler-sms') ?></p>
                            <p><?php echo esc_html__('[st_country] : Customer Country','traveler-sms') ?></p>
                            <p><?php echo esc_html__('[check_in] : Check In','traveler-sms') ?></p>
                            <p><?php echo esc_html__('[check_out] : Check Out','traveler-sms') ?></p>
                            <p><?php echo esc_html__('[total_price] : Total Price','traveler-sms') ?></p>
                            <p><?php echo esc_html__('[created_date] : Create Date','traveler-sms') ?></p>
                            </div>
                        </td>

                    </tr>

                    </tbody>
                </table>
                <input class="button button-primary" type="submit" name="save-sms-option" value="<?php echo esc_attr('Save Changes', 'traveler-sms') ?>"/>
            </form>
           <?php
}

        public static function inst()
        {
            if (!self::$_inst) {
                self::$_inst = new self();
            }

            return self::$_inst;
        }
    }
    STTSetting::inst();
}