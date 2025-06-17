<?php
declare(strict_types=1);
namespace Inc\Base\Controller;
use Inc\Base\Controller\BaseController;
use Abraham\TwitterOAuth\TwitterOAuth;
use \WP_User;
use \WP_Error;
use \Google_Client;
use \TravelHelper;
class STSocialLogin extends BaseController{
   public $clientID  = "";
   public $clientSecret   = "";
   public $redirectUri   =  "";
   public $createAuthUrl   =  "";
    function __construct(){
        //Google
        $this->clientID = stt_get_option('social_gg_client_id');
        $this->clientSecret = stt_get_option('social_gg_client_secret');
        $this->redirectUri = get_site_url().'/wp-admin/admin-ajax.php?action=st_googleplus';
        //End google

        add_action('wp_ajax_traveler.socialLogin', [$this, '__socialLogin']);
        add_action('wp_ajax_nopriv_traveler.socialLogin', [$this, '__socialLogin']);
        add_action('wp_enqueue_scripts', [$this, '__enqueueScripts']);

        
        add_action('init', [$this, '__addSocialEndpoint']);
        add_filter('query_vars', [$this, 'add_query_vars_filter'],10,1);
        
        add_action('template_redirect', [$this, '__handleSocialEndpoint']);
       


        add_shortcode( 'st-google-login', [$this, 'stGooogleLogin_func']);
        add_action( 'wp_ajax_st_googleplus', array($this, 'apiCallback'));
        add_action( 'wp_ajax_nopriv_st_googleplus', array($this, 'apiCallback'));
        add_action('init',array($this,'start_my_session'));
    }
    
    function stGooogleLogin_func($atts){
        // include template with the arguments (The $args parameter was added in v5.5.0)
        $attributes = shortcode_atts( array(
            'type' => '',
        ), $atts );
        return traveler_social_load_view( "shortcode-{$attributes['type']}");
    }

