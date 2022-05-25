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
			return SpLiveEdit.sendLiveLiveEdit($(this));
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
			o.setSPLiveEdit($('.live-edit'));
		}

		/**
		 * @param {jQuery} el
		 */
		o.setSPBadLiveEdit = function (el) {
			el.attr('title', 'Bad live edit configuration');
			el.css('color', 'red');
			el.removeClass('.live-edit');
			el.attr('contenteditable', false);
		};

		/**
		 * @param {jQuery} el
		 */
		o.setSPLiveEdit = function (el) {
			el.addClass('.live-edit');
			el.attr('contenteditable', true);
		};


		/**
		 *
		 * @param {jQuery} el
		 */
		o.sendLiveLiveEdit = function (el){

			if(el.data('ajax-target') == undefined){
				o.setSPBadLiveEdit(el);
				return false;
			}

			var urlInterface = $(this).data('ajax-target');


			var sendData = {
				'content': $(this).text(),
				'token': o.newToken
			};

			$.ajax({
				method: 'POST',
				url: urlInterface,
				dataType: 'json',
				data: sendData,
				success: function (data) {
					if(data.result) {
						// do stuff on success
					}
					else {
						// do stuff on error
					}
					o.newToken = data.newToken;
					o.dialogCountAddedProduct++; // indique qu'il faudra un rechargement de page à la fermeture de la dialogbox
					o.focusAtEndSearchInput($('#search-all-form-input')); // on replace le focus sur la recherche global pour augmenter la productivité
					o.setEventMessage(data.msg, data.result);
				},
				error: function (err) {
					o.setEventMessage(o.lang.errorAjaxCall, false);
				}
			});
		}

		/**
		 * affectation du contenu dans l'attribut title
		 *
		 * @param $element
		 * @param text
		 */
		o.setToolTip = function ($element, text){
			$element.attr("title",text);
			o.initToolTip($element);
		}


		/**
		 * initialisation de la tootip
		 * @param element
		 */
		o.initToolTip = function (element){
			if(!element.data('tooltipset')){
				element.data('tooltipset', true);
				element.tooltip({
					show: { collision: 'flipfit', effect:'toggle', delay:50 },
					hide: { delay: 50 },
					tooltipClass: 'mytooltip',
					content: function () {
						return $(this).prop('title');		/* To force to get title as is */
					}
				});
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
		 * equivalent de in_array en php
		 * @param needle
		 * @param haystack
		 * @returns {boolean}
		 */
		o.inArray = function inArray(needle, haystack) {
			var length = haystack.length;
			for(var i = 0; i < length; i++) {
				if(haystack[i] == needle) return true;
			}
			return false;
		}



	})(SpLiveEdit);

	/* Init live edit for compatible elements */
	SpLiveEdit.initLiveEdit();
});

