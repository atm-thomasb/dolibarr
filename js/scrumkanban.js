// Utilisation d'une sorte de namespace en JS
let scrumKanban = {};
(function(o) {

	// TODO see https://htmldom.dev/drag-to-scroll/

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
		fk_kanban : false,
		token: false // to set at init
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
		CloseDialog:"Fermer"
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

		o.jkanban = new jKanban({
			element : '#scrum-kanban',
			gutter  : '5px',
			click : function(el){
				// callback when any board's item are clicked
				o.cardClick(el);
			},
			context: function(el, e) {
				// callback when any board's item are right clicked
				console.log("Trigger on all items right-click!");
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

				// a cause d'un bug d'affichage j'enlève le footer lors du déplacement
				let boardSelector = el.getAttribute('data-id');
				$('.kanban-board[data-id=' + boardSelector + '] footer').hide();
			},
			dropBoard: function (el, target, source, sibling) {
				// callback when any board stop drag


				// TODO
				console.log(el);

				let sendData = {
					'fk_kanban': o.config.fk_kanban,
					'list-id': o.getDolListIdFromBoard(el),
					'before-list-id': o.getDolListIdFromBoard(sibling)
				};

				o.callKanbanInterface('changeListOrder', sendData, function(response){
					// do stuff ?
				});

				// reaffiche le bouton du footer
				let boardSelector = el.getAttribute('data-id');
				$('.kanban-board[data-id=' + boardSelector + '] footer').slideDown();
			},
			dragendBoard     : function (el) {

			},
			buttonClick: function(el, boardId) {
				// callback when the board's button is clicked

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
			// 	},
			// 	{
			// 		id: "_working",
			// 		title: "Working (Try drag me too)",
			// 		class: "warning",
			// 		item: [
			// 			{
			// 				title: "Do Something!"
			// 			},
			// 			{
			// 				title: "Run?"
			// 			}
			// 		]
			// 	},
			// 	{
			// 		id: "_done",
			// 		title: "Done (Can drop item only in working)",
			// 		class: "success",
			// 		dragTo: ["_working"],
			// 		item: [
			// 			{
			// 				title: "All right"
			// 			},
			// 			{
			// 				title: "Ok!"
			// 			}
			// 		]
			// 	}
			// ]
		});

		// Get all board
		o.getAllBoards();


		// Add new list (column)
		let addBoardDefault = document.getElementById('addkanbancol');
		addBoardDefault.addEventListener('click', function () {
			o.addKanbanList(o.langs.NewList);
		});

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
	o.dialogIFrame = function (dialogId, url, label = ''){

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

			},
			close: function (event, ui) {

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
		// TODO create in ajax before remove
		o.jkanban.removeBoard(listName)
	}
	// var removeBoard = document.getElementById('removeBoard');
	// removeBoard.addEventListener('click',function(){
	// 	o.jkanban.removeBoard('_done');
	// });

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
				o.jkanban.addBoards(response.data)
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
					o.setEventMessage(response.msg, response.result > 0 ? true : false );
				}
			},
			error: function (err) {
				o.setEventMessage(o.langs.errorAjaxCall, false);
			}
		});
	}

})(scrumKanban);
