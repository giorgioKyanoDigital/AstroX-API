<?php
class DB_Deckbuilder
{

	public $db;

	function __construct()
	{
		global $db;
		$this->db = $db;
	}

	function create($uid, $d)
	{
		$res = $this->db->insert(
			"INSERT INTO `decks` (`user`, `deck`) VALUES (?, ?);",
			[$uid, json_encode($d)]
		);
		if ($res) {
			return true;
		} else {
			return false;
		}
	}

	function get($id)
	{
		$r = $this->db->query("SELECT * FROM `decks` WHERE `id` = ?;", array($id));
		if (isset($r->id)) {
			$r->deck = json_decode($r->deck);

			return $r;
		} else {
			return null;
		}
	}

	function getfromplayerid($id)
	{
		return $this->db->query_all("SELECT `id`, `deck` AS `cards` FROM `decks` WHERE `user` = ?;", array($id));
	}

	function getid($id)
	{
		$res = $this->db->query("SELECT `id` FROM `projects` WHERE `id` = ?;", [$id]);
		if (isset($res->id)) {
			return $res->id;
		} else {
			return null;
		}
	}

	function getall($limit, $offset, $f)
	{

		$res = $this->db->query_all("SELECT	* FROM `decks` ORDER BY `id` DESC LIMIT $offset, $limit;", array_values($f));
		foreach ($res as &$v) {
			$v->data = json_decode($v->data);
		}
		return $res;
	}

	function getcount()
	{
		return $this->db->query("SELECT COUNT(*) as `count` FROM `projects`;");
	}

	function update($id, $d)
	{
		return $this->db->insert("UPDATE `decks` SET `deck` = ? WHERE `id` = ?;", array(json_encode($d), $id));
	}

	function delete($id)
	{
		return $this->db->delete("UPDATE `decks` SET `deck` = 0 WHERE `id` = ?;", array($id));
	}

	function exists($id)
	{
		return $this->db->exists("SELECT `id` FROM `decks` WHERE user = ?;", array($id));
	}

	public function getLastInsertID()
	{
		return $this->db->query('SELECT LAST_INSERT_ID() as `last` FROM `orders`')->last;
	}
}
