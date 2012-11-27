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

function editable(el, target, settings) {
	settings = $.extend({
		indicator : 'Ukládam...',
		tooltip   : 'Klikni pro úpravu',
		placeholder: 'Klikni pro úpravu',
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
		var withButtons = typeof(el.data('editable-buttons')) != 'undefined';
		var data = el.data('editable-data');
		var type = el.data('editable-type') || 'text';

		// Replace <br> with newline if its textarea
		if(!data && type == 'textarea') {
			data = function(value) {
				return value.replace(/\s*<br[\s\/]?>\s*/gi, '\n');
			};
		}

		editable(el, el.closest('[data-editable-action]').attr('data-editable-action'), {
			type: type,
			data: data,
			name: el.data('editable-name'),
			submitdata: {
				id: el.closest('[data-editable-id]').data('editable-id')
			},
			submit: withButtons ? '<button type="submit" class="btn btn-success">Uložit</button>' : null,
			cancel: withButtons ? '<button class="btn btn-danger">Zrušit</button>' : null
		});
	});
});
