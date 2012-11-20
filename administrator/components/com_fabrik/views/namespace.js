(function () {
	if (typeOf(window.FabrikAdmin) === 'object') {
		return;
	}
	FabrikAdmin = {};
	// Various Joomla element plugins used to control JForm elements
	FabrikAdmin.model = {'fields': {'fabriktable': {}, 'element': {}}};
}());

if (typeof(jQuery) !== 'undefined') {
	// Relay radio button group clicks for content added via ajax calls
	(function ($) {
		$(document).on('click', '.btn-group label:not(.active)', null, function (event) {
			var label = $(this);
			var input = $('#' + label.attr('for'));
			if (!input.prop('checked')) {
				label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
				if (input.val() === '') {
					label.addClass('active btn-primary');
				} else if (input.val() === 0) {
					label.addClass('active btn-danger');
				} else {
					label.addClass('active btn-success');
				}
				input.prop('checked', true);
			}
		});
	})(jQuery);
}