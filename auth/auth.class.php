<?php

/**
 * Created by PhpStorm.
 * User: Руслан
 * Date: 15.01.2016
 * Time: 0:08
 */
class Auth
{
	private $mysqli;
	private $_login;
	private $_password;

	public function getHash($str){
		return md5(md5(trim($str)));
	}

	public function __construct($login, $password)
	{
		include("settings.php");
		$this->mysqli = new mysqli($hostName, $userName, $password, $dbName);

		$this->_login = $login;
		$this->_password = $this->getHash($password);
	}

	function __destruct() {
		$this->mysqli->close();
	}

	public function Check(){
		$err = array();

		# проверям логин
		if(!preg_match("/^[a-zA-Z0-9]+$/", $this->_login)) {
			$err[] = "Логин может состоять только из букв английского алфавита и цифр";
		}

		if(strlen($this->_login) < 3 or strlen($this->_login) > 30) {
			$err[] = "Логин должен быть не меньше 3-х символов и не больше 30";
		}

		# проверяем, не сущестует ли пользователя с таким именем
		$sql = "SELECT COUNT(user_id) AS CNT FROM auth_users
				WHERE user_login='" . $this->mysqli->real_escape_string($this->_login) . "'";
		$result = $this->mysqli->query($sql);
		$data = mysqli_fetch_assoc($result);

		if ($data['CNT']){
			$err[] = "Пользователь с таким логином уже существует в базе данных";
		}

		return $err;

	}

	public function Register(){
		$sql = "INSERT INTO auth_users
				SET user_login='{$this->_login}',
					user_password='{$this->_password}'";
		$this->mysqli->query($sql);
	}

	public function getUserData(){
		$sql = "SELECT user_id, user_password FROM auth_users
				WHERE user_login='" . $this->mysqli->real_escape_string($this->_login) . "' LIMIT 1";
		$result = $this->mysqli->query($sql);
		$data = mysqli_fetch_assoc($result);

		return $data;
	}

	# Функция для генерации случайной строки
	private function generateCode($length = 6) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
		$code = "";
		$clen = strlen($chars) - 1;
		while (strlen($code) < $length) {
			$code .= $chars[mt_rand(0,$clen)];
		}
		return $code;
	}

	public function setCookie($userId){
		# Генерируем случайное число и шифруем его
		$hash = md5($this->generateCode(10));

		# Записываем в БД новый хеш авторизации и IP
		$sql = "UPDATE auth_users
				SET user_hash='".$hash."' ".
				"WHERE user_id='" . $userId . "'";
		$this->mysqli->query($sql);

		# Ставим куки
		setcookie("id", $userId, time()+60*60*24*30);
		setcookie("hash", $hash, time()+60*60*24*30);
	}

}