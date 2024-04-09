<?php
class Route_v1_Deckbuilder
{
	public $offset = true;

	// Get player decks
	function index($id)
	{
		global $app, $user, $config;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$auth = new Authentication_Token;

			if (!$auth->isuser()) {
				new Application_Exception(401);
			} else {
				$db = new DB_Deckbuilder;

				if ($id != 0) {
					if ($db->exists($id)) {
						$decks = $db->getfromplayerid($id);
					} else return ['success' => 'false'];
				} else 
				{
					$dbDeck = $db->getfromplayerid($user->id);
					foreach ($dbDeck as &$deck) {
						$deck->cards = json_decode($deck->cards);
					}

					return ['decks' => $dbDeck];
				}
			}
		}
	}

	function save()
	{
		global $app, $user, $config;
		$app->cors('private');

		if ($_SERVER["REQUEST_METHOD"] === "POST") {
			$d = json_decode(file_get_contents('php://input'));

			$auth = new Authentication_Token;
			if (!$auth->isuser()) {
				new Application_Exception(401);
			} else {
				$db = new DB_Deckbuilder;
				return ['success' => $db->create($user->id, $d->deck)];
			}
		}
	}

	function update($id)
	{
		global $app, $user, $config;
		$app->cors('private');

		if ($_SERVER["REQUEST_METHOD"] === "PATCH") {
			$d = json_decode(file_get_contents('php://input'));

			$auth = new Authentication_Token;
			if (!$auth->isuser()) {
				new Application_Exception(401);
			} else {
				if ($this->OwnedByUser($id, $user->id)) {
					// check if deck is owned by user
					$db = new DB_Deckbuilder;
					return $db->update($id, $d->deck);
				} else {
					new Application_Exception(401);
				}
			}
		}
	}

	function delete($id)
	{
		global $app, $user, $config;
		$app->cors('private');

		if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
			$auth = new Authentication_Token;
			if (!$auth->isuser() || !$user->loggedin) {
				new Application_Exception(401);
			} else {
				if ($this->OwnedByUser($id, $user->id)) {
					// check if deck is owned by user
					$db = new DB_Deckbuilder;
					return $db->delete($id);
				} else {
					new Application_Exception(401);
				}
			}
		}
	}

	function OwnedByUser($id, $userid)
	{
		$db = new DB_Deckbuilder;

		$deck = $db->get($id);
		if ($deck->user == $userid) {
			return true;
		} else {
			return false;
		}
	}
}
