<?php
class User_Session
{
	private $sdb;
	private $acc;

	function __construct()
	{
		$this->sdb = new DB_Session;
		$this->acc = new User_Account;
	}

	function create($uid)
	{
		global $app, $config;
		$sid = $app->createrandomstring(30);
		$this->sdb->create($sid, $uid, 'user');
		$em = "AES-256-CBC";
		$key = hash('sha256', $config->server->secret . $_SERVER['HTTP_USER_AGENT']);
		$iv = substr(hash('sha256', $config->server->secret . $_SERVER['HTTP_USER_AGENT']), 0, 16);
		$a = array('id' => $sid, 'user' => $uid);
		return str_replace('=', '', base64_encode(openssl_encrypt(json_encode($a), $em, $key, 0, $iv)));
	}

	function check($session)
	{
		global $user;
		$ses = $this->sdb->check($session->id);
		if ($ses->user == $session->user) {
			if ($ses->lastupdated > time()) {
				global $user;
				// $this->sdb->update($session->id);
				$user = $this->acc->adb->getuser($session->user);
				$user->loggedin = true;
				$user->perms = json_decode($this->acc->adb->getuserperms($user->id));
				return true;
			}
			else 
			{
				return false;
			}
		} else {
			return false;
		}
	}

	function revoke($session)
	{
		return $this->sdb->delete($session);
	}
}