    public function boot_session() {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();;
        }
    }

    //Gooogle
    public function getLoginUrl(){
        if(is_session_started() === false){
            session_start();
        }
        
        $google_plus = $this->initApi();
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            return false;
        } else {
            $url = $google_plus->createAuthUrl();
            return esc_url($url);
        }
    }
    public function initApi(){
        $client = new Google_Client();
        $client->setClientId($this->clientID);
        $client->setClientSecret($this->clientSecret);
        $client->setRedirectUri($this->redirectUri);
        $client->setScopes('email');
        $client->addScope("profile");
        // $plus = new Google_Service_Plus($client);
        return $client;
    }

    public function apiCallback() {
        $google_plus = $this->initApi();
        $this->google_details = $this->getUserDetails($google_plus);

        if(!session_id()) {
            session_start();
        }
        $this->redirect_url = (isset($_SESSION['st_google_url'])) ? $_SESSION['st_google_url'] : home_url();
        $this->createUser();
        $this->loginUser();
        
        header("Location: ".get_the_permalink(), true);
        die();
    }

    public function getUserDetails($google_plus){
        $redirect_url = (isset($_SESSION['st_google_url'])) ? $_SESSION['st_google_url'] : home_url();
        if (isset($_GET['code'])) {
            $google_plus = $this->initApi();
              $google_plus->authenticate($_GET['code']);
              $_SESSION['access_token'] = $google_plus->getAccessToken();
              // $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
              header('Location: ' . filter_var(get_home_url()."/#profile", FILTER_SANITIZE_URL));
        }
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
          $google_plus->setAccessToken($_SESSION['access_token']);
          $plus = new Google_Service_Plus($google_plus);
          $me = $plus->people->get('me');
          $id = $me['id'];
          $name =  $me['displayName'];
          $email =  $me['emails'][0]['value'];
          $profile_image_url = $me['image']['url'];
          $cover_image_url = $me['cover']['coverPhoto']['url'];
          $profile_url = $me['url'];
          $token = $_SESSION['access_token'];
          return array(
            'id' => $id,
            'email' => $email,
            'name' => $name,
            'profile_image_url' => $profile_image_url,
            'profile_url' => $profile_url,
            'token' => $token,
          );
        } else {
          return array(
            'error' => __('Not token','traveler-social-login'),
          );
        }
    }

    public function  createUser(){
        $wp_users = get_users(array(
            'meta_key'     => 'st_googleplus_id',
            'meta_value'   => $this->google_details['id'],
            'number'       => 1,
            'count_total'  => false,
            'fields'       => 'id',
        ));
        $id_key_user = $this->google_details['id'];
        $token = $this->google_details['token'];

        if(empty($wp_users[0])) {
            $gp_user = $this->google_details;
            $fullname = explode(' ',$gp_user['name']);
            // Create an username
            $username = sanitize_user(str_replace(' ', '_', strtolower($gp_user['name'])));

            // Creating our user
            $new_user = wp_create_user($username, wp_generate_password(), $gp_user['email']);

            if(is_wp_error($new_user)) {
                $_SESSION['st_googleplus_message'] = $new_user->get_error_message();
                // Redirect
                header("Location: ".get_home_url()."/#profile", true);
                die();
            }

            update_user_meta( $new_user, 'first_name', $fullname['first_name'] );
            update_user_meta( $new_user, 'last_name', $fullname['last_name'] );
            update_user_meta( $new_user, 'user_url', $gp_user['profile_url'] );
            update_user_meta( $new_user, 'st_googleplus_id', $gp_user['id'] );
            update_user_meta( $new_user, 'token', $token );
            wp_set_auth_cookie( $new_user );
        } else {
            global $wpdb;
            $id_user  =  ($wpdb->get_results( "SELECT `user_id` FROM `".$wpdb->prefix."session_tokens` WHERE  `meta_value` = '$id_key_user' "));
        }
        
    }

    public function loginUser(){
        $wp_users = get_users(array(
            'meta_key'     => 'st_googleplus_id',
            'meta_value'   => $this->google_details['id'],
            'number'       => 1,
            'count_total'  => false,
            'fields'       => 'id',
        ));
     
        if(empty($wp_users[0])) {
            return false;
        }
        wp_set_auth_cookie( $wp_users[0] );
    }
    //EndGoogle


    
    function add_query_vars_filter($query_vars )
    {
        $query_vars[] = 'social-login';
        return $query_vars ;
    }
    public function __addSocialEndpoint()
    {
        add_rewrite_endpoint('social-login', EP_ALL);
    }
    public function __handleSocialEndpoint()
    {
        // Check Login Handler
        global $wp_query;
        $path = $_SERVER['REQUEST_URI'];
        
        $path = substr($path, 1);
        if (isset($path[1])) {
            $path = explode('/', $path);
            $channel = isset($path[1]) ? $path[1] : 'facebook';
            $action = isset($path[2]) ? $path[2] : 'login';
            
            $position = str_contains($action, 'callback');
            if ($position !== false) {
                $action = 'callback';
            }
            
            switch ($action) {
                case "callback":
                    switch ($channel) {
                        case "twitter":
                            $this->twitterLoginCallBack();
                            break;
                    }
                    break;
                case "login":
                    switch ($channel) {
                        case "twitter":
                            $this->twitterLogin();
                            break;
                    }
                default:
            }
        }
    }
    public function channelStatus($channel = 'facebook')
    {
        switch ($channel) {
            case "google":
                if (st()->get_option('social_gg_login', 'off') != 'on') return false;
                if (!st()->get_option('social_gg_client_id') or !st()->get_option('social_gg_client_secret')) return false;
                break;
            case "twitter":
                if (st()->get_option('social_tw_login', 'off') != 'on') return false;
                if (!st()->get_option('social_tw_client_id') or !st()->get_option('social_tw_client_secret')) return false;
                break;
            case "facebook":
            default:
                if (st()->get_option('social_fb_login', 'off') != 'on') return false;
                if (!st()->get_option('social_fb_app_id')) return false;
                break;
        }
        return true;
    }
    public function __enqueueScripts()
    {
        $st_social_params = [];
        if ($this->channelStatus('google')) {
            wp_enqueue_script('google-api-client', 'https://apis.google.com/js/api:client.js');
            wp_enqueue_script('google-api', 'https://apis.google.com/js/api.js');
            $st_social_params['google_client_id'] = st()->get_option('social_gg_client_id');
        }
        //if(!empty($st_social_params))
        //{
        wp_localize_script('jquery', 'st_social_params', $st_social_params);
        //}
    }
    public function __socialLogin()
    {
        $this->verifyRequest();
        if (is_user_logged_in()) {
            $this->sendJson(['reload' => 1]);
        }
        $channel = !empty($_POST['channel']) ? $_POST['channel'] : 'facebook';
        if (empty($channel)) $this->sendError(esc_html__('Channel is missing', 'traveler'));
        switch ($channel) {
            case "facebook":
                $this->handleFacebookLogin();
                break;
            case "google":
                $this->handleGoogleLogin();
                break;
        }
    }
    public function twitterLoginCallBack()
    {
        if (class_exists("Abraham\TwitterOAuth\TwitterOAuth")) {
            if (empty($_GET['oauth_verifier'])) {
                echo esc_html__("Author Verifier ID is missing", 'traveler');
                die;
            }
            
            if (isset($_COOKIE['request_token']) && !empty($_COOKIE['request_token'])) {
                $request_token =  unserialize(stripslashes(gzuncompress(base64_decode($_COOKIE['request_token']))));
            }
            if(!empty($request_token)){
                $oauth_token = $request_token['oauth_token'];
                $oauth_token_secret = $request_token['oauth_token_secret'];
            }
            $connection = new \Abraham\TwitterOAuth\TwitterOAuth(
                st_traveler_get_option('social_tw_client_id'),
                st_traveler_get_option('social_tw_client_secret'),
                $oauth_token,
                $oauth_token_secret
            );
            try {
                $access_token = $connection->oauth("oauth/access_token", ['oauth_verifier' => $_GET['oauth_verifier'] , 'oauth_token' => $_GET['oauth_token']]);
                if (!empty($access_token['oauth_token'])) {
                    $handler = $this->handleTwitterAccount($access_token['user_id'], $access_token['screen_name']);
                    if (is_wp_error($handler)){
                        echo balanceTags($handler->get_error_message());
                    }
                    else {
                        //for window.open, reload parent
                        ?>
                        <script>
                            window.opener.location.reload();
                            window.close();
                        </script>
                        <?php
                    }
                }
            } catch (Exception $exception) {
                echo esc_html($exception->getMessage());
                die;
            }
            die;
        }
    }
    public function twitterLogin()
    {
        if (class_exists("Abraham\TwitterOAuth\TwitterOAuth")) {
            $connection = new \Abraham\TwitterOAuth\TwitterOAuth(st_traveler_get_option('social_tw_client_id'), st_traveler_get_option('social_tw_client_secret'));
            $request_token = $connection->oauth("oauth/request_token", ['oauth_callback' => site_url() . '/social-login/twitter/callback']);
            if (empty($request_token['oauth_token'])) {
                echo "Can not connect to twitter";
                die;
            }
            
            $data_compress = base64_encode(gzcompress(addslashes(serialize($request_token)), 9));
            TravelHelper::setcookie( 'request_token', $data_compress, time() + ( 86400 * 30 ) );
            
            $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
            wp_redirect($url);
            echo esc_html__("Redirecting you to twitter. Please wait", 'traveler');
            die;
        }
    }
    protected function handleTwitterAccount($oauth_uid, $display_name)
    {
        /**
         * @todo Kiểm tra xem OAuth ID đã đăng ký chưa, nếu rồi thì đăng nhập luôn
         *
         */
        $register_user = false;
        global $wpdb;
        $find_user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->usermeta} where meta_key = '_twitter_uid' and meta_value = %s", $oauth_uid));
        if ($find_user) {
            /**
             * @todo OAuth UID tồn tại, kiểm tra xem User đã tồn tại chưa
             */
            $user = new \WP_User($find_user->user_id);
            if (!empty($user->ID)) {
                /**
                 * @todo Login bằng UID
                 */
                return $this->loginByUserID($user->ID, $user->user_login, $user);
            } else {
                /**
                 * @todo trường hợp không tìm thấy User, xóa meta key cũ và đăng ký user mới
                 */
                $register_user = true;
                $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->usermeta} where umeta_id = %d", $find_user->umeta_id));
            }
        } else {
            /**
             * @todo Không tìm thấy meta key -> Tạo user
             */
            $register_user = true;
        }
        if ($register_user) {
            // Note: Twitter do not provide their user's email address and by default a random email will then be generated for them instead.
            $user_email = $oauth_uid . '@twitter.com';
            $user_id = $this->createOauthAccount('twitter', $user_email, $oauth_uid, $display_name);
            if (is_wp_error($user_id)) return $user_id;
            $this->loginByUserID($user_id, 'twitter_' . $oauth_uid);
            return true;
        }
    }
    protected function handleFacebookLogin()
    {
        $access_token = !empty($_POST['access_token']) ? $_POST['access_token'] : '';
        if (!$access_token) $this->sendError(esc_html__('Access Token is missing', 'traveler'));
        $baseFacebooAPI = 'https://graph.facebook.com/v3.1/';
        $response = wp_remote_get(add_query_arg(['fields' => 'id,name,email', 'access_token' => $access_token], $baseFacebooAPI . 'me'));
        if (is_wp_error($response)) {
            $this->sendError(esc_html__('Can not connect to Facebook. Please try again later', 'traveler'));
        }
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);
        if (empty($json)) $this->sendError(esc_html__('Can not read Facebook response. Please try again later', 'traveler'));
        if (!empty($json['error'])) $this->sendError(esc_html__("Facebook Error: ", 'traveler') . $json['error']['message']);
        /**
         * @todo Kiểm tra khách có cấp quyền lấy email chưa
         */
        if (empty($json['email'])){
            $json['email'] = $json['id'].'@domain.com';
        }
        /**
         * @todo Kiểm tra xem FB ID đã đăng ký chưa, nếu rồi thì đăng nhập luôn
         *
         */
        $fb_uid = $json['id'];
        $register_user = false;
        global $wpdb;
        $find_user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->usermeta} where meta_key = '_facebook_uid' and meta_value = %s", $fb_uid));
        if ($find_user) {
            /**
             * @todo FB UID tồn tại, kiểm tra xem User đã tồn tại chưa
             */
            $user = new \WP_User($find_user->user_id);
            if (!empty($user->ID)) {
                /**
                 * @todo Login bằng UID
                 */
                $this->loginByUserID($user->ID, $user->user_login, $user);
                $this->sendJson(['reload' => 1]);
            } else {
                /**
                 * @todo trường hợp không tìm thấy User, xóa meta key cũ và đăng ký user mới
                 */
                $register_user = true;
                $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->usermeta} where umeta_id = %d", $find_user->umeta_id));
            }
        } else {
            /**
             * @todo Không tìm thấy meta key -> Tạo user
             */
            $register_user = true;
        }
        if ($register_user) {
            $user_id = $this->createOauthAccount('facebook', $json['email'], $json['id'], $json['name']);
            if (is_wp_error($user_id)) {
                $this->sendError(esc_html__('Error: ', 'traveler') . $user_id->get_error_message());
            }
            $this->loginByUserID($user_id, 'facebook_' . $json['id']);
            $this->sendJson(['reload' => 1]);
        }
    }
    protected function handleGoogleLogin()
    {
        $user_email = !empty($_POST['useremail']) ? $_POST['useremail'] : '';
        /**
         * @todo Kiểm tra khách có cấp quyền lấy email chưa
         */
        if (empty($user_email)) $this->sendError(esc_html__("Google Error: You must allow us read your email address ", 'traveler'));
        /**
         * @todo Kiểm tra xem Google ID đã đăng ký chưa, nếu rồi thì đăng nhập luôn
         *
         */
        $oauth_uid = !empty($_POST['userid']) ? $_POST['userid'] : '';
        $register_user = false;
        global $wpdb;
        $find_user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->usermeta} where meta_key = '_google_uid' and meta_value = %s", $oauth_uid));
        if ($find_user) {
            /**
             * @todo Google UID tồn tại, kiểm tra xem User đã tồn tại chưa
             */
            $user = new \WP_User($find_user->user_id);
            if (!empty($user->ID)) {
                /**
                 * @todo Login bằng UID
                 */
                $this->loginByUserID($user->ID, $user->user_login, $user);
                $this->sendJson(['reload' => 1]);
            } else {
                /**
                 * @todo trường hợp không tìm thấy User, xóa meta key cũ và đăng ký user mới
                 */
                $register_user = true;
                $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->usermeta} where umeta_id = %d", $find_user->umeta_id));
            }
        } else {
            /**
             * @todo Không tìm thấy meta key -> Tạo user
             */
            $register_user = true;
        }
        if ($register_user) {
            $username = !empty($_POST['username']) ? $_POST['username'] : '';
            $user_id = $this->createOauthAccount('google', $user_email, $oauth_uid, $username);
            // if (is_wp_error($user_id)) {
            //     $this->sendError(esc_html__('Error: ', 'traveler') . $user_id->get_error_message());
            // }
            $this->loginByUserID($user_id, 'google_' . $oauth_uid);
            $this->sendJson(['reload' => 1]);
        }
    }
    /**
     * @todo Lấy thông tin google user từ access token
     *
     * @param $access_token
     * @return array|WP_Error
     */
    protected function getGoogleUserData($access_token)
    {
        $baseApiUrl = 'https://www.googleapis.com/plus/v1/';
        $response = wp_remote_get(add_query_arg([
            'access_token' => $access_token,
        ], $baseApiUrl . 'people/me'));
        if (is_wp_error($response)) return $response;
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);
        if (!empty($json[0]['error']) and !empty($json[0]['error']['message'])) return new \WP_Error('api_error', $json[0]['error']['message']);
        if (!empty($json['error_description'])) return new \WP_Error('api_error', $json['error_description']);
        if (empty($json) or empty($json['id'])) return new \WP_Error('api_error', esc_html__('Can not get user info', 'traveler'));
        return $json;
    }
    /**
     * @todo Lấy access token Google từ Authorization Code
     *
     * @param $authorizationCode
     * @return array|WP_Error
     */
    protected function getGoogleAccessToken($authorizationCode)
    {
        $baseApiUrl = 'https://www.googleapis.com/oauth2/v4/';
        $response = wp_remote_post($baseApiUrl . 'token', [
            'body' => [
                'client_id' => st_traveler_get_option('social_gg_client_id'),
                'client_secret' => st_traveler_get_option('social_gg_client_secret'),
                'code' => $authorizationCode,
                'redirect_uri' => site_url(),
                'grant_type' => 'authorization_code'
            ]
        ]);
        if (is_wp_error($response)) return $response;
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);
        if (!empty($json['error_description'])) return new \WP_Error('api_error', $json['error_description']);
        if (empty($json) or empty($json['access_token'])) return new \WP_Error('api_error', esc_html__('Can not get access token', 'traveler'));
        return $json['access_token'];
    }
    /**
     * @todo Đăng ký user bằng social channel, email và oauth_id
     *
     * @param $channel
     * @param $email
     * @param $oauth_id
     * @param $name
     *
     * @return boolean\int|WP_Error
     */
    protected function createOauthAccount($channel, $email, $oauth_id, $name = '')
    {
        $email_check = email_exists($email);
        if ($email_check) return new \WP_Error('api_error', esc_html__('Email exists', 'traveler'));
        $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
        $user_id = wp_insert_user([
            'display_name' => $name,
            'user_login' => $channel . '_' . $oauth_id,
            'user_email' => $email,
            'user_pass' => $random_password
        ]);
        if (!is_wp_error($user_id)) {
            update_user_meta($user_id, '_' . $channel . '_uid', $oauth_id);
        }
        return $user_id;
    }
    /**
     * @todo Force đăng nhập bằng user id và user_login
     *
     * @param $uid
     * @param $user_login
     * @return boolean
     */
    protected function loginByUserID($uid, $user_login, $user = array())
    {
        wp_set_current_user($uid, $user_login);
        wp_set_auth_cookie($uid);
        do_action('wp_login', $user_login, $user);
        return true;
    }
    protected function verifyRequest($action_name = 'st_frontend_security')
    {
        if (!$this->verifyNonce('_s', $action_name)) {
            $res = esc_html__('Your session has ended. Please reload the website', 'traveler');
            $this->sendError($res, ['error_code' => 'session']);
        }
        return true;
    }
    protected function verifyNonce($name = '_s', $action_name = '')
    {
        if (!isset($_POST[$name]) or !wp_verify_nonce($_POST[$name], $action_name)) return false;
        return true;
    }
    public function sendError($message, $extra = [])
    {
        $res = [];
        $res['message'] = $message;
        $res['status'] = 0;
        if (!empty($extra) and is_array($extra)) {
            $res = array_merge($res, $extra);
        }
        $this->sendJson($res);
    }
    protected function sendJson($res = [])
    {
        $res = wp_parse_args($res, [
            'status' => 1
        ]);
        @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
        echo(json_encode($res));
        die;
    }
}