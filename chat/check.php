<?php

//session_start();
//SSID
# Соединямся с БД 
//include("settings.php");
//require_once 'settings.php';
include("chat.class.php");
require_once 'auth.class.php';

//$mysqli = new mysqli($dbHostName, $dbUserName, $dbPassword, $dbName);

if (isset($_COOKIE['id']) and isset($_COOKIE['hash'])) 
{

	/*$sql = "SELECT * FROM users
			WHERE id = '".intval($_COOKIE['id'])."' LIMIT 1";
	$result = $mysqli->query($sql);
	$userdata = mysqli_fetch_assoc($result);*/

	$auth = new Auth();
	$userdata = $auth->getUserDataById($_COOKIE['id']);

	if (
		($userdata['hash'] !== $_COOKIE['hash']) or ($userdata['id'] !== $_COOKIE['id'])
	//	or (($userdata['user_ip'] !== $_SERVER['REMOTE_ADDR']) and ($userdata['user_ip'] !== "0"))
		)
	{
	/*	setcookie("id", "", time() - 3600*24*30*12, "/");
		setcookie("hash", "", time() - 3600*24*30*12, "/");

		print "Хм, что-то не получилось";*/
		header("Location: auth/login.php"); exit();
	}
	else {
		require_once '../PHP/vendor/twig/twig/lib/Twig/Autoloader.php';
		Twig_Autoloader::register();

		// указываем где хранятся шаблоны
		$loader = new Twig_Loader_Filesystem('templates');

		// инициализируем Twig
		$twig = new Twig_Environment($loader);

		// подгружаем шаблон
		$template = $twig->loadTemplate('main.tmpl');

		$chat = new Chat();
		$users = $chat->getUsers($userdata['id']);
		$context = array('users'=>$users,
						'userName'=>$userdata['name']
		);

		echo $template->render($context);

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