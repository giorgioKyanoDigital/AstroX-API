<?php

class DB_User
{
    public $db;

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function get($id)
    {
        return $this->db->query("SELECT * FROM `users` WHERE `id` = ?;", [$id]);
    }

    function getdeck($id)
    {
        return $this->db->query("SELECT `id`, `user` FROM `decks` WHERE `id` = ?;", [$id]);
    }

    function getcommander($uid, $commander)
    {
        return $this->db->query("SELECT `item` FROM `inventory` WHERE `user` = ? AND `item` = ?;", [$uid, $commander]);
    }

    function setdeck($id, $deck)
    {
        $d = $this->getdeck($deck);

        if (isset($d->id) && $d->user == $id)
            return $this->db->insert("UPDATE `users` SET `deck` = ? WHERE `id` = ?;", [$deck, $id]);
        else return false;
    }

    function setcommander($id, $commander)
    {
        $c = $this->getcommander($id, $commander);

        if (isset($c->item)) return $this->db->insert("UPDATE `users` SET `commander` = ? WHERE `id` = ?;", [$commander, $id]);
        else return false;
    }

    function exists($id)
    {
        return $this->db->exists("SELECT `id` FROM `users` WHERE id = ?;", array($id));
    }
}
