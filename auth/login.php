<?php
// Страница авторизации 

include ("auth.class.php");


if(isset($_POST['submit'])) {
# Вытаскиваем из БД запись, у которой логин равняеться введенному 
//$query = mysql_query("SELECT user_id, user_password FROM users WHERE user_login='".mysql_real_escape_string($_POST['login'])."' LIMIT 1");
//$data = mysql_fetch_assoc($query);

	$login = $_POST['login'];
	$password = $_POST['password'];
	$auth = new Auth($login, $password);
	$data = $auth->getUserData();

	# Соавниваем пароли
	if($data['user_password'] === $auth->getHash($password))
	{
		$auth->setCookie($data['user_id']);

		# Переадресовываем браузер на страницу проверки нашего скрипта
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

