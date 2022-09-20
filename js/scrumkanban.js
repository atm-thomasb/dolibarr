// Utilisation d'une sorte de namespace en JS
scrumKanban = {};
(function(o) {

	o.menuIcons = {
		copyIcon 		: `<svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2.5" style="margin-right: 7px" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>`,
		cutIcon 		: `<svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2.5" style="margin-right: 7px" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><circle cx="6" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><line x1="20" y1="4" x2="8.12" y2="15.88"></line><line x1="14.47" y1="14.48" x2="20" y2="20"></line><line x1="8.12" y1="8.12" x2="12" y2="12"></line></svg>`,
		pasteIcon 		: `<svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2.5" style="margin-right: 7px; position: relative; top: -1px" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>`,
		downloadIcon 	: `<svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2.5" style="margin-right: 7px; position: relative; top: -1px" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>`,
		deleteIcon 		: `<svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2.5" fill="none" style="margin-right: 7px" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>`,
		documentIcon 	: `<svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2.5" fill="none" style="margin-right: 7px" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><rect width="15.1" height="19.6" x="5" y="2.3" /><path fill="none" d="M7.4 13.6h10M7.5 16.6h10"/></svg>`
	}


	/**
	 * Store the max tms of all board element 
	 * used to compare with database and determine if need update
	 * @type {number}
	 */
	o.lastBoardUpdate = 0;

	/**
	 * Dolibarr token
	 * @type {string}
	 */
	o.newToken = '';

	/**
	 * Congig par défaut, les valeurs sont écrasées lors du chargement de la page en fonction de la configuration transmise
	 * @type {{}}
	 */
	o.config = {
		interface_kanban_url: '../interface-kanban.php',
		interface_liveupdate_url: '../interface-liveupdate.php',
		srumprojectModuleFolderUrl: '../',
		fk_kanban : false,
		token: false, // to set at init
		maxScrumTaskStepQty: 0,
		maxScrumTaskMaxQty: 0
	};


	/**
	 * lang par défaut, les valeurs sont écrasées lors du chargement de la page en fonction de la langue
	 * 	@type {{}}
 	 */
	o.langs = {
		NewList:"Nouvelle liste",
		NewCard:"Nouvelle carte",
		BackLog:"BackLog",
		errorAjaxCall:"Erreur d'appel ajax",
		errorAjaxCallDisconnected:"Vous êtes déconnecté",
		CloseDialog:"Fermer",
		Copy:"Copier",
		Delete:"Supprimer",
		CardClone:"Cloner",
		CardSplit:"Découper en plusieurs cartes",
		CardUsSplit:"Découper en tâches",
		AssignMe:"M'assigner à la tâche",
		UnAssignMe:"Me désengager de la tâche",
		PressEscapeToAvoid:"Appuyer sur la touche ECHAP pour annuler",
		ShowDolCard:"Afficher la fiche",
		SplitUsInTask:"Séparer l'user story en tâches scrum",
		SplitCard:"Découper la carte",
		CloneCard:"Cloner la carte",
		DeleteCardDialogTitle:"Supprimer cette carte ?",

		QtyPlanned : 'Quantités planifiés',
		QtyConsumed : 'Quantités consommées',
		QtyRemain : 'Quantités restantes',
		QtyRemainToSplit : 'Quantités découpables',
		AddScrumTaskLine : 'Ajouter une tâche scrum',
		SplitScrumTask : 'Découper la tâche scrum',
		NotSplittable : 'N\'est pas découpable',
		AddRemoveTags : 'Ajouter/supprimer une categorie',

		RemoveLine : 'Supprimer la ligne',
		AddLine : 'Ajouter une ligne',
		QtyScrumTaskAlreadySplited: 'Quantités découpées en tâche(s) scrum '

	};


	o.jKanban = false;

	o.init = function (config = {}, langs= {}){

		if(config && typeof config === 'object'){
			o.config = Object.assign(o.config, config);
		}

		o.newToken = o.config.token;

		if(langs && typeof langs === 'object'){
			o.langs = Object.assign(o.langs, langs);
		}

		o.initDarkMod();
		o.initToolTip($('.classfortooltip'));
		o.initAssignMeByPressingSpaceKey();


		o.jkanban = new jKanban({
			element : '#scrum-kanban',
			gutter  : '5px',
			widthBoard: '270px',
			click : function(el){
				// callback when any board's item are clicked
				// Attention : a éviter prévilégier des event listener séparés
			},
			context: function(el, e) {
				// callback when any board's item are right clicked
				o.addDropDownItemContextMenu(el, e);
			},
			dropEl: function(el, target, source, sibling){

				let sendData = {
					'fk_kanban': o.config.fk_kanban,
					'source-list-id': o.getDolListIdFromKanbanDragElement(source),
					'target-list-id': o.getDolListIdFromKanbanDragElement(target),
					'card-id': o.getDolCardIdFromCardElement(el),
					'before-card-id': o.getDolCardIdFromCardElement(sibling)
				};

				o.callKanbanInterface('dropItemToList', sendData, function(response){
					// do stuff ?
				});

				o.clearView();
			},
			dragendEl : function (el) {
				// callback when any board's item stop drag
				// o.setEventMessage('Work in progress drag end el', false);
			},
			dragBoard        : function (el, source) {
				// callback when any board stop drag

				// // a cause d'un bug d'affichage j'enlève le footer lors du déplacement
				// let boardSelector = el.getAttribute('data-id');
				// $('.kanban-board[data-id=' + boardSelector + '] footer').hide();
			},
			dropBoard: function (el, target, source, sibling) {
				// callback when any board stop drag
				// TODO : voir si pas plus judicieux d'envoyer la position de tout les boards au lieux de qq chose de relatif

				let sendData = {
					'fk_kanban': o.config.fk_kanban,
					'list-id': o.getDolListIdFromBoard(el),
					'before-list-id': o.getDolListIdFromBoard(sibling)
				};

				o.callKanbanInterface('changeListOrder', sendData, function(response){
					// do stuff ?
				});

				// // reaffiche le bouton du footer
				// let boardSelector = el.getAttribute('data-id');
				// $('.kanban-board[data-id=' + boardSelector + '] footer').slideDown();
			},
			dragendBoard     : function (el) {

			},
			buttonClick: function(el, boardId) {
				// callback when the board's button is clicked
				el.blur();// to avoid space key press
				o.clearView();
				o.addKanbanCardToList(boardId);

			},
			itemAddOptions: {
				enabled: true,
				content: '+ ' + o.langs.NewCard,
				class: 'kanban-list-add-button',
				footer: true
			}
			// itemHandleOptions: {
			// 	enabled             : true,                                 // if board item handle is enabled or not
			// 	handleClass         : "item_handle",                         // css class for your custom item handle
			// 	customCssHandler    : "drag_handler",                        // when customHandler is undefined, jKanban will use this property to set main handler class
			// 	customCssIconHandler: "drag_handler_icon",                   // when customHandler is undefined, jKanban will use this property to set main icon handler class. If you want, you can use font icon libraries here
			// 	customHandler       : "<span class='item_handle'>+</span> %title% "  // your entirely customized handler. Use %title% to position item title
			// 																		 // any key's value included in item collection can be replaced with %key%
			// },
			// propagationHandlers: [], // the specified callback does not cancel the browser event. possible values: "click", "context"
			// boards: [
			// 	{
			// 		id: "_todo",
			// 		title: "To Do (Can drop item only in working)",
			// 		class: "info,good",
			// 		dragTo: ["_working"],
			// 		item: [
			// 			{
			// 				id: "_test_delete",
			// 				title: "Try drag this (Look the console)",
			// 				drag: function(el, source) {
			// 					console.log("START DRAG: " + el.dataset.eid);
			// 				},
			// 				dragend: function(el) {
			// 					console.log("END DRAG: " + el.dataset.eid);
			// 				},
			// 				drop: function(el) {
			// 					console.log("DROPPED: " + el.dataset.eid);
			// 				}
			// 			},
			// 			{
			// 				title: "Try Click This!",
			// 				click: function(el) {
			// 					alert("click");
			// 				},
			// 				context: function(el, e){
			// 					alert("right-click at (" + `${e.pageX}` + "," + `${e.pageX}` + ")")
			// 				},
			// 				class: ["peppe", "bello"]
			// 			}
			// 		]
			// 	}
			// ]
		});

		// Get all board
		o.getAllBoards();

		// init du refresh du kanban
		setTimeout(function(){
			o.refreshAllBoards(true);
		}, 5000);

		// Open dialog for kanban item : pas d'utilisation du click fournis par le kanban pour permettre les clics sur des sous elements
		$(document).on('click','.kanban-item', function() {
			o.cardClick($(this)[0]);
		})

		o.loadJs(o.config.srumprojectModuleFolderUrl + '/js/liveedit.js', function (){
			o.setLiveEditForBoardsTitle();
		});


		// Add new list (column)
		let addBoardDefault = document.getElementById('addkanbancol');
		addBoardDefault.addEventListener('click', function () {
			o.addKanbanList(o.langs.NewList);
		});

		o.addDropDownMenuList();

		// init Highlight backround
		o.initHighlight();


		// TODO : bon pour l'instant ça marche pas
		//  Doit normalement permettre de scroll les liste en même temps que l'on fait un drag and drop
		//  mais je pense que le dragToScroll doit entrer en conflict
		// o.kanbanAutoScroll = dragAutoScroll([
		// 		document.querySelector('.kanban-container')
		// 	],{
		// 		margin: 20,
		// 		maxSpeed: 5,
		// 		scrollWhenOutside: true,
		// 		autoScroll: function(){
		// 			//Only scroll when the pointer is down, and there is a child being dragged.
		// 			return this.down && o.jKanban.drake.dragging;
		// 		}
		// 	}
		// );
	};


	/**
	 * @param HTMLElement el
	 */
	o.cardClick = function(el){

		if(el.getAttribute('data-cardurl') != undefined){
			let label = '';
			if(el.getAttribute('data-label') != undefined){
				label = el.getAttribute('data-label');
			}

			o.dialogIFrame(el.getAttribute('data-eid'), el.getAttribute('data-cardurl'), label);
		}
	}

	o.clearView = function(){

		// let kanbanAddForms = document.querySelectorAll('.add-item-form-container');
		// kanbanAddForms.forEach(addFormItem => {
		// 	addFormItem.remove();
		// });
	}

	/**
	 * Open Dialog iframe
	 * @param dialogId
	 * @param {JQuery} $target
	 * @param url
	 * @param label
	 */
	o.dialogIFrame = function (dialogId, url, label = '', callBackFunc = {}){


		callBackFunc =  Object.assign({
			open : undefined,
			close : undefined,
		}, callBackFunc);

		url = o.updateURLParameter(url, 'optioncss', 'print');

		let kanbanDialogId = 'kanbanitemdialog-' + dialogId;
		if(document.getElementById(kanbanDialogId) == undefined){
			$('body').append( $('<div id="kanbanitemdialog-' + dialogId + '" ></div>')); // put it into the DOM
		}

		$target = $('#' + kanbanDialogId);

		$target.html('<iframe class="iframedialog" id="iframedialog' + dialogId + '" style="border: 0px;" src="' + url + '" width="100%" height="98%"></iframe>');

		$target.dialog({
			autoOpen: false,
			modal: true,
			height: (window.innerHeight - 150),
			width: '80%',
			title: label,
			open: function (event, ui) {
				if (typeof callBackFunc.open === 'function'){
					callBackFunc.open(event, ui);
				}
			},
			close: function (event, ui) {
				if (typeof callBackFunc.close === 'function'){
					callBackFunc.close(event, ui);
				}
			}
		});

		$target.dialog('open');
	};


	o.addKanbanList = function(listName){

		let sendData = {
			'fk_kanban': o.config.fk_kanban
		};

		o.callKanbanInterface('addKanbanList', sendData, function(response){
			if(response.result > 0) {
				// recupérer les bonnes infos
				o.jkanban.addBoards([response.data])
			}
		});
	}

	/**
	 *	Add Kanban card to list
	 * @param {string} listName
	 */
	o.addKanbanCardToList = function(listName){

		let sendData = {
			'fk_kanban': o.config.fk_kanban,
			'fk_kanbanlist' : o.getDolListIdFromJKanbanBoardDomId(listName)
		};

		o.callKanbanInterface('getAllItemToList', sendData, function(response){
			if(response.result > 0) {
				// recupérer les bonnes infos
				o.jkanban.addElement( listName, response.data);
			}
		});
	}

	/**
	 * return dolibarr kanbanList id from dom board #id
	 * @param {string} domId
	 * @returns {string}
	 */
	o.getDolListIdFromJKanbanBoardDomId = function (domId){
		// remove board- part
		return domId.slice(6, domId.length);
	}

	/**
	 * return dolibarr kanbanList id from dom board element
	 * @param {Element} element
	 * @returns {string}
	 */
	o.getDolListIdFromKanbanDragElement = function (element){
		if(element == undefined){ return undefined; }
		return o.getDolListIdFromJKanbanBoardDomId(element.parentElement.getAttribute('data-id'));
	}

	/**
	 * return dolibarr kanbanList id from dom board element
	 * @param {Element} element
	 * @returns {string}
	 */
	o.getDolListIdFromBoard = function (element){
		if(element == undefined){ return undefined; }
		return o.getDolListIdFromJKanbanBoardDomId(element.getAttribute('data-id'));
	}

	/**
	 * return dolibarr card id from dom card element
	 * @param {Element} element
	 * @returns {string}
	 */
	o.getDolCardIdFromCardElement = function (element){
		if(element == undefined){ return undefined; }
		return element.getAttribute('data-objectid');
	}

	o.delKanbanList = function(listName){

		let sendData = {
			'fk_kanban': o.config.fk_kanban,
			'kanban_list_id' : o.getDolListIdFromJKanbanBoardDomId(listName)
		};

		o.callKanbanInterface('removeList', sendData, function(response){
			if(response.result > 0) {
				o.jkanban.removeBoard(listName)
			}
		});
	}


	o.delItem = function(eid){

		let item = o.jkanban.findElement(eid);

		let sendData = {
			'fk_kanban': o.config.fk_kanban,
			'card_id' : item.getAttribute('data-objectid')
		};

		o.callKanbanInterface('removeCard', sendData, function(response){
			if(response.result > 0) {
				o.jkanban.removeElement(item)
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
			console.log('CallBack function ' + $functionName + ' not found !')
			return false;
		}

		console.log('CallBack function ' + $functionName + ' executed')
		// execute function callback
		let fn = window[$functionName];
		return fn(el, data);
	}

	/**
	 *
	 * @param {string} msg
	 * @param {boolean} status
	 * @param {boolean} sticky
	 */
	o.setEventMessage = function (msg, status = true, sticky = false){

		let jnotifyConf = {
			delay: 1500                               // the default time to show each notification (in milliseconds)
			, type : 'error'
			, sticky: sticky                             // determines if the message should be considered "sticky" (user must manually close notification)
			, closeLabel: "&times;"                     // the HTML to use for the "Close" link
			, showClose: true                           // determines if the "Close" link should be shown if notification is also sticky
			, fadeSpeed: 150                           // the speed to fade messages out (in milliseconds)
			, slideSpeed: 250                           // the speed used to slide messages out (in milliseconds)
		}


		if(msg.length > 0){
			if(status){
				jnotifyConf.type = '';
				$.jnotify(msg, jnotifyConf);
			}
			else{
				$.jnotify(msg, jnotifyConf);
			}
		}
		else{
			$.jnotify('ErrorMessageEmpty', jnotifyConf);
		}
	}

	o.getAllBoards = function (){

		let sendData = {
			'fk_kanban': o.config.fk_kanban
		};

		o.callKanbanInterface('getAllBoards', sendData, function(response){
			if(response.result > 0) {
				// recupérer les bonnes infos
				o.jkanban.addBoards(response.data.boards);
				o.lastBoardUpdate = response.data.md5Boards;
			}
		});
	}

	// Allow automatic refresh of boards
	// TODO : pour l'instant c'est vraiment rudimentaire
	//   WebSocket ? ou server-sent events ?
	o.refreshAllBoards = function (autoRefresh = false){

		// todo : use o.lastBoardUpdate

		let sendData = {
			'fk_kanban': o.config.fk_kanban
		};

		o.callKanbanInterface('getAllBoards', sendData, function(response){

			if(response.result > 0 && response.data.md5Boards != o.lastBoardUpdate) {

				let boardsToDelete = [];

				// get all boards
				o.jkanban.options.boards.forEach(function(board, indexKey){
					boardsToDelete.push(board.id);
				});

				// remove board
				boardsToDelete.forEach(function(boardId, indexKey){
					o.jkanban.removeBoard(boardId);
				});

				// recupérer les bonnes infos
				o.jkanban.container.style.width = '';
				o.jkanban.addBoards(response.data.boards);
				o.lastBoardUpdate = response.data.md5Boards;
			}

			if(autoRefresh){
				setTimeout(function(){
					o.refreshAllBoards(autoRefresh);
				}, 5000);
			}
		});
	}

	o.callKanbanInterface = function (action, sendData = {}, callBackFunction){
		let ajaxData = {
			'data': sendData,
			'token': o.newToken,
			'action': action,
		};

		$.ajax({
			method: 'POST',
			url: o.config.interface_kanban_url,
			dataType: 'json',
			data: ajaxData,
			success: function (response) {

				if (typeof callBackFunction === 'function'){
					callBackFunction(response);
				} else {
					console.error('Callback function invalide for callKanbanInterface');
				}

				if(response.newToken != undefined){
					o.newToken = response.newToken;
				}

				if(response.msg.length > 0) {
					o.setEventMessage(response.msg, response.result > 0 ? true : false, response.result == 0 ? true : false );
				}
			},
			error: function (err) {

				if(err.responseText.length > 0){

					// detect login page in case of just disconnected
					let loginPage = $(err.responseText).find('[name="actionlogin"]');
					if(loginPage != undefined && loginPage.val() == 'login'){
						o.setEventMessage(o.langs.errorAjaxCallDisconnected, false);

						setTimeout(function (){
							location.reload();
						}, 2000);

					}else{
						o.setEventMessage(o.langs.errorAjaxCall, false);
					}
				}
				else{
					o.setEventMessage(o.langs.errorAjaxCall, false);
				}
			}
		});
	}

	o.setLiveEditForBoardsTitle = function (){
		let boardTitleSelector = '.kanban-list-label-field';

		// /** Apparement l'event click suffit mais j'avais créée ça pour detecter le drag et le click du coup j'ai envie de le garder au cas ou il est possible que j'en ai besoin plus tard  */
		// let startX; // start Y coordinate of the mouse
		// let startY; // start Y coordinate of the mouse
		//
		// $(document).on('mousedown',boardTitleSelector, function(event) {
		// 	startX = event.pageX;
		// 	startY = event.pageY;
		// });
		//
		//
		// $(document).on('mouseup',boardTitleSelector, function(event) {
		// 	const diffX = Math.abs(event.pageX - startX);
		// 	const diffY = Math.abs(event.pageY - startY);
		// 	let eventType;
		// 	let delta = 6; // delta of click to be a drag or a click
		// 	if (diffX < delta && diffY < delta) {
		// 		// It's a click of ${diffX}px on X`;
		//
		// 		// to avoid miss click or drag I use
		// 		if(SpLiveEdit.newToken.length == 0) {
		// 			SpLiveEdit.newToken = o.newToken; // mise à jour du token
		// 		}
		//
		// 		SpLiveEdit.setLiveUpdateAttributeForDolField($(this), {
		// 			element : 'scrumproject_scrumkanbanlist',
		// 			fk_element : o.getDolListIdFromJKanbanBoardDomId($(this).closest('.kanban-board').attr('data-id')),
		// 			field : 'label',
		// 			liveEditInterfaceUrl: o.config.interface_liveupdate_url
		// 		});
		//
		// 		SpLiveEdit.setSPLiveEdit($(this));
		// 		$(this).trigger('focus');
		//
		// 	} else {
		// 		// It's a Swipe of ${diffX}px on X`;
		// 	}
		// });

		$(document).on('click',boardTitleSelector, function() {
			if(SpLiveEdit.newToken.length == 0) {
				SpLiveEdit.newToken = o.newToken; // mise à jour du token
			}

			SpLiveEdit.setLiveUpdateAttributeForDolField($(this), {
				element : 'scrumproject_scrumkanbanlist',
				fk_element : o.getDolListIdFromJKanbanBoardDomId($(this).closest('.kanban-board').attr('data-id')),
				field : 'label',
				liveEditInterfaceUrl: o.config.interface_liveupdate_url
			});

			SpLiveEdit.setSPLiveEdit($(this));
			$(this).trigger('focus');
		});

		$(document).on('blur',boardTitleSelector+'.live-edit', function(){
			return SpLiveEdit.removeSPLiveEdit($(this));
		});
	}


	o.loadJs = function (jsFileUrl, callBackFunction){
		// Chargement de la librairie js
		let advps_script_to_load = document.createElement('script')
		advps_script_to_load.setAttribute('src', jsFileUrl);
		document.body.appendChild(advps_script_to_load);
		// now wait for it to load...
		advps_script_to_load.onload = () => {
			if (typeof callBackFunction === 'function'){
				// script has loaded, you can now use it safely
				callBackFunction();
			}
		};
	}

	o.addDropDownMenuList = function(){

		$(document).on('click','.kanban-header-dropdown-btn', function(e) {

			if($(this).attr('dropdownready') == undefined){
				$(this).attr('dropdownready', 1);
				let $menuDropDown = $(this);
				let menuDropDownId =  $menuDropDown.attr('id');

				let boardId = $menuDropDown.closest('.kanban-board').attr('data-id');

				let menuItems = [
					{
						content: o.menuIcons.documentIcon + o.langs.ShowDolCard,
						events: {
							click: function (e) {
								let $kanbanLabelField = $menuDropDown.closest('.kanban-title-board').find('.kanban-list-label-field');
								if($menuDropDown.attr('data-cardurl') != undefined){
									let label = '';
									if($kanbanLabelField != undefined && $kanbanLabelField.attr('data-label') != undefined){
										label = $kanbanLabelField.attr('data-label');
									}

									o.dialogIFrame(menuDropDownId, $menuDropDown.attr('data-cardurl'), label);
								}
								else{
									o.setEventMessage('Missing data for card Url', false);
								}
							}
							// mouseover: () => console.log("Copy Button Mouseover")
							// You can use any event listener from here
						}
					},
					{
						content: o.menuIcons.deleteIcon + o.langs.Delete,
						events: {
							click: function (e) {
								o.dialogDeleteBoard(boardId);
							}
							// mouseover: () => console.log("Copy Button Mouseover")
							// You can use any event listener from here
						},
						divider: "top" // top, bottom, top-bottom
					}
				];

				let tclick = new ContextMenu({
					target: '#' + menuDropDownId,
					mode: "dark", //"light", // default: "dark"
					menuItems,
					triggerType: 'click'
				});

				let contextMenu = tclick.init();
				tclick.openMenu (contextMenu, e);
			}
		});
	}

	/**
	 *
	 * @param HTMLElement el
	 */
	o.addDropDownItemContextMenu = function(el, e){
		if($(el).attr('dropdownready') == undefined){
			$(el).attr('dropdownready', 1);

			let $menuDropDown = $(el);
			if($(el).attr('id') == undefined){
				$(el).attr('id', 'kanban-' + $menuDropDown.attr('data-eid'))
			}

			let menuDropDownId = $(el).attr('id');
			let dataType = $(el).attr('data-type');


			let menuItems = [];

			// Assign me to card
			menuItems.push({
				content: '<i class="fa fa-user-plus" ></i>' + o.langs.AssignMe,
				events: {
					click: function (e) {
						let sendData = {
							'fk_kanban': o.config.fk_kanban,
							'card-id': el.getAttribute('data-objectid')
						};

						o.callKanbanInterface('assignMeToCard', sendData, function(response){
							if(response.result > 0) {
								// recupérer les bonnes infos
								o.jkanban.replaceElement(el, response.data);
							}
						});
					}
				}
			});

			// Un assign me to card
			menuItems.push({
				content: '<i class="fa fa-user-minus" ></i>' + o.langs.UnAssignMe,
				events: {
					click: function (e) {
						let sendData = {
							'fk_kanban': o.config.fk_kanban,
							'card-id': el.getAttribute('data-objectid')
						};

						o.callKanbanInterface('removeMeFromCard', sendData, function(response){
							if(response.result > 0) {
								// recupérer les bonnes infos
								o.jkanban.replaceElement(el, response.data);
							}
						});
					}
				}
			});


			// Card Tags
			menuItems.push({
				content: '<i class="fa fa-tags" ></i>' + o.langs.AddRemoveTags,
				events: {
					click: function (e) {
						o.dialogCardTags(el);
					}
				}
			});


			// Clone card menu : il n'est pas possible de cloner une US ou une tache : dans ces cas là il faut spliter le temps
			if(dataType != undefined && dataType != 'scrum-user-story' && dataType != 'scrum-user-story-task')
			{
				menuItems.push({
					content: o.menuIcons.copyIcon + o.langs.CardClone,
					events: {
						click: function (e) {
							o.dialogCloneCard(e);
						}
					}
				});
			}

			// Split US card Dialog
			if(dataType != undefined && dataType == 'scrum-user-story') {
				menuItems.push({
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
				menuItems.push({
					content: '<i class="fa fa-columns" ></i>' + o.langs.CardSplit,
					events: {
						click: function (e) {
							o.dialogSplitCard(el);
						}
					}
				});
			}

			menuItems.push({
				content: o.menuIcons.deleteIcon + o.langs.Delete,
				events: {
					click: function (e) {
						o.deleteCardDialog(el.getAttribute('data-eid'));
					}
					// mouseover: () => console.log("Copy Button Mouseover")
					// You can use any event listener from here
				},
				divider: "top" // top, bottom, top-bottom
			});



			let tclick = new ContextMenu({
				target: '#' + menuDropDownId,
				mode: "dark", //"light", // default: "dark"
				menuItems,
				triggerType: 'contextmenu'
			});

			let contextMenu = tclick.init();
			tclick.openMenu (contextMenu, e);
		}
	}

	/**
	 * init Highlight
	 */
	o.initHighlight = function(){

		o.pressEscapeCallback(()=>{o.removeHighlight()});

		// put it into the DOM
		$('body').prepend($('<div id="press-esc-to-cancel" class="small-notify">'+ o.langs.PressEscapeToAvoid +'</div>'));


		const toggleHighLight = function($el, usId){
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
			toggleHighLight($(this), usId);
		});

		$(document).on('click','.kanban-item[data-type="scrum-user-story-task"] .highlight-scrum-task', function(e) {
			e.stopPropagation();
			const usId = $(this).closest('.kanban-item').attr('data-fk_scrum_user_story_sprint');
			toggleHighLight($(this), usId);
		});

		// Fermeture au click sur le message de fermeture
		$(document).on('click', '#press-esc-to-cancel', function() {
			o.removeHighlight();
		});
	}

	/**
	 * remove all Highlight
	 * @param targetsSelector
	 * @param targetBringdown
	 */
	o.removeHighlight = function(targetsSelector = undefined){

		if(targetsSelector != undefined){
			$(targetsSelector + ' [data-highlight="1"]').attr('data-highlight', '0');
			$(targetsSelector + '.highlight-element').removeClass('highlight-element');
		}else{
			$('[data-highlight="1"]').attr('data-highlight', '0');
			$('.highlight-element').removeClass('highlight-element');
		}

		if(targetsSelector == undefined || $('[data-highlight="1"]').length == 0){
			$('#press-esc-to-cancel').removeClass('--active');
			$('.bringdown-element').removeClass('bringdown-element');
		}

	}

	/**
	 * highlight element
	 * @param targetsSelector
	 * @param targetBringdown
	 */
	o.setHighlight = function(targetsSelector, targetBringdown = undefined){
		$('#press-esc-to-cancel').addClass('--active');
		$(targetsSelector).addClass('highlight-element');

		if(targetBringdown != undefined){
			o.setBringdown(targetBringdown);
		}
	}


	/**
	 * bringdown element
	 * @param targetsSelector
	 */
	o.setBringdown = function(targetsSelector){
		$(targetsSelector).addClass('bringdown-element');
	}

	/**
	 * Create an event for eascape key press
	 * @param callbackFunction
	 */
	o.pressEscapeCallback = function (callbackFunction){
		document.onkeydown = function(evt) {
			evt = evt || window.event;
			var isEscape = false;
			if ("key" in evt) {
				isEscape = (evt.key === "Escape" || evt.key === "Esc");
			} else {
				isEscape = (evt.keyCode === 27);
			}
			if (isEscape) {
				callbackFunction();
			}
		};
	}

	/**
	 * Assign contact user to card by pressing space key
	 * @param callbackFunction
	 */
	o.initAssignMeByPressingSpaceKey = function (callbackFunction){
		document.addEventListener('keyup', event => {
			if (event.code === 'Space') {
				// todo limit ajx call to compatible elements
				$('.kanban-item:hover').each(function() {
					let sendData = {
						'fk_kanban': o.config.fk_kanban,
						'card-id': $( this ).attr('data-objectid')
					};
					let element = o.jkanban.findElement($( this ).attr('data-eid'));

					o.callKanbanInterface('toggleAssignMeToCard', sendData, function(response){
						if(response.result > 0) {
							// recupérer les bonnes infos
							o.jkanban.replaceElement(element, response.data);
						}
					});
				});
			}
		})
	}


	/**
	 * Remplace la valeur d'un paramètre dans une URL
	 * @param {string} url
	 * @param {string} param the get param
	 * @param {string} paramVal the new value
	 * @returns {string}
	 */
	o.updateURLParameter = function (url, param, paramVal)
	{
		var TheAnchor = null;
		var newAdditionalURL = "";
		var tempArray = url.split("?");
		var baseURL = tempArray[0];
		var additionalURL = tempArray[1];
		var temp = "";

		if (additionalURL)
		{
			var tmpAnchor = additionalURL.split("#");
			var TheParams = tmpAnchor[0];
			TheAnchor = tmpAnchor[1];
			if(TheAnchor)
				additionalURL = TheParams;

			tempArray = additionalURL.split("&");

			for (var i=0; i<tempArray.length; i++)
			{
				if(tempArray[i].split('=')[0] != param)
				{
					newAdditionalURL += temp + tempArray[i];
					temp = "&";
				}
			}
		}
		else
		{
			var tmpAnchor = baseURL.split("#");
			var TheParams = tmpAnchor[0];
			TheAnchor  = tmpAnchor[1];

			if(TheParams)
				baseURL = TheParams;
		}

		if(TheAnchor)
			paramVal += "#" + TheAnchor;

		var rows_txt = temp + "" + param + "=" + paramVal;
		return baseURL + "?" + newAdditionalURL + rows_txt;
	}

	o.initDarkMod = function(){
		$(function() {
			o.themeColorScheme = localStorage.getItem('data-theme-color-scheme');
			if(o.themeColorScheme== null){
				o.themeColorScheme = 'light';
				if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
					o.themeColorScheme = 'dark';
				}
			}

			localStorage.setItem('data-theme-color-scheme',o.themeColorScheme);
			$('html').attr('data-theme-color-scheme' , o.themeColorScheme);
		});


		$(document).on('click','#light-bulb-toggle', function(e) {
			e.stopPropagation();
			o.toggleDarkMod();
		});
	}

	o.toggleDarkMod = function (){
		if(o.themeColorScheme=='light' || o.themeColorScheme==''){
			o.themeColorScheme = 'dark';
		}else{
			o.themeColorScheme = 'light';
		}
		$('html').attr('data-theme-color-scheme',o.themeColorScheme);
		localStorage.setItem('data-theme-color-scheme',o.themeColorScheme);
	};


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

	/**
	 *
	 * @param {HTMLElement} el
	 */
	o.dialogSplitCard = function(el){

		const type = el.getAttribute('data-type');
		const objectId = el.getAttribute('data-objectid');

		// init quantity vars
		let qtyPlannedCurentItem; // quantité planifiée sur la carte d'origine
		let qtyRemain; // Quantité restante disponible sur la quantité planifiée, ex j'ai planifié 10H mais j'ai saisi 4heures de temps passé sur la tâche, j'ai donc consommé 4H sur les 10H il reste donc 6H

		let lineItemCounter = 0;


		/**
		 * @returns {boolean} true if split is good or false if not
		 * also toggle disable buttons
		 */
		const checkSplitDialogBTN = function (){

			let $addLineBtn =  $('#add-split-line');
			if($addLineBtn.length > 0){
				if(qtyRemain == 0){
					$addLineBtn.css('visibility','hidden');
				}else{
					$addLineBtn.css('visibility','');
				}
			}

			//ne pas permettre l'ajout si l'us n'est pas entièrement splitée
			if(type == 'scrum-user-story'){
				let $acceptBtn =  $('[data-btn-role="accept"]');
				if($acceptBtn.length > 0){
					if(qtyRemain > 0){
						$acceptBtn.prop('disabled', true);
						return false;
					}else{
						$acceptBtn.prop('disabled', false);
					}
				}
			}

			return true;
		}

		/**
		 * @param {object} tplVars
		 * @returns {string}
		 */
		const getSplitItemTpl = function(tplVars){

			tplVars = Object.assign(
				{
					label: '',
					qty_planned_min: '',
					qty_planned_max: '',
					qty_planned: '',
					data_lastValue: 0,
					maxScrumTaskStepQty: o.config.maxScrumTaskStepQty
				},
				tplVars
			)

			let content = '';
			content+= '<div class="dialog-form-control new-split-item-line">';
			content+= '	<div class="dialog-form-item">';
			content+= '		<input type="number" class="split-qty-planned new-item-qty-planned" data-lastvalue="' + tplVars.data_lastValue + '"   name="new-item-qty-planned" value="' + tplVars.qty_planned + '" min="' + tplVars.qty_planned_min + '" max="' + tplVars.qty_planned_max + '" step="' + tplVars.maxScrumTaskStepQty + '" />';
			content+= '	</div>';
			content+= '	<div class="dialog-form-item">';
			content+= '		<input type="text" class="split-item-label" name="new-item-label" value="' + o.htmlEntities(tplVars.label) + '" />';
			content+= '	</div>';
			content+= '	<div class="dialog-form-item">';
			content+= '		<span class="dialog-form-icon-btn btn-remove-split-line-card"><span class="fa fa-minus" title="'+o.htmlEntities(o.langs.RemoveLine)+'"></span></span>';
			content+= '	</div>';
			content+= '</div>';

			return content;
		}

		/**
		 * Ajoute une ligne avec prise en compte des données en cours
		 * @param {object} tplVars
		 */
		const addSplitLine = function(qty_planned_to_add = 0){

			qty_planned_to_add = parseFloat(qty_planned_to_add);

			lineItemCounter++;


			let maxQtyPlannedForLine = parseFloat(qtyRemain);
			if(parseFloat(o.config.maxScrumTaskMaxQty) > 0 && maxQtyPlannedForLine > parseFloat(o.config.maxScrumTaskMaxQty)){
				maxQtyPlannedForLine = parseFloat(o.config.maxScrumTaskMaxQty);
			}

			// todo mettre à jour les données d'entrées
			let newLine = getSplitItemTpl({
				label: $('[name="curent-item-label"]').val(),
				qty_planned_min: 0,
				qty_planned_max: maxQtyPlannedForLine,
				qty_planned: qty_planned_to_add,
				data_lastValue: 0,
			});

			let newLineAppended = $(newLine).appendTo('#split-line-form-container');
			newItemQuantityPlannedChange(newLineAppended.find('[name="new-item-qty-planned"]'));
			checkSplitDialogBTN();
		}

		/**
		 * Met à jour les quantité
		 * @param newPlannedQtyMvt
		 */
		const updateSplitQty = function(newPlannedQtyMvt = 0){
			newPlannedQtyMvt = parseFloat(newPlannedQtyMvt);

			if(qtyRemain - newPlannedQtyMvt < 0){
				newPlannedQtyMvt = qtyRemain;
			}

			qtyRemain = Math.round((qtyRemain - newPlannedQtyMvt) * 100) / 100;

			if(type == 'scrum-user-story'){
				// cas particulier des us
				$('#split-qty-task-planned').html(qtyRemain);
				$('#split-qty-task-planned').html(qtyPlannedCurentItem-qtyRemain);
			}
			else{
				qtyPlannedCurentItem = Math.round((qtyPlannedCurentItem - newPlannedQtyMvt) * 100) / 100;
				$('#curent-item-qty-planned').val(qtyPlannedCurentItem);
			}

			$('#split-qty-remain').html(qtyRemain);

			// mise à jour du max sur les inputs
			$('.new-item-qty-planned').each(function( index ) {

				let maxQtyPlannedForLine = parseFloat(qtyRemain);
				if(parseFloat(o.config.maxScrumTaskMaxQty) > 0 && maxQtyPlannedForLine > parseFloat(o.config.maxScrumTaskMaxQty)){
					maxQtyPlannedForLine = parseFloat(o.config.maxScrumTaskMaxQty);
				}

				if(maxQtyPlannedForLine < parseFloat($(this).attr('max'))){
					maxQtyPlannedForLine = parseFloat($(this).attr('max'));
				}

				$(this).attr('max', maxQtyPlannedForLine);
			})

			checkSplitDialogBTN();

			return newPlannedQtyMvt; // retourne la valeur appliquée
		}


		// Ajout au click sur button plus d'une ligne
		$(document).off('click', '#add-split-line'); // suppression du handler existant
		$(document).on('click', '#add-split-line', function (){
			addSplitLine(0);
		});

		// Fermeture au click sur le message de fermeture
		$(document).off('click', '.btn-remove-split-line-card'); // suppression du handler existant
		$(document).on('click', '.btn-remove-split-line-card', function() {
			let newLineQty = $(this).closest('.new-split-item-line').find('.split-qty-planned').val();
			updateSplitQty(-parseFloat(newLineQty));// re-alloue les quantités
			$(this).closest('.new-split-item-line').remove();
		});

		// Update des calcules
		$(document).off('change', '.new-item-qty-planned'); // suppression du handler existant
		$(document).on('change', '.new-item-qty-planned', function() {
			newItemQuantityPlannedChange($(this));
		});

		/**
		 *
		 * @param {jQuery} $el
		 */
		 function newItemQuantityPlannedChange($el) {
			let newLineQty = parseFloat($el.val());
			let oldLineQty = parseFloat($el.attr('data-lastvalue'));
			newLineQty = oldLineQty + updateSplitQty(newLineQty-oldLineQty);
			newLineQty = Math.round((newLineQty) * 100) / 100;
			$el.val(newLineQty); // force la valeur saisie avec la valeur de retour de updateSplitQty
			$el.attr('data-lastvalue', newLineQty);
		}

		o.callKanbanInterface('getScrumCardData', {'id': objectId}, function(response){
			if(response.result > 0) {
				// recupérer les info de la card
				let content = '';
				let canSplit = false;

				// Géneration du formulaire
				if(response.data.elementObject != undefined){

					// mise à jour des quantités de départ
					qtyPlannedCurentItem = parseFloat(response.data.elementObject.qty_planned);
					qtyRemain = parseFloat(response.data.elementObject.qty_remain_for_split);


					content+= '<div class="dialog-form-head" >';

					content+= '<span class="dialog-form-head-item">' + o.langs.QtyPlanned + ' : <span id="split-qty-planned" class="dialog-form-head-number"  >' + response.data.elementObject.qty_planned + '</span></span>';
					if(type == 'scrum-user-story'){
						content+= '<span class="dialog-form-head-item">' + o.langs.QtyScrumTaskAlreadySplited + ' : ';
						content+= '<span id="split-qty-task-planned" class="dialog-form-head-number" >' + response.data.elementObject.qty_task_planned + '</span>';
						content+= '</span>';
					}
					content+= '<span class="dialog-form-head-item">' + o.langs.QtyConsumed + ' : <span id="split-qty-consumed" class="dialog-form-head-number"  >' + response.data.elementObject.qty_consumed + '</span></span>';
					content+= '<span class="dialog-form-head-item split-qty-remain">' + o.langs.QtyRemainToSplit + ' : <span id="split-qty-remain" class="dialog-form-head-number"  >' + response.data.elementObject.qty_remain_for_split + '</span></span>';

					content+= '</div>';



					content+='<div class="dialog-form-body" id="split-line-form-container" >';

					canSplit = response.data.elementObject.qty_remain_for_split > 0;
					if(canSplit){

						let label = response.data.label;
						if(response.data.elementObject.label != undefined && response.data.elementObject.label.length){
							label = response.data.elementObject.label;
						}

						content+= '<div class="dialog-form-control  curent-split-item-line" >';
						content+= '	<div class="dialog-form-item">';
						let qtyPlannedvalueDisplayed = response.data.elementObject.qty_planned;
						// if(type == 'scrum-user-story'){ qtyPlannedvalueDisplayed = ''; }
						content+= '		<input type="number" id="curent-item-qty-planned" class="split-qty-planned" step="any" max="'+response.data.elementObject.qty_planned+'" name="curent-item-qty-planned" readonly value="'+qtyPlannedvalueDisplayed+'"/>';
						content+= '	</div>';

						let labelDislayed = label;
						// if(type == 'scrum-user-story'){ labelDislayed = ''; }
						content+= '	<div class="dialog-form-item">';
						content+= '		<input type="text"  class="split-item-label"  name="curent-item-label"  data-qty_remain_for_split="' + response.data.elementObject.qty_remain_for_split + '" readonly value="'+ o.htmlEntities(labelDislayed) +'" />';
						content+= '	</div>';


						content+= '	<div class="dialog-form-item">';
						content+= '		<span class="dialog-form-icon-btn" id="add-split-line">';
						if(type == 'scrum-user-story'){
							content+= '			<span class="btn-add fa fa-plus" title="'+o.htmlEntities(o.langs.AddScrumTaskLine)+'"></span>';
						}else{
							content+= '			<span class="btn-add fa fa-cut" title="'+o.htmlEntities(o.langs.SplitScrumTask)+'"></span>';
						}
						content+= '		</span>';
						content+= '	</div>';

						content+= '</div>';


						//
						// let maxQtyPlannedForLine = parseFloat(response.data.elementObject.qty_remain_for_split);
						// if(parseFloat(o.config.maxScrumTaskMaxQty) > 0 && maxQtyPlannedForLine > parseFloat(o.config.maxScrumTaskMaxQty)){
						// 	maxQtyPlannedForLine = parseFloat(o.config.maxScrumTaskMaxQty);
						// }
						// content+= getSplitItemTpl({
						// 	label : label,
						// 	qty_planned_min: 0,
						// 	qty_planned_max: maxQtyPlannedForLine,
						// 	qty_planned: 0,
						// });


					}else{
						content+='<strong>' + o.langs.NotSplittable + '</strong>';
					}

					content+='</div>';
				}
				else{
					content+='<div class="error" >Error data elementObject</div>';
				}

				const splitDialog = new Dialog({
					title: o.langs.SplitCard,
					content: content,
					onAccept: function(){

						if(canSplit && checkSplitDialogBTN()){
							// récupération des données de formulaire en Html5
							let sendData = {
								'id': objectId,
								'form': o.serializeFormJson($(splitDialog.dialog).find('form'))
							};

							o.callKanbanInterface('splitScrumCard', sendData, function(){
								o.refreshAllBoards();
							});

							return true;
						}

						return false;
					}
					,onOpen: function(){

						if(type == 'scrum-user-story' && parseFloat(o.config.maxScrumTaskMaxQty) > 0){
							let qtyRemainToSplit = parseFloat(qtyRemain);
							while(qtyRemainToSplit >= parseFloat(o.config.maxScrumTaskMaxQty)){
								addSplitLine(o.config.maxScrumTaskMaxQty);
								qtyRemainToSplit-=parseFloat(o.config.maxScrumTaskMaxQty);
							}

							if(qtyRemainToSplit>0){
								addSplitLine(qtyRemainToSplit);
							}
						}

						checkSplitDialogBTN();
					}
				});

				// utilisation par les promesses arrété pour l'instant
				// splitDialog.waitForUser().then((userValidate) => {
				// 	if(canSplit && userValidate){
				// 		// récupération des données de formulaire en Html5
				// 		let sendData = {
				// 			'id': objectId,
				// 			'form': o.serializeFormJson($(splitDialog.dialog).find('form'))
				// 		};
				//
				// 		o.callKanbanInterface('splitScrumCard', sendData, function(){
				// 			o.refreshAllBoards();
				// 		});
				//
				// 	}else{
				// 		// user cancel
				// 	}
				// });
			}
		});
	}

	/**
	 * 	A function to serialize an html form to JSON
	 * @param {JQuery} $el
	 * @returns {{}}
	 */
	o.serializeFormJson = function($el) {
		var obj = {};
		var a = $el.serializeArray();

		$.each(a, function() {
			if (obj[this.name]) {
				if (!obj[this.name].push) {
					obj[this.name] = [obj[this.name]];
				}
				obj[this.name].push(this.value || '');
			} else {
				obj[this.name] = this.value || '';
			}
		});
		return obj;
	};

	o.dialogCloneCard = function(el){
		// TODO detect type of element before
		//  User story and scrum task can not be cloned

		const content = '<h1 style="text-align: center;">Work in progress</h1>';


		const cloneDialog = new Dialog({
			title: o.langs.CloneCard,
			content: content
		});


		cloneDialog.waitForUser().then((userValidate) => {
			if(userValidate){

			}else{
				// user cancel
			}
		});
	}


	o.dialogCardTags = function(el){

		let sendData = {
			'fk_kanban': o.config.fk_kanban,
			'card-id': el.getAttribute('data-objectid')
		};

		let content = '<label>';
			content+= '<select id="card-tags" name="tags" multiple style="width: 100%"></select>';
			content+= '</label>';

		const tagsDialog = new Dialog({
			title: o.langs.AddRemoveTags,
			content: content,
			onOpen: function(){
				o.callKanbanInterface('getCardTags', sendData, function(response){
					if(response.result > 0) {
						let inputCardTags = $('#card-tags');
						o.updateInputListOptionsMultiselect(inputCardTags, response.data.tags);
						inputCardTags.select2({
							dropdownParent: $('.kanban-dialog')
						});
						$('.kanban-dialog').css({'overflow':'visible'});
					}
				});
			},
			onAccept: function(){
				sendData.tags = $('#card-tags').val();

				o.callKanbanInterface('updateCardTags', sendData, function(response) {
					if (response.result > 0) {
						tagsDialog.dialog.close()
					}
				});
			}
		});
	}


	o.deleteCardDialog = function(eid){
		// TODO detect type of element before
		//  User story and scrum task can not be deleted ?

		let content = '<h1 style="text-align: center;">Work in progress to delete ' + eid + '</h1>'
			+ '<p>Pour l\'instant la suppression se fait sans distinction, <strong>les cards spéciales</strong> ne sont pas prisent en compte : US, US-Tâches etc...</p>'

		const delDialog = new Dialog({
			title: o.langs.DeleteCardDialogTitle,
			dialogClass: '--danger',
			content: content
		});

		delDialog.waitForUser().then((userValidate) => {
			if(userValidate){
				o.delItem(eid);
			}else{
				// user cancel
			}
		});
	}

	o.dialogDeleteBoard = function(boardId){
		// TODO detect type of element before
		//  User story and scrum task can not be deleted ?

		let content = '<h1 style="text-align: center;">Work in progress to delete ' + boardId + '</h1>'
						+ '<p>Pour l\'instant la suppression se fait sans distinction, les cards présentes ne sont pas prisent en compte ni redistribuées.</p>'
						+ '<p>De plus les règles sur la supression du backlog et du done ne sonts pas établies.</p>';


		const splitDialog = new Dialog({
			title: o.langs.DeleteCardDialogTitle,
			dialogClass: '--danger',
			content: content
		});

		splitDialog.waitForUser().then((userValidate) => {
			if(userValidate){
				o.delKanbanList(boardId);
			}else{
				// user cancel
			}
		});
	}

	o.htmlEntities = function(str) {
		return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}

	/**
	 * add array element into select field
	 *
	 * @param {jQuery} target The select input jquery element
	 * @param {array} data an array of object
	 */
	o.updateInputListOptionsMultiselect = function(target, data = false)
	{
		/* Remove all options from the select list */
		target.empty();
		target.prop("disabled", true);

		if(Array.isArray(data))
		{
			/* Insert the new ones from the array above */
			for(var i= 0; i < data.length; i++)
			{
				let item = data[i];
				let newOption =  $('<option>', {
					value: item.id,
					text : item.label
				});

				if(item.selected > 0){
					newOption.prop('selected', true);
				}

				target.append(newOption);
			}

			if(data.length > 0){
				target.prop("disabled", false);
			}
		}
	}

})(scrumKanban);
