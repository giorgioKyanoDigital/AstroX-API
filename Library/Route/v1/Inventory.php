<?php

class Route_v1_Inventory
{
    public $offset = true;
    function index() // werkt
    {
        global $app, $user, $config;
        $app->cors('private');
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $d = json_decode(file_get_contents('php://input'));
            $auth = new Authentication_Token;
            if (!$auth->isuser()) {
                new Application_Exception(401);
            } else {
                $db = new DB_Inventory;
                $inventory = $db->getallitemsID($user->id);
                $items = [];

                foreach ($inventory as $i) {
                    if (isset($i->item))
                        $items[] = $db->getitem($i->item);
                }

                return ['items' => $items];
            }
        }
    }

    function cards() // werkt
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $d = json_decode(file_get_contents('php://input'));
            $auth = new Authentication_Token;
            if (!$auth->isuser()) {
                new Application_Exception(401);
            } else {
                $uid = $user->id;
                $db = new DB_Inventory;
                $cards = $db->getallcards();
                $cd = [];
                $inventory = $db->getallitems($uid);

                foreach ($cards as $c) {
                    $cd[$c->item] = $c->type;
                }
                
                foreach ($inventory as $key => $i) {
                    if (!isset($cd[$i->cardID]) || ($cd[$i->cardID] != 'card' && $cd[$i->cardID] != 'spell')) {
                        unset($inventory[$key]);
                    }
                }
                return ['cards' => array_values($inventory)];
            }
        } else new Application_Exception(404);
    }
}
