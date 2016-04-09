$(document).ready(function () {
	$('.toggle').click(function () {
		var selector = $(this).data('toggle');

		if (!selector) {
			return;
		}

		if (selector.charAt(0) !== '.' && selector.charAt(0) !== '#')
		{
			selector = '#' + selector;
		}

		$(selector).toggleClass('hide');
	})
});
