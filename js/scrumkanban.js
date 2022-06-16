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
				// callback when any board's item are dragged
				console.log(target.parentElement.getAttribute('data-id'));
				console.log(el, target, source, sibling);

				o.setEventMessage('DSL le drop n\'est pas encore géré', false)

				o.clearView();
			},
			dragendEl : function (el) {
				// callback when any board's item stop drag
				o.setEventMessage('Work in progress drag end el', false);
			},
			dragBoard        : function (el, source) {
				// callback when any board stop drag
				o.setEventMessage('Work in progress drag Board', false);

				// a cause d'un bug d'affichage j'enlève le footer lors du déplacement
				let boardSelector = el.getAttribute('data-id');
				$('.kanban-board[data-id=' + boardSelector + '] footer').hide();
			},
			dragendBoard     : function (el) {
				// callback when any board stop drag
				o.setEventMessage('Work in progress drag end Board', false);

				// reaffiche le bouton du footer
				let boardSelector = el.getAttribute('data-id');
				$('.kanban-board[data-id=' + boardSelector + '] footer').slideDown();
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

		// var toDoButton = document.getElementById('addToDo');
		// toDoButton.addEventListener('click',function(){
		// 	o.jkanban.addElement(
		// 		'_todo',
		//         {
		//             'title':'Test Add',
		//         }
		//     );
		// });

		// Add new list (column)
		var addBoardDefault = document.getElementById('addkanbancol');
		addBoardDefault.addEventListener('click', function () {
			o.addKanbanList(o.langs.NewList);
		});

		// var removeBoard = document.getElementById('removeBoard');
		// removeBoard.addEventListener('click',function(){
		// 	o.jkanban.removeBoard('_done');
		// });
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

		let kanbanDialogId = '#kanbanitemdialog-' + dialogId;
		if(document.getElementById(kanbanDialogId) == undefined){
			$('body').append( $('<div id="kanbanitemdialog-' + dialogId + '" ></div>')); // put it into the DOM
		}

		$target = $(kanbanDialogId);

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

	o.addKanbanCardToList = function(listName){

		let sendData = {
			'fk_kanban': o.config.fk_kanban,
			'fk_kanbanlist' : o.getDolKanbanIdFromJKanbanDomId(listName)
		};

		o.callKanbanInterface('getAllItemToList', sendData, function(response){
			if(response.result > 0) {
				// recupérer les bonnes infos
				o.jkanban.addElement( listName, response.data);
			}
		});
	}

	/**
	 *
	 * @param domId
	 * @returns string
	 */
	o.getDolKanbanIdFromJKanbanDomId = function (domId){
		// remove board- part
		return domId.slice(6, domId.length);
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


		if(sendData != undefined && typeof sendData === 'object'){
			ajaxData = Object.assign(ajaxData, sendData);
		}


		$.ajax({
			method: 'POST',
			url: o.config.interface_kanban_url,
			dataType: 'json',
			data: ajaxData,
			success: function (response) {

				callBackFunction(response);

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
