<?php

/**
 * Created by PhpStorm.
 * User: Руслан
 * Date: 04.01.2016
 * Time: 0:23
 */

define("HISTORY_PAGE_SIZE", 10);
class Chat
{

    private $mysqli;

    public function __construct(){

        include "settings.php";
        $this->mysqli = new mysqli($dbHostName, $dbUserName, $dbPassword, $dbName);

    }

    function __destruct() {
        $this->mysqli->close();
    }

    public function setMessageRead($curUser, $companion){

        $sql = "UPDATE messages
				SET is_readed = 1
				WHERE from_user = {$companion}
				    AND to_user = {$curUser}
					AND is_readed = 0";
        $this->mysqli->query($sql);
    }

    public function getHistory($curUser, $companion, $page = 0){

        $startRow = HISTORY_PAGE_SIZE*$page;

        $sql = "SELECT * FROM
					(SELECT
					    id AS msg_id,
					    msg_text,
						from_user,
						to_user,
						create_date,
						is_readed
					FROM messages
					  WHERE from_user IN ({$curUser}, {$companion})
						 AND to_user IN ({$curUser}, {$companion})
					 ORDER BY create_date DESC
					 LIMIT {$startRow} ," . HISTORY_PAGE_SIZE .
					")T
				ORDER BY create_date ASC";
        $result = $this->mysqli->query($sql);

        $history = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }

        $this->setMessageRead($curUser, $companion);

        return $history;
    }

    public function getUnreadMessages($curUser, $companion){
        $history = array();
        if ($companion == -1){
            return $history;
        };

        $sql = "SELECT msg_text,
					from_user,
					to_user,
					create_date
				FROM messages
				WHERE from_user = {$companion}
				    AND to_user = {$curUser}
					AND is_readed = 0";

        $result = $this->mysqli->query($sql);

        while ($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }

        $this->setMessageRead($curUser, $companion);
        return $history;

    }

    public function getReadedMessageIds($unreadMsgIds)
    {

        $ids = array();
        if (count($unreadMsgIds) > 0){

            $sql = "SELECT id FROM messages
                WHERE id in (" . implode(",", $unreadMsgIds) . ")
                  AND is_readed=1";

            $result = $this->mysqli->query($sql);

            if (!($result === FALSE)) {

                while ($row = mysqli_fetch_assoc($result)) {
                    $ids[] = $row['id'];
                }
            }
        }
        return $ids;
    }

    private function setLastActivityDate($curUser){
        $sql = "UPDATE users
                SET last_activity_date=now()
                WHERE id={$curUser}";
        $this->mysqli->query($sql);
    }

    function getOnlineUserIds($curUser){
        $sql = "SELECT id FROM users
                WHERE TIME_TO_SEC(TIMEDIFF(NOW(), last_activity_date))<20
                  AND ID <> " . $curUser;

        $ids = array();
        $result = $this->mysqli->query($sql);

    //    if (!($result === FALSE)) {

            while ($row = mysqli_fetch_assoc($result)) {
                $ids[] = $row["id"];
            };

    //    }
        return $ids;
    }

    function arraysIsEqual($arr1, $arr2){
        return ((count(array_diff($arr1, $arr2)) === 0)
            && (count(array_diff($arr2, $arr1)) === 0));
    }

    public function update($curUser, $companion, $unreadMsgIds){

    //    sleep(10);
        $maxExecTime = (int) ini_get('max_execution_time');
        if (($maxExecTime === 0)
            || ($maxExecTime > 30)) {
            $maxExecTime = 30;
        }


        $unreadMessages = $this->getUnreadMessages($curUser, $companion);
        $unreadMessagesCount = $this->getIncomingMessagesCount($curUser);
        $readMessageIds = $this->getReadedMessageIds($unreadMsgIds);
        $onlineUserIds = $this->getOnlineUserIds($curUser);

        $this->setLastActivityDate($curUser);
        $endTime = time() + $maxExecTime -6;
    //    for($i=0; $i<100; $i++){
        while (time()<$endTime){
            usleep(250000); //  250ms
            $unreadMessages1 = $this->getUnreadMessages($curUser, $companion);
            /*$unreadMessagesCount1 = $this->getIncomingMessagesCount($curUser);
            $readMessageIds1 = $this->getReadedMessageIds($unreadMsgIds);
            $onlineUserIds1 = $this->getOnlineUserIds($curUser);*/

            if (!$this->arraysIsEqual($unreadMessages1, $unreadMessages)){
        //    if ($unreadMessages1 != $unreadMessages){
                $unreadMessages = $unreadMessages1;
                break;
            }
            $unreadMessages = $unreadMessages1;
        }
        $result = array(
                        "unreadMsgs" => $unreadMessages,
                        "unreadMsgsCount" => $unreadMessagesCount,
                        "readMsgIds" => $readMessageIds,
                        "onlineUsers" => $onlineUserIds
                        );
        return $result;
    }

    public function getUsers($excludeUserId = -1){

       /* $sql = "SELECT id, name FROM users
				WHERE id <> " . $excludeUserId .
				" ORDER BY ID";*/

        $sql = "SELECT
                    A.id,
                    name,
                    (SELECT msg_text FROM messages WHERE id=last_id ) AS last_msg
                FROM users A
                LEFT JOIN (SELECT from_user, max(id) AS last_id FROM messages
                            WHERE to_user=$excludeUserId
                            GROUP BY from_user) B
                    ON A.id=B.from_user
                WHERE A.id <> $excludeUserId
                ORDER BY A.ID";

        $result = $this->mysqli->query($sql);
        $users = array();
        while ($row = mysqli_fetch_assoc($result)) {
        //    $row['last_msg'] = 'this is last message of user';
            $users[] = $row;
        };

        return $users;

    }

    public function insertMessage($fromUser, $toUser, $msg){
    //    sleep(mt_rand(2, 4));

        $this->mysqli->query(sprintf( 'INSERT INTO messages
								        (msg_text, to_user, from_user, create_date)
							          values
								        ("%1$s", %2$s, %3$s, now())',
                                    $msg, $toUser, $fromUser
                                    )
                            );

        $insertedId = $this->mysqli->insert_id;

        $result = $this->mysqli->query("SELECT DATE_FORMAT(create_date, '%T') AS create_date FROM messages WHERE id={$insertedId}");
        $row = mysqli_fetch_assoc($result);

        return array($insertedId, $row["create_date"]);
    }

    public function getIncomingMessagesCount($curUser){
        $sql = "SELECT
                  from_user AS user_id,
                  COUNT(from_user) AS msgs_count,
                  (SELECT msg_text FROM messages c
                    WHERE c.id=(SELECT MAX(id) FROM messages b
                                WHERE b.from_user=a.from_user)
                    ) AS last_msg
                FROM messages a
                WHERE to_user = {$curUser}
                  AND is_readed = 0
                GROUP BY from_user";
        $result = $this->mysqli->query($sql);
        $messagesCount = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $messagesCount[] = $row;
        };

        return $messagesCount;

    }

   /* public function getRenderedHistory($curUser, $companion, $pageIndex){
        require_once '../PHP/vendor/twig/twig/lib/Twig/Autoloader.php';
        Twig_Autoloader::register();

        // указывае где хранятся шаблоны
        $loader = new Twig_Loader_Filesystem('templates');

        // инициализируем Twig
        $twig = new Twig_Environment($loader);

        // подгружаем шаблон
        $template = $twig->loadTemplate('msg_history.tmpl');

        $chat = new Chat();
        $messages = $chat->getHistory($curUser, $companion, $pageIndex);
        $context = array('messages'=>$messages);

        echo $template->render($context);
    }*/

}