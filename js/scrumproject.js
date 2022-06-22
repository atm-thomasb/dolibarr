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

	/** utilisé par scrum card */

	// Toggle display
	$('body').on('change', '.scrum-project-form-toggle-trigger', function(e) {
		$('.scrum-project-form-toggle-target[data-toggle-trigger="' + $(this).attr('id') + '"]').attr('data-display', 0);
		$('.scrum-project-form-toggle-target[data-toggle-trigger="' + $(this).attr('id') + '"][data-toggle-trigger-value="' + $(this).val() + '"]').attr('data-display', 1);
	});

	// Reset imput
	$('body').on('change', '.scrum-project-form-reset-trigger[data-reset-target][data-reset-value]', function(e) {
		let resetTarget = $($(this).attr('data-reset-target'));
		if( resetTarget != undefined ){
			resetTarget.val($(this).attr('data-reset-value'));
		}
	});

	// Set imput value
	$('body').on('change', '.scrum-project-form-cloneval-trigger[data-cloneval-target]', function(e) {
		let clonevalTarget = $($(this).attr('data-cloneval-target'));
		if( clonevalTarget != undefined ){
			clonevalTarget.val($(this).attr('data-cloneval-value'));
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

		let url = new URL(window.location.href);
		if(!url.searchParams.get('fk_project')){
			url.searchParams.append('fk_project', $('#searchFormList input[name="fk_project"]').val());
		}

		$.ajax({
			url:url.href,
			type:'GET',
			success: function(data){
				let colSelector = userStoryLineSelector + ' .col-us-qty-planned';
				$(colSelector).html($(data).find(colSelector).html());
			}
		});
	}
}
