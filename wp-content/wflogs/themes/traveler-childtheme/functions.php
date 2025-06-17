<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 21/08/2015
 * Time: 9:45 SA
 */
add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles', 20 );

function enqueue_parent_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri() );
}


function meks_which_template_is_loaded() {
	if ( is_super_admin() ) {
		global $template;
		print_r( $template );
	}
}
 
function check_and_send_departure_date_notices() {
    $log_path = WP_CONTENT_DIR . '/debug-cron.log';
    $timestamp = current_time('mysql'); // Get the current time, according to your WordPress timezone settings.
    
    // Log the start of the cron job
    file_put_contents($log_path, "[$timestamp] Cron Job Started\n", FILE_APPEND);

    // Check for missed execution
    $last_run = get_option('last_departure_notice_run');
    if ($last_run) {
        $time_since_last_run = current_time('timestamp') - $last_run;
        // Assuming daily job, if it's been more than 24 hours, it was missed
        if ($time_since_last_run > 86400) {
            file_put_contents($log_path, "[$timestamp] Missed cron execution detected. Last run was at: " . date('Y-m-d H:i:s', $last_run) . "\n", FILE_APPEND);
        }
    }

    // Update the last run time
    update_option('last_departure_notice_run', current_time('timestamp'));

    global $wpdb;
    $table = $wpdb->prefix . 'st_order_item_meta';
    
    // Use WordPress's current_time function to get the timestamp for 2 days from now in the site's timezone
    $two_days_from_now = strtotime('+2 days', current_time('timestamp'));
    
    // Convert these to the start and end of the day in the site's timezone
    $start_of_day = strtotime(date('Y-m-d 00:00:00', $two_days_from_now));
    $end_of_day = strtotime(date('Y-m-d 23:59:59', $two_days_from_now));

    // Adjust your query as needed to include status 'completed'
    $query = $wpdb->prepare("SELECT order_item_id FROM {$table} WHERE check_in_timestamp >= %d AND check_in_timestamp <= %d AND status = 'complete'", $start_of_day, $end_of_day);
    $bookings = $wpdb->get_results($query);

    // Log the query and its result
    file_put_contents($log_path, "[$timestamp] Query executed: {$query}\n", FILE_APPEND);
    file_put_contents($log_path, "[$timestamp] Query returned " . count($bookings) . " bookings\n", FILE_APPEND);

    if (!empty($bookings)) {
        foreach ($bookings as $booking) {
            file_put_contents($log_path, "[$timestamp] Processing booking ID: {$booking->order_item_id}\n", FILE_APPEND);

            if (class_exists('STUser_f')) {
                $st_user = new STUser_f();
                $st_user->_send_partner_notice_departure_date($booking->order_item_id); // Send the email without logging the result
                
                // Send the departure SMS message
                STTInjects::inst()->sendDepartureSMS($booking->order_item_id);
                file_put_contents($log_path, "[$timestamp] Attempted to send departure email and SMS for booking ID {$booking->order_item_id}\n", FILE_APPEND);
            } else {
                file_put_contents($log_path, "[$timestamp] STUser_f class not found.\n", FILE_APPEND);
            }
        }
    } else {
        file_put_contents($log_path, "[$timestamp] No bookings found for the specified period.\n", FILE_APPEND);
    }

    // Log the end of the cron job
    $timestamp_end = current_time('mysql');
    file_put_contents($log_path, "[$timestamp_end] Cron Job Ended\n", FILE_APPEND);
}

// Add custom cron schedule
add_filter('cron_schedules', 'add_custom_cron_schedule');
function add_custom_cron_schedule($schedules) {
    if (!isset($schedules['daily'])) {
        $schedules['daily'] = [
            'interval' => 86400,
            'display'  => __('Once Daily'),
        ];
    }
    return $schedules;
}

