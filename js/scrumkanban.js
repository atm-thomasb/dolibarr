// Utilisation d'une sorte de namespace en JS
let scrumKanban = {};
(function(o) {

	// lang par défaut, les valeurs son ecrasées lors du chargement de la page en fonction de la langue
	o.config = {};

	// lang par défaut, les valeurs son ecrasées lors du chargement de la page en fonction de la langue
	o.langs = {
		NewList:"Nouvelle liste",
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
			boards  :o.getBoards()
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

		// Add new list
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

	o.getBoards = function (){
		return [
			{
				'id' : '_backlog',
				'title'  : o.langs.BackLog,
				'class' : 'info',
				'item'  : [
					{
						'title':'My Task Test',
					},
					{
						'title':'Buy Milk',
					}
				]
			},
			{
				'id' : '_working',
				'title'  : 'Working',
				'class' : 'warning',
				'item'  : [
					{
						'title':'Do Something!',
					},
					{
						'title':'Run?',
					}
				]
			},
			{
				'id' : '_done',
				'title'  : 'Done',
				'class' : 'success',
				'item'  : [
					{
						'title':'All right',
					},
					{
						'title':'Ok!',
					}
				]
			}
		];
	}
})(scrumKanban);
