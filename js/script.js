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
	});

	$('.btn-hide').click(function () {
		$.ajax({
			url: 'ajax.php',
			data: {
				action: 'hide',
				extension: extensionName,
				scope: $(this).data('scope'),
				value: $(this).data('string')
			}
		})
			.done(function (data) {
				if (data === '')
				{
					window.location.reload();
				}
			});
	});
});
