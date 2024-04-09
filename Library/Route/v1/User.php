<?php

class Route_v1_User
{
    public $offset = true;

    function index($id) // werkt
    {
        global $app, $user, $config;
        $app->cors('private');
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $input = json_decode(file_get_contents('php://input'));

            $auth = new Authentication_Token;
            if (!$auth->isuser()) {
                new Application_Exception(401);
            } else {
                $db = new DB_User;
                if ($id != 0)
                {
                    if ($db->exists($id))
                    return $db->get($id);
                    else return ['error' => 'user does not exist'];
                }
                else return $db->get($user->id);
            }
        } else if ($_SERVER['REQUEST_METHOD'] === "PATCH") {
            $input = json_decode(file_get_contents('php://input'));

            $auth = new Authentication_Token;
            if (!$auth->isuser()) {
                new Application_Exception(401);
            } else {
                $db = new DB_User;
                if (isset($input->deck)) {
                    return ['success' => $db->setdeck($user->id, $input->deck)];
                } else if (isset($input->commander)) {
                    return ['success' => $db->setcommander($user->id, $input->commander)];
                }
            }
        } else new Application_Exception(404);
    }
}
