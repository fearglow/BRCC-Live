<?php
/**
 * @package WordPress
 * @subpackage Traveler
 * @since 1.1.0
 *
 * Admin Rental Booking Management (All Bookings with Cancellation for Future Bookings)
 *
 * Created by ShineTheme
 */
$format       = TravelHelper::getDateFormat();
$screen       = "st_rental";
$current_user = wp_get_current_user();
$roles        = $current_user->roles;
?>
<div class="st-create">
    <h2><?php _e("Rental Booking", 'traveler'); ?></h2>
    <?php do_action('export_booking_history_button', $screen); ?>
    <?php
    $arr_query_arg = array(
        'sc'       => 'booking-rental',
        'scaction' => 'email-notification'
    );
    if (STInput::get('scaction') != 'email-notification') { ?>
        <a class="st_button_send_mail" href="<?php echo add_query_arg($arr_query_arg, get_permalink()); ?>"
           class="btn btn-primary btn-sm btn-sendmail-notice-link"
           title="<?php echo __('Send email notification depart date', 'traveler'); ?>">
           <?php echo __('Send email notification', 'traveler'); ?>
        </a>
    <?php } ?>
</div>

<!-- Inline style for search form -->
<style>
    form.search-form {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    form.search-form label {
        margin-bottom: 0;
        font-weight: bold;
    }
    form.search-form input[type="text"] {
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }
    form.search-form button {
        padding: 6px 12px;
        background-color: #337ab7;
        border: none;
        border-radius: 3px;
        color: #fff;
        cursor: pointer;
    }
    form.search-form button:hover {
        background-color: #286090;
    }
</style>

<!-- Search Form: Only Customer Name -->
<form method="get" action="<?php echo esc_url(get_permalink()); ?>" class="search-form">
    <input type="hidden" name="sc" value="booking-rental" />
    <input type="hidden" name="scaction" value="<?php echo esc_attr(STInput::get('scaction')); ?>" />
    <div class="form-group">
        <label for="st_custommer_name"><?php _e('Guest Name', 'traveler'); ?></label>
        <input type="text" name="st_custommer_name" id="st_custommer_name" 
               value="<?php echo esc_attr(STInput::get('st_custommer_name')); ?>" />
    </div>
    <button type="submit"><?php _e('Search', 'traveler'); ?></button>
</form>

<?php
// Pagination
$paged  = get_query_var('paged') ? intval(get_query_var('paged')) : 1;
$limit  = 10;
$offset = ($paged - 1) * $limit;

global $wpdb;
if (!empty($_GET['st_custommer_name'])) {
    // Custom query if searching by guest name.
    $c_name   = sanitize_text_field($_GET['st_custommer_name']);
    $querystr = "
        SELECT SQL_CALC_FOUND_ROWS {$wpdb->prefix}posts.*, {$wpdb->prefix}st_order_item_meta.*
        FROM {$wpdb->prefix}st_order_item_meta
        INNER JOIN {$wpdb->prefix}posts 
            ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}st_order_item_meta.order_item_id
        INNER JOIN {$wpdb->prefix}postmeta AS mt3 
            ON mt3.post_id = {$wpdb->prefix}st_order_item_meta.order_item_id
        WHERE 1=1 
            AND st_booking_post_type = 'st_rental'
            AND type = 'normal_booking'
            AND mt3.meta_key = 'st_first_name'
            AND mt3.meta_value LIKE '%" . esc_sql($c_name) . "%'
        ORDER BY {$wpdb->prefix}st_order_item_meta.id DESC
        LIMIT {$offset}, {$limit}
    ";
    $posts = $wpdb->get_results($querystr, OBJECT);
    $total = ceil($wpdb->get_var("SELECT FOUND_ROWS();") / $limit);
} else {
    // Otherwise, use existing function (pass false for author to retrieve all bookings).
    if (STInput::get('scaction') != 'email-notification') {
        $data_post = STUser_f::get_history_bookings('st_rental', $offset, $limit, false);
    } else {
        $data_post = STUser_f::get_history_bookings_send_mail('st_rental', $offset, $limit, false);
    }
    $posts = $data_post['rows'];
    $total = ceil($data_post['total'] / $limit);
}

