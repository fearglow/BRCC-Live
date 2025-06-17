<?php
/**
 * Plugin Name: Traveler Duplicate
 * Description: Duplicate page, post, services of traveler (hotel, tour, car, flight)
 * Version: 1.0
 * Author: Shinetheme
 * Author URI: https://shinetheme.com
 * Text Domain: traveler-duplicate
 */
if (!defined('ABSPATH')) {
    die('-1');
}
if (!class_exists('STTTravelerDuplicate')) {
    class STTTravelerDuplicate
    {

        protected static $_inst;
       
        function __construct()
        {
            $this->pluginPath = trailingslashit(plugin_dir_path(__FILE__));
            $this->pluginUrl = trailingslashit(plugin_dir_url(__FILE__));
            add_action('plugins_loaded', [$this, 'pluginSetup']);
            add_action('init', [$this, 'loadFiles'], 10);
            add_action('admin_enqueue_scripts', [$this, 'enqueueAdminDashboard']);
            

        }

        public function pluginSetup()
        {
            load_plugin_textdomain('traveler-duplicate', false, basename(dirname(__FILE__)) . '/languages');
        }

        public function loadFiles()
        {
          
            if (class_exists('STTravelCode')) {
               
            require_once($this->pluginPath . 'inc/core/duplicate.php');
                  
            }

        }

        

        public function enqueueAdminDashboard()
        {
            if (class_exists('STTravelCode')) {

                wp_enqueue_style('traveler-duplicate ', $this->pluginUrl . 'assets/css/traveler-backend-dup.css');
                wp_enqueue_script('traveler-duplicate', $this->pluginUrl . 'assets/js/traveler-backend-dup.js', ['jquery'], null, true);
                

            }
        }

        public function view( $name = '', $path = '', $params = null, $return = false )
        {
            $file =  $this->pluginPath . 'views/' . $path . '/' . $name . '.php' ;
            
            if ( is_file( $file ) ) {
                if ( !empty( $params ) && is_array( $params ) ) {
                    extract( $params );
                }
                ob_start();

                require( $file );

                $buffer = ob_get_clean();
                if ( $return ) {
                    return $buffer;
                } else {
                    echo $buffer ;
                }
            } else {
                die( 'Unable to load the requested file: views/' . $path . '/' . $name . '.php' );
            }
        }

        


        public static function inst()
        {
            if (!self::$_inst) {
                self::$_inst = new self();
            }

            return self::$_inst;
        }

    }

    STTTravelerDuplicate::inst();
}