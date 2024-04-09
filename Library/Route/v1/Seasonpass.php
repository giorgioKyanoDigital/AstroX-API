<?php

class Route_v1_Seasonpass
{
    public $offset = true;

    function index()
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            // $d = json_decode(file_get_contents('php://input'));

            // $auth = new Authentication_Token;
            // if (!$auth->isuser()) {
            //     new Application_Exception(401);
            // } else {

            $db = new DB_Seasonpass;
            return $db->getall(100, 0, []);
        } else new Application_Exception(404);
    }

    function claim()
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $d = json_decode(file_get_contents('php://input'));

            // $auth = new Authentication_Token;
            // if (!$auth->isuser()) {
            //     new Application_Exception(401);
            // } else {

            $db = new DB_Seasonpass;
            $slvl = $this->getSeasonLevel($user->sxp);

            if ($db->isclaimable($d->item, 33, $slvl))
            {
                $sItem = $db->get($d->item);
                $db->insertininventory(33, $d->item, $sItem->amount);
            }

        } else new Application_Exception(404);
    }

    function claimable()
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            // $d = json_decode(file_get_contents('php://input'));

            // $auth = new Authentication_Token;
            // if (!$auth->isuser()) {
            //     new Application_Exception(401);
            // } else {

            $db = new DB_Seasonpass;
            $claimedItems = $db->getclaimeditems(33);
            $seasonItems = $db->getall(100, 0, []);
            $slvl = $this->getSeasonLevel($user->sxp);

            $claimableItems = [];
            foreach ($seasonItems as $item) {
                if (!in_array($item->id, $claimedItems)) {
                    if ($item->level <= $slvl) {
                        $claimableItems[] = $item;
                    }
                }
            }

            return $claimableItems;
        } else new Application_Exception(404);
    }

    function level()
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            // $d = json_decode(file_get_contents('php://input'));

            // $auth = new Authentication_Token;
            // if (!$auth->isuser()) {
            //     new Application_Exception(401);
            // } else {

            $db = new DB_Seasonpass;
            return $this->getSeasonLevel($user->sxp);
        } else new Application_Exception(404);
    }

    function getSeasonLevel($sxp)
    {
        $level = 0;
        $c = 0;
        $addition = 0;
        for ($x = 0; $x <= 100; $x++) {
            if ($c == 5) {
                $addition += 100;
                $c = 0;
            }
            $clvlxp = (int)(($x * 100) + $addition);
            if ($sxp >= $clvlxp) {
                $level = $x;
            }
            $c++;
        }
        return $level;
    }
}
