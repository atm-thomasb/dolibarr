jQuery(function ($) {
	// using $ here will be safely even jQuery.noConflict() will be enabled

	let planFormSelector = '#form-scrum-user-story-plan-wizard';

	$('body').on('submit', planFormSelector, function(event) {
		event.preventDefault();
	});


	$('body').on('keyup keypress', planFormSelector + " input, " + planFormSelector + " button", function(e) {
		if (e.key === 'Enter') {
			e.preventDefault();
			$(this).closest('tr').find('.btn-add-us-planned').trigger('click');
			return false;
		}
	});


	let sprintSelect = $('#form-scrum-user-story-plan-wizard [name="fk_scrumsprint"]');
	sprintSelect.select2({
		minimumInputLength: 2,
		ajax: {
			multiple: false,
			url: function (params) {
				return $(this).attr('data-interface-url');
			},
			dataType: 'json',
			type: 'GET',
			quietMillis: 250,
			data: (term) => {
				return {
					action: 'get-sprint-autocompletion',
					fk_scrum_user_story : $(this).closest('tr[data-parent]').attr('data-parent'),
					term: term.term,
				};
			},
			processResults: (response) => {
				if (response.data.errors) {
					uspWiz.setEventMessage(response.data.errors, false);
					return [];
				}
				return {
					results: $.map(response.data.rows, function (item) {

						// update des données des sprints
						if($('.col-scrumsprint-qty-to-plan[data-fk_sprint="'+item.id+'"]').length > 0){
							$('.col-scrumsprint-qty-to-plan[data-fk_sprint="'+item.id+'"]').html(item.html_sprintQtyAvailable);
						}

						return {
							text: item.text,
							id: item.id,
							sprintQtyAvailable: item.sprintQtyAvailable,
							html_sprintQtyAvailable: item.html_sprintQtyAvailable
						};
					}),
				}
			},
			delay: 200,
		},
	});

	// Fetch the preselected item, and add to the control
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: sprintSelect.attr('data-interface-url'),
		data: {
			action: 'get-sprint-autocompletion'
		},
	}).then(function (response) {

		// create blank the option and append to Select2
		let option = new Option('', '', true);
		sprintSelect.append(option);

		let select2NewData = $.map(response.data.rows, function (item) {

			// create the option and append to Select2
			let option = new Option(item.text, item.id, false, false);
			sprintSelect.append(option);

			// update des données des sprints
			if($('.col-scrumsprint-qty-to-plan[data-fk_sprint="'+item.id+'"]').length > 0){
				$('.col-scrumsprint-qty-to-plan[data-fk_sprint="'+item.id+'"]').html(item.html_sprintQtyAvailable);
			}

			return {
				text: item.text,
				id: item.id,
				sprintQtyAvailable: item.sprintQtyAvailable,
				html_sprintQtyAvailable: item.html_sprintQtyAvailable
			};
		});

		// sprintSelect.trigger('change');

		// manually trigger the `select2:select` event
		sprintSelect.trigger({
			type: 'select2:select',
			params: {
				data: select2NewData
			}
		});
	});

	$(document).on('change', '#form-scrum-user-story-plan-wizard [name="fk_scrumsprint"]', function(e) {
		//I create a var data and works it like an Array
		let data = $(this).select2('data');

		// grab the Array item which matchs the id
		let dataSelected = data.find(item => item.id === $(this).val());

		$(this).closest('tr').find('.col-scrumsprint-qty-to-plan').html(dataSelected.html_sprintQtyAvailable);
	});


	// del button click
	$('body').on('click', '.btn-delete-us-planned', function(e) {
		e.preventDefault();

		$(this).prop('disabled', true);

		let fk_scrum_user_story_sprint = $(this).attr('data-fk_scrum_user_story_sprint');
		let lineContainer = $(this).closest('tr[data-parent]');

		$.ajax({
			method: 'POST',
			url: $(this).attr('data-interface-url'),
			dataType: 'json',
			data: {
				action : 'delete-us-planned',
				token: uspWiz.newToken,
				data:{
					fk_scrum_user_story_sprint : fk_scrum_user_story_sprint,
				}
			},
			success: function (response) {
				if (response.result > 0) {
					// do stuff on success
					lineContainer.slideUp();// suppression de la list

					// update des données des sprints
					if($('.col-scrumsprint-qty-to-plan[data-fk_sprint="'+ response.data.sprintInfos.id +'"]').length > 0){
						$('.col-scrumsprint-qty-to-plan[data-fk_sprint="'+ response.data.sprintInfos.id +'"]').html(response.data.sprintInfos.html_qtyAvailable);
					}

				} else if (response.result == 0) {
					// do stuff on idle

				} else if (response.result < 0) {
					// do stuff on error
				}

				if (response.newToken != undefined) {
					uspWiz.newToken = response.newToken;
				}

				uspWiz.reloadUserStoryInfos(lineContainer.attr('data-parent'));

				if (response.msg.length > 0) {
					uspWiz.setEventMessage(response.msg, response.result > 0 ? true : false);
				}
			},
			error: function (err) {
				uspWiz.setEventMessage(uspWiz.lang.errorAjaxCall, false);
			}
		});
	});

	// add button click
	$('body').on('click', '.btn-add-us-planned', function(e) {
		e.preventDefault();

		let thisButton = $(this);
		thisButton.prop('disabled', true);

		let fk_scrumuserstory = $(this).attr('data-fk_scrumuserstory');
		let formContainer = $('.add-line-form[data-parent="' + fk_scrumuserstory + '"]');

		$.ajax({
			method: 'POST',
			url: $(this).attr('data-interface-url'),
			dataType: 'json',
			data: {
				action : 'add-us-planned-to-sprint',
				token: uspWiz.newToken,
				data:{
					fk_scrum_user_story : fk_scrumuserstory,
					fk_scrum_sprint : formContainer.find('[name="fk_scrumsprint"]').val(),
					qty_planned: formContainer.find('[name="qty_planned"]').val(),
					label : formContainer.find('[name="label"]').val()
				}
			},
			success: function (response) {
				if (response.result > 0) {
					// do stuff on success

					// get line from reloaded page
					$.ajax({
						url:window.location.href,
						type:'POST',
						data:{
							action : 'list',
							token: uspWiz.newToken,
							toselect:[fk_scrumuserstory]
						},
						success: function(loadResponse){
							let newLine = $($(loadResponse).find('#scrum-user-story-sprint-'+response.data.id));
							uspWiz.initDefaultToolTipInDeep(newLine);

							// il faut recharger le live edit
							SpLiveEdit.setSPLiveEdit(newLine.find('[data-live-edit=1]'));

							newLine.insertBefore('.add-line-form[data-parent="'+fk_scrumuserstory+'"]');

							// update des données des sprints
							if($('.col-scrumsprint-qty-to-plan[data-fk_sprint="'+response.data.sprintInfos.id+'"]').length > 0){
								$('.col-scrumsprint-qty-to-plan[data-fk_sprint="'+response.data.sprintInfos.id+'"]').html(response.data.sprintInfos.html_qtyAvailable);
							}

							if($('.no-record-found[data-parent="'+fk_scrumuserstory+'"]').length>0){
								$('.no-record-found[data-parent="'+fk_scrumuserstory+'"]').hide();
							}

							$('.add-line-form[data-parent="'+fk_scrumuserstory+'"]').find('.col-scrumsprint-qty-to-plan').html('');

						}
					});

				} else if (response.result == 0) {
					// do stuff on idle

				} else if (response.result < 0) {
					// do stuff on error
				}

				// je rend le bouton apres 1 seconde
				setTimeout(() => {
					thisButton.prop('disabled', false);
				}, "1000")


				if (response.newToken != undefined) {
					uspWiz.newToken = response.newToken;
				}

				uspWiz.reloadUserStoryInfos(fk_scrumuserstory);

				if (response.msg.length > 0) {
					uspWiz.setEventMessage(response.msg, response.result > 0 ? true : false);
				}
			},
			error: function (err) {
				uspWiz.setEventMessage(uspWiz.lang.errorAjaxCall, false);
			}
		});
	});


