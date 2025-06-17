<?php

if(!class_exists('STTExportBooking')){
    class STTExportBooking {
        protected static $_inst;
        public $order_id = 0;
        public function __construct()
        {
           add_action('export_booking_item_title',[$this,'exportBookingItemTitle']);
           add_filter('export_booking_item_button',[$this,'exportBookingItemButtonShow'],10,2);
           add_action('export_booking_item_buttons',[$this,'exportBookingItemButtonShows']);
           add_action('export_booking_history_button',[$this,'exportBookingHistoryButtonShow']);
           $this->handlingExportPDF();
           $this->exportBookingHistoryPDF();
        }
        public function exportBookingHistoryPDF(){
            $st_export = STInput::get('st_export');
            $st_export_type = STInput::get('st_export_type');
            $start = STInput::get('start');
            $end = STInput::get('end');
            $screen = STInput::get('st_screen');

            $export_status = STInput::get('export_status');
            if($st_export == 'pdf' && $st_export_type == 'all'){
                $user_data = wp_get_current_user();
                $user_role = $user_data->roles[0];
                global $wpdb;
                $type_checkout = '';
                if (st()->get_option('use_woocommerce_for_booking') === 'on' && class_exists('Woocommerce')) {
                    $type_checkout = ' AND type="woocommerce" ';
                } else {
                    $type_checkout = ' AND type="normal_booking" ';
                }
                $sql = " SELECT wc_order_id,order_item_id,created,total_order , type, status FROM {$wpdb->prefix}st_order_item_meta WHERE 1=1 ".$type_checkout;

                if($user_role == 'administrator'){
                    $sql .="";
                }elseif($user_role == 'partner'){
                    $sql .=" AND partner_id = " . $user_data->ID ;
                }else{
                    $sql .=" AND user_id = " . $user_data->ID ;
                }

                if($export_status != 'all'){
                    $sql .= " AND status = '" .$export_status ."'";
                }

                if(empty($screen)){
                    $sql .= "";
                }elseif($screen == 'st_cars'){
                    $sql .= " AND ( st_booking_post_type = 'st_cars' OR st_booking_post_type = 'car_transfer' )";
                }else{
                    $sql .= " AND st_booking_post_type = '" . $screen . "' ";
                }

                if(!empty($start) && !empty($end)){
                    $start = date("Y-m-d", strtotime(str_replace('/','-',$start))) ;
                    $end = date("Y-m-d", strtotime(str_replace('/','-',$end))) ;
                    $sql .= " AND   created >= '". $start ."' AND created <= '". $end . "' GROUP BY id DESC ";
                }else{
                    $sql .= " GROUP BY id DESC LIMIT 10 ";
                }

                $result = $wpdb->get_results($sql);
                $this->exportBookingHistory($result);
            }
        }

        public function exportBookingHistory($list_data){
            $resize_page = apply_filters( 'st_resize_export_pdf', 'A4' );
            $mpdf = new \Mpdf\Mpdf(['autoLangToFont' => true,'autoScriptToLang' => true , 'format' => $resize_page]);
            $data = '';
            $data =   STTTravelerExport::inst()->view('export_booking_history','',array('list_data' => $list_data),true);
            $mpdf->WriteHTML($data);
            $mpdf->Output('booking.pdf','I');
        }

        public function exportBookingHistoryButtonShow($screen){
            return STTTravelerExport::inst()->view('export_history_popup','',array('screen' => $screen));
        }

        public function handlingExportPDF(){
            if(!empty($this->order_id = STInput::get('booking_id')) && $this->export_type = STInput::get('export_type') == 'pdf' ){
                $my_user = wp_get_current_user();
		        $user_book = get_post_meta($this->order_id,'user_id',true);
		        $user_partner = 0;
		        $order_data = array();
		        global $wpdb;
		        $sql = "SELECT * FROM {$wpdb->prefix}st_order_item_meta WHERE order_item_id = ".$this->order_id;
		        $rs = $wpdb->get_row($sql);
		        if(!empty($rs->partner_id)){
			        $user_partner = $rs->partner_id;
			        $order_data = $rs;
		        }
		        $is_checked = true;
                if(!is_user_logged_in()){
                    $is_checked = false;
                }

		        if(!empty($user_book) && $user_book != 0) {
			        if ($user_book != $my_user->ID) {
				        $is_checked = false;
			        }
		        }
		        if($user_partner == $my_user->ID ){
			        $is_checked = true;
		        }
		        if(current_user_can('manage_options')){
			        $is_checked = true;
                }

		        if($is_checked and !empty($order_data)){
                    $this->exportBookingItemDetail($rs->wc_order_id , $rs);
			        exit;
		        }else{
			        $current_url = $_SERVER['REQUEST_URI'];
			        $current_url = remove_query_arg('booking_id', $current_url);
			        wp_redirect(($current_url));
			        exit;
		        }
            }
        }

        public function exportBookingItemButtonShows($order_id){
            if(!empty($order_id)){
                $current_url = home_url();
                $current_url = add_query_arg(array(
                    'booking_id' => $order_id,
                    'export_type' => 'pdf'
                ), $current_url);
            }
            echo'<td>';
            echo'<a target="_blank" class="btn btn-xs btn-primary mt5 btn-export-booking"  href="' . esc_url($current_url) . '"><span class="hidden-xs">' . esc_html__( ' Export', 'traveler-export' ) . '</span></a>';
            echo'</td>';

        }

        public function exportBookingItemButtonShow($null,$order_id){
            $html = '';
            if(!empty($order_id)){
                $current_url = home_url();
                $current_url = add_query_arg(array(
                    'booking_id' => $order_id,
                    'export_type' => 'pdf'
                ), $current_url);
            }
            $html .='<td>';
            $html .='<a target="_blank" class="btn btn-xs btn-primary mt5 btn-export-booking"  href="' . esc_url($current_url) . '"><span>' . esc_html__( ' Export', 'traveler-export' ) . '</span></a>';
            $html .='</td>';
            return $html;
        }

        public function exportBookingItemTitle(){
            echo '<th style="width:10%">';
            echo esc_html__('Export Booking','traveler-export');
            echo '</th>';
        }

        public function exportBookingItemDetail($order_id, $rs){
            $resize_page = apply_filters( 'st_resize_export_pdf', 'A4' );
            $mpdf = new \Mpdf\Mpdf(['autoLangToFont' => true,'autoScriptToLang' => true , 'format' => $resize_page]);
            $data = '';
            $st_is_woocommerce_checkout = apply_filters( 'st_is_woocommerce_checkout', false );
            if($st_is_woocommerce_checkout){
                $data =   STTTravelerExport::inst()->view('woocommerce/export_booking_item','',array('order_id' => $order_id, 'data_order' => $rs),true);
            } else {
                $data =   STTTravelerExport::inst()->view('export_booking_item','',array('order_id' => $order_id),true);
            }
            $mpdf->WriteHTML($data);
            $mpdf->Output('booking.pdf','I');
        }


        public static function inst()
        {
            if (!self::$_inst) {
                self::$_inst = new self();
            }

            return self::$_inst;
        }
    }
    STTExportBooking::inst();
}