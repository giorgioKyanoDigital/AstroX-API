<?php
class DB_Template {

	public $db;

	function __construct() {
		global $db;
		$this->db = $db;
	}

	function create($d) {
		$res = $this->db->insert(
			"INSERT INTO `projects` (`project`, `relation_type`, `relation`, `reference`, `name`, `manager`, `data`) VALUES (?, ?, ?, ?, ?, ?, ?);",
			[$d->project, $d->relation_type, $d->relation, $d->reference, $d->name, $d->manager, $d->data]
		);
		if ($res) {
			return $this->db->query("SELECT LAST_INSERT_ID() AS `id`;")->id;
		} else {
			return false;
		}
	}

	function get($id) {
		$r = $this->db->query("SELECT * FROM `projects` WHERE `id` = ?;", array($id));
		if (isset($r->id)) {
			$r->data = json_decode($r->data);
		
			return $r;
		} else {
			return null;
		}
	}

	function getid($id) {
		$res = $this->db->query("SELECT `id` FROM `projects` WHERE `id` = ?;", [$id]);
		if (isset($res->id)) {
			return $res->id;
		} else {
			return null;
		}
	}

	function getall($limit, $offset, $f) {
	
		$res = $this->db->query_all("SELECT	* FROM `projects`  ORDER BY `id` DESC LIMIT $offset, $limit;", array_values($f));
		foreach ($res as &$v) {
			$v->data = json_decode($v->data);
		}
		return $res;
	}

	

	function getcount() {
		return $this->db->query("SELECT COUNT(*) as `count` FROM `projects`;");
	}

	function update($id, $d) {
		return $this->db->insert("UPDATE `projects` SET `data` = ? WHERE `id` = ?;", array($d->data, $id));
	}

	function updatediff($project_id, $mechanics) {
		$old_mechanics = array_column($this->getmechanics($project_id), 'id');
		$removed = array_diff($old_mechanics, $mechanics);
		$added = array_diff($mechanics, $old_mechanics);
		foreach ($removed as $m) {
			$this->db->delete('DELETE FROM `project_mechanics` WHERE `project` = ? AND `user` = ?;', [$project_id, $m]);
		}
		foreach ($added as $m) {
			$this->db->insert("INSERT INTO `project_mechanics` (`project`, `user`) VALUES (?, ?);", [$project_id, $m]);
		}
		return (object) ['removed' => $removed, 'added' => $added];
	}

	function delete($id) {
		return $this->db->delete("UPDATE `projects` SET `visible` = 0 WHERE `id` = ?;", array($id));
	}
	function activate($id) {
		return $this->db->insert("UPDATE `projects` SET `visible` = 1 WHERE `id` = ?;", array($id));
	}

	function exists($id) {
		return $this->db->exists("SELECT `id` FROM `projects` WHERE `id` = ?;", array($id));
	}

	function deleteproject($id) {
		return $this->db->delete("DELETE FROM `project_files` WHERE `id` = ?;", array($id));
	}
	public function getLastInsertID() {
		return $this->db->query('SELECT LAST_INSERT_ID() as `last` FROM `orders`')->last;
	}

}
