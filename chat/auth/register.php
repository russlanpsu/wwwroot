<?php

include("auth.class.php");

/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

// Страница регситрации нового пользователя

if(isset($_POST['submit'])) 
{
   $login = $_POST['login'];
   $password = $_POST['password'];
   $auth = new Auth();

  # Если нет ошибок, то добавляем в БД нового пользователя
   $err = $auth->Check($login, $password);
   if(count($err) == 0) {

     $auth->Register($login, $password);
     //header("Location: login.php"); exit();
     header("Location: /chat/check.php"); exit();
   }
  else {
    print "При регистрации произошли следующие ошибки:
    ";
    foreach($err AS $error) {
      print $error."\n";
  }
 }
}
include 'register.html';