<?php
class Authentication_Token {
	private $type;

	function __construct() {
		global $config, $session;
		if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$t = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
			if ($t[0] === "user") {
				$em = "AES-256-CBC";
				$key = hash('sha256', $config->server->secret . $_SERVER['HTTP_USER_AGENT']);
				$iv = substr(hash('sha256', $config->server->secret . $_SERVER['HTTP_USER_AGENT']), 0, 16);
				$session = json_decode(openssl_decrypt(base64_decode($t[1] . '=='), $em, $key, 0, $iv));
				if ($session == null) {
					new Application_Exception(401);
				}
				$userses = new User_Session;
				if ($userses->check($session)) {
					$this->type = "user";
				} else {
					new Application_Exception(500);
				}
			} else {
				new Application_Exception(401);
			}
		} else {
			new Application_Exception(401);
		}
	}

	function isuser() {
		if ($this->type === "user") {
			return true;
		} else {
			return false;
		}
	}
}
