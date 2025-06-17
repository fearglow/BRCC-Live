<?php
/**
 * Plugin Name: Custom Booking Calendar
 * Description: Custom plugin to display bookings from custom table on a calendar from campsite bookings.
 * Version: 1.0
 * Author: Ryan Pittman
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to check if the current user has the allowed role
function user_has_allowed_role() {
    $allowed_roles = array('super_admin', 'administrator', 'partner', 'host');
    foreach ($allowed_roles as $role) {
        if (current_user_can($role)) {
            return true;
        }
    }
    return false;
}

function custom_booking_calendar_enqueue_scripts() {
    global $wpdb; // Ensure you have access to the database

    // First, fetch the bookings to determine the earliest date
    $bookings = fetch_bookings();
    $earliestDate = !empty($bookings) ? $bookings[0]['start'] : date('Y-m-d');

    wp_enqueue_script('jquery');
    wp_enqueue_script('moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js', array('jquery'), '2.29.1', true);
    wp_enqueue_script('fullcalendar', "https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js", array('jquery', 'moment'), '3.10.2', true);
    wp_enqueue_style('custom-booking-styles', plugins_url('/css/custom-booking-styles.css', __FILE__), array(), '1.0', 'all');
    wp_enqueue_style('fullcalendar-css', "https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css", array(), '3.10.2', 'all');

    // Enqueue a custom script to initialize the calendar
    wp_enqueue_script('custom-booking-init', plugins_url('/js/init-calendar.js', __FILE__), array('jquery', 'fullcalendar'), '1.0', true);

    // Now localize the script with booking data, ajax URL, and nonce
    wp_localize_script('custom-booking-init', 'bookingData', array(
        'bookings' => $bookings,
        'earliestBookingDate' => $earliestDate,
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mark_as_paid_nonce'),
        'fetch_updated_bookings_nonce' => wp_create_nonce('fetch_updated_bookings_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'custom_booking_calendar_enqueue_scripts');

function fetch_bookings() {
    global $wpdb;

    $query = "
        SELECT 
            oim.user_id, oim.order_item_id, oim.check_in, oim.check_out, oim.st_booking_id, oim.total_order,
            p.post_title AS post_name, COALESCE(pm1.meta_value, um.meta_value) as first_name, 
            COALESCE(pm2.meta_value, um2.meta_value) as last_name, oim.adult_number, oim.child_number, 
            oim.infant_number, oim.status, oim.cash_paid,
            pm.meta_value as payment_method_name,
            pm_email.meta_value as email,
            pm_phone.meta_value as phone,
            pm_equipment_type.meta_value as equipment_type,
            pm_length_ft.meta_value as length_ft,
            pm_slide_outs.meta_value as slide_outs,
            pm_guest_name.meta_value as guest_name,
            pm_guest_title.meta_value as guest_title,
			pm_booking_post_type.meta_value as booking_post_type,
			pm_starttime.meta_value as starttime
        FROM {$wpdb->prefix}st_order_item_meta oim
        INNER JOIN {$wpdb->users} u ON oim.user_id = u.ID
        INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'first_name'
        INNER JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'last_name'
        INNER JOIN {$wpdb->posts} p ON oim.st_booking_id = p.ID
        LEFT JOIN {$wpdb->postmeta} pm ON oim.order_item_id = pm.post_id AND pm.meta_key = 'payment_method_name'      
        LEFT JOIN {$wpdb->postmeta} pm1 ON oim.order_item_id = pm1.post_id AND pm1.meta_key = 'st_first_name'
        LEFT JOIN {$wpdb->postmeta} pm2 ON oim.order_item_id = pm2.post_id AND pm2.meta_key = 'st_last_name'
        LEFT JOIN {$wpdb->postmeta} pm_email ON oim.order_item_id = pm_email.post_id AND pm_email.meta_key = 'st_email'
        LEFT JOIN {$wpdb->postmeta} pm_phone ON oim.order_item_id = pm_phone.post_id AND pm_phone.meta_key = 'st_phone'
        LEFT JOIN {$wpdb->postmeta} pm_equipment_type ON oim.order_item_id = pm_equipment_type.post_id AND pm_equipment_type.meta_key = 'equipment_type'
        LEFT JOIN {$wpdb->postmeta} pm_length_ft ON oim.order_item_id = pm_length_ft.post_id AND pm_length_ft.meta_key = 'length_ft'
        LEFT JOIN {$wpdb->postmeta} pm_slide_outs ON oim.order_item_id = pm_slide_outs.post_id AND pm_slide_outs.meta_key = 'slide_outs'
        LEFT JOIN {$wpdb->postmeta} pm_guest_name ON oim.order_item_id = pm_guest_name.post_id AND pm_guest_name.meta_key = 'guest_name'
        LEFT JOIN {$wpdb->postmeta} pm_guest_title ON oim.order_item_id = pm_guest_title.post_id AND pm_guest_title.meta_key = 'guest_title'
		LEFT JOIN {$wpdb->postmeta} pm_booking_post_type ON oim.order_item_id = pm_booking_post_type.post_id AND pm_booking_post_type.meta_key = 'st_booking_post_type'
		LEFT JOIN {$wpdb->postmeta} pm_starttime ON oim.order_item_id = pm_starttime.post_id AND pm_starttime.meta_key = 'starttime'
        WHERE oim.status = 'complete'
    ";

    $bookings = $wpdb->get_results($query, OBJECT);
	

    $formatted_bookings = array();
    foreach ($bookings as $booking) {
        $isCashPayment = strtolower(trim($booking->payment_method_name)) === 'cash';
        $booking_title = "Site: " . explode(':', $booking->post_name)[0] . "\nCustomer: " . $booking->first_name . ' ' . $booking->last_name;
        $site_parts = explode(':', $booking->post_name);
        $site_name_before_colon = trim($site_parts[0]);
        $adjusted_checkout_date = date('Y-m-d', strtotime($booking->check_out . ' +1 day'));
        $status = $isCashPayment && !$booking->cash_paid ? 'Cash Payment Due' : ($booking->cash_paid ? 'Paid Cash' : $booking->status);

        // Format equipment_type
        $formatted_equipment_type = $booking->equipment_type ? ucwords(str_replace('_', ' ', $booking->equipment_type)) : null;

        // Decode guest_name and guest_title arrays
        $guest_names = maybe_unserialize($booking->guest_name);
        $guest_titles = maybe_unserialize($booking->guest_title);

        // Combine guest names and weights
        $guest_details = [];
        if (is_array($guest_names) && is_array($guest_titles)) {
            foreach ($guest_names as $index => $name) {
                $weight = isset($guest_titles[$index]) ? $guest_titles[$index] . ' lbs' : '';
                $guest_details[] = $name . ' (' . $weight . ')';
            }
        }

        $formatted_booking = array(
            'id' => $booking->order_item_id,
            'title' => $booking_title,
            'site' => $site_name_before_colon,
            'start' => $booking->check_in,
            'end' => $adjusted_checkout_date,
            'display_end' => $booking->check_out,
            'adult_number' => $booking->adult_number,
            'child_number' => $booking->child_number,
            'infant_number' => $booking->infant_number,
            'status' => $status,
            'customer' => $booking->first_name . ' ' . $booking->last_name,
            'stay' => $booking->check_in . ' - ' . $booking->check_out,
            'isCashPayment' => $isCashPayment,
            'payment_method_name' => $booking->payment_method_name,
            'st_booking_id' => $booking->st_booking_id,
            'cash_paid' => $booking->cash_paid,
            'email' => $booking->email,
            'phone' => $booking->phone,
            'total_order' => $booking->total_order,
            'guest_details' => implode(', ', $guest_details),
			'booking_post_type' => $booking->booking_post_type,
			'starttime' => $booking->starttime,
        );

        // Conditionally add new fields if they have values
        if ($formatted_equipment_type) {
            $formatted_booking['equipment_type'] = $formatted_equipment_type;
        }
        if ($booking->length_ft) {
            $formatted_booking['length_ft'] = $booking->length_ft;
        }
        if ($booking->slide_outs) {
            $formatted_booking['slide_outs'] = $booking->slide_outs;
        }

        $formatted_bookings[] = $formatted_booking;
    }

    return $formatted_bookings;
}

add_action('wp_ajax_fetch_updated_bookings', 'fetch_updated_bookings_callback');





function fetch_updated_bookings_callback() {
    // Assuming fetch_bookings() returns an array of bookings
    $updated_bookings = fetch_bookings();

    // Send the updated bookings data back as JSON
    wp_send_json_success($updated_bookings);

    wp_die(); // Terminate AJAX execution
}

add_action('wp_ajax_mark_as_paid', 'mark_as_paid_callback');

function mark_as_paid_callback() {
    global $wpdb; 
    
    // Verify nonce for security
    check_ajax_referer('mark_as_paid_nonce', 'nonce');

    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
    // Prevent function from proceeding if booking ID is not provided
    if ($booking_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid booking ID.'));
        wp_die();
    }

    // Update the database to mark the booking as paid in cash
    $updated = $wpdb->update(
        "{$wpdb->prefix}st_order_item_meta",
        array('cash_paid' => 1), // Set cash_paid to true
        array('order_item_id' => $booking_id), 
        array('%d'), // Format of the value
        array('%d')  // Format of the where clause value
    );

    // Immediately check for any database errors
    if (!empty($wpdb->last_error)) {
        wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
        wp_die();
    }

    // Evaluate the result of the update operation
    if ($updated) {
        wp_send_json_success(array('message' => 'Booking marked as paid.'));
    } else {
        // Consider additional logic here to handle the case where $updated is 0 (no rows updated)
        // This might occur if the row was already marked as paid, or if the booking_id does not exist
        wp_send_json_error(array('message' => 'Failed to mark the booking as paid or booking already marked.'));
    }

    wp_die();
}

function custom_booking_calendar_shortcode() {
    if (!user_has_allowed_role()) {
        return 'You do not have permission to view this calendar.';
    }
    return '<div id="calendar"></div>';
}
add_shortcode('custom_booking_calendar', 'custom_booking_calendar_shortcode');

function custom_booking_calendar_with_debug_info_shortcode() {
    if (!user_has_allowed_role()) {
        return 'You do not have permission to view this calendar.';
    }

    ob_start(); // Start output buffering to capture all outputs

    // The calendar HTML
    $calendar_html = '<div id="calendar"></div>';
    echo $calendar_html;

    // Fetch and format bookings
    $formatted_bookings = fetch_bookings();

    // Print each booking's raw data for debugging
    echo '<h3>Booking Debug Information:</h3><pre>';
    foreach ($formatted_bookings as $booking) {
        print_r($booking); // Use print_r() to output the raw data of each booking
    }
    echo '</pre>';

    return ob_get_clean(); // Return the captured output
}
add_shortcode('custom_booking_calendar_debug', 'custom_booking_calendar_with_debug_info_shortcode');

// Add a new shortcode for the mobile-friendly booking module
add_shortcode('mobile_booking_module', 'display_mobile_booking_module');


function display_mobile_booking_module() {
    if (!user_has_allowed_role()) {
        return 'You do not have permission to view this content.';
    }

    ob_start();
    ?>
    <div class="mobile-booking-module">
        <div class="date-selector">
            <label for="booking-date">Select Date:</label>
            <input type="date" id="booking-date" name="booking-date" class="modern-date-input" />
        </div>
        <div class="check-ins-today">
            <h3>Check-Ins Today</h3>
            <!-- Check-ins will be populated here by JavaScript -->
        </div>
        <div class="check-outs-today">
            <h3>Check-Outs Today</h3>
            <!-- Check-outs will be populated here by JavaScript -->
        </div>
    </div>

    <!-- Modal Structure -->
    <div class="modal fade" id="guestInfoModal" tabindex="-1" role="dialog" aria-labelledby="guestInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="guestInfoModalLabel">Guest Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="guest-details"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}





function add_mobile_booking_module_styles() {
    ?>
    <style>
    .mobile-booking-module {
        font-family: 'Arial', sans-serif;
        color: #333;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin: 5px;
    }
    .mobile-booking-module h3 {
        color: #007BFF;
        margin-bottom: 20px;
        font-size: 1.5em;
    }
    .mobile-booking-module .check-in p,
    .mobile-booking-module .check-out p,
    .mobile-booking-module .guest p {
        margin: 10px 0;
        font-size: 1em;
    }
    .mobile-booking-module .check-in,
    .mobile-booking-module .check-out {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        cursor: pointer;
        transition: box-shadow 0.3s ease;
    }
    .mobile-booking-module .check-in:hover,
    .mobile-booking-module .check-out:hover {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .mobile-booking-module .guest-info {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 15px;
        margin-top: 30px;
    }
    .mobile-booking-module .guest-info .guest-details p {
        margin: 10px 0;
    }
    .status-text {
        font-weight: bold;
    }
    .date-selector {
        margin-bottom: 30px;
    }
    .modern-date-input {
        border: 1px solid #ccc;
        border-radius: 10px;
        padding: 15px;
        font-size: 16px;
        width: 100%;
        max-width: 300px;
        color: #333;
        background-color: #fff;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    .modern-date-input:focus {
        border-color: #007BFF;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        outline: none;
    }

    /* Modal Styles */
    .modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100% - 1rem);
    }
    .modal-content {
        border-radius: 10px;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        max-width: 100%;
    }
    .modal-header {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 15px;
    }
    .modal-header .close {
        margin: -1rem -1rem -1rem auto;
    }
    .modal-body {
        padding: 20px;
        font-size: 1em;
        line-height: 1.5;
    }
    .modal-footer {
        border-top: 1px solid #e9ecef;
        padding-top: 15px;
    }
    .modal-footer .btn {
        border-radius: 10px;
        padding: 10px 20px;
    }

    @media (max-width: 768px) {
        .modal-dialog {
            margin: 0;
            height: 100%;
        }

        .modal-content {
            width: 100%;
            height: auto;
            margin: 0;
            border-radius: 10px;
        }
    }
    </style>
    <?php
}
add_action('wp_head', 'add_mobile_booking_module_styles');



function getStatusText($status) {
    switch ($status) {
        case "pending":
            return 'Pending';
        case "complete":
        case "wc-completed":
            return 'Paid';
        case "incomplete":
            return 'NOT PAID';
        case "cancelled":
        case "wc-cancelled":
            return 'Cancelled';
        case "Cash Payment Due":
            return 'Cash Payment Due';
        case "Paid Cash":
            return 'Paid Cash';
        default:
            return 'Unknown';
    }
}


?>