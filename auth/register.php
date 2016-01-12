<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Страница регситрации нового пользователя 

# Соединямся с БД 
include("settings.php");
$mysqli = new mysqli($hostName, $userName, $password, $dbName);


if(isset($_POST['submit'])) 
{ 
$err = array(); 

# проверям логин 
if(!preg_match("/^[a-zA-Z0-9]+$/",$_POST['login'])) 
{ 
$err[] = "Логин может состоять только из букв английского алфавита и цифр"; 
} 

if(strlen($_POST['login']) < 3 or strlen($_POST['login']) > 30) 
{ 
$err[] = "Логин должен быть не меньше 3-х символов и не больше 30"; 
} 

# проверяем, не сущестует ли пользователя с таким именем 
$result = $mysqli->query("SELECT COUNT(user_id) AS CNT FROM users WHERE user_login='".$mysqli->real_escape_string($_POST['login'])."'"); 

//if(mysql_result($query, 0) > 0) 
if (mysqli_fetch_assoc($result)["CNT"]){ 
	$err[] = "Пользователь с таким логином уже существует в базе данных"; 
} 

# Если нет ошибок, то добавляем в БД нового пользователя 
if(count($err) == 0) 
{ 

$login = $_POST['login']; 

# Убераем лишние пробелы и делаем двойное шифрование 
$password = md5(md5(trim($_POST['password']))); 

//mysql_query("INSERT INTO users SET user_login='".$login."', user_password='".$password."'"); 
$mysqli->query("INSERT INTO users SET user_login='".$login."', user_password='".$password."'"); 
header("Location: login.php"); exit(); 
} 
else 
{ 
print "При регистрации произошли следующие ошибки:
"; 
foreach($err AS $error) 
{ 
print $error."
"; 
} 
} 
} 
?> 

 <form method="POST"> 
 Логин <input name="login" type="text">Пароль <input name="password" type="password">
 <input name="submit" type="submit" value="Зарегистрироваться"> 
 </form> 
 
