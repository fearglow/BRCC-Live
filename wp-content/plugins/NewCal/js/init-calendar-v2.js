jQuery(document).ready(function($) {
    var month = parseInt($('#custom-calendar-v2').data('month'));
    var year  = parseInt($('#custom-calendar-v2').data('year'));

    // 1) Load when page first loads
    loadCalendar(month, year);

    // 2) Prev/Next
    $('#prev-month').on('click', function() {
        month--;
        if (month < 1) {
            month = 12;
            year--;
        }
        loadCalendar(month, year);
    });
    $('#next-month').on('click', function() {
        month++;
        if (month > 12) {
            month = 1;
            year++;
        }
        loadCalendar(month, year);
    });

    // 3) loadCalendar AJAX
    var totalSites = 0;
    function loadCalendar(m, y) {
        $.ajax({
            url: bookingData.ajax_url,
            method: 'POST',
            data: {
                action: 'load_calendar_v2',
                nonce: bookingData.nonce,
                month: m,
                year: y
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Replace table HTML
                    $('#custom-calendar-v2').html(response.data.html);

                    // Replace debug info
                    $('#debug-entries').html(response.data.debug_html || '<p>No Debug Info</p>');

                    // Update month/year header
                    updateCalendarHeader(m, y);

                    // Store total sites for availability calc
                    totalSites = parseInt(response.data.total_sites) || 0;

                    // Render calendar pills
                    renderBookings(response.data.bookings, m, y);

                    // Render table view
                    renderBookingsTable(response.data.bookings);

                    // Highlight today
                    highlightToday(m, y);

                    // Update availability counts
                    updateAvailability(response.data.bookings, m, y, totalSites);
                } else {
                    console.log('No data returned or success=false.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    // 4) Insert booking pills
    function renderBookings(bookings, m, y) {
        if (!Array.isArray(bookings)) return;

        // Reset cells
        $('.calendar-row .day-cell').addClass('available-cell').removeClass('booked-cell');

        bookings.forEach(function(booking) {
            var allowedStatuses = ['complete', 'wc-completed', 'wp-completed'];
            if (allowedStatuses.indexOf(booking.status) === -1) return;
            var siteKey = (booking.site || '').trim();
            var row = $('.calendar-row[data-site="' + siteKey + '"]');
            if (!row.length) return;

            // Convert date strings to local dates
            var checkIn       = new Date((booking.start || '').replace(/-/g, '/'));
            var displayEnd    = new Date((booking.display_end || '').replace(/-/g, '/'));
            var checkOutPlus  = new Date((booking.end || '').replace(/-/g, '/'));
            if (isNaN(checkIn) || isNaN(displayEnd)) return;

            var startDay = checkIn.getDate();
            var endDay   = displayEnd.getDate();

            // If it starts in previous month, clamp to day=1
            var isPrevMonth = (checkIn.getMonth() + 1 < m) || (checkIn.getFullYear() < y);
            if (isPrevMonth) {
                startDay = 1;
            }
            // If it ends in next month, clamp to last day
            var isNextMonth = (checkOutPlus.getMonth() + 1 > m) || (checkOutPlus.getFullYear() > y);
            if (isNextMonth) {
                endDay = new Date(y, m, 0).getDate(); // last day
            }
            var spanDays = endDay - startDay + 1;
            if (spanDays < 1) return;

            // Pill
            var pill = $('<div class="booking-pill"></div>')
                .text(booking.customer)
                .attr('data-booking-id', booking.id)
                .attr('data-status', booking.status)
                .attr('data-customer', booking.customer)
                .attr('data-site', booking.site)
                .attr('data-stay', booking.stay || '')
                .attr('data-adult-number', booking.adult_number || '0')
                .attr('data-child-number', booking.child_number || '0')
                .attr('data-email', booking.email || '')
                .attr('data-phone', booking.phone || '')
                .attr('data-total-order', booking.total_order || '0')
                .attr('data-booking-post-type', booking.booking_post_type || '')
                .attr('data-equipment-type', booking.equipment_type || '')
                .attr('data-length-ft', booking.length_ft || '')
                .attr('data-slide-outs', booking.slide_outs || '')
                .attr('data-starttime', booking.starttime || '')
                .attr('data-display-end', booking.display_end || '');

            // Color
            pill.css('background-color', getStatusColor(booking.status));

            // Arrows
            if (isPrevMonth) pill.prepend('<span class="arrow-left">←</span>');
            if (isNextMonth) pill.append('<span class="arrow-right">→</span>');

            // Place pill
            var startCell = row.find('.day-cell[data-day="' + startDay + '"]');

            if (isNextMonth) {
                startCell.attr('colspan', spanDays)
                    .append(pill)
                    .removeClass('available-cell')
                    .addClass('booked-cell');
                for (var d = startDay + 1; d <= endDay; d++) {
                    row.find('.day-cell[data-day="' + d + '"]').remove();
                }
            } else {
                startCell.attr('colspan', Math.max(spanDays - 1, 1))
                    .append(pill)
                    .removeClass('available-cell')
                    .addClass('booked-cell');
                for (var d = startDay + 1; d < endDay; d++) {
                    row.find('.day-cell[data-day="' + d + '"]').remove();
                }

                var endCell = row.find('.day-cell[data-day="' + endDay + '"]');
                var $indicator = $('<div class="checkout-half-pill"></div>')
                    .css('background-color', getStatusColor(booking.status));
                endCell.addClass('checkout-cell booked-cell')
                    .removeClass('available-cell')
                    .append($indicator);
            }

            // Final day marker improved with partial color
        });
    }

    // 5) Pill click => custom modal
    $(document).on('click', '.booking-pill', function() {
        var $this = $(this);

        // Extract data
        var bookingId = $this.data('booking-id');
        var status    = $this.data('status');
        var customer  = $this.data('customer');
        var site      = $this.data('site');
        var stay      = $this.data('stay');
        var adultNum  = $this.data('adult-number');
        var childNum  = $this.data('child-number');
        var email     = $this.data('email');
        var phone     = $this.data('phone');
        var totalOrder= $this.data('total-order');
        var postType  = $this.data('booking-post-type');
        var eqType    = $this.data('equipment-type');
        var lengthFt  = $this.data('length-ft');
        var slideOuts = $this.data('slide-outs');
        var startTime = $this.data('starttime') || '';
        var dispEnd   = $this.data('display-end') || '';

        // Fill the modal
        var headerColor = getStatusColor(status);
        $('#modalHeader').css('background-color', headerColor);

        var bodyHtml = '';
        bodyHtml += '<p><strong>Booking ID:</strong> ' + bookingId + '</p>';
        bodyHtml += '<p><strong>Site:</strong> ' + site + '</p>';
        bodyHtml += '<p><strong>Customer:</strong> ' + customer + '</p>';
        bodyHtml += '<p><strong>Stay:</strong> ' + stay + '</p>';
        bodyHtml += '<p><strong>Adults:</strong> ' + adultNum + '</p>';
        bodyHtml += '<p><strong>Children:</strong> ' + childNum + '</p>';
        if (postType !== 'st_activity') {
            if (eqType) {
                bodyHtml += '<p><strong>Equipment Type:</strong> ' + eqType + '</p>';
            }
            if (lengthFt) {
                bodyHtml += '<p><strong>Length (ft):</strong> ' + lengthFt + '</p>';
            }
            if (slideOuts) {
                bodyHtml += '<p><strong>Slide-Outs:</strong> ' + slideOuts + '</p>';
            }
        } else {
            if (startTime) {
                bodyHtml += '<p><strong>Start Time:</strong> ' + startTime + '</p>';
            }
        }
        bodyHtml += '<p><strong>Status:</strong> ' + getStatusText(status) + '</p>';
        bodyHtml += '<p><strong>Total Order:</strong> $' + parseFloat(totalOrder).toFixed(2) + '</p>';
        bodyHtml += '<p><strong>Email:</strong> ' + email + '</p>';
        bodyHtml += '<p><strong>Phone:</strong> ' + phone + '</p>';

        // Mark as Paid if needed
        if (status === 'Cash Payment Due' || status === 'incomplete') {
            bodyHtml += '<button class="calendar-modal-paybtn" id="markAsPaidButton">Mark as Paid</button>';
        }

        $('#modalBody').html(bodyHtml);

        // Show the modal
        $('#bookingModal').fadeIn(200);

        // Mark as Paid handler
        $('#markAsPaidButton').off('click').on('click', function(e) {
            e.stopPropagation();
            markAsPaid(bookingId);
            closeModal(); // close after marking
        });
    });

    // 6) Modal close
    $('#modalClose').on('click', function() {
        closeModal();
    });
    function closeModal() {
        $('#bookingModal').fadeOut(200);
    }

    // 7) Mark as Paid
    function markAsPaid(bookingId) {
        $.ajax({
            url: bookingData.ajax_url,
            method: 'POST',
            data: {
                action: 'mark_as_paid',
                nonce: bookingData.nonce,
                booking_id: bookingId
            },
            success: function(res) {
                if (res.success) {
                    loadCalendar(month, year); // reload
                } else {
                    alert('Error: ' + (res.data && res.data.message ? res.data.message : 'Could not update.'));
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    }

    // 8) Update header
    function updateCalendarHeader(m, y) {
        var months = ["January","February","March","April","May","June",
                      "July","August","September","October","November","December"];
        $('#calendar-header').text(months[m - 1] + ' ' + y);
    }

    // 9) highlightToday if matches displayed month/year
    function highlightToday(m, y) {
        var now = new Date();
        if ((now.getMonth() + 1) === m && now.getFullYear() === y) {
            var d = now.getDate();
            $('#custom-calendar-v2')
                .find('.day-cell[data-day="' + d + '"]')
                .addClass('today');
        }
    }

    // Render table listing of bookings
    function renderBookingsTable(bookings) {
        if (!Array.isArray(bookings)) return;
        var $container = $('#booking-table-container');
        if (!$container.length) return;

        var $table = $('<table class="booking-list-table"></table>');
        var $thead = $('<thead><tr><th>Site</th><th>Customer</th><th>Check-In</th><th>Check-Out</th><th>Status</th></tr></thead>');
        var $tbody = $('<tbody></tbody>');

        bookings.forEach(function(b) {
            var $tr = $('<tr></tr>');
            $tr.append('<td>' + (b.site || '') + '</td>');
            $tr.append('<td>' + (b.customer || '') + '</td>');
            $tr.append('<td>' + (b.start || '') + '</td>');
            $tr.append('<td>' + (b.display_end || '') + '</td>');
            var $status = $('<td></td>')
                .text(getStatusText(b.status))
                .css('background-color', getStatusColor(b.status))
                .css('color', '#fff');
            $tr.append($status);
            $tbody.append($tr);
        });

        $table.append($thead).append($tbody);
        $container.empty().append($table);
    }

    // 10) Update availability counts per day
    function updateAvailability(bookings, m, y, total) {
        if (!Array.isArray(bookings) || !total) return;
        var daysInMonth = new Date(y, m, 0).getDate();
        var counts = {};
        for (var d = 1; d <= daysInMonth; d++) {
            counts[d] = 0;
        }
        bookings.forEach(function(b) {
            var allowedStatuses = ['complete', 'wc-completed', 'wp-completed'];
            if (allowedStatuses.indexOf(b.status) === -1) return;
            var start = new Date((b.start || '').replace(/-/g, '/'));
            var end   = new Date((b.end   || '').replace(/-/g, '/'));
            if (isNaN(start) || isNaN(end)) return;

            var startDay = start.getDate();
            var endDay   = end.getDate();
            var isPrevMonth = (start.getMonth() + 1 < m) || (start.getFullYear() < y);
            if (isPrevMonth) startDay = 1;
            var isNextMonth = (end.getMonth() + 1 > m) || (end.getFullYear() > y);
            if (isNextMonth) endDay = daysInMonth;

            for (var d = startDay; d <= endDay; d++) {
                counts[d]++;
            }
        });

        for (var d = 1; d <= daysInMonth; d++) {
            var avail = total - (counts[d] || 0);
            var cell = $('.availability-row .availability-cell[data-day="' + d + '"]');
            cell.text(avail);
            cell.toggleClass('none-available', avail <= 0);
        }
    }

    // Status text
    function getStatusText(s) {
        switch(s) {
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
                return s || 'Unknown';
        }
    }

    // Status color
    function getStatusColor(s) {
        switch(s) {
            case "pending":
                return "#E02020";
            case "complete":
            case "wc-completed":
            case "Paid Cash":
                return "#10CD78";
            case "incomplete":
                return "#FFAD19";
            case "cancelled":
            case "wc-cancelled":
                return "#7A7A7A";
            case "Cash Payment Due":
                return "#E02020";
            default:
                return "#000000";
        }
    }
});
