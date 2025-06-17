<?php
/**
 * Created by PhpStorm.
 * User: Dungdt
 * Date: 12/15/2015
 * Time: 3:19 PM
 */

if (!class_exists('ST_Vina_Stripe_Payment_Gateway') && class_exists('STAbstactPaymentGateway')) {
	class ST_Vina_Stripe_Payment_Gateway extends STAbstactPaymentGateway
	{
	    public static $_ints;
		private $default_status = TRUE;

		private $_gatewayObject = null;

		private $_gateway_id = 'vina_stripe';

		function __construct()
		{
			add_filter('st_payment_gateway_vina_stripe', array($this, 'get_name'));

			add_action('admin_notices', array($this, '_add_notices'));
			add_action('admin_init', array($this, '_dismis_notice'));
            add_action('wp_enqueue_scripts', array($this, '_load_scripts'));

		}

		function _load_scripts(){
			wp_register_style('vina-stripe-css', ST_VINA_STRIPE_PLUGIN_URL . 'assets/css/stripe.css');
			wp_register_script('st-library-stripe-js', 'https://js.stripe.com/v3/', array(), null, true);
			wp_register_script('st-checkout-stripe-js', 'https://checkout.stripe.com/checkout.js', ['jquery'], null, true);
			wp_register_script('st-stripe-js',ST_VINA_STRIPE_PLUGIN_URL.'assets/js/stripe.js', ['jquery'], null, true);
			wp_localize_script( 'jquery', 'st_plugin_params', [
	            'ajax_url' => admin_url( 'admin-ajax.php' ),
	            'home_url' => home_url('/'),
	            '_s'       => wp_create_nonce( '_wpnonce_security' ),
	        ] );
			if(st()->get_option('pm_gway_vina_stripe_enable'))
            {

                if (wp_script_is('stripe-api', 'enqueued' )) {

            	} else {
            		wp_enqueue_script('st-library-stripe-js');
            	}
            	wp_enqueue_style('vina-stripe-css');
                wp_enqueue_script('st-checkout-stripe-js');
                wp_enqueue_script('st-stripe-js');
            }
		}

		function _dismis_notice()
		{
			if (STInput::get('st_dismiss_stripe_notice')) {
				update_option('st_dismiss_stripe_notice', 1);
			}

		}

		function _add_notices()
		{
			if (get_option('st_dismiss_stripe_notice')) return;

			if (class_exists('STTravelCode')) {
				if (isset(STTravelCode::$plugins_data['Version'])) {
					$version = STTravelCode::$plugins_data['Version'];
					if (version_compare('1.3.2', $version, '>')) {
						$url = admin_url('plugin-install.php?tab=plugin-information&plugin=traveler-code&TB_iframe=true&width=753&height=350');
						?>
						<div class="error settings-error notice is-dismissible">
							<p class=""><strong><?php _e('Traveler Notice:', 'vina-stripe') ?></strong></p>

							<p>
								<?php printf(__('<strong>Traveler Stripe</strong> require %s version %s or above. Your current is %s', 'vina-stripe'), '<strong><em>' . __('Traveler Code', 'vina-stripe') . '</em></strong>', '<strong>1.3.2</strong>', '<strong>' . $version . '</strong>'); ?>
							</p>

							<p>
								<a href="http://shinetheme.com/demosd/documentation/how-to-update-the-theme-2/"
								   target="_blank"><?php _e('Learn how to update it', 'vina-stripe') ?></a>
								|
								<a href="<?php echo admin_url('index.php?st_dismiss_stripe_notice=1') ?>"
								   class="dismiss-notice"
								   target="_parent"><?php _e('Dismiss this notice', 'vina-stripe') ?></a>
							</p>
							<button type="button" class="notice-dismiss"><span
									class="screen-reader-text"><?php _e('Dismiss this notice', 'vina-stripe') ?>.</span>
							</button>
						</div>
						<?php
					}
				}
			}
		}

		function get_option_fields()
		{
			return array(
				array(
					'id'        => 'vina_stripe_return_url',
					'label'     => __('Return Url (Required)', 'vina-stripe'),
					'type'      => 'text',
					'section'   => 'option_pmgateway',
					'desc'      => __('You must enter the return url', 'vina-stripe'),
				),
                array(
                    'id'        => 'vina_stripe_publish_key',
                    'label'     => __('Publishable Key', 'vina-stripe'),
                    'type'      => 'text',
                    'section'   => 'option_pmgateway',
                    'desc'      => __('Your Stripe Publishable Key', 'vina-stripe'),
                    'condition' => 'pm_gway_vina_stripe_enable:is(on)'
                ),
				array(
					'id'        => 'vina_stripe_secret_key',
					'label'     => __('Secret Key', 'vina-stripe'),
					'type'      => 'text',
					'section'   => 'option_pmgateway',
					'desc'      => __('Your Stripe Secret Key', 'vina-stripe'),
					'condition' => 'pm_gway_vina_stripe_enable:is(on)'
				),
				array(
					'id'        => 'vina_stripe_enable_sandbox',
					'label'     => __('Enable Sandbox Mode', 'vina-stripe'),
					'type'      => 'on-off',
					'section'   => 'option_pmgateway',
					'std'       => 'on',
					'desc'      => __('Allow you to enable sandbox mode for testing', 'vina-stripe'),
					'condition' => 'pm_gway_vina_stripe_enable:is(on)'
				),
                array(
                    'id'        => 'vina_stripe_test_publish_key',
                    'label'     => __('Test Publishable Key', 'vina-stripe'),
                    'type'      => 'text',
                    'section'   => 'option_pmgateway',
                    'desc'      => __('Your Stripe Test Publishable Key for Sandbox mode', 'vina-stripe'),
                    'condition' => 'pm_gway_vina_stripe_enable:is(on),vina_stripe_enable_sandbox:is(on),vina_stripe_enable_sandbox:is(on)'
                ),
				array(
					'id'        => 'vina_stripe_test_secret_key',
					'label'     => __('Test Secret Key', 'vina-stripe'),
					'type'      => 'text',
					'section'   => 'option_pmgateway',
					'desc'      => __('Your Stripe Test Secret Key for Sandbox mode', 'vina-stripe'),
					'condition' => 'pm_gway_vina_stripe_enable:is(on),vina_stripe_enable_sandbox:is(on)'
				),

			);
		}

		function stop_change_order_status()
		{
			return true;
		}

		function _pre_checkout_validate()
		{
			return true;
		}

		function do_checkout($order_id)
		{

			$pp = $this->get_authorize_url($order_id);

			if (isset($pp['redirect_form']) and $pp['redirect_form'])
				$pp_link = $pp['redirect_form'];

			do_action('st_before_redirect_stripe');
			if ($pp['status']) {
				return $pp;
			}else{
				return array(
					'status'  => FALSE,
					'message' => isset($pp['message']) ? $pp['message'] : FALSE,
					'data'    => isset($pp['data']) ? $pp['data'] : FALSE,
					'error_step'=>'after_get_authorize_url',
					'redirect' => STCart::get_success_link($order_id),
					'raw_response'=>$pp
				);
			}
		}

		function package_do_checkout($order_id){
            $stripe_secret_key = ST_VinaStripe::get_inst()->vina_stripe_get_secret_key();

            $order   = STAdminPackages::get_inst()->get( '*', $order_id );
            $currency = TravelHelper::get_current_currency( 'name' );
            $infor_partner = $order->partner_info;
            $cart_infor = maybe_unserialize($infor_partner);
			$total=round( (float)$order->package_price, 2 );
			$vina_stripe_return_url = !empty(st()->get_option('vina_stripe_return_url')) ? st()->get_option('vina_stripe_return_url') : get_home_url();
			$vina_stripe_payment_method_id = STInput::post('vina_stripe_payment_method_id');

			\Stripe\Stripe::setApiKey($stripe_secret_key);
			header('Content-Type: application/json');
			$json_str = file_get_contents('php://input');
			$payment_intent = null;

			try {
				if (isset($vina_stripe_payment_method_id)) {
				  # Create the PaymentIntent
				  	$customer = \Stripe\Customer::create();
				   	$intent = \Stripe\PaymentIntent::create([
					    'payment_method' => $vina_stripe_payment_method_id,
					    'amount' => $total*100,
					    'currency'      => $currency,
	                    'description'   => __('Full name:','vina_stripe').' ' .$cart_infor['firstname'].' '.$cart_infor['lastname'].' '.__('Member Package','vina_stripe').":".$order->package_name,
					    'confirmation_method' => 'manual',
					    'use_stripe_sdk' => true,
						'setup_future_usage'=>'off_session',
					    'confirm' => true,
						'return_url' => esc_url($vina_stripe_return_url),
				   ]);
				}
				if (isset($payment_intent_id)) {
					$intent = \Stripe\PaymentIntent::retrieve(
						$payment_intent_id
					);

					$intent->confirm();
				}
				return $this->generatePaymentResponsePackage($intent,$order_id);
			} catch (\Stripe\Error\Base $e) {
				# Display error on client
				return ([
				  'error' => $e->getMessage(),
				  'message' => $e->getMessage(),
				  'status' => false,
				]);
			}


        }

		function get_authorize_url($order_id)
		{
			$stripe_secret_key = ST_VinaStripe::get_inst()->vina_stripe_get_secret_key();
			$total = get_post_meta($order_id, 'total_price', TRUE);
            $st_first_name = get_post_meta($order_id, 'st_first_name', TRUE);
            $st_last_name = get_post_meta($order_id, 'st_last_name', TRUE);
            $st_address = get_post_meta($order_id, 'st_address', TRUE);
            $st_zip_code = get_post_meta($order_id, 'st_zip_code', TRUE);
            $st_city = get_post_meta($order_id, 'st_city', TRUE);
            $st_country = get_post_meta($order_id, 'st_country', TRUE);
            $st_cart_info = get_post_meta($order_id, 'st_cart_info', TRUE);
            $st_booking_id = get_post_meta($order_id, 'st_booking_id', TRUE);
            $cart_infor = maybe_unserialize($st_cart_info);
            $total=round((float)$total,2);
			$vina_stripe_payment_method_id = STInput::post('vina_stripe_payment_method_id');
			\Stripe\Stripe::setApiKey($stripe_secret_key);
			header('Content-Type: application/json');
			$json_str = file_get_contents('php://input');
			$intent = null;

			$vina_stripe_return_url = !empty(st()->get_option('vina_stripe_return_url')) ? st()->get_option('vina_stripe_return_url') : get_home_url();
			try {
				if (isset($vina_stripe_payment_method_id)) {
				  # Create the PaymentIntent

				  $customer = \Stripe\Customer::create(
					[
						'name' => $st_first_name.' '.$st_last_name,
						'address' => [
						  'line1' => $st_address,
						  'postal_code' => $st_zip_code,
						  'city' => $st_city,
						  'state' => $st_city,
						  'country' =>$st_country,
						],
					  ]
				  );
				   $intent = \Stripe\PaymentIntent::create([
					    'payment_method' => $vina_stripe_payment_method_id,
					    'amount' => $total*100,
					    'currency'      => TravelHelper::get_current_currency('name'),
	                    'description'   => __('Full name:','vina_stripe').' ' .$st_first_name.' '.$st_last_name.' '.__('Name service:','vina_stripe').' '.esc_html($cart_infor[$st_booking_id]['title']),
					    'confirmation_method' => 'manual',
					    'use_stripe_sdk' => true,
					    'confirm' => true,
						'return_url' => esc_url($vina_stripe_return_url),
				   ]);
				}
				if (!empty($payment_intent_id)) {
					$intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);

					$intent->confirm();
				}
				return $this->generatePaymentResponse($intent,$order_id);
			} catch (\Stripe\Error\Base $e) {
				# Display error on client
				return ([
				  'error' => $e->getMessage(),
				  'message' => $e->getMessage(),
				  'status' => false,
				]);
			}

			//ST_VinaStripe::get_inst()->update_PaymentIntent($order_id);
			// return array(
			// 	'status'   => true,
			// 	'redirect' => STCart::get_success_link()
			// );
		}
		public function generatePaymentResponse($intent,$order_id) {
			# Note that if your API version is before 2019-02-11, 'requires_source_action'
			# appears as 'requires_source_action'.
			if ($intent->status == 'succeeded') {
				update_post_meta( $order_id, 'status', 'complete' );
				global $wpdb;
				$table = $wpdb->prefix . 'st_order_item_meta';
				$data  = [
					'status' => 'complete'
				];
				$where = [
					'order_item_id' => $order_id
				];
				$rs    = $wpdb->update( $table, $data, $where );

				$get_order = $this->st_get_order_by_order_item_id($order_id);
				if ( empty($get_order) ) {
					return;
				}
				global $wpdb;
				$post_type = $get_order['st_booking_post_type'];
				$check_in_timestamp = $get_order['check_in_timestamp'];
				$check_out_timestamp = $get_order['check_out_timestamp'];
				$post_id = $get_order['st_booking_id'];
				$booked = 1;

				switch ($post_type) {
                    case 'st_tours':
                    case 'st_activity':
                        $table_avai = $wpdb->prefix . 'st_tour_availability';
                        if ($post_type == 'st_activity') {
                            $table_avai = $wpdb->prefix . 'st_activity_availability';
                        }
                        $adult_number = $get_order['adult_number'];
                        $child_number = $get_order['child_number'];
                        $infant_number = $get_order['infant_number'];
                        $number_had_boooked = $adult_number + $child_number + $infant_number;
                        $sql = $wpdb->prepare("UPDATE {$table_avai} SET number_booked = IFNULL(number_booked, 0) + %d WHERE post_id = %d AND check_in = %s", $number_had_boooked, $post_id, $check_in_timestamp);
                        $wpdb->query($sql);
                    	break;
					case 'st_rental':
						$table_st_rental_avai = $wpdb->prefix . 'st_rental_availability';
						$sql = $wpdb->prepare("UPDATE {$table_st_rental_avai} SET number_booked = IFNULL(number_booked, 0) + %d WHERE post_id = %d AND check_in >= %d AND check_out <= %d", $booked, $post_id, $check_in_timestamp , $check_out_timestamp);
						$wpdb->query($sql);
						break;
					case 'st_hotel':
					case 'hotel_room':
						$table_st_room_avai = $wpdb->prefix . 'st_room_availability';
						$sql = $wpdb->prepare("UPDATE {$table_st_room_avai} SET number_booked = IFNULL(number_booked, 0) + %d WHERE post_id = %d AND check_in >= %d AND check_out <= %d", $booked, $post_id, $check_in_timestamp , $check_out_timestamp);
						$wpdb->query($sql);
						break;
                }


				return array(
					'status'   => true,
					'redirect_form' => STCart::get_success_link($order_id),
					'success' => true,
					);
			} else
			if ($intent->status == 'requires_action' &&
			    $intent->next_action->type == 'use_stripe_sdk') {
			    # Tell the client to handle the action
				return array(
					'status'   => true,
					'redirect_form' => STCart::get_success_link($order_id),
					'requires_source_action' => true,
					'payment_intent_client_secret' => $intent->client_secret
					);
			}else
			if ($intent->status == 'requires_source_action' &&
			    $intent->next_action->type == 'use_stripe_sdk') {
			    # Tell the client to handle the action
				return array(
					'status'   => true,
					'redirect_form' => STCart::get_success_link($order_id),
					'requires_source_action' => true,
					'payment_intent_client_secret' => $intent->client_secret
					);
			}
			else {
				# Invalid status
				http_response_code(500);
				return array(
					'status'   => false,
					'error' => __('Invalid PaymentIntent status',ST_TEXTDOMAIN),
				);
			}
		}

		public function st_get_order_by_order_item_id( $order_item_id ) {
			global $wpdb;
			$querystr  = 'SELECT * FROM  ' . $wpdb->prefix . 'st_order_item_meta WHERE 1=1 AND order_item_id = ' . $order_item_id;
			$pageposts = $wpdb->get_row( $querystr, ARRAY_A );
			return $pageposts;
		}

		public function generatePaymentResponsePackage($intent,$order_id) {
			# Note that if your API version is before 2019-02-11, 'requires_source_action'
			# appears as 'requires_source_action'.
			if ($intent->status == 'succeeded') {
				return array(
					'status'   => true,
					'redirect_form' => STAdminPackages::get_inst()->get_return_url( $order_id ),
					'redirect' => STAdminPackages::get_inst()->get_return_url( $order_id ),
					'success' => true,
					'order_id' => $order_id,
					);
			}
			if ($intent->status == 'requires_action' &&
			    $intent->next_action->type == 'use_stripe_sdk') {
			    # Tell the client to handle the action
				return array(
					'status'   => true,
					'redirect_form' => STAdminPackages::get_inst()->get_return_url( $order_id ),
					'redirect' => STAdminPackages::get_inst()->get_return_url( $order_id ),
					'requires_source_action' => true,
					'payment_intent_client_secret' => $intent->client_secret,
					'order_id' => $order_id,
					);
			} else if ($intent->status == 'requires_source_action' &&
			    $intent->next_action->type == 'use_stripe_sdk') {
			    # Tell the client to handle the action
				return array(
					'status'   => true,
					'redirect_form' => STAdminPackages::get_inst()->get_return_url( $order_id ),
					'redirect' => STAdminPackages::get_inst()->get_return_url( $order_id ),
					'requires_source_action' => true,
					'payment_intent_client_secret' => $intent->client_secret,
					'order_id' => $order_id,
					);
			}else {
				# Invalid status
				http_response_code(500);
				return array(
					'status'   => false,
					'error' => __('Invalid PaymentIntent status',ST_TEXTDOMAIN),
					);

			}

		}


		function check_complete_purchase($order_id)
		{
			return apply_filters('st_stripe_complete_purchase',false);
		}

		function package_completed_checkout($order_id){
            if (!class_exists('STAdminPackages')) {
                return ['status' => false];
            }

            $status = STInput::get('status');
            if ( TravelHelper::st_compare_encrypt( (int) $order_id . 'st1', $status ) ) {

                return true;
            }
        }

		function html()
		{
		    echo ST_VinaStripe::get_inst()->loadTemplate('stripe_creative');

		}

		function get_name()
		{
			return __('Credit Card', 'vina-stripe');
		}

		function get_default_status()
		{
			return $this->default_status;
		}

		function is_available($item_id = FALSE)
		{
			if (st()->get_option('pm_gway_vina_stripe_enable') == 'off') {
				return FALSE;
			}

			$stripe_secret_key = st()->get_option('vina_stripe_secret_key');
			$stripe_enable_sandbox = st()->get_option('vina_stripe_enable_sandbox');
			$stripe_test_secret_key = st()->get_option('vina_stripe_test_secret_key');

			if ($stripe_enable_sandbox == 'on') {
				if (!$stripe_test_secret_key) return FALSE;

			} elseif (!$stripe_secret_key) {
				return FALSE;
			}

			if ($item_id) {
				$meta = get_post_meta($item_id, 'is_meta_payment_gateway_vina_stripe', TRUE);
				if ($meta == 'off') {
					return FALSE;
				}
			}

			return TRUE;
		}

		function getGatewayId()
		{
			return $this->_gateway_id;
		}

		function is_check_complete_required()
		{
			return true;
		}

		function get_logo()
		{
			return ST_VINA_STRIPE_PLUGIN_URL. 'assets/img/vinasp-logo.png';
		}

        static function instance() {
            if ( ! self::$_ints ) {
                self::$_ints = new self();
            }

            return self::$_ints;
        }

        static function add_payment( $payment ) {
            $payment['vina_stripe'] = self::instance();

            return $payment;
        }
	}
    add_filter( 'st_payment_gateways', array( 'ST_Vina_Stripe_Payment_Gateway', 'add_payment' ) );
}