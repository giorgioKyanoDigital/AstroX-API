<?php

class Route_v1_Gameserver
{
    public $offset = true;

    function setmatch() // werkt
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $d = json_decode(file_get_contents('php://input'));

            if ($app->IsServer($config)) {
                $db = new DB_Gameserver;
                $match = $db->getmatch($d->matchid);
                $db->setrewards($db->getuser($d->winner), 200);
                $db->setrewards($db->getuser($d->loser), 100);

                return ['success' => $db->setmatchwinner($d->winner, $match->id)];
            } else {
                new Application_Exception(401);
            }
        }
        
        else new Application_Exception(404);
    }

    function commander($id) // werkt
    {
        global $app, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $d = json_decode(file_get_contents('php://input'));

            if ($app->IsServer($config)) {
                $db = new DB_Gameserver;
                return $db->getcommander($id);
            } else {
                new Application_Exception(401);
            }
        }
        
        else new Application_Exception(404);
    }
    
    function deck($id) // werkt
    {
        global $app, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {

            if ($app->IsServer($config)) {
                $db = new DB_Gameserver;
                $deck = $db->getdeckid($id);
                $d = $db->getdeck($deck->deck);
                return ["cards" => json_decode($d->deck)];
            } else {
                new Application_Exception(401);
            }
        }
        
        else new Application_Exception(404);
    }

    function settutorial()
    {
        global $app, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $d = json_decode(file_get_contents('php://input'));

            if ($app->IsServer($config)) {
                $db = new DB_Gameserver;
                return [ "success" => $db->settutorial($d->id)];
            } else {
                new Application_Exception(401);
            }
        } else new Application_Exception(404);
    }
}