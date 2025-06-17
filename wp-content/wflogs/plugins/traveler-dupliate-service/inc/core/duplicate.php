<?php
if(!class_exists('STTDuplicate')){
    class STTDuplicate {
        protected static $_inst;
        private $output;
        public function __construct()
        {
            add_filter('post_row_actions',[$this,'sttDuplicatePostLink'],10,2);
            add_filter('page_row_actions',[$this,'sttDuplicatePostLink'],10,2);
            add_action('wp_ajax_stt_duplicate',[$this,'sttDupplicatePost']);         
        }

        /**
         * @param string $table_name Table name
         * @param array $arr_exclude_column Exclude Column with result
         * 
         * @return array mixed
         */
        public static function sttGetColumnFromTable( $table_name, $arr_exclude_column = array() ) {
            global $wpdb;

            $arr_cols = [];

            if (empty($table_name)) return [];

            $prefix_table_name = $wpdb->prefix . $table_name;

            $str_query = "SHOW COLUMNS FROM " . $prefix_table_name;
            
            $columns = $wpdb->get_results($str_query, ARRAY_A);
            
            foreach($columns as $column) {
                if (!in_array($column['Field'], $arr_exclude_column)) {
                    array_push($arr_cols, $column['Field']);
                    
                }
            }
            return $arr_cols;
        }

        /**
         * @param integer $post_id The ID Post
         * @param integer $num_row The number of row will each
         * 
         * @return
         */
        
        public function sttDupplicatePost() {
            $stt_duplicate_number = (int)STInput::post('stt_duplicate_number');
            $stt_check = STInput::post('stt_check');
            $stt_check_room = STInput::post('stt_check_room');
            $stt_check_room_availability = STInput::post('stt_check_room_availability');
            $post_id = STInput::post('post_id');
            if(empty($stt_duplicate_number) || $stt_duplicate_number < 0){
                echo json_encode([
                    'status' => 0,
                    'message' => '<div class="alert form_alert alert-danger">' . esc_html__('Enter a valid number','traveler-duplicate') . '</div>'
                ]);
                die;
            }else{
                global $wpdb;
                $name_number = get_option('stt_name_number',0);
                for($i = 1; $i <= $stt_duplicate_number; $i++){
                    
                    $cols = self::sttGetColumnFromTable('posts', array('ID'));
                    $new_cols = $cols;
                    $name_number= $name_number + 1;
                    update_option('stt_name_number',$name_number);            
                    $new_cols = str_replace('post_name','CONCAT(post_name,'.'"-' . esc_html((int)$name_number) .'"'.')',$new_cols);
                    
                    //Duplicate post
                    $sql_current_row = "INSERT INTO {$wpdb->prefix}posts " . "(". implode(',', $cols) .") SELECT " . implode(',', $new_cols) . " FROM {$wpdb->prefix}posts WHERE ID = %d";
                    $wpdb->query($wpdb->prepare($sql_current_row, $post_id));

                    $insert_id = $wpdb->insert_id;
                    
                    //Duplicate post meta
                    $sql_post_meta = "INSERT INTO {$wpdb->prefix}postmeta(meta_id,post_id,meta_key,meta_value) SELECT NULL,$insert_id,meta_key,meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d ";
                    $wpdb->query($wpdb->prepare($sql_post_meta, $post_id));

                    //Duplicate category
                    $sql_category = "INSERT INTO {$wpdb->prefix}term_relationships(object_id,term_taxonomy_id,term_order) SELECT $insert_id,term_taxonomy_id,term_order FROM {$wpdb->prefix}term_relationships WHERE object_id = %d";
                    $wpdb->query($wpdb->prepare($sql_category, $post_id));

                    $post_type = get_post($post_id)->post_type;
                    $post_type_icl = get_post($insert_id)->post_type;
                    //Duplicate icl_object_id WPML
                    if ( class_exists('SitePress') ) {
                        global $sitepress;
	                    $sitepress->set_element_language_details($insert_id, 'post_'.$post_type_icl ,false, ICL_LANGUAGE_CODE,null,$check_duplicates = true );
                    }
                    if($post_type == 'st_tours'){
                        $cols_service = self::sttGetColumnFromTable('st_tours');
                        $new_cols_service = $cols_service;
                        $new_cols_service = str_replace('post_id',$insert_id,$new_cols_service);
                        $sql_service = "INSERT INTO {$wpdb->prefix}st_tours(" . implode(',',$cols_service) .") SELECT " . implode(',',$new_cols_service) . " FROM {$wpdb->prefix}st_tours WHERE post_id = %d";
                        $wpdb->query($wpdb->prepare($sql_service,$post_id));
                        if($stt_check == '1'){
                            $cols_service_avai = self::sttGetColumnFromTable('st_tour_availability');
                            $new_cols_service_avai = $cols_service_avai;
                            $new_cols_service_avai = str_replace('post_id',$insert_id,$new_cols_service_avai);
                            $new_cols_service_avai = str_replace('id','null',$new_cols_service_avai);
                            $sql_service_avai = "INSERT INTO {$wpdb->prefix}st_tour_availability(" . implode(',',$cols_service_avai) .") SELECT " . implode(',',$new_cols_service_avai) . " FROM {$wpdb->prefix}st_tour_availability WHERE post_id = %d";
                            $wpdb->query($wpdb->prepare($sql_service_avai,$post_id));
                        }
                    }elseif($post_type == 'st_activity'){
                        $cols_service = self::sttGetColumnFromTable('st_activity');
                        $new_cols_service = $cols_service;
                        $new_cols_service = str_replace('post_id',$insert_id,$new_cols_service);
                        $sql_service = "INSERT INTO {$wpdb->prefix}st_activity(" . implode(',',$cols_service) .") SELECT " . implode(',',$new_cols_service) . " FROM {$wpdb->prefix}st_activity WHERE post_id = %d";
                        $wpdb->query($wpdb->prepare($sql_service,$post_id));

                        if($stt_check == '1'){
                            $cols_service_avai = self::sttGetColumnFromTable('st_activity_availability');
                            $new_cols_service_avai = $cols_service_avai;
                            $new_cols_service_avai = str_replace('post_id',$insert_id,$new_cols_service_avai);
                            $new_cols_service_avai = str_replace('id','null',$new_cols_service_avai);
                            $sql_service_avai = "INSERT INTO {$wpdb->prefix}st_activity_availability(" . implode(',',$cols_service_avai) .") SELECT " . implode(',',$new_cols_service_avai) . " FROM {$wpdb->prefix}st_activity_availability WHERE post_id = %d";
                            $wpdb->query($wpdb->prepare($sql_service_avai,$post_id));
                        }  
                    }elseif($post_type == 'st_flight'){
                        $cols_service = self::sttGetColumnFromTable('st_flights');
                        $new_cols_service = $cols_service;
                        $new_cols_service = str_replace('post_id',$insert_id,$new_cols_service);
                        $new_cols_service = str_replace('id','NULL',$new_cols_service);
                        $sql_service = "INSERT INTO {$wpdb->prefix}st_flights(" . implode(',',$cols_service) .") SELECT " . implode(',',$new_cols_service) . " FROM {$wpdb->prefix}st_flights WHERE post_id = %d";
                        $wpdb->query($wpdb->prepare($sql_service,$post_id));
                        if($stt_check == '1'){
                            $cols_service_avai = self::sttGetColumnFromTable('st_flight_availability');
                            $new_cols_service_avai = $cols_service_avai;
                            $new_cols_service_avai = str_replace('post_id',$insert_id,$new_cols_service_avai);
                            $new_cols_service_avai = str_replace('id','null',$new_cols_service_avai);
                            $sql_service_avai = "INSERT INTO {$wpdb->prefix}st_flight_availability(" . implode(',',$cols_service_avai) .") SELECT " . implode(',',$new_cols_service_avai) . " FROM {$wpdb->prefix}st_flight_availability WHERE post_id = %d";
                            $wpdb->query($wpdb->prepare($sql_service_avai,$post_id));
                        }    
                    }elseif($post_type == 'st_rental'){
                        $cols_service = self::sttGetColumnFromTable('st_rental');
                        $new_cols_service = $cols_service;
                        $new_cols_service = str_replace('post_id',$insert_id,$new_cols_service);
                        $sql_service = "INSERT INTO {$wpdb->prefix}st_rental(" . implode(',',$cols_service) .") SELECT " . implode(',',$new_cols_service) . " FROM {$wpdb->prefix}st_rental WHERE post_id = %d";
                        $wpdb->query($wpdb->prepare($sql_service,$post_id));

                        if($stt_check == '1'){
                            $cols_service_avai = self::sttGetColumnFromTable('st_rental_availability');
                            $new_cols_service_avai = $cols_service_avai;
                            $new_cols_service_avai = str_replace('post_id',$insert_id,$new_cols_service_avai);
                            $new_cols_service_avai = str_replace('id','null',$new_cols_service_avai);
                            $new_cols_service_avai = str_replace('parent_null','parent_id',$new_cols_service_avai);  
                            $sql_service_avai = "INSERT INTO {$wpdb->prefix}st_rental_availability(" . implode(',',$cols_service_avai) .") SELECT " . implode(',',$new_cols_service_avai) . " FROM {$wpdb->prefix}st_rental_availability WHERE post_id = %d";
                            $wpdb->query($wpdb->prepare($sql_service_avai,$post_id));
                        }
                    }elseif($post_type == 'st_cars'){
                        $cols_service = self::sttGetColumnFromTable('st_cars');
                        $new_cols_service = $cols_service;
                        $new_cols_service = str_replace('post_id',$insert_id,$new_cols_service);
                        $sql_service = "INSERT INTO {$wpdb->prefix}st_cars(" . implode(',',$cols_service) .") SELECT " . implode(',',$new_cols_service) . " FROM {$wpdb->prefix}st_cars WHERE post_id = %d";
                        $wpdb->query($wpdb->prepare($sql_service,$post_id));
                    }elseif($post_type == 'st_hotel'){
                        $cols_service = self::sttGetColumnFromTable('st_hotel');
                        $new_cols_service = $cols_service;
                        $new_cols_service = str_replace('post_id',$insert_id,$new_cols_service);
                        $sql_service = "INSERT INTO {$wpdb->prefix}st_hotel(" . implode(',',$cols_service) .") SELECT " . implode(',',$new_cols_service) . " FROM {$wpdb->prefix}st_hotel WHERE post_id = %d";
                        $wpdb->query($wpdb->prepare($sql_service,$post_id));
                        if($stt_check_room == '1'){
                            $sql_list_room = "SELECT post_id FROM {$wpdb->prefix}hotel_room WHERE room_parent = {$post_id} ";
                            $list_room = $wpdb->get_results($sql_list_room,ARRAY_A);
                            if(!empty($list_room)){
                                $room_id = $insert_id;
                                foreach($list_room as $room){
                                    $room_id = $room_id +1;

                                    $cols_room = self::sttGetColumnFromTable('posts');
                                    $new_cols_room = $cols_room;
                                            
                                    $new_cols_room = str_replace('ID',$room_id,$new_cols_room);
                                    
                                    //Duplicate room
                                    $sql_room_row = "INSERT INTO {$wpdb->prefix}posts " . "(". implode(',', $cols_room) .") SELECT " . implode(',', $new_cols_room) . " FROM {$wpdb->prefix}posts WHERE ID = %d";
                                    $wpdb->query($wpdb->prepare($sql_current_row, $room['post_id']));
                
                                    //Duplicate room meta
                                    $sql_room_meta = "INSERT INTO {$wpdb->prefix}postmeta(meta_id,post_id,meta_key,meta_value) SELECT NULL,$room_id,meta_key,meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d ";
                                    $wpdb->query($wpdb->prepare($sql_room_meta, $room['post_id']));
                                    
                                    $sql_parent_room = " UPDATE {$wpdb->prefix}postmeta SET meta_value = {$insert_id} WHERE post_id = {$room_id} and meta_key = 'room_parent' ";
                                    $wpdb->query($sql_parent_room);
                                    //Duplicate category
                                    $sql_room_category = "INSERT INTO {$wpdb->prefix}term_relationships(object_id,term_taxonomy_id,term_order) SELECT $room_id,term_taxonomy_id,term_order FROM {$wpdb->prefix}term_relationships WHERE object_id = %d";
                                
                                    $wpdb->query($wpdb->prepare($sql_room_category, $room['post_id']));                            

                                    $cols_service_child = self::sttGetColumnFromTable('hotel_room');
                                    $new_cols_service_child = $cols_service_child;
                                    $new_cols_service_child = str_replace('room_parent',$insert_id,$new_cols_service_child);
                                    $new_cols_service_child = str_replace('post_id',$room_id,$new_cols_service_child);
                                    $sql_service_child = "INSERT INTO {$wpdb->prefix}hotel_room(" . implode(',',$cols_service_child) .") SELECT " . implode(',',$new_cols_service_child) . " FROM {$wpdb->prefix}hotel_room WHERE room_parent = %d and post_id = {$room['post_id']}";
                                    $wpdb->query($wpdb->prepare($sql_service_child,$post_id));
                                    if($stt_check_room_availability == '1'){  
                                        $cols_service_avai = self::sttGetColumnFromTable('st_room_availability');
                                        $new_cols_service_avai = $cols_service_avai;
                                        $new_cols_service_avai = str_replace('post_id',$room_id,$new_cols_service_avai);
                                        $new_cols_service_avai = str_replace('id','null',$new_cols_service_avai);
                                        $new_cols_service_avai = str_replace('parent_null',$insert_id,$new_cols_service_avai);  
                                        $sql_service_avai = "INSERT INTO {$wpdb->prefix}st_room_availability(" . implode(',',$cols_service_avai) .") SELECT " . implode(',',$new_cols_service_avai) . " FROM {$wpdb->prefix}st_room_availability WHERE post_id = %d";
                                        $wpdb->query($wpdb->prepare($sql_service_avai,$room['post_id']));
                                    }
                                }
                            }
                            
                        }
                    }elseif($post_type == 'hotel_room'){
                        $cols_service = self::sttGetColumnFromTable('hotel_room');
                        $new_cols_service = $cols_service;
                        $new_cols_service = str_replace('post_id',$insert_id,$new_cols_service);
                        $sql_service = "INSERT INTO {$wpdb->prefix}hotel_room(" . implode(',',$cols_service) .") SELECT " . implode(',',$new_cols_service) . " FROM {$wpdb->prefix}hotel_room WHERE post_id = %d";
                        $wpdb->query($wpdb->prepare($sql_service,$post_id));

                        if($stt_check == '1'){
                            $cols_service_avai = self::sttGetColumnFromTable('st_room_availability');
                            $new_cols_service_avai = $cols_service_avai;
                            $new_cols_service_avai = str_replace('post_id',$insert_id,$new_cols_service_avai);
                            $new_cols_service_avai = str_replace('id','null',$new_cols_service_avai);
                            $new_cols_service_avai = str_replace('parent_null','parent_id',$new_cols_service_avai);  
                            $sql_service_avai = "INSERT INTO {$wpdb->prefix}st_room_availability(" . implode(',',$cols_service_avai) .") SELECT " . implode(',',$new_cols_service_avai) . " FROM {$wpdb->prefix}st_room_availability WHERE post_id = %d";
                            $wpdb->query($wpdb->prepare($sql_service_avai,$post_id));
                        }

                    }
  
                }
                
                echo json_encode([
                    'status' => 1,
                    'message' => '<div class="alert form_alert alert-success">' . esc_html__('Duplicate successful','traveler-duplicate') .'</div>'  
                ]);
                die;
                

            }

            
        }

        public function sttDuplicatePostLink($actions,$post){
            $actions['stt-duplicate'] = '<a class="duplicate-link" data-id="'. esc_attr($post->ID) .'">'. esc_html__('Duplicate','traveler-duplicate') .'</a>';
            echo STTTravelerDuplicate::inst()->view('duplicate','backend',['postID'=>$post->ID]);
            
            return $actions;
        }

        public function add_message( $output )
        {
            $this->output[] = $output;
        }

        public function clear_message()
        {
            $this->output = [];
        }

        public function show_message()
        {
            $html = '';
            foreach ( $this->output as $value ) {
                $html .= esc_html( $value );
            }
            
            return $html;
        }
        public static function inst()
        {   
            if (!self::$_inst) {
                self::$_inst = new self();
            }

            return self::$_inst;
        }
    }
    STTDuplicate::inst();
}