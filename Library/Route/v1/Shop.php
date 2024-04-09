<?php

class Route_v1_Shop
{
    public $offset = true;

    function index() // werkt
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            // $d = json_decode(file_get_contents('php://input'));

            // $auth = new Authentication_Token;
            // if (!$auth->isuser()) {
            //     new Application_Exception(401);
            // } else {

            $db = new DB_Shop;
            return ["Items"=> $db->getall(100, 0, [])];
        } else new Application_Exception(404);
    }

    function buy()
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $d = json_decode(file_get_contents('php://input'));

            // $auth = new Authentication_Token;
            // if (!$auth->isuser()) {
            //     new Application_Exception(401);
            // } else {

            $db = new DB_Shop;
            $balance = $db->getuserbalance(33)->yarl;
            $price = $db->getprice($d->item) * $d->qty;

            if ($balance >= $price) {
                $db->decreasebalance(33, $balance - $price);
                return ["success" => $db->buyitem(33, $d->item, $d->qty)];
            } else return ["success" => "false"];
            // }
        } else new Application_Exception(404);
    }

    function balance()
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $d = json_decode(file_get_contents('php://input'));

            // $auth = new Authentication_Token;
            // if (!$auth->isuser()) {
            //     new Application_Exception(401);
            // } else {
                $db = new DB_Shop;
                return $db->getuserbalance(33);
            // }
        } else new Application_Exception(404);
    }
}
