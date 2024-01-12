jQuery(function ($) {

	// Disable double click on some elements and add loading icon
	$('body').on('click', '#sendmail', function(event) {
		if ($(this).attr('aria-busy') == true){
			console.log('Double click blocked by scrumproject_card_only.js');
			event.preventDefault();
			return;
		}

		event.target.setAttribute('aria-busy', 'true');

	});
});