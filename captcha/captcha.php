<?php
session_start();
$string = "";
for ($i = 0; $i < 5; $i++){
    $string .= chr(rand(97, 118)); //или $string .= mt_rand(1, 9); если Вы хотите чтоб были цифры
}
$_SESSION['rand'] = $string;
$dir = "";
$image = imagecreatetruecolor(85, 25); //размер создаваемой картинки
$color = imagecolorallocate($image, 137, 15, 200); //цвет букв или цифр картинки
$white = imagecolorallocate($image, 246, 246, 246); //цвет фона картинки
imagefilledrectangle($image,0,0,399,99,$white);
imagettftext ($image, 17, 0, 10, 20, $color, $dir."verdana.ttf", $_SESSION['rand']);
header("Content-type: image/png");
imagepng($image);