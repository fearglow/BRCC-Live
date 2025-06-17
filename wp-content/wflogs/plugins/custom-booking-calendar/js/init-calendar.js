jQuery(document).ready(function($) {
		// Function to format date to MM-DD-YYYY
		function formatDateToMMDDYYYY(date) {
			var month = ('0' + (date.getMonth() + 1)).slice(-2);
			var day = ('0' + date.getDate()).slice(-2);
			var year = date.getFullYear();
			return month + '-' + day + '-' + year;
		}

		// Get the current date
		var currentDate = new Date();
		var formattedCurrentDate = currentDate.toISOString().split('T')[0];

		// Check if there are events in the current month
		var eventsInCurrentMonth = bookingData.bookings.some(function(event) {
			var eventStartDate = new Date(event.start);
			return eventStartDate.getFullYear() === currentDate.getFullYear() && eventStartDate.getMonth() === currentDate.getMonth();
		});

		// Determine the default date for the calendar
		var defaultDate;
		if (eventsInCurrentMonth) {
			defaultDate = formattedCurrentDate;
		} else {
			var earliestBookingDate = new Date(bookingData.earliestBookingDate);
			defaultDate = earliestBookingDate > currentDate ? bookingData.earliestBookingDate : formattedCurrentDate;
		}

		// Calendar initialization
		$('#calendar').fullCalendar({
			events: function(start, end, timezone, callback) {
				var events = bookingData.bookings.map(function(event) {
					return {
						...event,
						allDay: true // Set all events to all-day
					};
				});
				callback(events);
			},
        defaultDate: defaultDate,
        height: 'auto',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,listWeek,listMonth'
        },
        showNonCurrentDates: false,
        eventRender: function(event, element, view) {
            element.find('.fc-time').remove();
            element.attr('title', event.title + ": " + event.start.format("MMM D") + " - " + event.end.format("MMM D"));
            element.addClass('custom-booking-styles');
            var statusColor = getStatusColor(event.status);
            element.css('background-color', statusColor);
            element.css('border-color', statusColor);

            // Set data-event-id attribute for each event element
            element.attr('data-event-id', event.id);

            // Customize for list views to show "stay" information
            if (view.type === 'listWeek' || view.type === 'listMonth') {
                var stayInfo = $('<div class="custom-stay-info"></div>');
                stayInfo.text(event.status);
                element.find('.fc-list-item-time').empty().append(stayInfo);
            }
        },
        views: {
            month: {
                eventLimit: 5,
                buttonText: 'Month Overview'
            },
            listDay: {
                buttonText: 'Day'
            },
            listWeek: {
                buttonText: 'Week List'
            },
            listMonth: {
                buttonText: 'Month List'
            }
        },
        eventClick: function(calEvent, jsEvent, view) {
            var modal = $('#bookingDetailsModal');
            var statusColor = getStatusColor(calEvent.status);

            modal.find('.modal-header').css('background-color', statusColor);
            modal.find('.modal-header').css('color', calEvent.status === 'Cash Payment Due' ? '#fff' : '#333');

            modal.find('.modal-body').html('');

            // Dynamically create the content - add additional fields from bookings query
            var content = '<p><strong>Site:</strong> ' + calEvent.site + '</p>' +
                '<p><strong>Customer:</strong> ' + calEvent.customer + '</p>' +
                '<p><strong>Stay:</strong> ' + calEvent.stay + '</p>' +
                '<p><strong>Adults:</strong> ' + calEvent.adult_number + '</p>' +
                '<p><strong>Children:</strong> ' + calEvent.child_number + '</p>';

            // Conditionally add guest details and equipment details
            if (calEvent.booking_post_type === 'st_activity') {
                content += '<p><strong>Guest Details:</strong> ' + calEvent.guest_details + '</p>';
				content += '<p><strong>Start Time:</strong> ' + calEvent.starttime + '</p>';
            }
            if (calEvent.booking_post_type !== 'st_activity') {
                if (calEvent.equipment_type) {
                    content += '<p><strong>Equipment Type:</strong> ' + calEvent.equipment_type + '</p>';
                }
                if (calEvent.length_ft) {
                    content += '<p><strong>Length (ft):</strong> ' + calEvent.length_ft + '</p>';
                }
                if (calEvent.slide_outs) {
                    content += '<p><strong>Slide-Outs:</strong> ' + calEvent.slide_outs + '</p>';
                }
            }
            content += '<p><strong>Status:</strong> <span style="color: ' + statusColor + ';">' + getStatusText(calEvent.status) + '</span></p>';

            if (calEvent.status === "Cash Payment Due") {
                content += '<p><strong>Total Order:</strong> $' + parseFloat(calEvent.total_order).toFixed(2) + '</p>';
            }

            modal.find('.modal-body').append(content);

            // Conditionally add the "Mark as Paid" button if it's a cash payment due
            if (calEvent.status === "Cash Payment Due") {
                var cashPaymentButton = $('<button class="btn btn-secondary btn-sm">Mark as Paid</button>');
                cashPaymentButton.on('click', function(e) {
                    e.stopPropagation();
                    markAsPaid(calEvent.id);
                    modal.modal('hide');
                });
                modal.find('.modal-body').append(cashPaymentButton);
            }

            modal.modal('show');
        }
    });

    // New code for handling check-in and check-out clicks
    $('.check-in, .check-out').on('click', function() {
        var bookingId = $(this).data('booking-id');
        var booking = bookingData.bookings.find(function(b) {
            return b.id == bookingId;
        });

        if (booking) {
            var statusColor = getStatusColor(booking.status);
            var guestDetails = '<p><strong>Name:</strong> ' + booking.customer + '</p>' +
                '<p><strong>Email:</strong> ' + booking.email + '</p>' +
                '<p><strong>Phone:</strong> ' + booking.phone + '</p>' +
                '<p><strong>Check-In:</strong> ' + formatDateToMMDDYYYY(new Date(booking.start)) + '</p>' +
                '<p><strong>Check-Out:</strong> ' + formatDateToMMDDYYYY(new Date(booking.display_end)) + '</p>' +
                '<p><strong>Adults:</strong> ' + booking.adult_number + '</p>' +
                '<p><strong>Children:</strong> ' + booking.child_number + '</p>';

            // Conditionally add guest details and equipment details
            if (booking.booking_post_type === 'st_activity') {
                guestDetails += '<p><strong>Guest Details:</strong> ' + booking.guest_details + '</p>';
				guestDetails += '<p><strong>Start Time:</strong> ' + calEvent.starttime + '</p>';
            }
            if (booking.booking_post_type !== 'st_activity') {
                if (booking.equipment_type) {
                    guestDetails += '<p><strong>Equipment Type:</strong> ' + booking.equipment_type + '</p>';
                }
                if (booking.length_ft) {
                    guestDetails += '<p><strong>Length (ft):</strong> ' + booking.length_ft + '</p>';
                }
                if (booking.slide_outs) {
                    guestDetails += '<p><strong>Slide-Outs:</strong> ' + booking.slide_outs + '</p>';
                }
            }
            guestDetails += '<p><strong>Total Order:</strong> $' + parseFloat(booking.total_order).toFixed(2) + '</p>' +
                '<p><strong>Status:</strong> <span style="color: ' + statusColor + ';">' + getStatusText(booking.status) + '</span></p>';

            // Conditionally add the "Mark as Paid" button if it's a cash payment due
            if (booking.status === "Cash Payment Due") {
                guestDetails += '<button class="btn btn-secondary btn-sm" id="markAsPaidButton">Mark as Paid</button>';
            }

            $('#guestInfoModal .guest-details').html(guestDetails);
            $('#guestInfoModal').modal('show');

            // Attach the click event to the Mark as Paid button
            $('#markAsPaidButton').on('click', function(e) {
                e.stopPropagation();
                markAsPaid(booking.id);
                $('#guestInfoModal').modal('hide');
            });
        }
    });

    function refreshCalendarEvents() {
        $.ajax({
            url: bookingData.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fetch_updated_bookings',
                nonce: bookingData.fetch_updated_bookings_nonce
            },
            success: function(response) {
                if (response.success) {
                    // Assuming your calendar events are directly tied to bookingData.bookings
                    bookingData.bookings = response.data;

                    // Now refetch the calendar events to reflect the updated bookings
                    $('#calendar').fullCalendar('refetchEvents');
                    refreshMobileBookings($('#booking-date').val()); // Refresh mobile bookings
                } else {
                    alert('Failed to fetch updated bookings.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Failed to communicate with the server. Please try again.');
            }
        });
    }

    // Function to mark a booking as paid
    function markAsPaid(bookingId) {
        $.ajax({
            url: bookingData.ajax_url,
            type: 'POST',
            data: {
                action: 'mark_as_paid',
                booking_id: bookingId,
                nonce: bookingData.nonce
            },
            success: function(response) {
                console.log("AJAX call succeeded.", response); // Add this line for debugging
                if (response.success) {
                    refreshCalendarEvents();
                } else {
                    alert('There was an error marking the booking as paid.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Failed to communicate with the server. Please try again.');
            }
        });
    }

    function getStatusText(status) {
        switch (status) {
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

    function getStatusColor(status) {
        switch (status) {
            case "pending":
                return '#E02020'; // Red
            case "complete":
            case "wc-completed":
                return '#10CD78'; // Green
            case "incomplete":
                return '#FFAD19'; // Orange
            case "cancelled":
            case "wc-cancelled":
                return '#7A7A7A'; // Grey
            case "Cash Payment Due":
                return '#E02020'; // Red - for cash payments due
            case "Paid Cash":
                return '#10CD78'; // Green
            default:
                return '#000000'; // Black for unknown status
        }
    }

    function refreshMobileBookings(selectedDate) {
        var checkInsToday = bookingData.bookings.filter(function(booking) {
            return new Date(booking.start).toISOString().split('T')[0] === selectedDate;
        });

        var checkOutsToday = bookingData.bookings.filter(function(booking) {
            return new Date(booking.display_end).toISOString().split('T')[0] === selectedDate;
        });

        // Create a Date object from the selectedDate string
        var selectedDateObj = new Date(selectedDate);
        // Add one day to the Date object
        selectedDateObj.setDate(selectedDateObj.getDate() + 1);

        var checkInHtml = checkInsToday.map(function(booking) {
            var statusColor = getStatusColor(booking.status);
            return '<div class="check-in" data-booking-id="' + booking.id + '" data-status="' + booking.status + '" style="background-color:' + statusColor + ';">' +
                '<p>' + booking.customer + ' - Site ' + booking.site + ' - <span class="status-text">' + getStatusText(booking.status) + '</span></p>' +
                '</div>';
        }).join('');

        var checkOutHtml = checkOutsToday.map(function(booking) {
            var statusColor = getStatusColor(booking.status);
            return '<div class="check-out" data-booking-id="' + booking.id + '" data-status="' + booking.status + '" style="background-color:' + statusColor + ';">' +
                '<p>' + booking.customer + ' - Site ' + booking.site + ' - <span class="status-text">' + getStatusText(booking.status) + '</span></p>' +
                '</div>';
        }).join('');

        $('.check-ins-today').html('<h3>Check-Ins on ' + formatDateToMMDDYYYY(selectedDateObj) + '</h3>' + checkInHtml);
        $('.check-outs-today').html('<h3>Check-Outs on ' + formatDateToMMDDYYYY(selectedDateObj) + '</h3>' + checkOutHtml);

        // Reattach the click event handlers
        $('.check-in, .check-out').on('click', function() {
            var bookingId = $(this).data('booking-id');
            var booking = bookingData.bookings.find(function(b) {
                return b.id == bookingId;
            });

            if (booking) {
                var statusColor = getStatusColor(booking.status);
                var guestDetails = '<p><strong>Name:</strong> ' + booking.customer + '</p>' +
                    '<p><strong>Email:</strong> ' + booking.email + '</p>' +
                    '<p><strong>Phone:</strong> ' + booking.phone + '</p>' +
                    '<p><strong>Check-In:</strong> ' + formatDateToMMDDYYYY(new Date(booking.start)) + '</p>' +
                    '<p><strong>Check-Out:</strong> ' + formatDateToMMDDYYYY(new Date(booking.display_end)) + '</p>' +
                    '<p><strong>Adults:</strong> ' + booking.adult_number + '</p>' +
                    '<p><strong>Children:</strong> ' + booking.child_number + '</p>';

                // Conditionally add guest details and equipment details
                if (booking.booking_post_type === 'st_activity') {
                    guestDetails += '<p><strong>Guest Details:</strong> ' + booking.guest_details + '</p>';
					guestDetails += '<p><strong>Start Time:</strong> ' + booking.starttime + '</p>';
                }
                if (booking.booking_post_type !== 'st_activity') {
                    if (booking.equipment_type) {
                        guestDetails += '<p><strong>Equipment Type:</strong> ' + booking.equipment_type + '</p>';
                    }
                    if (booking.length_ft) {
                        guestDetails += '<p><strong>Length (ft):</strong> ' + booking.length_ft + '</p>';
                    }
                    if (booking.slide_outs) {
                        guestDetails += '<p><strong>Slide-Outs:</strong> ' + booking.slide_outs + '</p>';
                    }
                }
                guestDetails += '<p><strong>Total Order:</strong> $' + parseFloat(booking.total_order).toFixed(2) + '</p>' +
                    '<p><strong>Status:</strong> <span style="color: ' + statusColor + ';">' + getStatusText(booking.status) + '</span></p>';

                // Conditionally add the "Mark as Paid" button if it's a cash payment due
                if (booking.status === "Cash Payment Due") {
                    guestDetails += '<button class="btn btn-secondary btn-sm" id="markAsPaidButton">Mark as Paid</button>';
                }

                $('#guestInfoModal .guest-details').html(guestDetails);
                $('#guestInfoModal').modal('show');

                // Attach the click event to the Mark as Paid button
                $('#markAsPaidButton').on('click', function(e) {
                    e.stopPropagation();
                    markAsPaid(booking.id);
                    $('#guestInfoModal').modal('hide');
                });
            }
        });

        applyStatusColors(); // Apply status colors again after refresh
    }

    // Event listener for date selection
    $('#booking-date').on('change', function() {
        var selectedDate = $(this).val();
        refreshMobileBookings(selectedDate);
    });

    // Initial load with today's date
    var today = new Date().toISOString().split('T')[0];
    $('#booking-date').val(today);
    refreshMobileBookings(today);

    // Function to apply status colors and text
    function applyStatusColors() {
        $('.check-in, .check-out').each(function() {
            var status = $(this).data('status');
            var statusColor = getStatusColor(status);
            $(this).css('background-color', statusColor);
            $(this).find('.status-text').text(getStatusText(status));
        });
    }

    applyStatusColors(); // Apply status colors on initial load

    // Use mouseenter and mouseleave for custom hover effect
    $(document).on('mouseenter', '.fc-event', function() {
        var eventId = $(this).attr('data-event-id');
        $('.fc-event[data-event-id="' + eventId + '"]').addClass('event-hover');
    }).on('mouseleave', '.fc-event', function() {
        var eventId = $(this).attr('data-event-id');
        $('.fc-event[data-event-id="' + eventId + '"]').removeClass('event-hover');
    });
});
