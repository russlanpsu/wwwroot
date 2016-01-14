<?php

include ("auth.class.php");

/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

// Страница регситрации нового пользователя

if(isset($_POST['submit'])) 
{
   $login = $_POST['login'];
   $password = $_POST['password'];
   $auth = new Auth($login, $password);

  # Если нет ошибок, то добавляем в БД нового пользователя
   $err = $auth->Check();
   if(count($err) == 0) {

     $auth->Register();
     //header("Location: login.php"); exit();
     header("Location: check.php"); exit();
   }
  else {
    print "При регистрации произошли следующие ошибки:
    ";
    foreach($err AS $error) {
      print $error."\n";
  }
 }
} 
?>
<span>Регистрация</span>
<form method="POST">
 Логин <input name="login" type="text">Пароль <input name="password" type="password">
 <input name="submit" type="submit" value="Зарегистрироваться">
</form>
<div><a href="login.php">Войти</a></div>

 
