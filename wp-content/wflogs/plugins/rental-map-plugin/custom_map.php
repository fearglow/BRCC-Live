<?php
/**
 * Plugin Name: Rental Map Plugin
 * Plugin URI: https://www.quakedigitalmarketing.com/rental-map-plugin
 * Description: Adds a custom map configuration to the WordPress admin for managing rental locations.
 * Version: 1.0
 * Author: Ryan Pittman
 * Author URI: https://www.quakedigitalmarketing.com
 */

// Hook into the WordPress 'admin_menu' action to add a new menu item for the rental map configuration
add_action('admin_menu', 'add_custom_map_admin_page');

// This function registers a new page in the WordPress admin dashboard
function add_custom_map_admin_page() {
    add_menu_page(
        'Rental Map Configuration', // The title of the page
        'Rental Map', // The text for the menu item
        'manage_options', // The capability required for this menu to be displayed to the user
        'rental-map-config', // The slug name for the menu
        'render_custom_map_admin_page', // The function that displays the page content
        'dashicons-location', // The icon for the menu item
        6 // The position in the menu order this item should appear
    );
}

// Renders the custom map admin page content
function render_custom_map_admin_page() {
    ?>
    <style>
        #imageMap {
            max-width: 100%;
            height: auto;
            background: url('/wp-content/uploads/2022/05/SITE-MAP.jpg') no-repeat center center;
            background-size: contain;
            position: relative;
        }
		.rental-marker {
            position: absolute;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .rental-marker img {
            width: 100%;
            height: auto;
        }
    </style>
    <div class="wrap">
        <h2>Rental Map Configuration</h2>
        <p>Drag the markers to set locations for each rental posting.</p>
        <div id="imageMap" style="padding-top: calc(600 / 700 * 100%);"></div> <!-- Maintain aspect ratio -->
    </div>
    <div id="rentalsList">
        <?php foreach (get_rentals_for_map() as $rental) : ?>
            <div class="rental" data-id="<?php echo esc_attr($rental['post_id']); ?>">
                <?php echo esc_html($rental['post_name']); ?>
				
            </div>
			
        <?php endforeach; ?>
		<?php echo '<button id="clearMarkers" class="button button-primary">Clear All Locations</button>'; ?>
    </div>
    <?php
}

// Hooks into the 'admin_enqueue_scripts' action to enqueue necessary styles and scripts
function enqueue_custom_map_scripts($hook) {
    if ('toplevel_page_rental-map-config' !== $hook) {
        return;
    }

    $map_icon = st()->get_option('st_rental_icon_map_marker', '');
    if (empty($map_icon)) {
        $map_icon = get_template_directory_uri() . '/v2/images/markers/ico_mapker_rental.png';
    }

    // Enqueue scripts
    
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
    wp_enqueue_script('jquery-ui-droppable');
    wp_enqueue_script('custom-map-admin-js', plugins_url('js/custom-map-admin.js', __FILE__), array('leaflet-js', 'jquery-ui-draggable'), '1.0', true);

    // Localize script with nonce and rentals data
    $rentals_data = get_rentals_for_map();
    wp_localize_script('custom-map-admin-js', 'adminMap', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('admin_map_nonce'),
        'rentals' => $rentals_data,
        'mapIconUrl' => $map_icon
    ));
}
add_action('admin_enqueue_scripts', 'enqueue_custom_map_scripts');

// Registers an AJAX action for logged-in users: 'wp_ajax_{action}'
add_action('wp_ajax_save_rental_location', 'save_rental_location');


