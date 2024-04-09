<?php

class DB_Matches
{
    public $db;

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function get($id)
    {
        return $this->db->query("SELECT * FROM `matches` WHERE `id` = ?;", [$id]);
    }

    function getidPlayerAmount($id)
    {
        return $this->db->query("SELECT `id`, `player1`, `player2`, `wager` FROM `matches` WHERE `id` = ?;", [$id]);
    }

    function getplayers($id)
    {
        $r = $this->db->query("SELECT player1, player2 FROM `matches` WHERE `id` = ?;", [$id]);
        if (isset($r->id)) {
            $r->data = json_decode($r->data);

            return $r;
        } else {
            return null;
        }
    }

    function getallmatches($id)
    {
        return $this->db->query_all("SELECT * FROM `matches` WHERE `player1` = ? OR 'player2' = ?;", array($id, $id));
    }

    function create($matchid)
    {
        $res = $this->db->insert(
            "INSERT INTO `matches` (`matchid`) VALUES (?);",
            [$matchid]
        );
        if ($res) {
            return $this->db->query("SELECT `id` AS `matchid` FROM `matches` WHERE `matchid` = ?;", [$matchid]);
        } else {
            return false;
        }
    }

    function createwager($matchid, $wager)
    {
        $res = $this->db->insert(
            "INSERT INTO `matches` (`matchid`, `wager`) VALUES (?, ?);",
            [$matchid, $wager]
        );
        if ($res) {
            return $this->db->query("SELECT * FROM `matches` WHERE `matchid` = ?;", [$matchid]);
        } else {
            return false;
        }
    }

    function join($matchid, $userid)
    {
        return $this->db->insert("UPDATE `matches` SET `player1` = ? WHERE `id` = ?;", array($userid, $matchid));
    }

    function joinp2($matchid, $userid)
    {
        return $this->db->insert("UPDATE `matches` SET `player2` = ? WHERE `id` = ?;", array($userid, $matchid));
    }

    function decreasebalance($userid, $amount)
    {
        $b = $this->db->query("SELECT `yarl` FROM `bank` WHERE `user` = ?;", array($userid));

        if ($b->yarl < $amount) {
            return false;
        }

        else return $this->db->insert("UPDATE `bank` SET `yarl` = ? WHERE `user` = ?;", array($b->yarl - $amount, $userid));
    }

    function settutorial($id)
    {
        return $this->db->insert("UPDATE `users` SET `finished_tutorial` = true WHERE `id` = ?;", array($id));
    }
}