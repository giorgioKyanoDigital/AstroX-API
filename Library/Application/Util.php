<?php
class Application_Util
{

	public function createrandomstring($char)
	{
		$characters = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array();
		$length = strlen($characters) - 1;
		for ($i = 0; $i < $char; $i++) {
			$n = rand(1, $length);
			$pass[] = $characters[$n];
		}
		return implode($pass);
	}

	public function cors($t)
	{
		if (!isset($_SERVER['HTTP_ORIGIN'])) {
			return;
		}
		if ($t === 'private') {
			global $config;
			if (in_array(parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST), $config->auth->allowed_domains)) {
				header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
			} else {
				preg_match('/[^.]*\.[^.]{2,3}(?:\.[^.]{2,3})?$/', parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST), $d);
				if (isset($d[0]) && in_array($d[0], $config->auth->allowed_domains)) {
					header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
				}
			}
		} else if ($t === 'public') {
			header('Access-Control-Allow-Origin: *');
		} else {
			header('Access-Control-Allow-Origin: *');
			new Application_Exception(500, 'ec: SWCK-API-100');
		}
	}

	/*
	type: user | server
	info: (for type user) object {
		type: login,enabled2fa,disabled2fa etc
		user: user id
		data: object with any aditional data related to the event, for login ip and useragent for example. If nothing don't define.
	}
	*/
	public function log($type, $info)
	{
		global $db;
		switch ($type) {
			case "user":
				if (property_exists($info, 'data')) {
					return $db->insert("INSERT INTO `user_log` (`user`,`type`,`at`,`data`) VALUES (?,?,?,?);", array($info->user, $info->type, time(), json_encode($info->data)));
				} else {
					return $db->insert("INSERT INTO `user_log` (`user`,`type`,`at`) VALUES (?,?,?);", array($info->user, $info->type, time()));
				}
				break;
			case "server":
				$db->insert("INSERT INTO `server_log` (`at`,`data`) VALUES (?,?);", [time(), json_encode($info)]);
				break;
		}
	}

	public function IsServer($config)
	{
		$token = null;
		$headers = apache_request_headers();

		if (isset($headers['token']))
			$token = $headers['token'];
		else if (isset($headers['Token']))
			$token = $headers['Token'];

		return $token == $config->server->gameserversecret;
	}
}
