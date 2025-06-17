<div class="st-dashboard-stat st-month-madison st-dashboard-new st-month-1">
    <h1><?php _e('Welcome to the Host Dashboard', 'traveler'); ?></h1>

</div><div class="calendar-container" style="margin-top: 30px;">
    <?php echo do_shortcode('[custom_booking_calendar]'); ?>
	

</div>
<!-- Booking Details Modal -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1" role="dialog" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bookingDetailsModalLabel">Booking Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Dynamic content will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>