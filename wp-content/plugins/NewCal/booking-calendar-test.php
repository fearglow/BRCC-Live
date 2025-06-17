<?php
/**
 * Plugin Name: Pure CSS Modal Booking Calendar
 * Description: Table-based calendar pulling from st_order_item_meta, using a custom (non-Bootstrap) modal and dynamic debug output.
 * Version: 1.2
 * Author: Ryan Pittman
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// 1) Role check
function user_has_allowed_role_newcal() {
    $allowed_roles = array('super_admin', 'administrator', 'partner', 'host');
    foreach ($allowed_roles as $role) {
        if (current_user_can($role)) {
            return true;
        }
    }
    return false;
}

// 2) Admin Menu
function booking_calendar_test_admin_menu() {
    add_menu_page(
        'Booking Calendar Test',
        'Booking Calendar Test',
        'manage_options',
        'booking-calendar-test',
        'booking_calendar_test_admin_page',
        'dashicons-calendar-alt',
        6
    );
}
add_action('admin_menu', 'booking_calendar_test_admin_menu');

function booking_calendar_test_admin_page() {
    echo '<h1>Booking Calendar Test</h1>';
    // Output the shortcode
    echo do_shortcode('[custom_booking_calendar_v2]');
}

// 3) The Shortcode: Table + Debug + Custom Modal
function custom_booking_calendar_v2_shortcode() {
    if (!user_has_allowed_role_newcal()) {
        return '<p>You do not have permission to view this calendar.</p>';
    }

    ob_start();
    ?>
    <!-- Calendar Controls -->
    <div class="calendar-controls">
        <button id="prev-month">Previous</button>
        <h2 id="calendar-header" style="display:inline-block; margin:0 10px;"><?php echo date('F Y'); ?></h2>
        <button id="next-month">Next</button>
    </div>

    <!-- Table placeholder -->
    <div id="custom-calendar-v2"
         data-month="<?php echo date('m'); ?>"
         data-year="<?php echo date('Y'); ?>"></div>

    <!-- Debug container (will be dynamically filled) -->
    <div id="debug-output" class="debug-output">
        <h2>Debug Output</h2>
        <div id="debug-entries">Loading...</div>
    </div>

    <!-- Simple custom modal for booking details (No Bootstrap) -->
    <div class="calendar-modal" id="bookingModal">
      <div class="calendar-modal-content">
        <span class="calendar-modal-close" id="modalClose">&times;</span>
        <div class="calendar-modal-header" id="modalHeader">Booking Details</div>
        <div class="calendar-modal-body" id="modalBody"></div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_booking_calendar_v2', 'custom_booking_calendar_v2_shortcode');

// 4) Enqueue our CSS/JS (No Bootstrap)
function enqueue_custom_booking_calendar_v2_scripts() {
    // Our custom table & modal CSS
    wp_enqueue_style(
        'custom-booking-styles-v2',
        plugins_url('/css/custom-booking-styles-v2.css', __FILE__),
        array(),
        '1.0',
        'all'
    );

    // Our custom JS
    wp_enqueue_script(
        'custom-booking-init-v2',
        plugins_url('/js/init-calendar-v2.js', __FILE__),
        array('jquery'),
        '1.0',
        true
    );

    // Provide AJAX + nonce
    wp_localize_script('custom-booking-init-v2', 'bookingData', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('fetch_updated_bookings_nonce'),
    ));
}
add_action('admin_enqueue_scripts', 'enqueue_custom_booking_calendar_v2_scripts');
add_action('wp_enqueue_scripts', 'enqueue_custom_booking_calendar_v2_scripts');

// 5) fetch_all_campsites()
function fetch_all_campsites() {
    global $wpdb;
    $q = "
        SELECT DISTINCT p.ID, p.post_title
        FROM {$wpdb->prefix}posts p
        WHERE p.post_type = 'st_rental'
          AND p.post_status = 'publish'
    ";
    $results = $wpdb->get_results($q, ARRAY_A);
    foreach ($results as &$r) {
        $parts = explode(':', $r['post_title']);
        $r['site_code'] = isset($parts[0]) ? trim($parts[0]) : $r['post_title'];
    }
    return $results;
}

// 6) fetch_bookings() from st_order_item_meta
function fetch_bookings() {
    global $wpdb;
    // Re-check statuses if you want more than these
    $query = "
        SELECT 
            oim.user_id,
            oim.order_item_id AS id,
            oim.check_in,
            oim.check_out,
            oim.st_booking_id,
            oim.total_order,
            p.post_title AS post_name,
            COALESCE(pm1.meta_value, um.meta_value) AS first_name, 
            COALESCE(pm2.meta_value, um2.meta_value) AS last_name,
            oim.adult_number,
            oim.child_number,
            oim.infant_number,
            oim.status,
            oim.cash_paid,
            pm.meta_value AS payment_method_name,
            pm_email.meta_value AS email,
            pm_phone.meta_value AS phone,
            pm_equipment_type.meta_value AS equipment_type,
            pm_length_ft.meta_value AS length_ft,
            pm_slide_outs.meta_value AS slide_outs,
            pm_guest_name.meta_value AS guest_name,
            pm_guest_title.meta_value AS guest_title,
            pm_booking_post_type.meta_value AS booking_post_type,
            pm_starttime.meta_value AS starttime
        FROM {$wpdb->prefix}st_order_item_meta oim
        INNER JOIN {$wpdb->users} u ON oim.user_id = u.ID
        INNER JOIN {$wpdb->usermeta} um  ON u.ID = um.user_id  AND um.meta_key  = 'first_name'
        INNER JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'last_name'
        INNER JOIN {$wpdb->posts} p ON oim.st_booking_id = p.ID
        LEFT JOIN {$wpdb->postmeta} pm  ON oim.order_item_id = pm.post_id  AND pm.meta_key  = 'payment_method_name'
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
        WHERE oim.status IN ('complete','wc-completed')
    ";
    $rows = $wpdb->get_results($query, ARRAY_A);
    if (!$rows) return array();

    $formatted = array();
    foreach ($rows as $row) {
        // Payment
        $isCash = (strtolower(trim($row['payment_method_name'])) === 'cash');
        $derived_status = $isCash && !$row['cash_paid']
            ? 'Cash Payment Due'
            : ($row['cash_paid'] ? 'Paid Cash' : $row['status']);

        // site code
        $parts = explode(':', $row['post_name']);
        $siteName = isset($parts[0]) ? trim($parts[0]) : $row['post_name'];

        // stay range
        $stay_str = $row['check_in'] . ' - ' . $row['check_out'];

        // +1 day so pill includes last night
        $adjusted_checkout = date('Y-m-d', strtotime($row['check_out'] . ' +1 day'));

        // guest details if needed
        $guest_names  = maybe_unserialize($row['guest_name']);
        $guest_titles = maybe_unserialize($row['guest_title']);
        $guest_details = array();
        if (is_array($guest_names) && is_array($guest_titles)) {
            foreach ($guest_names as $i => $nm) {
                $weight = isset($guest_titles[$i]) ? $guest_titles[$i].' lbs' : '';
                $guest_details[] = $nm . ($weight ? " ($weight)" : '');
            }
        }

        // eq type
        $eq_type = $row['equipment_type']
            ? ucwords(str_replace('_', ' ', $row['equipment_type']))
            : '';

        $booking = array(
            'id'        => $row['id'],
            'site'      => $siteName,
            'start'     => $row['check_in'],
            'end'       => $adjusted_checkout,
            'display_end'=> $row['check_out'],
            'adult_number' => $row['adult_number'],
            'child_number' => $row['child_number'],
            'infant_number'=> $row['infant_number'],
            'customer'  => trim($row['first_name'].' '.$row['last_name']),
            'status'    => $derived_status,
            'isCashPayment' => $isCash,
            'cash_paid' => $row['cash_paid'],
            'payment_method_name' => $row['payment_method_name'],
            'st_booking_id'=> $row['st_booking_id'],
            'email'     => $row['email'],
            'phone'     => $row['phone'],
            'total_order'=> $row['total_order'],
            'starttime' => $row['starttime'],
            'booking_post_type' => $row['booking_post_type'],
            'stay'      => $stay_str,
            'guest_details' => implode(', ', $guest_details),
        );
        if ($eq_type) {
            $booking['equipment_type'] = $eq_type;
        }
        if (!empty($row['length_ft'])) {
            $booking['length_ft'] = $row['length_ft'];
        }
        if (!empty($row['slide_outs'])) {
            $booking['slide_outs'] = $row['slide_outs'];
        }
        $formatted[] = $booking;
    }
    return $formatted;
}

// 7) Filter by month
function fetch_bookings_for_month($month, $year) {
    $all = fetch_bookings();
    $filtered = array_filter($all, function($b) use ($month, $year) {
        $start_ts = strtotime($b['start']);
        $end_ts   = strtotime($b['end']);
        if (!$start_ts || !$end_ts) return false;
        $start_m = date('m', $start_ts);
        $start_y = date('Y', $start_ts);
        $end_m   = date('m', $end_ts);
        $end_y   = date('Y', $end_ts);

        return (
            ($start_m == $month && $start_y == $year) ||
            ($end_m   == $month && $end_y   == $year)
        );
    });
    return $filtered;
}

// 8) AJAX: load_calendar_v2 => returns [html, bookings, debugHtml]
function load_calendar_v2_ajax() {
    check_ajax_referer('fetch_updated_bookings_nonce', 'nonce');

    $m = isset($_POST['month']) ? intval($_POST['month']) : date('m');
    $y = isset($_POST['year'])  ? intval($_POST['year'])  : date('Y');

    $campsites = fetch_all_campsites();
    $bookings  = fetch_bookings_for_month($m, $y);

    $html = generate_calendar_v2_html($m, $y, $campsites, $bookings);
    $debugHtml = build_debug_html($bookings, $m, $y);

    wp_send_json_success([
        'html'        => $html,
        'bookings'    => $bookings,
        'debug_html'  => $debugHtml,
        'total_sites' => count($campsites)
    ]);
}
add_action('wp_ajax_load_calendar_v2', 'load_calendar_v2_ajax');
add_action('wp_ajax_nopriv_load_calendar_v2', 'load_calendar_v2_ajax');

// build table
function generate_calendar_v2_html($month, $year, $campsites, $bookings) {
    $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $total_sites = count($campsites);
    ob_start(); ?>
    <table class="calendar-grid" data-total-sites="<?php echo $total_sites; ?>">
      <thead>
        <tr>
          <th class="empty-header-cell"></th>
          <?php for ($d = 1; $d <= $num_days; $d++): ?>
            <th class="header-day"><?php echo $d; ?></th>
          <?php endfor; ?>
        </tr>
        <tr class="availability-row">
          <th class="availability-label">Available</th>
          <?php for ($d = 1; $d <= $num_days; $d++): ?>
            <th class="availability-cell" data-day="<?php echo $d; ?>"></th>
          <?php endfor; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($campsites as $site): ?>
          <tr class="calendar-row" data-site="<?php echo esc_attr($site['site_code']); ?>">
            <td class="campsite-name"><?php echo esc_html($site['site_code']); ?></td>
            <?php for ($d = 1; $d <= $num_days; $d++): ?>
              <td class="day-cell" data-day="<?php echo $d; ?>"></td>
            <?php endfor; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php
    return ob_get_clean();
}

// Custom function to build debug HTML
function build_debug_html($bookings, $month, $year) {
    if (empty($bookings)) {
        return '<p>No bookings found for ' . date('F Y', strtotime("$year-$month-01")) . '.</p>';
    }
    $output = '';
    foreach ($bookings as $b) {
        $startDay   = intval(date('d', strtotime($b['start'])));
        $endDay     = intval(date('d', strtotime($b['end'])));
        $startMonth = intval(date('m', strtotime($b['start'])));
        $endMonth   = intval(date('m', strtotime($b['end'])));
        if ($startMonth < $month) {
            $startDay = 1;
        }
        if ($endMonth > $month) {
            $endDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        }
        $spanDays   = $endDay - $startDay + 1;
        $arrowRight = ($endMonth > $month) ? 'Yes' : 'No';

        $output .= '<p>';
        $output .= 'Site: ' . $b['site'] . '<br>';
        $output .= 'Customer: ' . $b['customer'] . '<br>';
        $output .= 'Start: ' . $b['start'] . '<br>';
        $output .= 'End: ' . $b['end'] . '<br>';
        $output .= 'SpanDays: ' . $spanDays . '<br>';
        $output .= 'ArrowRight? ' . $arrowRight . '<br>';
        $output .= '</p>';
    }
    return $output;
}

// 9) Mark as Paid
function mark_as_paid_ajax() {
    check_ajax_referer('fetch_updated_bookings_nonce', 'nonce');
    global $wpdb;

    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    if (!$booking_id) {
        wp_send_json_error(['message' => 'Invalid booking ID']);
    }

    $table = $wpdb->prefix . 'st_order_item_meta';
    $res = $wpdb->update(
        $table,
        ['cash_paid' => 1],
        ['order_item_id' => $booking_id],
        ['%d'],
        ['%d']
    );
    if ($res === false) {
        wp_send_json_error(['message' => 'DB error: ' . $wpdb->last_error]);
    } elseif ($res === 0) {
        wp_send_json_error(['message' => 'Failed or already paid.']);
    } else {
        wp_send_json_success(['message' => 'Booking marked as paid.']);
    }
}
add_action('wp_ajax_mark_as_paid', 'mark_as_paid_ajax');
add_action('wp_ajax_nopriv_mark_as_paid', 'mark_as_paid_ajax');

// 10) (Optional) fetch_updated_bookings
function fetch_updated_bookings_callback() {
    check_ajax_referer('fetch_updated_bookings_nonce', 'nonce');
    $all = fetch_bookings();
    wp_send_json_success($all);
}
add_action('wp_ajax_fetch_updated_bookings', 'fetch_updated_bookings_callback');
add_action('wp_ajax_nopriv_fetch_updated_bookings', 'fetch_updated_bookings_callback');
