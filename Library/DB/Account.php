<?php
class DB_Account {
	public $db;

	function __construct() {
		global $db;
		$this->db = $db;
	}

	function register($d) {
		$t = time();
		$hash = Authentication_Password::generate($d->passwd);
		$q = $this->db->insert(
			"INSERT INTO `users` (`username`,`email`,`register_date`) VALUES (?,?,?);",
			array(strtolower($d->usrname), $d->email, $t)
		);
		if ($q) {
			$this->db->insert("INSERT INTO `user_permissions` (`user`, `data`) VALUES (LAST_INSERT_ID(), ?);", array($d->perms));
			return $this->db->insert("INSERT INTO `user_authentication` (`user`, `passwd`) VALUES (LAST_INSERT_ID(), ?);", array($hash));
		} else {
			return false;
		}
	}

	function getuser($id) {
		$r = $this->db->query("SELECT * FROM `users` WHERE `id` = ?;", array($id));
		$r->staff = boolval($r->staff);
		return $r;
	}
	function isuseractive($id) {
		$r = $this->db->query("SELECT * FROM `users` WHERE `id` = ?;", array($id));
		$r = boolval($r->active);
		return $r;
	}

	function getusername($id) {
		return $this->db->query("SELECT `id`, `firstname`, `lastname` FROM `users` WHERE `id` = ?;", array($id));
	}

	function getusers($o = null) {
		if ($o === null) {
			return $this->db->query_all("SELECT * FROM `users` WHERE `active` = 1 LIMIT 100;", array());
		} else {
			return $this->db->query_all("SELECT * FROM `users` WHERE `active` = 1 LIMIT $o, 100;", array());
		}
	}

	function getusernames() {
		return $this->db->query_all("SELECT `id`, `firstname`, `lastname` FROM `users`;", []);
	}

	function getuseridbyteamleder($id) {
		$r = $this->db->query("SELECT `id` FROM `users` WHERE `teamleader` = ?", array($id));
		if (isset($r->id)) {
			return $r->id;
		} else {
			return null;
		}
	}

	function getuseridbyusername($e) {
		return $this->db->query("SELECT `id` FROM `users` WHERE `username` = ?;", array($e));
	}
	function getuseridbyemail($e) {
		return $this->db->query("SELECT `id` FROM `users` WHERE `email` = ?;", array($e));
	}

	function getauthdata($id) {
		return $this->db->query("SELECT `passwd`,`2fa_enabled` FROM `user_authentication` WHERE `user` = ?;", array($id));
	}

	function getuserperms($id) {
		return $this->db->query("SELECT `data` FROM `user_permissions` WHERE `user` = ?;", array($id))->data;
	}

	function updateuserperms($p, $id) {
		
		return $this->db->insert("UPDATE `user_permissions` SET `data` = ? WHERE `user` = ?;", array($p, $id));
	}

	function isemailused($e) {
		return $this->db->exists("SELECT `email` FROM `users` WHERE `email` = ?;", array($e));
	}

	function registerdatatoken($id, $t, $u, $e) {
		return $this->db->insert("INSERT INTO `data_tokens` (`id`,`type`,`user`,`expires`) VALUES (?,?,?,?);", array($id, $t, $u, $e));
	}

	function getdatatoken($id) {
		return $this->db->query("SELECT `user`,`type`,`expires` FROM `data_tokens` WHERE `id` = ?;", array($id));
	}

	function deletedatatoken($id) {
		return $this->db->insert("DELETE FROM `data_tokens` WHERE `id` = ?;", array($id));
	}

	function add2fasecret($s, $id) {
		return $this->db->insert("UPDATE `user_authentication` SET `2fa_enabled` = 1, `2fa_secret` = ? WHERE `user` = ?;", array($s, $id));
	}

	function get2fasecret($id) {
		return $this->db->query("SELECT `2fa_secret` FROM `user_authentication` WHERE `user` = ?;", array($id));
	}
	function getusersbirthdays() {
		return $this->db->query_all("SELECT `firstname`, `lastname`, `dateofbirth` FROM `users`;");
	}

	function getuserbirthday($id) {
		return $this->db->query("SELECT `firstname`, `lastname`, `dateofbirth` FROM `users` WHERE `id` = ?;", array($id));
	}

	function getuserinfo($id) {
		$r = $this->db->query("SELECT `id`,`firstname`,`lastname`,`staff`,`profilepicture`, 'bio', `signature`, `dateofbirth` FROM `users` WHERE `id` = ?;", array($id));
		if (!isset($r->id)) {
			return null;
		}
		$r->staff = boolval($r->staff);
		return $r;
	}

	function getusersinfo($ids) {
		$in = str_repeat('?,', count($ids) - 1) . '?';
		$r = $this->db->query_all("SELECT `id`,`firstname`,`lastname`,`staff`,`profilepicture` FROM `users` WHERE `active` = 1 AND `id` IN ($in);", $ids);
		foreach ($r as &$d) {
			$d->staff = boolval($d->staff);
			$d->profilepicture = boolval($d->profilepicture);
		}
		return $r;
	}

	function updateuserdata($id, $d) {
		return $this->db->insert(
			"UPDATE `users` SET `firstname` = ?,`lastname` = ?,`phonenumber` = ?,`email` = ?,`title` = ?,`bio` = ?,`signature` = ?,`dateofbirth` = ? WHERE `id` = ?;",
			array($d->firstname, $d->lastname, $d->phonenumber, $d->email, $d->title, $d->bio, $d->signature, $d->dateofbirth, $id)
		);
	}

	function updatepassword($u, $p) {
		return $this->db->insert("UPDATE `user_authentication` SET `passwd` = ? WHERE `user` = ?;", array($p, $u));
	}

	function updatename($u, $f, $n) {
		return $this->db->insert("UPDATE `users` SET `firstname` = ?, `lastname` = ? WHERE `id` = ?;", array($f, $n, $u));
	}

	function updateemail($u, $v) {
		return $this->db->insert("UPDATE `users` SET `email` = ? WHERE `id` = ?;", array($v, $u));
	}

	function updatedob($u, $v) {
		return $this->db->insert("UPDATE `users` SET `dateofbirth` = ? WHERE `id` = ?;", array($v, $u));
	}

	function updatephonenumber($u, $v) {
		return $this->db->insert("UPDATE `users` SET `phonenumber` = ? WHERE `id` = ?;", array($v, $u));
	}

	function active($u, $v) {
		return $this->db->insert("UPDATE `users` SET `active` = " . ($v ? 1 : 0) . " WHERE `id` = ?;", array($u));
	}

	function updateavatar($u, $v) {
		return $this->db->insert("UPDATE `users` SET `profilepicture` = ? WHERE `id` = ?;", array($v, $u));
	}

	function isavataridused($id) {
		return $this->db->exists("SELECT `profilepicture` FROM `users` WHERE `profilepicture` = ?;", array($id));
	}

	function getuserproduct($id) {
		return $this->db->query("SELECT * FROM `user_products` WHERE `id` = ?;", array($id));
	}

	function getrenewableproducts($c, $b) {
		return $this->db->query("SELECT * FROM `user_products` WHERE `renew` = 1 AND `interval` = ? AND `last_renewed` < ?;", array($c, $b));
	}

	function updateproductrenewdate($id) {
		return $this->db->insert("UPDATE `user_products` SET `last_renewed` = ? WHERE `id` = ?;", array(time(), $id));
	}

	function deleteoldsessions($id)
	{
		return $this->db->delete("DELETE FROM `sessions` WHERE `user` = ?;", array($id));
	}
}
