<?php

class Route_v1_Tournament
{
    public $offset = true;

    function index($id)
    {
        global $app, $user, $config;
        $app->cors('private');
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $input = json_decode(file_get_contents('php://input'));

            $auth = new Authentication_Token;
            if (!$auth->isuser()) {
                new Application_Exception(401);
            } else {
                $db = new DB_Tournament;
                return $db->getalltournaments();
            }
        } else new Application_Exception(404);
    }

    function create()
    {
        global $app, $user, $config;
        $app->cors('private');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'));

            $auth = new Authentication_Token;
            if (!$auth->isuser()) {
                new Application_Exception(401);
            } else {
                $db = new DB_Tournament;

                if (!$input->playerpayprize) {
                    $balance = $db->getuserbalance($user->id);

                    if ($balance->yarl >= $input->price) {
                        // $db->decreasebalance($user->id, $balance->yarl - $input->price);
                        $users = [];
                        if ($input->join) {
                            array_push($users, $user->id);
                        }

                        $users = json_encode($users);

                        // create matches
                        $log = log($input->amount, 2); // check if the amount is a power of 2
                        if (floor($log) == $log) { // is this a whole number?
                            $t = $db->createtournament($user->id, $input->amount, $users, $input->price, $input->playerpayprize);
                            $matchAmount = $input->amount - 1;
                            $matchesPerStage = ceil($matchAmount / 2);
                            $stages = ceil(log($input->amount, 2));

                            for ($i = 0; $i < $matchAmount; $i++) {
                                $stage = $stages - floor(log($i + 1, 2));
                                $m = $db->creatematch($t->id, $stage);

                                if ($i == $matchAmount - 1) {
                                    $db->joinmatchp1($user->id, $m->id);
                                }
                            }
                        }

                        // echo "the number of matches is " . $matchAmount . "and the number of stages is" . $stages;

                        return $t;
                    } else new Application_Exception(402);
                }
            }
        } else new Application_Exception(404);
    }

    function join()
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'));

            $auth = new Authentication_Token;
            if (!$auth->isuser()) {
                new Application_Exception(401);
            } else {
                $db = new DB_Tournament;
                $tournament = $db->gettournament($input->id);
                $users = json_decode($tournament->users);

                if (count($users) < $tournament->users_amount) {
                    if (!in_array($user->id, $users)) {
                        array_push($users, $user->id);
                        $users = json_encode($users);

                        if ($tournament->participants_pay_prize) {
                            $balance = $db->getuserbalance($user->id);
                            $price = $tournament->price / $tournament->users_amount;
                            $db->decreasebalance($user->id, $balance->yarl - $price);
                        }


                        // get matches and join the first one
                        $matches = $db->getmatches($input->id, 1);
                        foreach ($matches as $match) {
                            if ($match->player1 == null) {
                                $db->joinmatchp1($user->id, $match->id);
                                break;
                            } else if ($match->player2 == null) {
                                $db->joinmatchp2($user->id, $match->id);
                                break;
                            }
                        }

                        $t = $db->updateusers($input->id, $users);
                        $users = json_decode($t->users);

                        if (count($users) == $t->users_amount) {
                            // tournament can be started'
                            $tournament->started = true;
                            $db->setstarted($input->id);
                        }

                        return $t;
                    } else return ["success" => false];
                } else return ["success" => false];
            }
        } else new Application_Exception(404);
    }

    function matches($id)
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $input = json_decode(file_get_contents('php://input'));

            $auth = new Authentication_Token;
            if (!$auth->isuser()) {
                new Application_Exception(401);
            } else {
                $db = new DB_Tournament;
                $tournament = $db->gettournament($id);
                return $db->getallmatches($id);
            }
        } else new Application_Exception(404);
    }

    function setmatch($id)
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'));

            $auth = new Authentication_Token;
            if (!$app->IsServer($config)) {
                new Application_Exception(401);
            } else {
                $db = new DB_Tournament;
                $tournament = $db->gettournament($id);

                $m = $db->setmatch($input->id, $input->winner);
                $matches = $db->getmatches($id, $m->stage + 1);

                $matchid = 0;
                foreach ($matches as $match) {
                    if ($match->player1 == null) {
                        $matchid = $match->id;
                        $db->joinmatchp1($input->winner, $match->id);
                        break;
                    } else if ($match->player2 == null) {
                        $matchid = $match->id;
                        $db->joinmatchp2($input->winner, $match->id);
                        break;
                    }
                }

                $m = $db->getmatch($matchid);
                return $m;
            }
        } else new Application_Exception(404);
    }

    function leave($id)
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $input = json_decode(file_get_contents('php://input'));

            $auth = new Authentication_Token;
            if (!$app->IsServer($config)) {
                new Application_Exception(401);
            } else {
                $db = new DB_Tournament;
                $tournament = $db->gettournament($id);

                if ($tournament->host == $user->id) {
                    $db->deleteallmatches($id);
                    return ["success" => $db->deletetournament($id)];
                } else {
                    $users = json_decode($tournament->users);
                    $key = array_search($user->id, $users);
                    unset($users[$key]);
                    $users = json_encode($users);

                    $db->updateusers($id, $users);

                    $matches = $db->getmatches($id, 1);

                    foreach ($matches as $match)
                    {
                        if ($match->player1 == $user->id)
                        {
                            $db->leavematchp1($match->id);
                            break;
                        }
                        else if ($match->player2 == $user->id)
                        {
                            $db->leavematchp2($match->id);
                            break;
                        }
                    }

                    return ["success" => true];
                }
            }
        } else new Application_Exception(404);
    }

    function tournamentstarted($id)
    {
        global $app, $user, $config;
        $app->cors('private');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $input = json_decode(file_get_contents('php://input'));

            $auth = new Authentication_Token;
            if (!$app->IsServer($config)) {
                new Application_Exception(401);
            } else {
                $db = new DB_Tournament;

                if ($db->exists($id)) {
                    $tournament = $db->gettournament($id);
                    if ($tournament->started) return ["success" => 1];
                    else return ["success" => 2];
                }

                else return ["success" => 3];

                // 1 - started
                // 2 - not started
                // 3 - doesnt exist (anymore)
            }
        }
    }
}
