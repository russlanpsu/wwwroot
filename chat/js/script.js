var updateIntervalId;
var msgIndex = 0;
var updateRequestEnabled = false;
var $xhrUpdate;

function setIntervalUpdate(){
	return setInterval(function(){
		if (updateRequestEnabled) update();
	}, 50);
}

$(function(){

	update();
//	updateIntervalId = setIntervalUpdate();

	$('.user_item').click(function(){

		if (!($xhrUpdate === undefined) && ($xhrUpdate.readyState != 4)){
			$xhrUpdate.abort();
			updateRequestEnabled = true;
		}

		var $activeUser = $('.active_user');
		if ($activeUser.length > 0){
			$($activeUser[0]).removeClass('active_user');
		}

		var $this = $(this);
		$this.addClass('active_user');
		$this.find('.msg_count').html('');
		//$this.find('.last_msg').html('');

		$('#history').attr('page-index', 0);

		getHistory();

		if (!(updateIntervalId === undefined)){
			clearInterval(updateIntervalId);
		}
	//	updateIntervalId = setIntervalUpdate();
		update();
	})
	
	$('#btnSend').click(function(){
		sendMessage();
	})
	
	$('#msgField').keypress(function(event){
		if (event.which == 13){
			sendMessage();			
			event.preventDefault();
		}
	})
	
	$('#msgField').bind('input propertychange', function() {
		$('#btnSend').attr('disabled', $(this).val().length == 0);
		$('#status').html("");
	})

	$('#history').scroll(function(){

		var $this = $(this);
		console.log($this.scrollTop());

		if ($this.scrollTop() == 0){
			//$($this.children()[0]).before('<div>test</div>');

			var pageIndex = +$this.attr('page-index');
			if (pageIndex != -1) {
				pageIndex += 1;
				var isLastPage = getHistory(pageIndex);
				if (isLastPage) {
					pageIndex = -1;
				}
				$this.attr('page-index', pageIndex);
			}

		}

	})

})

/*function CreateUsersList_old(targetListId, users, excludedUserId){
	
	if (excludedUserId === undefined){
		excludedUserId = -1;
	}
	
	var fromUserList = document.getElementById(targetListId);			
    for(var i=0; i<users.length; i++){
        var user = users[i];
        if (user.id != excludedUserId){
            var optionItem = document.createElement('option');
            optionItem.id = user.id;
            optionItem.innerHTML = user.name;
            fromUserList.appendChild(optionItem);
        }
    }
}*/

function renderTemplate(tmplId, target, context, appendKind){
	var $tmpl = $('#' + tmplId);

	var template = Handlebars.compile($tmpl.html());
	var rendered = template(context);

//	var ptrn =/(ftp|http|https):\/\/((\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?)/gi;
//	rendered = rendered.replace(ptrn, '<a href="$1$2" target="_blank">$2</a>');


	var $target = $('#' + target);

	switch (appendKind){

		case 'before':

			var firstChild = $target.children()[0];
			var $firstChild = $(firstChild);
			$firstChild.before(rendered);

			//$target.scrollTop($firstChild.offset().top - 10);
			$target.scrollTop($firstChild.offset().top - 10);

			break;

		case 'insert':

			$target.html(rendered);
			$target.scrollTop(document.getElementById(target).scrollHeight);
			break;

		case 'append':

			$target.append(rendered);
			$target.scrollTop(document.getElementById(target).scrollHeight);
			break;

	}
}

/*function CreateUsersList(users, excludedUserId){

	if (excludedUserId === undefined){
		excludedUserId = -1;
	};

	for (var i=0; i<users.length; i++){
		if (excludedUserId == users[i].id){
			users.splice(i, 1);
			break;
		}
	}

	var context = {users: users};

	renderTemplate('tmpl_user_list', 'cont_user_list', context, 'insert');

	$('.user_item').click(function(){

		var $activeUser = $('.active_user');
		if ($activeUser.length > 0){
			$($activeUser[0]).removeClass('active_user');
		};

		var $this = $(this);
		$this.addClass('active_user');
		$this.html($this.attr('user-name'));

		$('#history').attr('page-index', 0);

		getHistory();

		if (!(updateIntervalId === undefined)){
			clearInterval(updateIntervalId);
		}
		updateIntervalId = setInterval(function(){update()}, 5000);

	})

};*/


