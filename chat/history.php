<?php

include 'chat.class.php';

$action = $_POST['action'];

if (!(isset($action))){
	echo "Params are not setted";
	exit;
}


//	$mysqli = $mysqli = new mysqli("mysql.main-hosting.com", "u277145571_admin", "pass_word", "u277145571_db");
//	$mysqli = new mysqli($hostName, "root", "pass_word", "dev_schema");

$chat = new Chat();

switch ($action){

	case "getUsers":

		$users = $chat->getUsers();
		echo json_encode($users);
		break;

	case "getHistory":

		$fromUser = $_POST['fromUser'];
		$toUser = $_POST['toUser'];
		$historyPageIndex = (isset($_POST['historyPageIndex'])) ?
								$_POST['historyPageIndex'] :
								0;
		$history = $chat->getHistory($fromUser, $toUser, $historyPageIndex);

		echo json_encode($history);
		break;

	case "update":

		$fromUser = $_POST['fromUser'];
		$toUser = $_POST['toUser'];
		/** @var TYPE_NAME $unreadMsgIds */
		$unreadMsgIds = json_decode($_POST['unreadMessages']);
		$history = $chat->update($fromUser, $toUser, $unreadMsgIds);
		echo json_encode($history);
		break;

	case "insertMessage":

		$fromUser = $_POST['fromUser'];
		$toUser = $_POST['toUser'];
		$msg = $_POST['msg'];
		list($msgId, $createDate) = $chat->insertMessage($fromUser, $toUser, $msg);
		echo json_encode(array("msg_id"=>$msgId, "create_date"=>$createDate));

		break;

	case "getUnreadMessagesCount":

		if (isset($_POST["user"])) {
			$msgCount = $chat->getIncomingMessagesCount($_POST["user"]);
			echo json_encode($msgCount);
		};
		break;
	case "getRenderedHistory":
		$fromUser = $_POST['fromUser'];
		$toUser = $_POST['toUser'];
		$pageIndex = $_POST['historyPageIndex'];
		$history = $chat->getRenderedHistory($fromUser, $toUser, $pageIndex);

}