// Register daily order check event
add_action('wp', 'register_daily_order_check_event');
function register_daily_order_check_event() {
    if (!wp_next_scheduled('send_departure_date_notice')) {
        wp_schedule_event(time(), 'daily', 'send_departure_date_notice');
        $timestamp = current_time('mysql');
        $log_path = WP_CONTENT_DIR . '/debug-cron.log';
        file_put_contents($log_path, "[$timestamp] Cron event scheduled\n", FILE_APPEND);
    }
}

// Action hook for the custom cron event
add_action('send_departure_date_notice', 'check_and_send_departure_date_notices');








function st_add_custom_admin_page() {
    add_menu_page(
        'Test Email Send', // Page title
        'Test Email Send', // Menu title
        'manage_options', // Capability
        'st-test-email-send', // Menu slug
        'st_custom_admin_page_content', // Function to display the admin page
        'dashicons-email-alt', // Icon URL
        6 // Position
    );
}



add_action('admin_menu', 'st_add_custom_admin_page');




function st_custom_admin_page_content() {
    // Check if the user has submitted the form
    if (isset($_POST['st_test_order_id'])) {
        $order_id = sanitize_text_field($_POST['st_test_order_id']);

        // Make sure the class exists before using it
        if (class_exists('STUser_f') && class_exists('STTInjects')) {
            // Instantiate the STUser_f class
            $st_user = new STUser_f();

            // Use the method to send the partner notice
            $result = $st_user->_send_partner_notice_departure_date($order_id);

            // Use the method to send the departure SMS
            STTInjects::inst()->sendDepartureSMS($order_id);

            // Display the result
            echo '<div>Triggered email and SMS send for Order ID: ' . esc_html($order_id) . '. Email Result: ' . ($result ? 'Success' : 'Fail') . '</div>';
        } else {
            echo '<div>Error: STUser_f or STTInjects class not found.</div>';
        }
    }

    // Display the form
    ?>
    <div class="wrap">
        <h2>Test Email and SMS Send</h2>
        <form method="post">
            <label for="st_test_order_id">Order ID:</label>
            <input type="text" id="st_test_order_id" name="st_test_order_id">
            <input type="submit" value="Send Test Email and SMS">
        </form>
    </div>
    <?php
}

function st_send_email_by_order_id($order_id) {
    $log_path = WP_CONTENT_DIR . '/debug-cron-send.log';
    $timestamp = current_time('mysql'); // Current time for logging
    
    if (class_exists('STUser_f')) {
        $user_info = get_userdata(5);
        if ($user_info) {
            file_put_contents($log_path, "[$timestamp] Switching to User ID 5.\n", FILE_APPEND);
            wp_set_current_user(5);

            file_put_contents($log_path, "[$timestamp] Prepared email headers.\n", FILE_APPEND);

             try {
                // Attempt to send the email
                $result = STUser_f::_send_partner_notice_departure_date($order_id);
                
                // Log the result of the email send attempt
                file_put_contents($log_path, "[$timestamp] Email send attempt for order ID $order_id returned: " . var_export($result, true) . "\n", FILE_APPEND);
            } catch (Exception $e) {
                // Log any exceptions thrown during the email send attempt
                file_put_contents($log_path, "[$timestamp] Exception caught while sending email for order ID $order_id: " . $e->getMessage() . "\n", FILE_APPEND);
                $result = false;
            }

            wp_set_current_user(0); // Revert to previous user
            return $result;
        } else {
            file_put_contents($log_path, "[$timestamp] User ID 5 not found.\n", FILE_APPEND);
            return false;
        }
    } else {
        file_put_contents($log_path, "[$timestamp] STUser_f class not found.\n", FILE_APPEND);
        return false;
    }
}

// Manually trigger the cron event from the admin page
function st_trigger_cron_job_admin_page() {
    add_menu_page('Trigger Cron Job', 'Trigger Cron Job', 'manage_options', 'st-trigger-cron', 'st_trigger_cron_job_page_content', 'dashicons-update');
}
add_action('admin_menu', 'st_trigger_cron_job_admin_page');

