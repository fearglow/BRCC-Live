<?php
/**
Plugin Name: Traveler Stripe
Plugin URI: https://shinetheme.com/
Description: This plugin is used for Traveler Theme
Version: 1.0.9
Author: ShineTheme
Author URI: https://shinetheme.com/
License: GPLv2 or later
Text Domain: vina_stripe
*/
if ( !function_exists( 'add_action' ) ) {
	echo __('Hi there!  I\'m just a plugin, not much I can do when called directly.','vina_stripe');
	exit;
}
require_once('vendor/autoload.php');
define( 'ST_VINA_STRIPE_VERSION', '1.0.0' );
define( 'ST_VINA_STRIPE_MINIMUM_WP_VERSION', '5.0' );
define( 'ST_VINA_STRIPE_PLUGIN_PATH', trailingslashit(plugin_dir_path(__FILE__)) );
define( 'ST_VINA_STRIPE_PLUGIN_URL', trailingslashit(plugin_dir_url(__FILE__)) );
define( 'ST_VINA_STRIPE_DELETE_LIMIT', 100000 );
define('ST_VINA_STRIPE_FOLDER_PLUGIN', 'st-vina-stripe');
class ST_VinaStripe{
	public $key_secret_vina_stripe;
	public $table_name='st_order_item_meta';
	public function __construct(){
		$theme = wp_get_theme(); // gets the current theme
		if ( 'Traveler' == $theme->name || 'Traveler' == $theme->parent_theme ) {
			add_action('init', [$this, '_pluginLoader'], 20);
			add_action('wp_enqueue_scripts', [$this, '_vinapluginEnqueue']);
			add_action( 'wp_ajax_vina_stripe_confirm_server', array($this,'vina_stripe_confirm_server' ) );
			add_action( 'wp_ajax_nopriv_vina_stripe_confirm_server', array($this, 'vina_stripe_confirm_server' ));
			// add_action( 'wp_ajax_vina_stripe_confirm', array($this,'vina_stripe_confirm' ) );
			// add_action( 'wp_ajax_nopriv_vina_stripe_confirm', array($this, 'vina_stripe_confirm' ));
			add_action( 'wp_ajax_vina_stripe_package_confirm_server', array($this,'vina_stripe_package_confirm_server' ) );
			add_action( 'wp_ajax_nopriv_vina_stripe_package_confirm_server', array($this, 'vina_stripe_package_confirm_server' ));
		}
	}
	public function vina_stripe_get_public_key(){
		$vina_stripe_test_public_key = st()->get_option('vina_stripe_test_publish_key', 'pk_test_iSN4LrONtD7mfXWA4dQJl41t');
		$vina_stripe_public_key = st()->get_option('vina_stripe_publish_key');
		$sanbox_stripe = st()->get_option('vina_stripe_enable_sandbox', 'on');

		if($sanbox_stripe === 'on'){
		 	return $vina_stripe_test_public_key;
		} else {
			return $vina_stripe_public_key;
		}
	}
	public function vina_stripe_get_secret_key(){
		$vina_stripe_test_secret_key = st()->get_option('vina_stripe_test_secret_key', 'sk_test_gMCmYqK4OojvyBRaaWvx85X5');
		$vina_stripe_secret_key = st()->get_option('vina_stripe_secret_key' );
		$sanbox_stripe = st()->get_option('vina_stripe_enable_sandbox', 'on');
		if($sanbox_stripe == 'on'){
		 	return $vina_stripe_test_secret_key;
		} else {
			return $vina_stripe_secret_key;
		}
	}
	public function vina_stripe_package_confirm_server(){
		$stripe_secret_key = $this->vina_stripe_get_secret_key();
		$intent = null;
		$payment_intent_id = isset($_POST['payment_intent_id']) ? $_POST['payment_intent_id'] : false;
		$st_order_id = isset($_POST['st_order_id']) ? $_POST['st_order_id'] : '';
		$data_step2 = isset($_POST['data_step2']) ? $_POST['data_step2'] : array();
		try {
			if(!empty($st_order_id)){
				$order   = STAdminPackages::get_inst()->get( '*', $st_order_id );
	            $currency = TravelHelper::get_current_currency( 'name' );
	            $infor_partner = $order->partner_info;
	            $cart_infor = maybe_unserialize($infor_partner);
	            $total=round( (float)$order->package_price, 2 );
				\Stripe\Stripe::setApiKey($stripe_secret_key);
				if (isset($payment_method_id)) {
				  # Create the PaymentIntent
				   $intent = \Stripe\PaymentIntent::create([
					    'payment_method' => $payment_method_id,
					    'amount' => $total*100,
					    'currency'      => TravelHelper::get_current_currency('name'),
	                    'description'   => __('Full name:','vina_stripe').' ' .$cart_infor['firstname'].' '.$cart_infor['lastname'].' '.__('Member Package','vina_stripe').":".$order->package_name,
					    'confirmation_method' => 'manual',
					    'confirm' => true,
				   ]);
				}
			}
				if (isset($payment_intent_id)) {
					$intent = \Stripe\PaymentIntent::retrieve(
						$payment_intent_id
					);
					$intent->confirm();
				}
				echo $this->generatePaymentResponsePackage_server($intent,$data_step2);
			} catch (\Stripe\Error\Base $e) {
				# Display error on client
				echo json_encode([
					'error' => $e->getMessage(),
					'data' => $data_step2,
				]);
		}
		die();
	}
	public function generatePaymentResponsePackage_server($intent,$data_step2){
		if ($intent->status == 'succeeded') {
		  # The payment didn’t need any additional actions and completed!
		  # Handle post-payment fulfillment
		  	$st_order_id = !empty($_POST['st_order_id']) ? $_POST['st_order_id'] : '';
			global $wpdb;
			$table = $wpdb->prefix . 'st_member_packages_order';
			$data  = [
				'status' => 'complete'
			];
			$where = [
				'id' => $st_order_id
			];
			$rs    = $wpdb->update( $table, $data, $where );
			$wpdb->query($sql_update);
			add_filter('st_stripe_complete_purchase',true);
		    echo json_encode([
		    	"success" => true,
		    	'data' => $data_step2,
		  	]);
		} else {
			echo json_encode([
		    	"success" => false,
		    	'data' => $data_step2,
		  	]);
		}
	}
	public function vina_stripe_confirm(){
		$st_order_id = isset($_POST['st_order_id']) ? $_POST['st_order_id'] : '';
		STCart::send_mail_after_booking( $st_order_id, true );
		update_post_meta($st_order_id, 'status', 'complete');
		global $wpdb;
		$table = $wpdb->prefix . 'st_order_item_meta';
		$data  = [
			'status' => 'complete'
		];
		$where = [
			'order_item_id' => $st_order_id
		];
		$wpdb->update( $table, $data, $where );
		echo json_encode([
			"success" => true,
		]);
		die();
	}
	public function vina_stripe_confirm_server(){
		$stripe_secret_key = $this->vina_stripe_get_secret_key();
		$intent = null;
		$payment_intent_id = isset($_POST['payment_intent_id']) ? $_POST['payment_intent_id'] : false;
		$st_order_id = isset($_POST['st_order_id']) ? $_POST['st_order_id'] : '';
		$data_step2 = isset($_POST['data_step2']) ? $_POST['data_step2'] : array();
		try {
			if(!empty($st_order_id)){
				$total = get_post_meta($st_order_id, 'total_price', TRUE);
	            $st_first_name = get_post_meta($st_order_id, 'st_first_name', TRUE);
	            $st_last_name = get_post_meta($st_order_id, 'st_last_name', TRUE);
	            $st_cart_info = get_post_meta($st_order_id, 'st_cart_info', TRUE);
	            $st_booking_id = get_post_meta($st_order_id, 'st_booking_id', TRUE);
	            $cart_infor = maybe_unserialize($st_cart_info);
	            $total=round((float)$total,2);
				\Stripe\Stripe::setApiKey($stripe_secret_key);
				if (isset($payment_method_id)) {
				  # Create the PaymentIntent
				   $intent = \Stripe\PaymentIntent::create([
					    'payment_method' => $payment_method_id,
					    'amount' => $total*100,
					    'currency'      => TravelHelper::get_current_currency('name'),
	                    'description'   => __('Full name:','vina_stripe').' ' .$st_first_name.' '.$st_last_name.' '.__('Name service:','vina_stripe').' '.esc_html($cart_infor[$st_booking_id]['title']),
					    'confirmation_method' => 'manual',
					    'confirm' => true,
				   ]);
				}
			}
				if (isset($payment_intent_id)) {
					$intent = \Stripe\PaymentIntent::retrieve(
						$payment_intent_id
					);
					$intent->confirm();
				}
				echo $this->generatePaymentResponse_server($intent,$data_step2,$st_order_id);
			} catch (Exception $e) {
				# Display error on client
				echo  json_encode([
					'error' => $e->getMessage(),
					'data' => $data_step2,
					'message' => $e->getMessage(),
				]);
			}
		die();
	}
	public function generatePaymentResponse_server($intent,$data_step2,$st_order_id) {
		# Note that if your API version is before 2019-02-11, 'requires_source_action'
		# appears as 'requires_source_action'.
		if ($intent->status == 'requires_action' &&
		    $intent->next_action->type == 'use_stripe_sdk') {
		  # Tell the client to handle the action

			update_post_meta($st_order_id, 'status', 'imcomplete');
			update_post_meta($st_order_id, 'transaction_id', $intent->id);
			global $wpdb;
			$table = $wpdb->prefix . 'st_order_item_meta';
			$data  = [
				'status' => 'imcomplete'
			];
			$where = [
				'order_item_id' => $st_order_id
			];
			$rs    = $wpdb->update( $table, $data, $where );
			$wpdb->query($sql_update);
			echo json_encode([
				'requires_action' => true,
				'payment_intent_client_secret' => $intent->client_secret,
				'data' => $data_step2,
			]);
		}
		if ($intent->status == 'requires_source_action' &&
			    $intent->next_action->type == 'use_stripe_sdk') {
			    # Tell the client to handle the action

				update_post_meta($st_order_id, 'status', 'imcomplete');
				update_post_meta($st_order_id, 'transaction_id', $intent->id);
				global $wpdb;
				$table = $wpdb->prefix . 'st_order_item_meta';
				$data  = [
					'status' => 'imcomplete'
				];
				$where = [
					'order_item_id' => $st_order_id
				];
				$rs    = $wpdb->update( $table, $data, $where );
				$wpdb->query($sql_update);
				add_filter('st_stripe_complete_purchase',true);
			echo json_encode([
				'requires_action' => true,
				'payment_intent_client_secret' => $intent->client_secret,
				'data' => $data_step2,
			]);
		}
		else if ($intent->status == 'succeeded') {
		  # The payment didn’t need any additional actions and completed!
		  # Handle post-payment fulfillment

			update_post_meta($st_order_id, 'status', 'complete');
			update_post_meta($st_order_id, 'transaction_id', $intent->id);
			global $wpdb;
			$table = $wpdb->prefix . 'st_order_item_meta';
			$data  = [
				'status' => 'complete'
			];
			$where = [
				'order_item_id' => $st_order_id
			];
			$rs    = $wpdb->update( $table, $data, $where );
			$wpdb->query($sql_update);
			add_filter('st_stripe_complete_purchase',true);
		    echo json_encode([
		    	"success" => true,
		    	'data' => $data_step2,
		  	]);
		} else {
		  # Invalid status
		  http_response_code(500);
		  echo json_encode(['error' => 'Invalid PaymentIntent status', 'data' => $data_step2,]);
		}
	}
	public function _vinapluginEnqueue()
    {
    	$vina_stripe_test_public_key = st()->get_option('vina_stripe_test_publish_key', 'pk_test_iSN4LrONtD7mfXWA4dQJl41t');
		$vina_stripe_public_key = st()->get_option('vina_stripe_publish_key');
        wp_register_script('st-vina-stripe-js', 'https://js.stripe.com/v3/', ['jquery'], null, true);
        $sanbox_stripe = st()->get_option('vina_stripe_enable_sandbox', 'on');
        $stripe_params['vina_stripe'] = [
            'publishKey' => $vina_stripe_public_key,
            'testPublishKey' => $vina_stripe_test_public_key,
            'sanbox' => ($sanbox_stripe == 'on') ? 'sandbox' : 'live',
        ];
        wp_localize_script('jquery', 'st_vina_stripe_params', $stripe_params);
        wp_localize_script( 'jquery', 'vina_plugin_params', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'home_url' => home_url('/'),
            '_s'       => wp_create_nonce( '_wpnonce_security' ),
        ] );
        wp_enqueue_script('st-vina-stripe-js');
        wp_register_style('st-vina-stripe-css', ST_VINA_STRIPE_PLUGIN_URL . 'assets/css/stripe.css');
		wp_enqueue_script('st-vina-checkout-stripe-js', 'https://checkout.stripe.com/checkout.js', array(), false, true);
		wp_enqueue_script('st-vinad-stripe-js', ST_VINA_STRIPE_PLUGIN_URL.'assets/js/stripe.js', array(), false, true);
		wp_enqueue_style('st-vina-stripe-css');
    }
    public function loadTemplate($name, $data = null)
    {
        if (is_array($data))
            extract($data);
        $template = ST_VINA_STRIPE_PLUGIN_PATH . 'views/' . $name . '.php';
        if (is_file($template)) {
            $templateCustom = locate_template(ST_VINA_STRIPE_FOLDER_PLUGIN . '/views/' . $name . '.php');
            if (is_file($templateCustom)) {
                $template = $templateCustom;
            }
            ob_start();
            require($template);
            $html = ob_get_clean();
            return $html;
        }
    }
    public function _pluginLoader()
    {
        require_once(ST_VINA_STRIPE_PLUGIN_PATH . 'inc/stripe.php');
		if ( !class_exists( 'Stripe\Stripe' ) ) {
			require_once(ST_VINA_STRIPE_PLUGIN_PATH . 'vendor/stripe/stripe-php/init.php');
		}
    }
    public static function get_inst()
    {
        static $instance;
        if (is_null($instance)) {
            $instance = new self();
        }
        return $instance;
    }
}
ST_VinaStripe::get_inst();