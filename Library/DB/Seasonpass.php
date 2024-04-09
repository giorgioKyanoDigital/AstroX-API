<?php
class DB_Seasonpass
{
    public $db;

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function get($item)
    {
        return $this->db->query("SELECT * FROM `seasonitems` WHERE `id` = ?;", [$item]);
    }

    function getall($limit, $offset, $f)
    {

        return $this->db->query_all("SELECT	* FROM `seasonitems` ORDER BY `id` DESC LIMIT $offset, $limit;", array_values($f));
    }

    function getclaimeditems($uid)
    {
        global $config;
        return $this->db->query_all("SELECT `item` FROM `claimed_seasonitems` WHERE `user` = ? AND `season` = ?;", [$uid, $config->game->season]);
    }

    function isclaimable($item, $uid, $slvl)
    {
        if (!$this->db->exists("SELECT `item` FROM `claimed_seasonitems` WHERE `item` = ? AND `user` = ?;", array($item, $uid)))
        {
            $res = $this->db->query("SELECT `level` FROM `seasonitems` WHERE `id` = ?;", [$item]);

            if ($res->level <= $slvl)
            {
                return true;
            }

            else return false;
        }

        else return false;
    }

    function insertininventory($uid, $item, $qty)
    {
        if ($this->exists($uid, $item))
        {
            return $this->db->insert("UPDATE `inventory` SET `qty` = `qty` + ? WHERE `id` = ?;", array($qty, $item));
        }

        else
        {
            return $this->db->insert("INSERT INTO `inventory` (`user`, `item`, `qty`) VALUES (?, ?, ?);", array($uid, $item, $qty));
        }
    }

    function exists($uid, $item) {
		return $this->db->exists("SELECT `item` FROM `inventory` WHERE `item` = ? AND `user` = ?;", array($item, $uid));
	}
}
