<?php
class User
{
	public static function setLogin($username, $password, $autologin)
	{
		global $cookie_name, $cookie_time;
		$password = Password::genPassword($password);
		if ($autologin > 0) {
			$val = $username . "/" . $password;
			setcookie($cookie_name, $val, time() + $cookie_time, "/");
		}
		$_SESSION["loggedin"] = $username;
		return array("message" => "You have successfully been logged in");
	}

	public static function setLoginHashed($username, $password, $autologin)
	{
		global $cookie_name, $cookie_time;
		if ($autologin > 0) {
			$val = $username . "/" . $password;
			setcookie($cookie_name, $val, time() + $cookie_time, "/");
		}
		$_SESSION["loggedin"] = $username;
		return array("message" => "You have successfully been logged in");
	}

	public static function checkLogin($username, $password)
	{
		$password = Password::genPassword($password);
		$check = Db::queryField("SELECT username FROM skq_users WHERE password = :password AND username = :username", "username", array(":username" => $username, ":password" => $password), 1);
		if (isset($check))
			return true;
		return false;
	}

	public static function checkLoginHashed($username, $password)
	{
		$check = Db::queryField("SELECT username FROM skq_users WHERE password = :password AND username = :username", "username", array(":username" => $username, ":password" => $password), 1);
		if (isset($check))
			return true;
		return false;
	}

	public static function autoLogin()
	{
		global $cookie_name, $cookie_time;
		if (isset($_COOKIE[$cookie_name])) {
			$cookie = explode("/", $_COOKIE[$cookie_name]);
			$username = $cookie[0];
			$password = $cookie[1];
			$check = self::checkLoginHashed($username, $password);
			if ($check) {
				self::setLoginHashed($username, $password, 1);
				return true;
			}
			return false;
		}
		return false;
	}

	public static function isLoggedIn()
	{
		return isset($_SESSION["loggedin"]);
	}

	public static function getUserInfo()
	{
		if (isset($_SESSION["loggedin"])) {
			$id = Db::query("SELECT id, username, email, dateCreated, admin, moderator FROM skq_users WHERE username = :username", array(":username" => $_SESSION["loggedin"]), 1);
			return @array("id" => $id[0]["id"], "username" => $id[0]["username"], "admin" => $id[0]["admin"], "moderator" => $id[0]["moderator"], "email" => $id[0]["email"], "dateCreated" => $id[0]["dateCreated"]);
		}
		else
			return null;
	}

	public static function getUserID()
	{
		if (isset($_SESSION["loggedin"])) {
			$id = Db::queryField("SELECT id FROM skq_users WHERE username = :username", "id", array(":username" => $_SESSION["loggedin"]), 1);
			return $id;
		}
		return null;
	}

	public static function isModerator()
	{
		$info = self::getUserInfo();
		return $info["moderator"] == 1;
	}

	public static function isAdmin()
	{
		$info = self::getUserInfo();
		return $info["admin"] == 1;
	}
}
