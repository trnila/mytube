"use strict";

$(function () {
	$.nette.ext('history').cache = false;
	$.nette.init();
});

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