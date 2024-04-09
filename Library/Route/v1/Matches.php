<?php

class Route_v1_Matches
{
    public $offset = true;

    function index($id) // werkt
    {
        global $app, $user, $config;
        $app->cors('private');
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $input = json_decode(file_get_contents('php://input'));

            // $auth = new Authentication_Token;
            // if (!$auth->isuser()) {
            //     new Application_Exception(401);
            // } else {
            $db = new DB_Matches;
            return $db->getallmatches(33);
            // }
        } else new Application_Exception(404);
    }

    function classic() // werkt
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $d = json_decode(file_get_contents('php://input'));

            if ($app->IsServer($config)) {
                $db = new DB_Matches;
                $matchid = $app->createrandomstring(20);
                $match = $db->create($matchid);
                $match->matchid = (string)$match->matchid;
                return $match;
            } else {
                new Application_Exception(401);
            }
        } else new Application_Exception(404);
    }

    function wager() // werkt
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $d = json_decode(file_get_contents('php://input'));

            if ($app->IsServer($config)) {
                $db = new DB_Matches;
                $matchid = $app->createrandomstring(20);
                return $db->createwager($matchid, $d->wager);
            } else {
                new Application_Exception(401);
            }
        }
    }

    function join() // werkt
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $d = json_decode(file_get_contents('php://input'));

            $auth = new Authentication_Token;
            if ($auth->isuser()) {
                $db = new DB_Matches;
                $match = $db->get($d->matchid);

                if (!$match) {
                    new Application_Exception(401);
                }

                if ($match->wager > 0) {
                    // decrease user balance
                    if (!$db->decreasebalance(33, $match->wager)) {
                        return ['error' => 'Not enough balance.'];
                    }
                }

                if ($match->player1 == $user->id || $match->player2 == $user->id) {
                    new Application_Exception(403);
                }

                if (!isset($match->player1))
                    $db->join($d->matchid, $user->id);
                else $db->joinp2($d->matchid, $user->id);

                return $db->getidPlayerAmount($d->matchid);
            } else {
                new Application_Exception(401);
            }
        } else new Application_Exception(404);
    }

    function get()
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $d = json_decode(file_get_contents('php://input'));
            $auth = new Authentication_Token;
            if (!$auth->isuser()) {
                new Application_Exception(401);
            } else {
                $db = new DB_Matches;
                return $db->getplayers($d->matchid);
            }
        }
    }

    function settutorial()
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $d = json_decode(file_get_contents('php://input'));
            $auth = new Authentication_Token;
            if ($auth->isuser()) {
                $db = new DB_Gameserver;
                return [ "success" => $db->settutorial($user->id)];
            } else {
                new Application_Exception(401);
            }
        } else new Application_Exception(404);
    }
}