function sendMessage(){
	var $msgField = $('#msgField');

	//$msgField.attr('disabled', true);

	var msgText = $msgField.val();

	if (msgText.length == 0){
		return;
	}

	$msgField.val('');
	$('#btnSend').attr('disabled', true);

//	var $fromUser = $('#fromUser option:selected');
//	var $toUser = $("#toUser option:selected");
	var $toUser = $($('.active_user')[0]);
	
//	var fromUserId = $fromUser.attr('id');
	var fromUserId = getCurrentUserId();

	var toUserId = $toUser.attr('id');

	msgIndex++;
	var msg = {
		msg_id: -msgIndex,
		from_user: fromUserId,
		msg_text: msgText,
		//create_date: new Date().toLocaleTimeString(),
		isOutcoming: true
	};

	var messages = [msg];
	appendMessagesToHistory(messages, fromUserId, 'append');

	var imgEl = document.createElement('img');
	imgEl.setAttribute('src', 'img/loading.gif');
	imgEl.setAttribute('msg-id', msg.msg_id);
	imgEl.classList.add('msg-loading');

	$(".unread_msg[msg-id='" + msg.msg_id + "']").before(imgEl);

	$.ajax({
		type: 'POST',
		url:'history.php',
		data:{
				action: 'insertMessage',
				fromUser: fromUserId,
				toUser: toUserId,
				msg:msgText
		},

		success: function (data) {

			var parsedData = JSON.parse(data);

			$(".msg-loading[msg-id='" + msg.msg_id + "']").remove();
			$(".date-caption[msg-id='" + msg.msg_id + "']").html(parsedData.create_date);
			$(".unread_msg[msg-id='" + msg.msg_id + "']").attr('msg-id', parsedData.msg_id);
			$('#status').text('Сообщение отправлено');

			$msgField.attr('disable', false);

		},

		error: function(jqXHR, textStatus, errorThrown ){
			$('#status').text('textStatus: ' + textStatus + '; errorThrown: ' + errorThrown);
		}

	})
}

function appendMessagesToHistory(messages, fromUserId, appendKind){

	messages.forEach(function(msg){
		msg.isOutcoming = (msg.from_user == fromUserId);
		if (msg.isOutcoming === true){
			msg.isReaded = (msg.is_readed == 1);
		}
	});
	var context = {messages: messages};
	renderTemplate('tmpl_msg_history', 'history', context, appendKind);


}

function updateIncomingMessagesCount(messagesCount){
	$('.user_item').each(function(){
		var $this = $(this);
		var userId = $this.attr('id');
		for(var i=0; i<messagesCount.length; i++){
			var item=messagesCount[i];
			if (userId == item.user_id){
			//	$this.html($this.attr('user-name') + ' (' + item.msgs_count + ')');
				$($this.find('.msg_count')[0]).html('+' + item.msgs_count);
				$($this.find('.last_msg')[0]).html(item.last_msg);
				break;
			}
		}
	});
}

function getHistory(pageIndex){
	var result;
//	var $fromUser = $("#fromUser option:selected");
//	var $toUser = $("#toUser option:selected");
	var $toUser = $($('.active_user')[0]);

//	var fromUserId = $fromUser.attr('id');
	var fromUserId = getCurrentUserId();

	var toUserId = $toUser.attr('id');

	/*$.post('history.php',

		{action: 'getHistory',
			fromUser: fromUserId,
			toUser: toUserId,
			historyPageIndex: pageIndex},

		function(data){
			var history = JSON.parse(data);

			history.forEach(function(msg){
				msg.isOutcoming = (msg.from_user == fromUserId);
			});

			var context = {messages: history};
			renderTemplate('tmpl_msg_history', 'history', context, 'before');

		});*/

	if (pageIndex === undefined){
		pageIndex = 0;
	};
	var data = {action: 'getHistory',
		fromUser: fromUserId,
		toUser: toUserId,
		historyPageIndex: pageIndex};

	$.ajax({
		type: 'POST',

		url: 'history.php',

		data: data,

		async: false,

		success: function(data) {
			var messages = JSON.parse(data);

			var appendKind = (pageIndex == 0) ? 'insert' : 'before';
			appendMessagesToHistory(messages, fromUserId, appendKind);

			result = (messages.length == 0);

		}

	});

	return result;

}

