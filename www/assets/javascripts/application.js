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
})

$.editable.types.text.element = function(settings, original) {
	var input = $('<input type="text" />');
	if (settings.width  != 'none') { input.attr('width', settings.width);  }
	if (settings.height != 'none') { input.attr('height', settings.height); }
	/* https://bugzilla.mozilla.org/show_bug.cgi?id=236791 */
	//input[0].setAttribute('autocomplete','off');
	input.attr('autocomplete','off');
	$(this).append(input);
	return(input);
};

//$.editable.types.


function editable(el, target, settings) {
	settings = $.extend({
		indicator : 'Ukládam...',
		tooltip   : 'Klikni pro úpravu',
	//	submit: '<button type="submit" class="btn btn-success">Uložit</button>',
	//	cancel: '<button class="btn btn-danger">Zrušit</button>',
		ajaxoptions: {
			dataType: 'json'
		},
		callback: function(response, settings) {
			if(response.error) {
				var title = el.attr('title');
				el.attr('title', null);

				el.popover('destroy');
				el.popover({
					animation: true,
					title: 'Chyba',
					placement: 'bottom',
					delay: 100,
					content: response.error
				});
				el.html(response.original);
				el.click();
				el.attr('title', title);
				return;
			}

			el.popover('destroy');

			el.html(response.value);
		}
	}, settings);

	el.editable(target, settings);
};

$(document).ready(function() {
	$(".editable").each(function() {
		var el = $(this);

		editable(el, el.closest('[data-editable-action]').attr('data-editable-action'), {
			type: el.data('editable-type') || 'text',
			data: el.data('editable-data'),
			name: el.data('editable-name'),
			submitdata: {
				id: el.closest('[data-editable-id]').data('editable-id')
			}
		});
	});
});