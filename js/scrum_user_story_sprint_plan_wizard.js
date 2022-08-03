jQuery(function ($) {
	// using $ here will be safely even jQuery.noConflict() will be enabled

	$('#form-scrum-user-story-plan-wizard [name="fk_scrumsprint"]').select2({
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
					term: term.term,
				};
			},
			processResults: (response) => {
				if (response.data.errors) {
					uspWiz.setEventMessage(response.data.errors, false);
					return [];
				}
				return {
					results: $.map(response.data.rows, function (item) { return { text: item.text, id: item.id}; }),
				}
			},
			delay: 200,
		},
	});



	// add button click
	$('body').on('click', '.btn-add-us-planned', function(e) {
		e.preventDefault();

		$.ajax({
			method: 'POST',
			url: $(this).attr('data-interface-url'),
			dataType: 'json',
			data: {
				action : 'addUsPlannedToSprint',
				fk_scrumuserstory : $(this).attr('data-fk_scrumuserstory'),
				token: uspWiz.newToken
			},
			success: function (data) {
				if (data.result > 0) {
					// do stuff on success
				} else if (data.result == 0) {
					// do stuff on idle

				} else if (data.result < 0) {
					// do stuff on error
				}

				if (data.newToken != undefined) {
					uspWiz.newToken = data.newToken;
				}

				if (data.msg.length > 0) {
					uspWiz.setEventMessage(data.msg, data.result > 0 ? true : false);
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




	})(uspWiz);


	uspWiz.GetNewToken();
});



