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

/**
 * use as callback function for liveEdit
 * Reload project task planned time after update sprint planned time
 * @param {jQuery} el
 * @param responseData
 */
function scrumsprintProjectTasksPlanningLiveUpdate(el, responseData){

	if(el.parent().data('parent') != undefined){
		let userStoryLineSelector = '#user-story-' + el.parent().data('parent');

		// set html value as imported
		el.html(responseData.value);

		console.log(responseData.value);

		$.ajax({
			url:window.location.href,
			type:'GET',
			success: function(data){
				let colSelector = userStoryLineSelector + ' .col-us-qty-planned';
				$(colSelector).html($(data).find(colSelector).html());
			}
		});
	}
}
