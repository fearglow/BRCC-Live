jQuery(document).ready(function($) {
    console.log(adminMap);

    function initializeMapAndMarkers() {
        $('#imageMap').empty(); // Clear existing markers before initializing

        adminMap.rentals.forEach(function(rental) {
            const posX = parseFloat(rental.posX); // Assume posX and posY are stored as percentages
            const posY = parseFloat(rental.posY);

            const markerHtml = `<div class="rental-marker" data-id="${rental.post_id}" style="left: ${posX}%; top: ${posY}%; position: absolute;">
                <img src="${adminMap.mapIconUrl}" alt="Marker" style="width: 30px; height: 30px;">
                <span>${rental.post_name}</span>
            </div>`;

            $('#imageMap').append(markerHtml);
        });

        makeMarkersDraggable();
    }

    function makeMarkersDraggable() {
        $('.rental-marker').draggable({
            containment: "#imageMap",
            stop: function(event, ui) {
                var marker = $(this);
                var mapWidth = $('#imageMap').width();
                var mapHeight = $('#imageMap').height();
                var posX = (ui.position.left / mapWidth) * 100; // Calculate percentage
                var posY = (ui.position.top / mapHeight) * 100; // Calculate percentage
                var rentalId = marker.data('id');

                saveMarkerPosition(rentalId, posX, posY); // Save new position as percentage
            }
        });
    }

    function saveMarkerPosition(rentalId, posX, posY) {
        jQuery.ajax({
            url: adminMap.ajax_url,
            type: 'POST',
            data: {
                action: 'save_rental_location',
                nonce: adminMap.nonce,
                posX: posX,
                posY: posY,
                postID: rentalId
            },
            success: function(response) {
                console.log('Position updated:', response);
            },
            error: function(error) {
                console.error('Error updating position:', error);
            }
        });
    }

    $('#imageMap').droppable({
        accept: ".rental, .rental-marker",
		containment: '#imageMap', // Make sure this matches the ID of your map container
        drop: function(event, ui) {
            var rentalId = ui.draggable.data('id');
            var mapWidth = $(this).width();
            var mapHeight = $(this).height();
            var posX = (ui.offset.left - $(this).offset().left) / mapWidth * 100; // Calculate percentage
            var posY = (ui.offset.top - $(this).offset().top) / mapHeight * 100; // Calculate percentage
            var existingMarker = $('.rental-marker[data-id="' + rentalId + '"]');

            if (!existingMarker.length) {
                var markerHtml = `<div class="rental-marker" data-id="${rentalId}" style="left: ${posX}%; top: ${posY}%; position: absolute;">
                    <img src="${adminMap.mapIconUrl}" alt="Marker">
                    <span>${ui.draggable.text()}</span>
                </div>`;
                $('#imageMap').append(markerHtml);
                makeMarkersDraggable(); // Re-initialize draggable functionality for new marker
            }

            saveMarkerPosition(rentalId, posX, posY); // Save or update position
        }
    });

    $('.rental').draggable({
        helper: 'clone',
        revert: 'invalid'
    });

    initializeMapAndMarkers();

    // Clear All Locations button functionality
    $('#clearMarkers').on('click', function() {
        if (confirm('Are you sure you want to clear all locations?')) {
            jQuery.ajax({
                url: adminMap.ajax_url,
                type: 'POST',
                data: {
                    action: 'clear_all_rental_locations',
                    nonce: adminMap.nonce
                },
                success: function(response) {
                    console.log(response);
                    location.reload(); // Reload to reflect changes
                },
                error: function(error) {
                    console.error('Error clearing positions:', error);
                }
            });
        }
    });



    // Update marker positions on window resize
    function updateMarkerPositionsOnResize() {
        var mapWidth = $('#imageMap').width();
        var mapHeight = $('#imageMap').height();

        $('.rental-marker').each(function() {
            var marker = $(this);
            var posXPercent = parseFloat(marker.data('original-posx'));
            var posYPercent = parseFloat(marker.data('original-posy'));
            var posX = (mapWidth * posXPercent) / 100;
            var posY = (mapHeight * posYPercent) / 100;

            marker.css({ left: posX + 'px', top: posY + 'px' });
        });
    }

    $(window).resize(function() {
        updateMarkerPositionsOnResize();
    });
	
});	