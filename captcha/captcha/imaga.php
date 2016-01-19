<?php
include('capgen.php');
if(isset($_REQUEST[session_name()])){
	session_start();
}
$captcha = new EasyCaptcha();
if($_REQUEST[session_name()]){
	$_SESSION['captcha_keystring'] = $captcha->getKeyString();
}
?>