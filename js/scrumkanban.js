// Utilisation d'une sorte de namespace en JS
let scrumKanban = {};
(function(o) {

	/**
	 * Store the max tms of all board element 
	 * used to compare with database and determine if need update
	 * @type {number}
	 */
	o.lastBoardUpdate = 0;

	/**
	 * Congig par défaut, les valeurs sont écrasées lors du chargement de la page en fonction de la configuration transmise
	 * @type {{}}
	 */
	o.config = {
		interface_kanban_url: '../scripts/interface-kanban.php',
		interface_liveupdate_url: '../scripts/interface-liveupdate.php',
		fk_kanban : false
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

		if(langs && typeof langs === 'object'){
			o.langs = Object.assign(o.langs, langs);
		}

		console.log(o.langs);
		o.jkanban = new jKanban({
			element : '#scrum-kanban',
			gutter  : '5px',
			click : function(el){
				o.cardClick(el);
			},
			context: function(el, e) {
				console.log("Trigger on all items right-click!");
			},
			dropEl: function(el, target, source, sibling){
				console.log(target.parentElement.getAttribute('data-id'));
				console.log(el, target, source, sibling);

				o.clearView();
			},
			buttonClick: function(el, boardId) {

				o.clearView();

				o.jkanban.addElement(boardId, {
					title: o.langs.NewCard
				});

				// console.log(el);
				// console.log(boardId);
				// create a form to enter element
				// var formItem = document.createElement("form");
				// formItem.setAttribute("class", "itemform");
				// formItem.innerHTML =
				// 	'<div class="add-item-form-container">' +
				// 		'<input class="form-control" autofocus />' +
				// 		'<button type="submit" class="btn btn-primary btn-xs pull-right">Submit</button>' +
				// 		'<button type="button" id="CancelBtn" class="btn btn-default btn-xs pull-right">Cancel</button>' +
				// 	'</div>';
				//
				// o.jkanban.addForm(boardId, formItem);
				// formItem.addEventListener("submit", function(e) {
				// 	e.preventDefault();
				// 	var text = e.target[0].value;
				// 	o.jkanban.addElement(boardId, {
				// 		title: text
				// 	});
				// 	formItem.parentNode.removeChild(formItem);
				// });
				// document.getElementById("CancelBtn").onclick = function() {
				// 	formItem.parentNode.removeChild(formItem);
				// };
			},
			itemAddOptions: {
				enabled: true,
				content: '+ ' + o.langs.NewCard,
				class: 'kanban-list-add-button',
				footer: true
			}
		});


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
		alert(el.innerHTML);
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
	o.dialogIFrame = function (dialogId, $target, url, label = ''){
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

		// TODO create in ajax before add
		o.jkanban.addBoards(
			[{
				'id' : '_default',
				'title'  : listName,
				'item'  : []
			}]
		)
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
			'fk_kanban': o.config.fk_kanban,
			'token': o.newToken,
			'action': 'getAllBoards',
		};

		o.callKanbanInterface(sendData, function(data){
			if(data.result > 0) {
				// recupérer les bonnes infos
				o.jkanban.addBoards(
					[{
						'id' : '_default',
						'title' : listName,
						'item' : []
					}]
				)
			}
		});
	}

	o.callKanbanInterface = function (sendData, callBackFunction){
		$.ajax({
			method: 'POST',
			url: o.config.interface_kanban_url,
			dataType: 'json',
			data: sendData,
			success: function (data) {

				callBackFunction(data);

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

})(scrumKanban);
