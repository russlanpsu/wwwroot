<?php
// Страница авторизации 


# Функция для генерации случайной строки 
function generateCode($length = 6) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
	$code = "";
	$clen = strlen($chars) - 1;
	while (strlen($code) < $length) {
		$code .= $chars[mt_rand(0,$clen)];
	}
	return $code;
} 


# Соединямся с БД 

include("settings.php");
$mysqli = new mysqli($hostName, $userName, $password, $dbName);


if(isset($_POST['submit'])) {
# Вытаскиваем из БД запись, у которой логин равняеться введенному 
//$query = mysql_query("SELECT user_id, user_password FROM users WHERE user_login='".mysql_real_escape_string($_POST['login'])."' LIMIT 1");
//$data = mysql_fetch_assoc($query);

	$sql = "SELECT user_id, user_password FROM auth_users
			WHERE user_login='".$mysqli->real_escape_string($_POST['login'])."' LIMIT 1";
	$result = $mysqli->query($sql);
	$data = mysqli_fetch_assoc($result);

	# Соавниваем пароли
	if($data['user_password'] === md5(md5($_POST['password'])))
	{
	# Генерируем случайное число и шифруем его
	$hash = md5(generateCode(10));

	if(!@$_POST['not_attach_ip'])
	{
	# Если пользователя выбрал привязку к IP
	# Переводим IP в строку
	$insip = ", user_ip=INET_ATON('".$_SERVER['REMOTE_ADDR']."')";
	}

	# Записываем в БД новый хеш авторизации и IP
	//mysql_query("UPDATE users SET user_hash='".$hash."' ".$insip." WHERE user_id='".$data['user_id']."'");
		$sql = "UPDATE auth_users SET user_hash='".$hash."' ".$insip." WHERE user_id='".$data['user_id']."'";
		$mysqli->query($sql);

	# Ставим куки
		setcookie("id", $data['user_id'], time()+60*60*24*30);
		setcookie("hash", $hash, time()+60*60*24*30);

	# Переадресовываем браузер на страницу проверки нашего скрипта
		header("Location: check.php"); exit();
	} else {
		print "Вы ввели неправильный логин/пароль";
	}
} 
?> 
<form method="POST"> Логин <input name="login" type="text">Пароль <input name="password" type="password">Не прикреплять к IP(не безопасно) 
<input type="checkbox" name="not_attach_ip"><br> <br /><input name="submit" type="submit" value="Войти"></form> 
