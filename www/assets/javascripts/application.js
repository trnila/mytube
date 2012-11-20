"use strict";

$(function () {
	$.nette.ext('history').cache = false;
	$.nette.init();
});

// Searching
$(function() {
	var timer;
	$("#video-search input").keyup(function(evt) {
		var self = $(this);
		clearInterval(timer);

		timer = setTimeout(function() {
			self.addClass('loading');

			$.nette.ajax({}, self.closest('form'), evt).done(function() {
				self.removeClass('loading');
			});
			
		}, 100);
	});
});

// show map on profile
$(document).ready(function() {
	$("[data-map-location]").each(function() {
		var el = $(this);
		var geocoder = new google.maps.Geocoder();
		var mapOptions = {
			zoom: 8,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(this, mapOptions);

		// Geocode address
		geocoder.geocode({'address': el.attr('data-map-location')}, function(results, status) {
			if(status == google.maps.GeocoderStatus.OK) {
				map.setCenter(results[0].geometry.location);
				var marker = new google.maps.Marker({
					map: map,
					position: results[0].geometry.location,
					icon: el.attr('data-map-marker') ? el.attr('data-map-marker') : null
				});
			}
			else {
				el.fadeOut();
			}
		});
	});
});


$(document).on('click', '[data-confirm]', function() {
	return confirm($(this).data('confirm'));
});