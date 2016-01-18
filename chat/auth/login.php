<?php
// Страница авторизации 

//include ("auth.class.php");
include_once 'auth.class.php';

if (isset($_GET['out']) && $_GET['out'] == 1){
	setcookie("id", "", 0, '/');
	setcookie("hash", "", 0, '/');
//	header("Location: check.php"); exit();
}

if(isset($_POST['submit'])) {
# Вытаскиваем из БД запись, у которой логин равняеться введенному 

	$login = $_POST['login'];
	$password = $_POST['password'];
	$auth = new Auth();
	$data = $auth->getUserDataByLogin($login);

	# Сравниваем пароли
	if($data['password'] === $auth->getHash($password))
	{
		$auth->setCookie($data['id']);

		header("Location: /chat/check.php"); exit();
	} else {
		print "Вы ввели неправильный логин/пароль";
	}
}
include 'login.html';