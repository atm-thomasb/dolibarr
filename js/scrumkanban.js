

window.addEventListener('AdvKanban_init', function (e){
	let o = e.detail.advKanban;

	o.callScrumProjectKanbanInterface = function (action, sendData = {}, callBackFunction){
		return o.callInterface(o.config.interface_scrum_project_kanban_url, action, sendData, callBackFunction);
	}

	o.sprintResumeDialog = function(){

		let sendData = {
			'fk_kanban': o.config.fk_kanban
		};

		o.callScrumProjectKanbanInterface('getSprintResumeData', sendData, function(response){
			let resumeDialog = new Dialog({
				title: o.langs.SprintResume,
				content: response.data.html,
				onOpen : function (){

				}
			});

			o.initToolTip($(resumeDialog.dialog).find('.classfortooltip'));
		});
	}


	const toggleScrumUserStoryAndTaskHighLight = function($el, usId){
		const tagetUserStory = '.kanban-item[data-type="scrum-user-story"][data-targetelementid="'+usId+'"]';
		const tagetUserStoryTask = '.kanban-item[data-type="scrum-user-story-task"][data-fk_scrum_user_story_sprint="'+usId+'"]';

		if($el.attr('data-highlight') == '1'){
			o.removeHighlight(tagetUserStory);
			o.removeHighlight(tagetUserStoryTask);
			return;
		}

		$(tagetUserStory + ' .highlight-scrum-task').attr('data-highlight', '1');
		$(tagetUserStoryTask + ' .highlight-scrum-task').attr('data-highlight', '1');

		o.setHighlight(tagetUserStory, '.kanban-item');
		o.setHighlight(tagetUserStoryTask);
	}

	$(document).on('click','.kanban-item[data-type="scrum-user-story"] .highlight-scrum-task', function(e) {
		e.stopPropagation();
		const usId = $(this).closest('.kanban-item').attr('data-targetelementid');
		toggleScrumUserStoryAndTaskHighLight($(this), usId);
	});

	$(document).on('click','.kanban-item[data-type="scrum-user-story-task"] .highlight-scrum-task', function(e) {
		e.stopPropagation();
		const usId = $(this).closest('.kanban-item').attr('data-fk_scrum_user_story_sprint');
		toggleScrumUserStoryAndTaskHighLight($(this), usId);
	});


	// Open dialog for kanban resume
	$(document).on('click','#kanban-resume-btn', function() {
		o.sprintResumeDialog();
	})
});


window.addEventListener('AdvKanban_refreshAllBoards', function (e){
	let o = e.detail.advKanban;

	if(e.detail.response < 1) {
		return;
	}

	let sendData = {
		'fk_kanban': o.config.fk_kanban
	};

	o.callScrumProjectKanbanInterface('getSprintInfo', sendData, function(response){
		if (response.data && response.data.sprintInfos){
			let preTargetQuery = '#kanban-header-scrum-sprint-resume .kanban-header__item__value[data-element="scrumproject_scrumsprint"]';

			$(preTargetQuery +'[data-field="date_start"]').html(response.data.sprintInfos.date_start);
			$(preTargetQuery +'[data-field="date_end"]').html(response.data.sprintInfos.date_end);
			$(preTargetQuery +'[data-field="qty_velocity"]').html(response.data.sprintInfos.qty_velocity);
			$(preTargetQuery +'[data-field="qty_planned"]').html(response.data.sprintInfos.qty_planned);
			$(preTargetQuery +'[data-field="qty_done"]').html(response.data.sprintInfos.qty_done);
			$(preTargetQuery +'[data-field="qty_consumed"]').html(response.data.sprintInfos.qty_consumed);
			$(preTargetQuery +'[data-field="qty_us_planned_done"]').html(response.data.sprintInfos.qty_us_planned_done);


			o.initToolTip($('#advance-kanban').find('.classfortooltip'),1000);
		}
	});
});

/**
 * On activate filters
 */
window.addEventListener('AdvKanban_activeCardsFilters', function (e) {


	let scrumTask = $('.highlight-element[data-type="scrum-user-story"]');
	if(scrumTask.length > 0){
		scrumTask.each(function(){
			// TODO faire le decompte des heures
		});
	}

	let textScrumResume = $('#nb-scrum-task-found');
	if(textScrumResume.length > 0){
		textScrumResume.text(scrumTask.length);
	}




	let scrumUs = $('.highlight-element[data-type="scrum-user-story-task"]');
	if(scrumUs.length > 0){
		scrumUs.each(function(){
			// TODO faire le decompte des heures
		});
	}

	let textScrumUsResume = $('#nb-scrum-user-story-found');
	if(textScrumUsResume.length > 0){
		textScrumUsResume.text(scrumUs.length);
	}
});

window.addEventListener('AdvKanban_addDropDownItemContextMenu', function (e){
	let o = e.detail.advKanban;
	let dataType = e.detail.dataType;
	let el = e.detail.el;
	let menuItems = e.detail.menuItems;


	// Split US card Dialog
	if(dataType != undefined && dataType == 'scrum-user-story') {
		// will insert item into menuItems at the specified index (deleting 0 items first, that is, it's just an insert).
		// menuItems.length - 1 to be before delete
		menuItems.splice(menuItems.length - 1, 0, {
			content: '<i class="fa fa-columns" ></i>' + o.langs.CardUsSplit,
			events: {
				click: function (e) {
					o.dialogSplitCard(el);
				}
			}
		});
	}

	// Split US TASK card Dialog
	if(dataType != undefined && dataType == 'scrum-user-story-task') {


		//Saisir du temp dans une tâche
		// will insert item into menuItems at the specified index (deleting 0 items first, that is, it's just an insert).
		menuItems.splice(2, 0, {
			content: '<i class="fa fa-hourglass-o" ></i>' + o.langs.SprintTaskAddTime,
			events: {
				click: function (e) {
					//dialog iFrame redirection vers le temps consommé de la carte
					if(el.getAttribute('data-cardTimeUrl') != undefined){
						let label = '';
						if(el.getAttribute('data-label') != undefined){
							label = el.getAttribute('data-label');
						}
						o.dialogIFrame(el.getAttribute('data-eid'), el.getAttribute('data-cardTimeUrl'), label);
					}
				}
			}
		});

		// will insert item into menuItems at the specified index (deleting 0 items first, that is, it's just an insert).
		// menuItems.length - 1 to be before delete
		menuItems.splice(menuItems.length - 1, 0, {
			content: '<i class="fa fa-columns" ></i>' + o.langs.CardSplit,
			events: {
				click: function (e) {
					o.dialogSplitCard(el);
				}
			}
		});
	}
});