if (STInput::get('scaction') == 'email-notification') {
    echo st()->load_template('user/user-booking-rental', 'email', array('posts' => $posts, 'offset' => $offset));
} else { ?>
    <div class="infor-st-setting">
        <table class="table table-bordered table-striped table-booking-history">
            <thead>
                <tr>
                    <th class="hidden-xs"><?php echo __('#ID', 'traveler'); ?></th>
                    <th class="hidden-xs"><?php _e("Customer", 'traveler'); ?></th>
                    <th><?php _e("Rental Name", 'traveler'); ?></th>
                    <th class="hidden-xs"><?php _e("Check-in/Check-out", 'traveler'); ?></th>
                    <th class="hidden-xs"><?php _e("Price", 'traveler'); ?></th>
                    <th class="hidden-xs" width="10%"><?php _e("Order Date", 'traveler'); ?></th>
                    <th><?php _e("Status", 'traveler'); ?></th>
                    <th width="10%"><?php _e("Action", 'traveler'); ?></th>
                    <?php do_action('export_booking_item_title'); ?>
                </tr>
            </thead>
            <tbody id="data_history_book booking-history-title">
                <?php if (!empty($posts)) {
                    $i = 1 + $offset;
                    foreach ($posts as $value) {
                        $post_id   = $value->wc_order_id;
                        $item_id   = $value->st_booking_id;
                        // For future booking check:
                        $checkin_ts = strtotime($value->check_in);
                        ?>
                        <tr>
                            <td class="hidden-xs"><?php echo esc_attr($post_id); ?></td>
                            <td class="booking-history-type hidden-xs">
                                <?php
                                if ($post_id) {
                                    $name = get_post_meta($post_id, 'st_first_name', true);
                                    if (!empty($name)) {
                                        $name .= " " . get_post_meta($post_id, 'st_last_name', true);
                                    }
                                    if (!$name) {
                                        $name = get_post_meta($post_id, 'st_name', true);
                                    }
                                    if (!$name) {
                                        $name = get_post_meta($post_id, 'st_email', true);
                                    }
                                    if (!$name) {
                                        $name = get_post_meta($post_id, '_billing_first_name', true) . " " . get_post_meta($post_id, '_billing_last_name', true);
                                    }
                                    echo esc_html($name);
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($item_id) {
                                    echo "<a href='" . get_the_permalink($item_id) . "' target='_blank'>" . get_the_title($item_id) . "</a>";
                                }
                                ?>
                            </td>
                            <td class="hidden-xs">
                                <?php 
                                $date = $value->check_in;
                                if ($date) echo date('d/m/Y', strtotime($date)); ?><br>
                                <i class="fa fa-long-arrow-right"></i><br>
                                <?php 
                                $date = $value->check_out;
                                if ($date) echo date('d/m/Y', strtotime($date)); 
                                ?>
                            </td>
                            <td class="hidden-xs">
                                <?php
                                if ($value->type == "normal_booking") {
                                    $total_price = get_post_meta($post_id, 'total_price', true);
                                } else {
                                    $total_price = get_post_meta($post_id, '_order_total', true);
                                    if (empty($total_price)) {
                                        global $wpdb;
                                        $querystr = "SELECT total_amount FROM " . $wpdb->prefix . "wc_orders WHERE id = '{$post_id}'";
                                        $total_price = $wpdb->get_row($querystr, OBJECT)->total_amount;
                                    }
                                }
                                $currency = get_post_meta($post_id, 'currency', true);
                                echo TravelHelper::format_money_from_db($total_price, $currency);
                                ?>
                            </td>
                            <td class="hidden-xs"><?php echo date_i18n($format, strtotime($value->created)); ?></td>
                            <td>
                                <?php
                                $data_status = STUser_f::_get_order_statuses();
                                $status = 'pending';
                                if ($value->type == "normal_booking") {
                                    $status = esc_html(get_post_meta($value->order_item_id, 'status', true));
                                } else {
                                    $status = esc_html($value->status);
                                }
                                $data_status_all = STUser_f::_get_all_order_statuses();
                                $status_string = isset($data_status[$status]) ? $data_status[$status] : (isset($data_status_all[$status]) ? $data_status_all[$status] : '');
                                $status_color  = '';
                                switch ($status) {
                                    case "pending":
                                        $status_color = '#E02020';
                                        break;
                                    case "complete":
                                    case "wc-completed":
                                        $status_color = '#10CD78';
                                        break;
                                    case "incomplete":
                                        $status_color = '#FFAD19';
                                        break;
                                    case "cancelled":
                                    case "wc-cancelled":
                                        $status_color = '#7A7A7A';
                                        break;
                                    default:
                                        $status_color = '#000';
                                }
                                echo '<span class="suser-status"><span style="color: ' . esc_attr($status_color) . '">' . esc_html($status_string) . '</span></span>';
                                if (in_array($status, ['incomplete', 'pending', 'wc-processing', 'wc-on-hold']) && (in_array('administrator', $roles) || in_array('partner', $roles))) {
                                    ?>
                                    <a data-order-id="<?php echo esc_attr($value->order_item_id); ?>" 
                                       data-id="<?php echo esc_attr($value->id); ?>" 
                                       href="#" class="suser-approve">
                                       <?php echo __('Approve', 'traveler'); ?>
                                    </a>
                                    <div class="suser-message"><div class="spinner"></div></div>
                                <?php } ?>
                            </td>
                            <td>
                                <a data-toggle="modal" data-target="#info-booking-modal"
                                   class="btn btn-xs btn-primary mt5 btn-info-booking"
                                   data-service_id="<?php echo esc_html($item_id); ?>"
                                   data-order_id="<?php echo esc_html($post_id); ?>"
                                   href="javascript:void(0);">
                                   <i class="fa fa-info-circle"></i>
                                   <span class="hidden-xs"><?php _e('Details', 'traveler'); ?></span>
                                </a>
                                <?php
                                // Show Cancel button if booking is in the future and user is Admin/Partner.
                                if (strtotime($value->check_in) > time() && (in_array('administrator', $roles) || in_array('partner', $roles))) { ?>
                                    <a data-toggle="modal" data-target="#cancel-booking-modal"
									   class="btn btn-xs btn-danger mt5 confirm-cancel-booking"
									   data-order_id="<?php echo esc_attr($value->wc_order_id); ?>"
									   data-order_encrypt="<?php echo esc_attr($value->id); ?>"
									   href="#"
									>
									   <i class="fa fa-times"></i>
									   <span class="hidden-xs"><?php _e('Cancel', 'traveler'); ?></span>
									</a>
                                <?php } ?>
                            </td>
                            <?php do_action('export_booking_item_buttons', $value->order_item_id); ?>
                        </tr>
                        <?php
                        $i++;
                    }
                } else {
                    echo '<h5>' . __('No Rental', 'traveler') . '</h5>';
                }
                ?>
            </tbody>
        </table>
    </div>
<?php }
st_paging_nav('', null, $total);
?>

<!-- Cancel Booking Modal (Identical to User-Side) -->
<div class="modal fade modal-cancel-booking" id="cancel-booking-modal" tabindex="-1" role="dialog" aria-labelledby="cancelBookingLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="<?php echo __('Close', 'traveler'); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="cancelBookingLabel"><?php echo __('Cancel Booking Information', 'traveler'); ?></h4>
            </div>
            <div class="modal-body">
                <div style="display: none;" class="overlay-form">
                    <i class="fa fa-spinner text-color"></i>
                </div>
                <div class="modal-content-inner"><!-- Loaded via AJAX using the global cancellation JS --></div>
            </div>
            <div class="modal-footer">
                <button id="" type="button" class="next btn btn-primary hidden"><?php echo __('Next', 'traveler'); ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close', 'traveler'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Booking Details Modal (Identical to User-Side) -->
<div class="modal fade modal-cancel-booking modal-info-booking" id="info-booking-modal" tabindex="-1" role="dialog" aria-labelledby="cancelBookingLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="<?php echo __('Close', 'traveler'); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="cancelBookingLabel"><?php echo __('Booking Details', 'traveler'); ?></h4>
            </div>
            <div class="modal-body">
                <div style="display: none;" class="overlay-form">
                    <i class="fa fa-spinner text-color"></i>
                </div>
                <div class="modal-content-inner"><!-- Loaded via AJAX --></div>
            </div>
        </div>
    </div>
</div>
