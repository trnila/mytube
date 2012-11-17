"use strict";

$(function () {
	$.nette.ext('history').cache = false;
	$.nette.init();
});

$(function() {
	var timer;
	$("#video-search").keyup(function(evt) {
		var self = $(this);
		clearInterval(timer);

		timer = setTimeout(function() {
			var query = self.val();
			if(!query.length < 1) {
				return;
			}

			$.nette.ajax({}, self.closest('form'), evt);
			
		}, 100);
	});
});