<?php

class DB_Main {
	private $db;

	function __construct() {
		global $config;
		$sql = $config->sql;
		try {
			$this->db = new PDO("mysql:host=$sql->host;dbname=$sql->database;charset=utf8", $sql->username, $sql->password);
			$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		} catch (PDOException $e) {
			new Application_Exception(500);
		}
	}

	function prepare($q) {
		return $this->db->prepare($q);
	}

	function query($q, $a = array()) {
		$q = $this->db->prepare($q);
		$q->execute($a);
		return $q->fetch(PDO::FETCH_OBJ);
	}

	function insert($q, $a = array()) {
		return $this->db->prepare($q)->execute($a);
	}

	function delete($q, $a = array()) {
		return $this->db->prepare($q)->execute($a);
	}

	function query_all($q, $a = array()) {
		$q = $this->db->prepare($q);
		$q->execute($a);
		return $q->fetchAll(PDO::FETCH_OBJ);
	}

	function exists($q, $a = array()) {
		$q = $this->db->prepare($q);
		$q->execute($a);
		if ($q->rowCount() >= 1) {
			return true;
		} else {
			return false;
		}
	}

	function getlastinsertedid() {
		return $this->db->lastInsertId();
	}
}
