<?php

/**
 * Created by PhpStorm.
 * User: yakushevr
 * Date: 27.01.2016
 * Time: 16:33
 */
class UserEvents
{
    private $mysqli;

    function __construct(){
        include "settings.php";
        $this->mysqli = new mysqli($dbHostName, $dbUserName, $dbPassword, $dbName);
    }

    function __destruct() {
        $this->mysqli->close();
    }

    public function insertEvent($userId, $events){
		$jsonEvents = json_encode($events);
        $sql = "INSERT INTO user_events
                  (user_id, events)
                VALUES
                  ($userId, '$jsonEvents')";
        $this->mysqli->query($sql);
    }

    public function deleteEvent($userId){
        $sql = "DELETE FROM user_events
                WHERE user_id=$userId";
        $this->mysqli->query($sql);
    }

    public function writeEvent($userId, $events){
        $this->deleteEvent($userId);
        $this->insertEvent($userId, $events);
    }


    public function readEvent($userId){

        $result = null;

        $sql = "SELECT events FROM user_events
                WHERE user_id=$userId";
        $query = $this->mysqli->query($sql);

        if ($query->num_rows > 0){
            $data = mysqli_fetch_assoc($query);
            $result = json_decode($data["events"]);
        }

        return $result;

    }

}