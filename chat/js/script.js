var msgIndex = 0;
var $xhrUpdate;
var userInfoDialog;

$(function(){

	document.getElementById('avatar_file').addEventListener('change', handleFileSelect, false);

	update(0);

	userInfoDialog = $('#userInfoDialog').dialog({
		autoOpen: false,
		modal: false,
		width: 400,
		buttons:{
			'OK': function(){
				uploadAvatar();
				$(this).dialog('close');
			},
			'Отмена': function(){
				$(this).dialog('close');
			}
		}
	});
	$('.user_item').click(function(){

		var curUserId = getCurrentUserId();

		var $this = $(this);
		var companionId = $this.attr('id');
		setCompanion(curUserId, companionId);
		update(0);

		var $activeUser = $('.active_user');
		if ($activeUser.length > 0){
			$($activeUser[0]).removeClass('active_user');
		}

		$this.addClass('active_user');
		$this.find('.msg_count').html('');
		//$this.find('.last_msg').html('');

		$('#history').attr('page-index', 0);

		getHistory();

	})

	$('#user_list img').click(function(event){
		userAvatarClick(event);
	});

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
		//sendTyping();
	})

	$('#history').scroll(function(){

		var $this = $(this);
		console.log($this.scrollTop());

		if ($this.scrollTop() == 0){

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

function sendTyping(){
	var toUserId = $($('.active_user')).attr('id');
	var data = {action: 'sendTyping',
			fromUser: getCurrentUserId(),
			toUser: toUserId};
	$.ajax({
		type: "POST",
		url: "history.php",
		data: data,
		success: function(data){

		}
	});
}

function historyOnScroll(){

    var $this = $(this);
    console.log($this.scrollTop());

    if ($this.scrollTop() == 0){

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

}

function setCompanion(fromUser, toUser){
	$.ajax({
		type: 'POST',
		url: 'history.php',
		data: {	action: 'setCompanion',
				fromUser: fromUser,
				toUser: toUser
				},
		async: true,
		success: function(data){

		}
	})
}

function renderTemplate(tmplId, target, context, appendKind){
	var $tmpl = $('#' + tmplId);

	var template = Handlebars.compile($tmpl.html());
	var rendered = template(context);

	var $target = $('#' + target);

	switch (appendKind){

		case 'before':

			var firstChild = $target.children()[0];
			var $firstChild = $(firstChild);
			$firstChild.before(rendered);

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
		url:  'history.php',
		data:{
				action: 'insertMessage',
				fromUser: fromUserId,
				toUser: toUserId,
				msg: msgText
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

    if (messages.length > 0){
        var context = {messages: messages};
        renderTemplate('tmpl_msg_history', 'history', context, appendKind);
    }
    else{
        //$('#history').html('');
    }
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
	var $toUser = $($('.active_user')[0]);
	var fromUserId = getCurrentUserId();
	var toUserId = $toUser.attr('id');

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

			if (messages.length > 0) {
				var appendKind = (pageIndex == 0) ? 'insert' : 'before';
				appendMessagesToHistory(messages, fromUserId, appendKind);
			}else if (pageIndex == 0){
				$('#history').html('');
			}
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

	/*$('.user_item').each(function(index, item){
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
	});*/
	//return;
	$('img.user_online').each(function(index, item){
		var $item = $(item);
		var userId = $item.closest('li').attr('id');
		if (userIds.indexOf(userId) == -1){
			$item.toggle(false);
		}
		else{
			$item.toggle(true);
		}
	});
}

function update(wait){

	var fromUserId = getCurrentUserId();

	var $activeUser = $('.active_user');
	if ($activeUser.length > 0){
		var $toUser = $($activeUser[0]);
		var toUserId = $toUser.attr('id');
	}

	var createNewRequest = false;

	if (($xhrUpdate === undefined)
		|| ($xhrUpdate.readyState == 4))
	{
		createNewRequest = true;
	}

	if (!createNewRequest){
		setTimeout(function () {
			update(25)
		}, 50);
	}

	var unreadMessageIds = getUnreadMessageIds();
	var lastMsgId = $('.msg-text.in_msg').last().attr('msg-id');
	var data = {action: 'update',
		fromUser: fromUserId,
		//toUser: (toUserId === undefined) ? -1 : toUserId,
		wait: wait,
		unreadMessages: JSON.stringify(unreadMessageIds),
		lastMsgId: lastMsgId
	};

	if (createNewRequest) {
		$xhrUpdate = $.ajax({
			type: 'POST',
			url: 'history.php',
			timeout: 30000,
			data: data,

			success: function (data) {

				//var histDiv = document.getElementById('history');
				var receivedData = JSON.parse(data);
				var history;

				history = receivedData.unreadMsgs;
				updateIncomingMessagesCount(receivedData.unreadMsgsCount);
				setUsersOnline(receivedData.onlineUsers);

				setMessagesReaded(receivedData.readMsgIds);

				if (history.length > 0) {
					appendMessagesToHistory(history, fromUserId, 'append');
				}

			},

			complete: function (jqXHR, status) {
                setTimeout(function () {
                    update(25)
                }, 50);
				if ((status == "timeout") || (status == "error")) {

				}
			}
		})
	}
}

function getCurrentUserId(){
	return getCookie('id');
}

function getCookie(name) {
	var value = "; " + document.cookie;
	var parts = value.split("; " + name + "=");
	if (parts.length == 2) return parts.pop().split(";").shift();
}

function uploadAvatar() {
	var fd = new FormData(document.getElementById("avatar_form"));
	/*var fd = new FormData();
	fd.append('file', document.getElementById("avatar_file"));*/
	fd.append('userId', getCurrentUserId());
	$.ajax({
		url: 'upload.php',
		type: 'POST',
		data: fd,
		enctype: 'multipart/form-data',
		processData: false,  // tell jQuery not to process the data
		contentType: false   // tell jQuery not to set contentType
	}).done(function( data ) {
		$($('#user_avatar')[0]).attr('src', data);
	});
	return false;
}

function userAvatarClick(event){
	var id = $(event.currentTarget).closest("li").attr("id");
	event.stopPropagation();
	alert (id);

	return false;
}

function showUserInfoDialog() {
	userInfoDialog.dialog('open');
}

function simulateClick(id){
	$('#'+ id).click();
}

function handleFileSelect(evt) {
	var files = evt.target.files; // FileList object
	var f = files[0];
	// Loop through the FileList and render image files as thumbnails.
//	for (var i = 0, f; f = files[i]; i++) {

		// Only process image files.
		if (!f.type.match('image.*')) {
			//continue;
		}

		var reader = new FileReader();

		// Closure to capture the file information.
		reader.onload = (function(theFile) {
			return function(e) {
				// Render thumbnail.
				/*var span = document.createElement('span');
				span.innerHTML = ['<img class="thumb" src="', e.target.result,
					'" title="', escape(theFile.name), '"/>'].join('');
				document.getElementById('list').insertBefore(span, null);*/
				$('#user_avatar').attr('src', e.target.result);

			};
		})(f);

		// Read in the image file as a data URL.
		reader.readAsDataURL(f);
//	}
}