function get_rentals_for_map() {
    global $wpdb;
    $rentals = $wpdb->get_results("
        SELECT r.post_id, p.post_name,
        pm1.meta_value as posX, pm2.meta_value as posY
        FROM {$wpdb->prefix}st_rental r
        INNER JOIN {$wpdb->prefix}posts p ON r.post_id = p.ID
        INNER JOIN {$wpdb->prefix}postmeta pm1 ON r.post_id = pm1.post_id AND pm1.meta_key = 'posX'
        INNER JOIN {$wpdb->prefix}postmeta pm2 ON r.post_id = pm2.post_id AND pm2.meta_key = 'posY'
        WHERE p.post_status = 'publish'
    ", ARRAY_A);

    return $rentals;
}

// Handles the AJAX request to save the new location of a rental
function save_rental_location() {
    // Nonce verification for security
    check_ajax_referer('admin_map_nonce', 'nonce');

    // Sanitizes and stores the posX, posY, and post ID from the AJAX request
    $posX = isset($_POST['posX']) ? sanitize_text_field($_POST['posX']) : '';
    $posY = isset($_POST['posY']) ? sanitize_text_field($_POST['posY']) : '';
    $postID = isset($_POST['postID']) ? intval($_POST['postID']) : 0;

    // Checks if the necessary data is present and then updates the post meta with the new positions
    if (!empty($posX) && !empty($posY) && !empty($postID)) {
        update_post_meta($postID, 'posX', $posX);
        update_post_meta($postID, 'posY', $posY);
        wp_send_json_success('Position saved successfully.');
    } else {
        wp_send_json_error('Failed to save position.');
    }
}

// Register AJAX action for logged-in users
add_action('wp_ajax_clear_all_rental_locations', 'clear_all_rental_locations');

// Handler function to clear all rental positions
function clear_all_rental_locations() {
    check_ajax_referer('admin_map_nonce', 'nonce'); // Security check

    global $wpdb;
    // Assuming your posX and posY are stored in postmeta table
    $result1 = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = '' WHERE meta_key = %s",
            'posX'
        )
    );

    $result2 = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = '' WHERE meta_key = %s",
            'posY'
        )
    );

    if ($result1 !== false && $result2 !== false) {
        wp_send_json_success('All positions have been cleared.');
    } else {
        wp_send_json_error('Failed to clear positions.');
    }
}

function rental_image_map_shortcode() {
    // Output styling for the front-end, where the image map container can be responsive
    $output = '<style>
        #imageMap {
            width: 100%; /* Responsive width */
            height: auto; /* Adjust height automatically to maintain aspect ratio */
            background: url(\'/wp-content/uploads/2022/05/SITE-MAP.jpg\') no-repeat center center;
            background-size: contain; 
            position: relative;
            overflow: hidden; /* Prevent markers from overflowing the container */
        }
        .rental-marker {
            position: absolute;
            cursor: pointer; /* Change cursor to indicate clickable markers */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .rental-marker img {
            width: 30px; /* Adjust the marker icon size */
            height: 30px; /* Adjust the marker icon size */
        }
    </style>';

    $output .= '<div id="imageMap"></div>'; // Container for the map

    $rentals = get_rentals_for_map();
    foreach ($rentals as $rental) {
        // Ensure posX and posY are interpreted as percentages
        $posX = get_post_meta($rental['post_id'], 'posX', true) . '%';
        $posY = get_post_meta($rental['post_id'], 'posY', true) . '%';

        $map_icon = st()->get_option('st_rental_icon_map_marker', '');
        if (empty($map_icon)) {
            $map_icon = get_template_directory_uri() . '/v2/images/markers/ico_mapker_rental.png';
        }

        // Output each rental marker with posX and posY as percentages
        $output .= "<div class=\"rental-marker\" data-id=\"{$rental['post_id']}\" style=\"left: {$posX}; top: {$posY};\">" .
                   "<img src=\"{$map_icon}\" alt=\"{$rental['post_name']}\" title=\"{$rental['post_title']}\">" .
                   "</div>";
    }

    return $output;
}
add_shortcode('rental_image_map', 'rental_image_map_shortcode');



function rental_image_map_single_shortcode() {
    global $post; // Access the current post object.

    // Fetch the current post's coordinates as percentages.
    $posXPercentage = get_post_meta($post->ID, 'posX', true);
    $posYPercentage = get_post_meta($post->ID, 'posY', true);

    // Map icon selection.
    $map_icon = st()->get_option('st_rental_icon_map_marker', '');
    if (empty($map_icon)) {
        $map_icon = get_template_directory_uri() . '/v2/images/markers/ico_mapker_rental.png';
    }

    // Output styling and container for the map.
    $output = '<div id="imageMapSingle" style="max-width: 100%; height: auto; position: relative; overflow: hidden;">
        <img src="/wp-content/uploads/2022/05/SITE-MAP.jpg" style="width: 100%; height: auto;" alt="Site Map">
        <div class="rental-marker-single" style="position: absolute; width: 30px; height: 30px; transform: translate(-50%, -50%); cursor: pointer;">
            <img src="' . esc_url($map_icon) . '" style="width: 100%; height: 100%;" alt="Location Marker">
        </div>
    </div>';

    // JavaScript to adjust the marker's position based on the percentages.
    $output .= "<script>
        window.addEventListener('load', function() {
            var imageMap = document.getElementById('imageMapSingle');
            var marker = document.querySelector('.rental-marker-single');

            // Apply the percentages directly for positioning
            marker.style.left = '{$posXPercentage}%';
            marker.style.top = '{$posYPercentage}%';
        });
    </script>";

    return $output; // Return the complete HTML and JavaScript for the shortcode.
}
add_shortcode('rental_image_map_single', 'rental_image_map_single_shortcode');

