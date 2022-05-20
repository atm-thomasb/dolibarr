jQuery(function ($) {
	// using $ here will be safely even jQuery.noConflict() will be enabled

	$('.toggle-more-btn').on('click', function (){
		if($(this).data('target') != undefined){

			let lineId = $(this).data('target');
			let childrenLines = $('.toggle-line-display[data-parent=' + lineId + ']');
			let classToAdd = '--open';
			if($(this).hasClass(classToAdd)){
				childrenLines.removeClass(classToAdd);
				$(this).removeClass(classToAdd);
				$(this).find('.fa').removeClass('fa-minus-square').addClass('fa-plus-square');
			}else{
				$(this).addClass(classToAdd);
				childrenLines.addClass(classToAdd);
				$(this).find('.fa').removeClass('fa-plus-square').addClass('fa-minus-square');
			}
		}
	});
});
