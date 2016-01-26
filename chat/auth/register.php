<?php

include_once("auth.class.php");

/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

// Страница регситрации нового пользователя

$msg="";
if(isset($_POST['submit'])) 
{
    if (!isset($_POST['g-recaptcha-response'])) {
        $msg = "Капча не установлена";
    }else {
        require_once "recaptchalib.php";
        $recaptcha = $_POST['g-recaptcha-response'];
        // ваш секретный ключ
        $secret = "6LflFhYTAAAAAF01b0XPvv4HlHfEbD_rSPcnN9Dx";

        $reCaptcha = new ReCaptcha($secret);
        $response = $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $recaptcha);
        if ($response->success) {

            $login = $_POST['login'];
            $password = $_POST['password'];
            $auth = new Auth();

            # Если нет ошибок, то добавляем в БД нового пользователя
            $err = $auth->Check($login, $password);
            if (count($err) == 0) {

                $auth->Register($login, $password);
                //header("Location: login.php"); exit();
                header("Location: /chat/check.php");
                exit();

            } else {
                print "При регистрации произошли следующие ошибки:\n";
                foreach ($err AS $error) {
                    print $error . "\n";
                }
            }
        }
    }
}
include 'register.html';