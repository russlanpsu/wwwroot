<?
header("Content-type:text/html; charset=UTF-8");
$urla="http://youon.ru/"; //Адрес
session_start();

if(count($_POST)>0){
	if(isset($_SESSION['captcha_keystring']) && strtolower($_SESSION['captcha_keystring']) == strtolower($_POST['keystring'])){
//Здесь расположен код исполнения в случае верного ввода капчи
//echo "<script>window.location = '".$urla."';</script>"; //У нас это переадресация на страницу в переменной $urla
echo "Капча введена верно";
	}else{
		echo '<p style="color:#ff0f0f;">Ошибка - неправильный ввод числа</p>';
	}
}
?>
<div class="fcheck">
<form method="post">
<p>Введите капчу:</p>
<p><img title="Если Вы не видите число на картинке, нажмите на картинку мышкой" onclick="this.src=this.src+'&amp;'+Math.round(Math.random())" src="captcha/imaga.php?<?php echo session_name()?>=<?php echo session_id()?>">	
<p><input type="text" name="keystring"><input type="submit" value="ОК"></p>
<p style="font-size:10px;">Если не видишь код - кликни по картинке</p>
</form>
</div>
<?php
unset($_SESSION['captcha_keystring']);
?>
