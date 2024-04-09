<?php
class DB_Shop
{
    public $db;

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function getall($limit, $offset, $f) {
	
		return $this->db->query_all("SELECT	* FROM `shop` ORDER BY `id` DESC LIMIT $offset, $limit;", array_values($f));
	}

    function getuserbalance($uid)
    {
        return $this->db->query("SELECT `yarl`, `silver` FROM `bank` WHERE `user` = ?;", [$uid]);
    }

    function getprice($item)
    {
        $res = $this->db->query("SELECT * FROM `shop` WHERE `item` = ?;", [$item]);

        if (isset($res->cost)) {
            return $res->cost;
        } else {
            return null;
        }
    }

    function decreasebalance($uid, $newbalance)
    {
        return $this->db->insert("UPDATE `bank` SET `yarl` = ? WHERE `user` = ?;", [$newbalance, $uid]);
    }

    function buyitem($uid, $item, $qty)
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
