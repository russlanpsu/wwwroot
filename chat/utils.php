<?php
/**
 * Created by PhpStorm.
 * User: yakushevr
 * Date: 02.02.2016
 * Time: 17:51
 */

//ini_set("display_errors", "1");
//error_reporting(E_ALL);

function setUserAvatarUrl($userId, $url){
    include_once "settings.php";
    $sql = "UPDATE users
            SET avatar_url='$url'
            WHERE id=$userId";
    $mysqli = new mysqli($dbHostName, $dbUserName, $dbPassword, $dbName);
    $mysqli->query($sql);
}