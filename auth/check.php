<?php

//session_start();
//SSID
# Соединямся с БД 
include("settings.php");
$mysqli = new mysqli($hostName, $userName, $password, $dbName);

if (isset($_COOKIE['id']) and isset($_COOKIE['hash'])) 
{

	$sql = "SELECT *, INET_NTOA(user_ip) FROM auth_users WHERE user_id = '".intval($_COOKIE['id'])."' LIMIT 1";
	$result = $mysqli->query($sql);
	$userdata = mysqli_fetch_assoc($result);

	if (
		($userdata['user_hash'] !== $_COOKIE['hash']) or ($userdata['user_id'] !== $_COOKIE['id'])
	//	or (($userdata['user_ip'] !== $_SERVER['REMOTE_ADDR']) and ($userdata['user_ip'] !== "0"))
		)
	{
		setcookie("id", "", time() - 3600*24*30*12, "/");
		setcookie("hash", "", time() - 3600*24*30*12, "/");

		print "Хм, что-то не получилось";
	}
	else {
	//	print "Привет, ".$userdata['user_login'].". Всё работает!";
		include("index.html");
	}
}
else{
//	print "Включите куки";
	include("login.php");
}

/*$_SESSION['id'] = '1';
$_SESSION['hash'] = 'sdkljfgakl;djfg';*/


/*echo '<pre>';
print_r($_SESSION);*/
?> 