jQuery(function ($) {
	// using $ here will be safely even jQuery.noConflict() will be enabled

	$('.toggle-more-btn').on('click', function (){
		if($(this).data('target') != undefined){
			let lineId = $(this).data('target');
			let childrenLines = $('.toggle-line-display[data-parent' + lineId + ']');
			let classToAdd = '--open';
			if($(this).hasClass(classToAdd)){
				$(this).removeClass(classToAdd);
				childrenLines.removeClass(classToAdd);
			}else{
				$(this).addClass(classToAdd);
				childrenLines.addClass(classToAdd);
			}
		}
	});
});
