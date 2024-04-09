<?php

class DB_Inventory
{
    public $db;

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function getallitems($id)
    {
        return $this->db->query_all("SELECT `item` AS `cardID`, `qty` FROM `inventory` WHERE `user` = ?;", array($id));
    }

    function getallitemsID($id)
    {
        return $this->db->query_all("SELECT `item` FROM `inventory` WHERE `user` = ?;", array($id));
    }

    function getitem($id)
    {
        return $this->db->query("SELECT * FROM `products` WHERE `item` = ?;", array($id));
    }

    function getallcards()
    {
        return $this->db->query_all("SELECT * FROM `products` WHERE `type` = 'card' OR `type` = 'spell';");
    }
}
