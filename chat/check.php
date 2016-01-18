<?php

include_once '/chat.class.php';
include_once '/auth/auth.class.php';
include_once 'settings.php';

if (isset($_COOKIE['id']) and isset($_COOKIE['hash'])) 
{
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
	//	header("Location: {ROOT_PATH} login.php"); exit();
		include($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'chat/login.php');exit();
	}
	else {
	//	require_once '../PHP/vendor/twig/twig/lib/Twig/Autoloader.php';
	//	require_once '..\PHP\Twig-1.23.3\lib\Twig\Autoloader.php';
		include_once $TWIG_AUTOLOADER_PATH;

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
//	include($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'chat/auth/login.php');
	header('Location: /chat/auth/login.php');
//	echo  $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'chat/auth/login.php';
}