function st_trigger_cron_job_page_content() {
    echo '<div class="wrap"><h1>Trigger Cron Job</h1>';
    if (isset($_GET['trigger_cron']) && current_user_can('manage_options')) {
        do_action('send_departure_date_notice');
        echo '<p>Cron job triggered!</p>';
    }
    echo '<a href="'.admin_url('admin.php?page=st-trigger-cron&trigger_cron=true').'" class="button button-primary">Trigger Cron Job</a>';
    echo '</div>';
}

add_action('wp_footer', 'show_included_files');
add_action('admin_footer', 'show_included_files'); // Ensure it works in the admin area as well.

function show_included_files() {
    if (current_user_can('administrator')) {
        $included_files = get_included_files();
        $theme_directories = [get_template_directory(), get_stylesheet_directory()];
        ?>
        <div id="filesToggleButton" style="position: fixed; bottom: 20px; right: 20px; z-index: 10000;">
            <button onclick="toggleFilesVisibility();" style="cursor: pointer;">Show Files</button>
        </div>
        <div id="includedFilesContainer" style="background-color: #fff; color: #000; padding: 10px; position: fixed; bottom: 0; right: -700px; /* Adjust as per your desired off-screen start position */ width: 680px; /* Adjusted width */ height: 200px; overflow-x: auto; /* Enable horizontal scrolling */ white-space: nowrap; /* Prevent text wrapping */ z-index: 9999; transition: right 0.5s;">
            <strong>Included Theme Files:</strong><br>
            <div id="filesList">
                <?php foreach ($included_files as $filename) {
                    foreach ($theme_directories as $theme_directory) {
                        if (strpos($filename, $theme_directory) !== false) {
                            echo $filename . '<br>';
                            break;
                        }
                    }
                } ?>
            </div>
        </div>
        <script>
        function toggleFilesVisibility() {
            var container = document.getElementById('includedFilesContainer');
            var btn = document.getElementById('filesToggleButton').getElementsByTagName('button')[0];
            // Check if the container is visible
            if (container.style.right === "0px") {
                container.style.right = "-700px"; // Adjust according to the new width
                btn.innerHTML = "Show Files";
            } else {
                container.style.right = "0"; // Show the container
                btn.innerHTML = "Hide Files";
            }
        }
        </script>
        <?php
    }
}

function redirect_subscriber_on_login( $redirect_to, $request, $user ) {
    // Is there a user to check?
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        // Check that the user is a subscriber
        if ( in_array( 'subscriber', $user->roles ) ) {
            // Redirect them to the homepage
            return home_url('/user-settings/');
        } else {
            // Otherwise return the default redirect
            return $redirect_to;
        }
    } else {
        return $redirect_to;
    }
}
add_filter( 'login_redirect', 'redirect_subscriber_on_login', 10, 3 );

function redirect_subscribers_from_admin() {
    // Check if the current user is a subscriber and if they are attempting to access an admin page
    if (is_admin() && current_user_can('subscriber') && !defined('DOING_AJAX')) {
        wp_redirect(home_url('/user-settings/'));
        exit;
    }
}
add_action('admin_init', 'redirect_subscribers_from_admin');

function add_host_role() {
    if (null === get_role('host')) {
    add_role(
        'host',
        __('Host'),
        array(
                'read'         => true,
                'edit_posts'   => false,
                'upload_files' => true,
        )
    );
}
}

function register_metabox( $custom_metabox ) {
	/**
	 * Register our meta boxes using the
	 * ot_register_meta_box() function.
	 */
	if ( function_exists( 'ot_register_meta_box' ) ) {
		if ( ! empty( $custom_metabox ) ) {
			foreach ( $custom_metabox as $value ) {
				ot_register_meta_box( $value );
			}
		}
	}
}

function loadFrontInit() {
	require __DIR__ . '/front/class.admin.rental.php';
	require __DIR__ . '/front/rental.helper.php';

	require __DIR__ . '/front/class.rental.php';
	require __DIR__ . '/front/booking-rental.php';
}
add_action( 'init', 'loadFrontInit' );
