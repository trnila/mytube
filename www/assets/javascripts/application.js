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

// editable elements setup
$(document).ready(function() {
	$(".editable").editable({
		success: function(response) {
			if(response && response.error) {
				return response.error;
			}
		}
	});
});

// save active tab to location
$(document).ready(function() {
	if(document.location.hash.length) {
		var show = $('a[href="' + document.location.hash + '"]').click();
	}

	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var el = $(e.target);
		console.log(el[0]);
		console.log(el.closest('ul').find('li:first a')[0]);

		if(!el.is(el.closest('ul').find('li:first a'))) {
			document.location.hash = $(e.target).attr('href');
		} else {
			document.location.hash = '';
		}
	});
});

// .autogrow
$(document).ready(function() {
	$("textarea.autogrow").autoGrow();
});