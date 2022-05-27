jQuery(function ($) {
	// using $ here will be safely even jQuery.noConflict() will be enabled

	$(document).on('focus','.live-edit', function() {
		if($(this).data('ajax-target') == undefined){
			SpLiveEdit.setSPBadLiveEdit($(this));
			return false;
		}
	});


	$(document).on('blur','.live-edit', function(){
		return SpLiveEdit.sendLiveLiveEdit($(this));
	});

	$(document).on('keydown', '.live-edit', function(e) {
		if(e.key == 'Enter'){
			e.preventDefault();
			SpLiveEdit.sendLiveLiveEdit($(this), true);
			$(this).trigger('blur');
		}
	});


// Utilisation d'une sorte de namespace en JS
	let SpLiveEdit = {};
	(function(o) {
		// lang par défaut, les valeurs son ecrasées lors du chargement de la page en fonction de la langue
		o.lang = {
			"Saved":"Sauvegard\u00e9",
			"errorAjaxCall":"Erreur d'appel ajax",
			"SearchProduct":"Recherche de produits\/services",
			"CloseDialog":"Fermer"
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
		 * function to call on document ready
		 */
		o.initLiveEdit = function (){
			o.GetNewToken();
			o.setSPLiveEdit($('[data-live-edit=1]'));
		}

		/**
		 * @param {jQuery} el
		 */
		o.setSPBadLiveEdit = function (el) {
			el.attr('title', 'Bad live edit configuration');
			el.css('color', 'red');
			el.removeClass('live-edit');
			el.attr('contenteditable', false);
		};

		/**
		 * @param {jQuery} el
		 */
		o.setSPLiveEdit = function (el) {
			el.addClass('live-edit');
			el.attr('contenteditable', true);
		};


		/**
		 *
		 * @param {jQuery} el
		 * @param forceUpdate bool to force update when old and new value are same
		 */
		o.sendLiveLiveEdit = function (el, forceUpdate = false){

			if(el.data('ajax-target') == undefined){
				o.setSPBadLiveEdit(el);
				return false;
			}

			let urlInterface = el.data('ajax-target');

			let sendData = {
				'value': el.text(),
				'token': o.newToken,
				'action': 'liveFieldUpdate',
				'forceUpdate' : forceUpdate ? 1 : 0 // js bool is send as string ...
			};

			$.ajax({
				method: 'POST',
				url: urlInterface,
				dataType: 'json',
				data: sendData,
				success: function (data) {
					if(data.result > 0) {
						// do stuff on success
						if(el.data('ajax-success-callback') != undefined){
							o.callBackFunction(el.data('ajax-success-callback'), el, data);
						}
					}
					else if(data.result == 0) {
						// do stuff on idle
						if(el.data('ajax-idle-callback') != undefined){
							o.callBackFunction(el.data('ajax-fail-callback'), el, data);
						}
					}
					else if(data.result < 0) {
						// do stuff on error
						if(el.data('ajax-fail-callback') != undefined){
							o.callBackFunction(el.data('ajax-fail-callback'), el, data);
						}
					}

					if(data.newToken != undefined){
						o.newToken = data.newToken;
					}

					if(data.msg.length > 0) {
						o.setEventMessage(data.msg, data.result > 0 ? true : false );
					}
				},
				error: function (err) {
					o.setEventMessage(o.lang.errorAjaxCall, false);
				}
			});
		}

		/**
		 * @param $functionName
		 * @returns {boolean}
		 */
		o.isCallableFunction = function ($functionName){
			return window[$functionName] instanceof Function;
		}

		/**
		 * @param $functionName
		 * @param {jQuery} el
		 * @returns {boolean}
		 */
		o.callBackFunction = function ($functionName, el = null, data = null){
			if(!o.isCallableFunction($functionName)){
				return false;
			}

			// execute function callback
			let fn = window[$functionName];
			return fn(el, data);
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

	})(SpLiveEdit);

	/* Init live edit for compatible elements */
	SpLiveEdit.initLiveEdit();
});
