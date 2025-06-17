<?php
/**
 * Plugin Name: Traveler Export Booking PDF
 * Description:Export your booking history to pdf file.
 * Version: 1.0.5
 * Author: Shinetheme
 * Author URI: https://shinetheme.com
 * Text Domain: traveler-export
 */
if (!defined('ABSPATH')) {
    die('-1');
}
if (!class_exists('STTTravelerExport')) {
    class STTTravelerExport
    {

        protected static $_inst;

        function __construct()
        {
            $this->pluginPath = trailingslashit(plugin_dir_path(__FILE__));
            $this->pluginUrl = trailingslashit(plugin_dir_url(__FILE__));
            add_action('plugins_loaded', [$this, 'pluginSetup']);
            add_action('init', [$this, 'loadFiles'], 10);
            add_action('wp_enqueue_scripts', [$this, 'pluginEnqueue']);
        }

        public function pluginSetup()
        {
            load_plugin_textdomain('traveler-export', false, basename(dirname(__FILE__)) . '/languages');
        }

        public function loadFiles()
        {

            if (class_exists('STTravelCode')) {
                require_once($this->pluginPath . 'inc/plugins/vendor/autoload.php');
                require_once($this->pluginPath . 'inc/libraries/Emogrifier.php');
                require_once($this->pluginPath . 'inc/core/export-booking.php');
            }
        }

        public function pluginEnqueue()
        {
            if (class_exists('STTravelCode')) {
                if (is_page_template('template-user.php')) {
                    wp_enqueue_script('traveler-export', $this->pluginUrl . 'assets/js/traveler-export.js', ['jquery'], null, true);
                    wp_enqueue_style('traveler-export', $this->pluginUrl . 'assets/css/traveler-export.css');
                }
            }
        }

        public function view($name = '', $path = '', $params = null, $return = false)
        {
            $file =  $this->pluginPath . 'views/' . $path . '/' . $name . '.php' ;

            if (is_file($file)) {
                if (!empty($params) && is_array($params)) {
                    extract($params);
                }
                ob_start();

                require($file);

                $buffer = ob_get_clean();
                if ($return) {
                    return $buffer;
                } else {
                    echo $buffer ;
                }
            } else {
                die('Unable to load the requested file: views/' . $path . '/' . $name . '.php');
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

    STTTravelerExport::inst();
}
