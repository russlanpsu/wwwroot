<?php

/**
 * Created by PhpStorm.
 * User: Руслан
 * Date: 15.01.2016
 * Time: 0:08
 */
//require_once 'settings.php';
//require_once 'settings.php';
//include_once '\..\settings.php';
class Auth
{
	private $mysqli;
	/*private $_login;
	private $_password;*/

	public function getHash($str){
		return md5(md5(trim($str)));
	}

//	public function __construct($login, $password)
	public function __construct()
	{
		include('settings.php');

		$this->mysqli = new mysqli($dbHostName, $dbUserName, $dbPassword, $dbName);

		/*$this->_login = $login;
		$this->_password = $this->getHash($password);*/
	}

	function __destruct() {
		$this->mysqli->close();
	}

	public function Check($login, $password){
		$err = array();

		# проверям логин
		if(!preg_match("/^[a-zA-Z0-9]+$/", $login)) {
			$err[] = "Логин может состоять только из букв английского алфавита и цифр";
		}

		if(strlen($login) < 3 or strlen($login) > 30) {
			$err[] = "Логин должен быть не меньше 3-х символов и не больше 30";
		}

		# проверяем, не сущестует ли пользователя с таким именем
		$sql = "SELECT COUNT(id) AS CNT FROM users
				WHERE name='" . $this->mysqli->real_escape_string($login) . "'";
		$result = $this->mysqli->query($sql);
		$data = mysqli_fetch_assoc($result);

		if ($data['CNT']){
			$err[] = "Пользователь с таким логином уже существует в базе данных";
		}

		return $err;

	}

	public function Register($login, $password){
		$passwordHash = $this->getHash($password);
		$sql = "INSERT INTO users
				SET name='$login',
					password='{$passwordHash}'";
		$this->mysqli->query($sql);

		$userId = $this->mysqli->insert_id;
		$this->setCookie($userId);
	}

	public function getUserDataByLogin($login){
		$sql = "SELECT id, password, name FROM users
				WHERE name='" . $this->mysqli->real_escape_string($login) . "' LIMIT 1";
		$result = $this->mysqli->query($sql);
		$data = mysqli_fetch_assoc($result);

		return $data;
	}

	public function getUserDataById($userId){
		$sql = "SELECT * FROM users
			WHERE id = '".intval($userId)."' LIMIT 1";
		$result = $this->mysqli->query($sql);
		$userdata = mysqli_fetch_assoc($result);

		return $userdata;

	}

	# Функция для генерации случайной строки
	private function generateCode($length = 6) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
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
		$sql = "UPDATE users
				SET hash='".$hash."' ".
				"WHERE id='" . $userId . "'";
		$this->mysqli->query($sql);

		# Ставим куки
		setcookie("id", $userId, time()+60*60*24*30, '/');
		setcookie("hash", $hash, time()+60*60*24*30, '/');
	}

}