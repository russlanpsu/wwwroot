<?php
// Страница авторизации 

//include ("auth.class.php");
require_once 'auth.class.php';

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

		header("Location: check.php"); exit();
	} else {
		print "Вы ввели неправильный логин/пароль";
	}
} 
?>
<span>Войти как</span>
<form method="POST"> Логин <input name="login" type="text">Пароль <input name="password" type="password">Не прикреплять к IP(не безопасно) 
<input type="checkbox" name="not_attach_ip"><br> <br /><input name="submit" type="submit" value="Войти"></form>
<div><a href="register.php">Регистрация</a></div>

