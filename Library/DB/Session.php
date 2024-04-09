<?php
class DB_Session {
	public $db;

	function __construct() {
		global $db;
		$this->db = $db;
	}

	function create($sid, $uid) {
		return $this->db->insert("INSERT INTO `sessions` (`id`, `user`, `lastupdated`) VALUES (?, ?, ?);", array($sid, $uid, time() + (3600 * 12)));
	}

	function checkifexists($id) {
		return $this->db->exists("SELECT * FROM `sessions` WHERE id = ?;", array($id));
	}

	function check($id) {
		return $this->db->query("SELECT * FROM `sessions` WHERE id = ?;", array($id));
	}

	function update($id) {
		return $this->db->insert("UPDATE `sessions` SET `lastupdated` = ? WHERE `id` = ?;", array(time() + (3600 * 12), $id));
	}

	function delete($id) {
		return $this->db->delete("DELETE FROM `sessions` WHERE `id` = ?;", array($id));
	}
}
