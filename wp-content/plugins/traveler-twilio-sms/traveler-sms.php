<?php
/**
 * Plugin Name: Traveler  SMS
 * Description: Integrate with Twilio SMS. Support the system to send messages to customer, partner, admin when booking
 * Version: 1.4
 * Author: Shinetheme
 * Author URI: https://shinetheme.com
 * Text Domain: traveler-sms
 */
if (!defined('ABSPATH')) {
    die('-1');
}

if (!class_exists('STTTwilioSMS')) {
    class STTTwilioSMS
    {

        protected static $_inst;

        function __construct()
        {

            $this->pluginPath = trailingslashit(plugin_dir_path(__FILE__));
            $this->pluginUrl = trailingslashit(plugin_dir_url(__FILE__));
            add_action('plugins_loaded', [$this, 'pluginSetup']);
            add_action('init', [$this, 'loadFiles'], 10);
            add_action('wp_enqueue_scripts', [$this, 'pluginEnqueue']);
            add_action('admin_enqueue_scripts', [$this, 'enqueueAdminDashboard']);

        }

        public function pluginSetup()
        {
            load_plugin_textdomain('traveler-sms', false, basename(dirname(__FILE__)) . '/languages');
        }

        public function loadFiles()
        {
            if (class_exists('STTravelCode')) {

                require_once($this->pluginPath . 'inc/plugins/vendor/autoload.php');
                require_once($this->pluginPath . 'inc/core/Inject.php');
                require_once($this->pluginPath . 'inc/libraries/sms-setting.php');
                require_once($this->pluginPath . 'inc/helper/helper.php');

            }

        }

        public function pluginEnqueue()
        {
            if (class_exists('STTravelCode')) {
                wp_enqueue_style('traveler-niceselect', $this->pluginUrl . 'asset/css/nice-select.css');
                wp_enqueue_style('traveler-sms', $this->pluginUrl . 'asset/css/traveler-sms.css');
                wp_enqueue_script('traveler-niceselect-min-js', $this->pluginUrl . 'asset/js/jquery.nice-select.js', ['jquery'], null, true);
                wp_enqueue_script('traveler-sms-js', $this->pluginUrl . 'asset/js/traveler-sms.js', ['jquery'], null, true);

            }
        }

        public function enqueueAdminDashboard()
        {
            if (class_exists('STTravelCode')) {

                wp_enqueue_style('traveler-admin-sms', $this->pluginUrl . 'asset/css/traveler-admin-sms.css');
                wp_enqueue_script('traveler-admin-js', $this->pluginUrl . 'asset/js/traveler-admin-sms.js', ['jquery'], null, true);

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

    STTTwilioSMS::inst();
}
