<?php

class DB_Tournament
{
    public $db;

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function getalltournaments()
    {
        return $this->db->query_all("SELECT	* FROM `tournaments` ORDER BY `id`;");
    }

    function createtournament($uid, $amount, $users, $price, $participants_pay_prize)
    {
        $b = $this->db->insert("INSERT INTO `tournaments` (`host`, `users_amount`, `users`, `price`, `participants_pay_prize`) VALUES (?, ?, ?, ?, ?);", [$uid, $amount, $users, $price, $participants_pay_prize]);
        if ($b)
        {
            $id = $this->getLastInsertID();
            return $this->db->query("SELECT * FROM `tournaments` WHERE `id` = ?;", [$id]);
        }
    }

    function creatematch($tid, $stage)
    {
        $m = $this->db->insert("INSERT INTO `tournament_matches` (`stage`, `tournament`) VALUES (?, ?);", [$stage, $tid]);
        if ($m)
        {
            $id = $this->getLastInsertID();
            return $this->db->query("SELECT * FROM `tournament_matches` WHERE `id` = ?;", [$id]);
        }
    }

    function joinmatchp1($uid, $mid)
    {
        return $this->db->insert("UPDATE `tournament_matches` SET `player1` = ? WHERE `id` = ?;", [$uid, $mid]);
    }
    
    function joinmatchp2($uid, $mid)
    {
        return $this->db->insert("UPDATE `tournament_matches` SET `player2` = ? WHERE `id` = ?;", [$uid, $mid]);
    }

    function getmatch($mid)
    {
        return $this->db->query("SELECT * FROM `tournament_matches` WHERE `id` = ?;", [$mid]);
    }

    function getallmatches($tid)
    {
        return $this->db->query_all("SELECT * FROM `tournament_matches` WHERE `tournament` = ? ORDER BY `id` DESC;", [$tid]);
    }

    function getmatches($tid, $stage)
    {
        return $this->db->query_all("SELECT * FROM `tournament_matches` WHERE `tournament` = ? AND `stage` = ? ORDER BY `id` DESC;", [$tid, $stage]);
    }

    function leavematchp1($mid)
    {
        return $this->db->insert("UPDATE `tournament_matches` SET `player1` = ? WHERE `id` = ?;", [null, $mid]);
    }

    function leavematchp2($mid)
    {
        return $this->db->insert("UPDATE `tournament_matches` SET `player2` = ? WHERE `id` = ?;", [null, $mid]);
    }

    function setstarted($tid)
    {
        return $this->db->insert("UPDATE `tournaments` SET `started` = ? WHERE `id` = ?;", [true, $tid]);
    }

    function updateusers($uid, $users)
    {
        if ($this->db->insert("UPDATE `tournaments` SET `users` = ? WHERE `id` = ?;", [$users, $uid]))
        {
            return $this->db->query("SELECT * FROM `tournaments` WHERE `id` = ?;", [$uid]);
        }
    }

    function setmatch($mid, $winner)
    {
        $b = $this->db->insert("UPDATE `tournament_matches` SET `winner` = ? WHERE `id` = ?;", [$winner, $mid]);
        if ($b)
        {
            return $this->db->query("SELECT `stage` FROM `tournament_matches` WHERE `id` = ?;", [$mid]);
        }
    }

    function gettournament($id)
    {
        return $this->db->query("SELECT * FROM `tournaments` WHERE `id` = ?;", [$id]);
    }

    function getuserbalance($uid)
    {
        return $this->db->query("SELECT * FROM `bank` WHERE `user` = ?;", [$uid]);
    }

    function decreasebalance($uid, $newbalance)
    {
        return $this->db->insert("UPDATE `bank` SET `yarl` = ? WHERE `user` = ?;", [$newbalance, $uid]);
    }

    function deletetournament($tid)
    {
        return $this->db->delete("DELETE FROM `tournaments` WHERE `id` = ?;", [$tid]);
    }

    function deleteallmatches($tid)
    {
        return $this->db->delete("DELETE FROM `tournament_matches` WHERE `tournament` = ?;", [$tid]);
    }
    
    function exists($tid)
    {
        return $this->db->exists("SELECT * FROM `tournaments` WHERE `id` = ?;", [$tid]);
    }

    function getLastInsertID() {
		return $this->db->query('SELECT LAST_INSERT_ID() as `last` FROM `tournaments`')->last;
	}
}
