<?php
class Route_v1_Auth {
	public $offset = false;
	public $product = false;

	function step() {
		global $app, $config;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$d = json_decode(file_get_contents('php://input'));
			
			if ($d === null || !isset($d->username, $d->passwd) && !isset($d->email, $d->passwd)) {
				new Application_Exception(400);
			}
			// if (!preg_match(Util_Regex::$name, $d->username) || !preg_match(Util_Regex::$password, $d->passwd)) {
			// 	new Application_Exception(400);
			// }
			$acc = new User_Account;
			$logintype = isset($d->username) ? "u" : "e" ;
			if(isset($d->username)) {
				$r = $acc->login($d->username, $d->passwd, $logintype);
			}else{
				$r = $acc->login($d->email, $d->passwd, $logintype);
			}
	
			if ($r->r === '2fa_required') {
				$id = $app->createrandomstring(32);
				$e = time() + 300;
				$acc->adb->registerdatatoken($id, '2fa', $r->u, $e);
				$em = "AES-256-CBC";
				$key = hash('sha256', $config->server->secret);
				$iv = substr(hash('sha256', $config->server->secret), 0, 16);
				$a = array('id' => $id, 'user' => $r->u, 'expires' => $e, 'ip' => $_SERVER['REMOTE_ADDR'], 'agent' => $_SERVER['HTTP_USER_AGENT']);
				$v = str_replace('=', '', base64_encode(openssl_encrypt(json_encode($a), $em, $key, 0, $iv)));
				return array("t" => 2, "v" => $v, "u" => $r->u);
			} elseif ($r->r === 'success') {
				$dbacc = new DB_Account;
				$ui = $dbacc->getuser($r->u);
				// if ($ui->staff || (new DB_Account)->isuseractive($ui->id)) {
					
					$dbacc->deleteoldsessions($ui->id);
					$us = new User_Session;
					$s = $us->create($r->u);

					$app->log('user', (object) [
						'user' => $r->u,
						'type' => 'login',
						'data' => [
							'ip' => $_SERVER['REMOTE_ADDR'],
							'user-agent' => $_SERVER['HTTP_USER_AGENT'],
						],
					]);
					return array(/*"t" => 1,*/ "token" => $s, "id" => $r->u);
				// } else {
				// 	new Application_Exception(403);
				// }
			} else {
				new Application_Exception(401);
			}
		} else {
			new Application_Exception(400);
		}
	}

	function steps() {
		global $config, $app;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$d = json_decode(file_get_contents('php://input'));
			if ($d === null || !isset($d->t, $d->v)) {
				// $secret = Util_Base32::encode(Authentication_Totp::GenerateSecret(16));
				// $adb = new DB_Account;
				// $adb->add2fasecret($secret, 1);
				new Application_Exception(400);
				die;
			}

			if (!preg_match('/^\d{6}$/', $d->v)) {
				new Application_Exception(400);
			}

			$em = "AES-256-CBC";
			$key = hash('sha256', $config->server->secret);
			$iv = substr(hash('sha256', $config->server->secret), 0, 16);
			$td = json_decode(openssl_decrypt(base64_decode($d->t . '=='), $em, $key, 0, $iv));
			if ($td === null || !isset($td->id, $td->user, $td->expires, $td->ip, $td->agent)) {
				new Application_Exception(400);
			}

			$acc = new User_Account;
			$ti = $acc->adb->getdatatoken($td->id);
			if ($ti === null || $ti->id !== $td->id || $ti->expires !== $td->expires || $ti->type !== "2fa") {
				new Application_Exception(401);
			}
			if ($_SERVER['REMOTE_ADDR'] === $td->ip && $_SERVER['HTTP_USER_AGENT'] === $td->agent || $td->expires >= time()) {
				$q = $acc->adb->get2fasecret($td->user);
				if ((new Authentication_Totp)->GenerateToken(Util_Base32::decode($q['2fa_secret'])) === $d->v) {
					$us = new User_Session;
					$s = $us->create($td->user);
					$app->log('user', (object) [
						'user' => $td->u,
						'type' => 'login',
						'data' => [
							'ip' => $_SERVER['REMOTE_ADDR'],
							'user-agent' => $_SERVER['HTTP_USER_AGENT'],
						],
					]);
					return array("t" => 1, "v" => $s);
				} else {
					return array("t" => 3, "v" => $d->t);
				}
			} else {
				new Application_Exception(400);
			}
		} else {
			new Application_Exception(404);
		}
	}

	function register() {
		global $app, $config;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$d = json_decode(file_get_contents('php://input'));
			if ($d === null || !isset($d->token, $d->passwd, $d->rpasswd, $d->usrname)) {
				new Application_Exception(400);
			}
			$em = "AES-256-CBC";
			$key = hash('sha256', $config->server->secret);
			$iv = substr(hash('sha256', $config->server->secret), 0, 16);
			$td = json_decode(openssl_decrypt(base64_decode($d->token . '=='), $em, $key, 0, $iv));

			if ($td === null || !isset($td->id, $td->type, $td->expires)) {
				new Application_Exception(400);
			}

			$acc = new User_Account;
			$ti = $acc->adb->getdatatoken($td->id);
			if (isset($ti->scalar) || $ti->expires !== $td->expires || $ti->type !== "register") {
				new Application_Exception(401);
			}

			if (time() >= $td->expires) {
				$acc->adb->deletedatatoken($td->id);
				new Application_Exception(400);
			}

			$d->email = $td->data->eml;
			$d->perms = json_encode(['userlevel_user']);
			if (
				$d->passwd !== $d->rpasswd //||
				// !preg_match(Util_Regex::$password, $d->passwd) ||
				// !preg_match(Util_Regex::$password, $d->rpasswd) ||
				// !preg_match(Util_Regex::$name, $d->usrname) //||
				// !preg_match(Util_Regex::$name, $d->lname) ||
				// !preg_match(Util_Regex::$dob, $d->dob)
			) {
				new Application_Exception(400);
			}

			$acc->adb->deletedatatoken($td->id);
			$r = $acc->register($d);
			if ($r->r === "success") {
				return (object) ['s' => 4];
			} elseif ($r->r === "email_used") {
				return (object) ['s' => 5];
			} else {
				return (object) ['s' => 50];
			}
		} else {
			new Application_Exception(404);
		}
	}

	function reset() {
		global $app, $config;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$d = json_decode(file_get_contents('php://input'));
			if ($d === null || !isset($d->token, $d->passwd, $d->rpasswd)) {
				new Application_Exception(400);
			}
			$em = "AES-256-CBC";
			$key = hash('sha256', $config->server->secret);
			$iv = substr(hash('sha256', $config->server->secret), 0, 16);
			$td = json_decode(openssl_decrypt(base64_decode($d->token . '=='), $em, $key, 0, $iv));
			if ($td === null || !isset($td->id, $td->type, $td->expires)) {
				new Application_Exception(400);
			}
			$adb = new DB_Account;
			$ti = $adb->getdatatoken($td->id);
			if ($ti === null || $ti->expires !== $td->expires || $ti->type !== "reset") {
				new Application_Exception(401);
			}
			if (time() >= $td->expires) {
				$adb->deletedatatoken($td->id);
				new Application_Exception(400);
			}
			$adb->deletedatatoken($td->id);
			if (
				$d->passwd !== $d->rpasswd ||
				!preg_match(Util_Regex::$password, $d->passwd) ||
				!preg_match(Util_Regex::$password, $d->rpasswd)
			) {
				new Application_Exception(400);
			}

			$u = $adb->getuseridbyemail($td->data->email);
			if (isset($u->id) && $adb->updatepassword($u->id, Authentication_Password::generate($d->passwd))) {
				return (object) ['s' => 7];
			} else {
				return (object) ['s' => 8];
			}
		} else {
			new Application_Exception(404);
		}
	}

	function change() {
		global $app;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$d = json_decode(file_get_contents('php://input'));
			if ($d === null || !isset($d->current, $d->passwd, $d->rpasswd)) {
				new Application_Exception(400);
			}
			if (
				$d->current === $d->passwd ||
				$d->passwd !== $d->rpasswd ||
				!preg_match(Util_Regex::$password, $d->passwd) ||
				!preg_match(Util_Regex::$password, $d->rpasswd) ||
				!preg_match(Util_Regex::$password, $d->current)
			) {
				new Application_Exception(400);
			}
			$auth = new Authentication_Token;
			if ($auth->isuser()) {
				global $user;
				$adb = new DB_Account;
				if (Authentication_Password::check($d->current, $adb->getauthdata($user->id)->passwd)) {
					if ($adb->updatepassword($user->id, Authentication_Password::generate($d->passwd))) {
						return (object) ['s' => 7];
					} else {
						return (object) ['s' => 8];
					}
				} else {
					new Application_Exception(401);
				}
			} else {
				new Application_Exception(401);
			}
		} else {
			new Application_Exception(404);
		}
	}

	function request() {
		global $app, $config;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$d = json_decode(file_get_contents('php://input'));
			if ($d === null || !isset($d->email) || !preg_match(Util_Regex::$email, $d->email)) {
				new Application_Exception(400);
			}
			$dba = new DB_Account;
			if ($dba->isemailused($d->email)) {
				return ['s' => 13];
			}
			$id = $app->createrandomstring(32);
			$e = time() + 86400;
			$dba->registerdatatoken($id, 'register', -12, $e);
			$em = "AES-256-CBC";
			$key = hash('sha256', $config->server->secret);
			$iv = substr(hash('sha256', $config->server->secret), 0, 16);
			$a = array('id' => $id, 'type' => 'register', 'expires' => $e, 'data' => ['eml' => $d->email]);
			$v = str_replace('=', '', base64_encode(openssl_encrypt(json_encode($a), $em, $key, 0, $iv)));

			$mail = file_get_contents(getcwd() . '/Library/Util/mail/account_register.html');
			$mail = preg_replace('/{{registrationurl}}/', $config->auth->accounturl . '/register?token=' . $v, $mail);
			$mail = preg_replace('/{{email}}/', $d->email, $mail);
			$so = [
				'from' => 'AstroX XCard Platform <noreply@' . $config->temp->mailgun->domain . '>',
				'to' => $d->email,
				'subject' => 'Account registration',
				'html' => $mail,
			];
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_HTTPHEADER => array(
					'Content-Type: multipart/form-data'
				),
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => $so,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERPWD => 'api:' . $config->temp->mailgun->token,
				CURLOPT_URL => 'https://api.mailgun.net/v3/' . $config->temp->mailgun->domain . '/messages'
			));

			$r = curl_exec($ch);
			curl_close($ch);
			var_dump($r);
			return array("s" => 12);
		} else {
			new Application_Exception(404);
		}
	}

	function forgot() {
		global $app, $config;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$d = json_decode(file_get_contents('php://input'));
			if ($d === null || !isset($d->email) || !preg_match(Util_Regex::$email, $d->email)) {
				new Application_Exception(400);
			}
			$dba = new DB_Account;
			if ($dba->isemailused($d->email)) {
				$id = $app->createrandomstring(32);
				$e = time() + 900;
				$dba->registerdatatoken($id, 'reset', -6, $e);
				$em = "AES-256-CBC";
				$key = hash('sha256', $config->server->secret);
				$iv = substr(hash('sha256', $config->server->secret), 0, 16);
				$a = array('id' => $id, 'type' => 'reset', 'expires' => $e, 'data' => ['email' => $d->email]);
				$v = str_replace('=', '', base64_encode(openssl_encrypt(json_encode($a), $em, $key, 0, $iv)));
				$mail = file_get_contents(getcwd() . '/Library/Util/mail/account_forgot.html');
				$mail = preg_replace('/{{passwordreseturl}}/', $config->auth->accounturl . '/passwordreset?' . $v, $mail);
				$so = [
					"Content-type" => "text/html",
					'From' => 'Kyano <noreply@Kyano.nl>',
				];
				mail($d->email, 'Wachtwoord reset', $mail, $so);
			}
			return array("s" => 6,"url"=> $config->auth->accounturl . '/passwordreset?' . $v);
		} else {
			new Application_Exception(404);
		}
	}
}
