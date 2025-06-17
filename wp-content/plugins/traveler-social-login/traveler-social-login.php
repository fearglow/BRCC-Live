<?php
/**
 * @package  Traveler Social Login
 */
/*
Plugin Name: Traveler Social Login
Plugin URI: https://www.facebook.com/shinethemetoday
Description: Plugin only for Theme: Shinetheme Traveler. Please only use with traveler.zip theme
Version: 1.0.5
Author: ShineTheme
Requires PHP: 7.2
Author URI: https://www.facebook.com/shinethemetoday
License: GPLv2 or later
Text Domain: traveler-social-login
*/


defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}
add_action('init', 'st_api_social_init');
function st_api_social_init(){
    if(function_exists('st')){
        if ( class_exists( 'Inc\\ST_Social_Init' ) ) {
            Inc\ST_Social_Init::register_services();
        }
    }


}

define('ST_SOCIAL_VERSION', '1.0.0');
define( 'ST_SOCIAL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ST_SOCIAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'TRAVELER_SOCIAL_PLUGIN_DIR' ) ) {
	define( 'TRAVELER_SOCIAL_PLUGIN_DIR', plugin_dir_path( dirname( __FILE__ ) ) );
}

if ( ! defined( 'TRAVELER_SOCIAL_PLUGIN_URI' ) ) {
	define( 'TRAVELER_SOCIAL_PLUGIN_URI', plugin_dir_url( dirname( __FILE__ ) ) );
}



if ( ! function_exists( 'traveler_social_load_view' ) ) {
    function traveler_social_load_view( $file_name = "", $data = array() ) {
        if ( file_exists( ST_SOCIAL_PLUGIN_PATH . '/inc/Base/View/' . $file_name . '.php' ) ) {
            if ( is_array( $data ) && count( $data ) ) {
                extract( $data );
            }
            ob_start();
            include( ST_SOCIAL_PLUGIN_PATH . '/inc/Base/View/' .  $file_name . '.php' );

            return ob_get_clean();
        }
        return false;
    }
}
if(!function_exists('stt_get_option')){
    function stt_get_option($option_id, $default = false) {
        $st_traveler_cached_options = get_option(stt_options_id());
        if (isset($st_traveler_cached_options[$option_id]) && !empty($st_traveler_cached_options[$option_id]))
            return $st_traveler_cached_options[$option_id];
        return $default;
    }
}
if(!function_exists('stt_options_id')){
    function stt_options_id() {
        return apply_filters('st_options_id', 'option_tree');
    }
}


