<?php
class DB_Gameserver
{

    public $db;

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function getmatch($id) {
		return $this->db->query("SELECT * FROM `matches` WHERE `id` = ?;", array($id));
	}

    function setmatchwinner($w, $mid)
    {
        return $this->db->insert("UPDATE `matches` SET `outcome` = ? WHERE `id` = ?;", array($w, $mid));
    }
    
    function settutorial($id)
    {
        return $this->db->insert("UPDATE `users` SET `finished_tutorial` = true WHERE `id` = ?;", array($id));
    }

    function getcommander($id)
    {
        return $this->db->query("SELECT `commander` FROM `users` WHERE `id` = ?;", array($id));
    }
    
    function getdeckid($uid)
    {
        return $this->db->query("SELECT `deck` FROM `users` WHERE `id` = ?;", array($uid));
    }

    function getdeck($deck)
    {
        return $this->db->query("SELECT * FROM `decks` WHERE `id` = ?;", array($deck));
    }

    function getallcards()
    {
        return $this->db->query_all("SELECT * FROM `products` WHERE `type` = 'card';");
    }

    function getallitems($id)
    {
        return $this->db->query_all("SELECT `item` AS `cardID`, `qty` FROM `inventory` WHERE `user` = ?;", array($id));
    }

    function getuser($id)
    {
        return $this->db->query("SELECT * FROM `users` WHERE `id` = ?;", array($id));
    }

    function setrewards($u, $xp)
    {
        $b = $this->db->query("SELECT * FROM `boosters` WHERE `user` = ?;", array($u->id));

        if (isset($b) && $b)
        {
            $xp = $xp / 100 * $b->amount + $xp;
            $sxp = $u->sxp + $xp;
            return $this->db->insert("UPDATE `users` SET `sxp` = `sxp` + ? WHERE `id` = ?", array($xp, $u->id));
        }

        else 
        {
            $sxp = $u->sxp + $xp;
            return $this->db->insert("UPDATE `users` SET `sxp` = ? WHERE `id` = ?", array($sxp, $u->id));
        }
    }
}