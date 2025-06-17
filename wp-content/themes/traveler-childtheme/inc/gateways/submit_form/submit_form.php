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
if (!class_exists('STGatewaySubmitform')) {
    class STGatewaySubmitform extends STAbstactPaymentGateway
    {

        private $_gateway_id = 'st_submit_form';

        function __construct()
        {
            add_filter('st_payment_gateway_st_submit_form_name', array($this, 'get_name'));
        }

        function html()
        {
            echo st()->load_template('gateways/submit_form');
        }

        /**
         * Perform the checkout process and update reservation availability.
         *
         * @update 1.1.1
         */
        function do_checkout($order_id)
        {
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
                // For other conditions, retain the original logic
                if (st()->get_option('enable_email_confirm_for_customer', 'on') !== 'off') {
                    $status_order = 'incomplete';
                }
            }

            // Update the order status
            update_post_meta($order_id, 'status', $status_order);
            do_action('st_booking_change_status', $status_order, $order_id, 'normal_booking');
            $order_token = get_post_meta($order_id, 'order_token_code', TRUE);

            // Destroy cart on success
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

            // Directly execute the SQL query and import_event logic to update reservations
            $this->update_reservation_availability();

            return array(
                'status' => TRUE,
            );
        }

        /**
         * Update reservation availability based on today's date and status 'complete'.
         */
        function update_reservation_availability()
        {
            global $wpdb;

            $table = $wpdb->prefix . 'st_order_item_meta';

            // Calculate today's timestamp at 12:00 AM (midnight)
            $today_midnight = strtotime('today midnight');

            // Modify the SQL query to include a condition for status being 'complete' and check_in_timestamp from today forward
            $sql = $wpdb->prepare(
                "SELECT *
                FROM {$table}
                WHERE st_booking_post_type = 'st_rental'
                AND check_in_timestamp >= %d
                AND status = 'complete'",
                $today_midnight
            );

            $results = $wpdb->get_results($sql, ARRAY_A);

            foreach ($results as $result) {
                $post_id = $result['st_booking_id'];
                $start = $result['check_in_timestamp'];
                $end = $result['check_out_timestamp'];
                $this->import_event($post_id, $start, $end);
            }
        }

        /**
         * Import event data into the rental availability table.
         *
         * @param int $post_id
         * @param int $start
         * @param int $end
         */
        private function import_event($post_id, $start, $end)
        {
            global $wpdb;

            $table_st_rental_avai = $wpdb->prefix . 'st_rental_availability';

            for ($i = $start; $i <= $end; $i = strtotime('+1 day', $i)) {
                $where_avai = [
                    'post_id'  => $post_id,
                    'check_in' => $i,
                ];

                // Update the data array to include the number_booked field
                if ($i == $start) {
                    $data_avai = [
                        'priority' => 1,
                        'number_booked' => 1, // Set number_booked to 1
                    ];
                } elseif ($i == $end) {
                    $data_avai = [
                        'priority' => 2,
                        'number_booked' => 1, // Set number_booked to 1
                    ];
                } else {
                    $data_avai = [
                        'priority' => 0,
                        'number_booked' => 1, // Set number_booked to 1
                    ];
                }

                // Update the table with the new data
                $wpdb->update($table_st_rental_avai, $data_avai, $where_avai);
            }
        }

        function package_do_checkout($order_id)
        {
            if (!class_exists('STAdminPackages')) {
                return ['status' => TravelHelper::st_encrypt($order_id . 'st0'), 'message' => __('This function is off', 'traveler')];
            }
            return [
                'status'       => TravelHelper::st_encrypt($order_id . 'st1'),
                'redirect_url' => STAdminPackages::get_inst()->get_return_url($order_id),
            ];
        }

        function package_completed_checkout($order_id)
        {
            return true;
        }

        function check_complete_purchase($order_id)
        {
            // Additional logic can be added here if needed
        }

        function stop_change_order_status()
        {
            return true;
        }

        function get_name()
        {
            return __('Cash', 'traveler');
        }

        function is_available($item_id = FALSE)
        {
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
            if (empty(trim($logo_submit_form))) {
                $logo_submit_form = ST_TRAVELER_URI . '/img/gateway/nm-logo.png';
            }
            return $logo_submit_form;
        }

        function is_check_complete_required()
        {
            return false;
        }

        function getGatewayId()
        {
            return $this->_gateway_id;
        }
    }
}
?>