function getUnreadMessageIds(){
	var result = [];
	$('.unread_msg').each(function(index, item){
		var msgId = item.getAttribute('msg-id');
		result.push(msgId);
	});
	return result;
}

function setMessagesReaded(msgIds){
	$('.unread_msg').each(function(){
		var msg_id = this.getAttribute('msg-id');
		if (msgIds.indexOf(msg_id) != -1){
			this.classList.remove('unread_msg');
		}
	});
}

function setUsersOnline(userIds){
	$('.user_item').each(function(index, item){
		var userId = item.getAttribute('id');
		var $item = $(item);
		if (userIds.indexOf(userId) == -1){
			if ($item.hasClass('user_online')){
				$item.removeClass('user_online');
				$item.addClass('user_offline');
			}
		}else{
			if (!$item.hasClass('user_online')){
				$item.addClass('user_online');
				$item.removeClass('user_offline');
			}
		}
	});
}

function update(){

//	var $fromUser = $("#fromUser option:selected");
//	var fromUserId = $fromUser.attr('id');
	var fromUserId = getCurrentUserId();

	var $activeUser = $('.active_user');
	if ($activeUser.length > 0){
		var $toUser = $($activeUser[0]);
		var toUserId = $toUser.attr('id');
	}

/*	if (toUserId == -1){
		return;
	};*/

	var unreadMessageIds = getUnreadMessageIds();
	var data = {action: 'update',
				fromUser: fromUserId,
				toUser: (toUserId === undefined) ? -1 : toUserId,
				unreadMessages: JSON.stringify(unreadMessageIds)
				};
	updateRequestEnabled = false;
	$xhrUpdate = $.ajax({
		type: 'POST',
		url: 'history.php',
		timeout: 30000,
		data: data,

		success: function(data){

			//var histDiv = document.getElementById('history');
			var receivedData = JSON.parse(data);
			var history;

			history = receivedData.unreadMsgs;
			updateIncomingMessagesCount(receivedData.unreadMsgsCount);
			setUsersOnline(receivedData.onlineUsers);

			setMessagesReaded(receivedData.readMsgIds);

			if (history.length > 0) {
				appendMessagesToHistory(history, fromUserId, 'append');
				//histDiv.scrollTop = histDiv.scrollHeight;
			}
			updateRequestEnabled = true;
			//update();
			setTimeout(function(){update()}, 50);
		},

		complete: function(jqXHR, status){
			if ((status == "timeout")||(status == "error")){
				updateRequestEnabled = true;
				//update();
			}
		}

	})
}

function getCurrentUserId(){
	return getCookie('id');
}

function getCookie(name) {
	var value = "; " + document.cookie;
	var parts = value.split("; " + name + "=");
	if (parts.length == 2) return parts.pop().split(";").shift();
}

/*
function getRenderedHistory(pageIndex){

	var fromUserId = getCurrentUserId();

	var $activeUser = $('.active_user');
	if ($activeUser.length > 0){
		var $toUser = $($activeUser[0]);
		var toUserId = $toUser.attr('id');
	}

	var data = {action: 'getRenderedHistory',
				fromUser: fromUserId,
				toUser: toUserId,
				historyPageIndex: pageIndex};
	var receivedData = '';
	$.ajax({
		type: 		'POST',
		url: 		'history.php',
		data: 		data,
		async: 		false,
		success:	function(data){
					//	receivedData = JSON.parse(data);
						receivedData = data;
					}

	});
	return receivedData;
}*/
