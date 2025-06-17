<?php 
namespace Inc\API\Library;
use Inc\API\Settings;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_Query;
if (!class_exists('STApiLibrary')) {
    class STApiLibrary extends Settings {
        public $settings = [];
        private $rest_url = 'traveler';

        public function _init_rest_api(){
            
            register_rest_route(  $this->rest_url, '/get-list-item/(?P<type>[a-z0-9]+(?:_[a-z0-9]+)*)',
                array(
                    'callback'    => array($this, 'get_list_item'),
                    'methods'         => WP_REST_Server::READABLE,
                    'permission_callback' => '__return_true',
                    'args'            => array(
                        'type' => array (
                            'required' => true
                        ),
                    ),
                )
            );

            register_rest_route(  $this->rest_url, '/get-template-item',
                array(
                    'callback'    => array($this, 'get_template_item'),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'methods'         => WP_REST_Server::READABLE,
                    'args'            => array(
                        'id' => array (
                            'required' => true
                        ),
                        
                    ),
                )
            );

        }

        function get_list_item(WP_REST_Request $request) {
            $get_params = $request->get_params();
            $type = $get_params['type'];
            $args = array(
                'post_type'              => 'elementor_library', 
                'post_status'            => array('publish'),
                'posts_per_page'         => -1, 
                'order'                  => 'DESC', 
                'orderby'                => 'ID', 
                'tax_query' => array(
                    array(
                        'taxonomy' => 'elementor_library_type', // taxonomy slug
                        'field'    => 'slug',
                        'terms'    => $type, // term ids
                    ),
                ),
            );
            $query = new WP_Query($args);
            $array_data = [];
            if($query->have_posts()) : while($query->have_posts()) : $query->the_post();
                $post_categories = get_the_terms( get_the_ID(), 'elementor_library_category' );
                
                $category = $post_categories[0]->name ?? 0;
                $categoryID = $post_categories[0]->term_id ?? 0;
                $live_url = get_permalink(get_the_ID());  
                if($categoryID != 0){
                    $array_item_infor = [
                        'ID' =>  get_the_ID(),
                        'title' => get_the_title(),
                        'live_url' => $live_url,
                        'image' =>  wp_get_attachment_url( get_post_thumbnail_id(get_the_ID()) ),
                        'category' => $category,
                        'cateID'   => $categoryID,
                    ];
                    $array_data[] =$array_item_infor;
                }
            endwhile;endif; wp_reset_postdata();
           
            if(!empty($array_data)){
                echo json_encode([
                    'success' => true,
                    'data' => $array_data,
                    'notice' => __('Found','traveler-rest-api'),
                ]);
            }
            die();
        }
    
        function get_template_item(WP_REST_Request $request) {
            $get_params = $request->get_params();
            $id = $get_params['id'];
            $user_domain = $get_params['user-domain'];
            $user_name = $get_params['user-name'];
            $user_email = $get_params['user-email'];
            $section_name = get_the_title($id);
            $template_export_data = "";
            if (class_exists("\\Elementor\\Plugin")) {
                $post_ID = $get_params['id'];
                $pluginElementor = \Elementor\Plugin::instance();
                $template_document = $pluginElementor->documents->get( $post_ID );
                $template_export_data = $template_document->get_export_data();
            }
            //inser db 
            global $wpdb;
            $table = $wpdb->prefix.'st_customer';
            $data = array('user_domain' => $user_domain, 'user_name' => $user_name,'user_email'=>$user_email,'section_name'=>$section_name);
            $format = array('%s','%s','%s','%s');
            $wpdb->insert($table,$data,$format);
            $my_id = $wpdb->insert_id;
    
            if(!empty($template_export_data)) {
                echo json_encode(
                    [
                        'success' => true,
                        'data' => $template_export_data,
                        'notice' => __('Found','traveler-rest-api'),
                    ]
                );
            }
            die();
        }
    
    }

    
   
}