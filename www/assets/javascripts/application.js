"use strict";

$(function () {
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

// Data confirms
$(document).on('click', '[data-confirm]', function() {
	return confirm($(this).data('confirm'));
});

// Thumbnails changes
$(document).on('mouseenter', '.video img', function() {
	var video = $(this).parent();

	var timer = setInterval(function() {
		var img = video.find("img:visible");
		var next = img.next('img').length ? img.next('img') : img.parent().find("img").first();

		img.hide();
		next.show();
	}, 350);

	$(this).mouseleave(function() {
		clearInterval(timer);
		video.find("img").hide();
		video.find("img:first").show();
	});
});

$(document).ready(function() {
	$(".editable").editable({
		success: function(response) {
			if(response && response.error) {
				return response.error;
			}
		}
	});
});