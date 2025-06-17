<?php
/**
 * @package WordPress
 * @subpackage Traveler
 * @since 1.0
 *
 * Class STGatewaySubmitform
 *
 * Created by ShineTheme
 *
 */
if(!class_exists('STGatewaySubmitform'))
{
    class STGatewaySubmitform extends STAbstactPaymentGateway
	{

		private $_gateway_id='st_submit_form';

		function __construct()
		{
			add_filter('st_payment_gateway_st_submit_form_name', array($this, 'get_name'));

		}

		function html()
		{
			echo st()->load_template('gateways/submit_form');
		}

		/**
		 *
		 *
		 * @update 1.1.1
		 * */
		function do_checkout($order_id) {
			// Default status is 'pending'
			$status_order = 'pending';

			// Check if the current user has the 'administrator' or 'partner' role
			$user = wp_get_current_user();
			$allowed_roles = ['administrator', 'partner'];
			$is_allowed_role = array_intersect($allowed_roles, $user->roles) ? true : false;

			// Check if the gateway is 'Bank Transfer'
			$is_bank_transfer = $this->_gateway_id === 'st_submit_form';

			// If the user is an admin or partner and using 'Bank Transfer', set status to 'completed'
			if ($is_allowed_role && $is_bank_transfer) {
				$status_order = 'complete';
			} else {
				// For other conditions, you might want to retain the original logic
				if (st()->get_option('enable_email_confirm_for_customer', 'on') !== 'off') {
					$status_order = 'incomplete';
				}
			}

			// Update the order status
			update_post_meta($order_id, 'status', $status_order);
			do_action('st_booking_change_status', $status_order, $order_id, 'normal_booking');
			$order_token = get_post_meta($order_id, 'order_token_code', TRUE);

			//Destroy cart on success
			STCart::destroy_cart();

			if ($order_token) {
				$array = array(
					'order_token_code' => $order_token
				);
			} else {
				$array = array(
					'order_code' => $order_id,

				);
			}

			return array(
				'status'   => TRUE,
			);

		}

		function package_do_checkout($order_id){
            if (!class_exists('STAdminPackages')) {
                return ['status' => TravelHelper::st_encrypt($order_id . 'st0'), 'message' => __('This function is off', 'traveler')];
            }
            return [
                'status'       => TravelHelper::st_encrypt( $order_id . 'st1' ),
                'redirect_url' => STAdminPackages::get_inst()->get_return_url( $order_id ),
            ];
        }

        function package_completed_checkout($order_id){
            return true;
        }

		function check_complete_purchase($order_id){

		}

		/**
		 *
		 * @return bool
		 */
		function stop_change_order_status()
		{
			return true;
		}


		function get_name()
		{
			return __('Cash', 'traveler');
		}

		/**
		 * Check payment method for all items or specific is enable
		 *
		 *
		 * @update 1.1.7
		 * @param bool $item_id
		 * @return bool
		 */
		function is_available($item_id = FALSE) {
			$user = wp_get_current_user();
			$allowed_roles = ['administrator', 'partner']; // Define allowed roles here

			$result = FALSE;
			if (array_intersect($allowed_roles, $user->roles)) {
				// If user has any of the allowed roles, make the gateway available
				$result = TRUE;
			} else {
				// Check if the gateway is enabled globally and not disabled for the specific item
				if (st()->get_option('pm_gway_st_submit_form_enable') == 'on') {
					$result = TRUE;
					if ($item_id) {
						$meta = get_post_meta($item_id, 'is_meta_payment_gateway_st_submit_form', TRUE);
						if ($meta == 'off') {
							$result = FALSE;
						}
					}
				}
			}

			return $result;
		}

		function _pre_checkout_validate()
		{
			return TRUE;
		}

		function get_option_fields()
		{
            return array(
                array(
                    'id' => 'submit_form_logo',
                    'label' => __('Logo', 'traveler'),
                    'desc' => __('To change logo', 'traveler'),
                    'type' => 'upload',
                    'section' => 'option_pmgateway',
                    'condition' => 'pm_gway_' . $this->_gateway_id . '_enable:is(on)'
                ),
                array(
                    'id' => 'submit_form_desc',
                    'label' => __('Description', 'traveler'),
                    'type' => 'textarea',
                    'section' => 'option_pmgateway',
                    'condition' => 'pm_gway_' . $this->_gateway_id . '_enable:is(on)'
                ),
            );
		}

		function get_default_status()
		{
			return TRUE;
		}

		function get_logo()
		{
		    $logo_submit_form = st()->get_option('submit_form_logo', ST_TRAVELER_URI . '/img/gateway/nm-logo.png');
		    if(empty(trim($logo_submit_form))){
		    	$logo_submit_form = ST_TRAVELER_URI . '/img/gateway/nm-logo.png';
		    }
			return $logo_submit_form;
		}

		function is_check_complete_required(){
			return false;
		}

		function getGatewayId()
		{
			return $this->_gateway_id;
		}
    }
}