// Utilisation d'une sorte de namespace en JS
	uspWiz = {};
	(function(o) {
		// lang par défaut, les valeurs son ecrasées lors du chargement de la page en fonction de la langue
		o.lang = {
			"errorAjaxCall":"Erreur d'appel ajax",
		};


		o.newToken = '';
		o.interfaceUrl = '';

		/**
		 * Get new token
		 */
		o.GetNewToken = function (){
			if($('input[name=token]').length > 0){
				o.newToken = $('input[name=token]').val();
			}
		}

		/**
		 *
		 * @param msg
		 * @param status
		 */
		o.setEventMessage = function (msg, status = true){

			if(msg.length > 0){
				if(status){
					$.jnotify(msg, 'notice', {timeout: 5},{ remove: function (){} } );
				}
				else{
					$.jnotify(msg, 'error', {timeout: 0, type: 'error'},{ remove: function (){} } );
				}
			}
			else{
				$.jnotify('ErrorMessageEmpty', 'error', {timeout: 0, type: 'error'},{ remove: function (){} } );
			}
		}

		/**
		 * initialisation de la tootip sur tout les
		 * @param {JQuery} targetContainer
		 */
		o.initDefaultToolTipInDeep = function (targetContainer){
			targetContainer.find('.classfortooltip').each(function(el){
				o.initToolTip($(this));
			});
		}

		/**
		 * initialisation de la tootip
		 * @param {JQuery} element
		 */
		o.initToolTip = function (element){

			if(!element.data("tooltipset")){
				element.data("tooltipset", true);
				element.tooltip({
					show: { collision: "flipfit", effect:"toggle", delay:50 },
					hide: { delay: 50 },
					tooltipClass: "mytooltip",
					content: function () {
						return $(this).prop("title");		/* To force to get title as is */
					}
				});
			}
		}

		o.reloadUserStoryInfos = function(fk_scrumuserstory, fk_scrumsprint = 0){

			let containerLine = $('#user-story-' + fk_scrumuserstory);

			$.ajax({
				method: 'POST',
				url: o.interfaceUrl,
				dataType: 'json',
				data: {
					action : 'get-user-story-infos',
					token: o.newToken,
					data:{
						fk_scrumsprint: fk_scrumsprint,
						id : fk_scrumuserstory
					}
				},
				success: function (response) {

					if (response.result > 0) {
						// do stuff on success
						containerLine.find('.col-us-qty-planned').html(response.data.html_plannedBadge);

						if(response.data.sprintInfos !== undefined){
							// update des données des sprints
							if($('.col-scrumsprint-qty-to-plan[data-fk_sprint="'+response.data.sprintInfos.id+'"]').length > 0){
								$('.col-scrumsprint-qty-to-plan[data-fk_sprint="'+response.data.sprintInfos.id+'"]').html(response.data.sprintInfos.html_qtyAvailable);
							}
						}

						// reload des tooltip
						uspWiz.initDefaultToolTipInDeep(containerLine);
					}

					if (response.newToken != undefined) {
						uspWiz.newToken = response.newToken;
					}

					if (response.msg.length > 0) {
						uspWiz.setEventMessage(response.msg, response.result > 0 ? true : false);
					}
				},
				error: function (err) {
					uspWiz.setEventMessage(uspWiz.lang.errorAjaxCall, false);
				}
			});
		}

	})(uspWiz);

	uspWiz.GetNewToken();
	uspWiz.interfaceUrl = $('#form-scrum-user-story-plan-wizard input[name="interface-url"]').val();
});


/**
 *
 * @param {JQuery} el
 * @param {object} data
 */
function scrumUserStorySprintPlanningWizardLiveUpdate( el = null, data = null){
	let lineContainer = el.closest('tr[data-parent]');
	let fk_scrumstrint = lineContainer.find('.col-scrumsprint-qty-to-plan').attr('data-fk_sprint');
	uspWiz.reloadUserStoryInfos(lineContainer.attr('data-parent'), fk_scrumstrint);
}
