<?php
class Route_v1_Users {
	public $offset = true;
	public $product = false;

	public function index($id, $a) {
		global $app;
		$app->cors('private');
		$dba = new DB_Account;
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$auth = new Authentication_Token;
			if (is_numeric($id)) {
				global $user;
				if ($id == '0') {
					if ($auth->isuser()) {
						return $user;
					} else {
						new Application_Exception(401);
					}
				} else {
					if ($auth->isuser()) {
						if (intval($id) === $user->id || in_array('userlevel_admin', $user->perms)) {
							$r = $dba->getuser($id);
						} else {
							$r = $dba->getuserinfo($id);
						}
						if ($r === null) {
							new Application_Exception(404);
						} else {
							return $r;
						}
					}
				}
			} elseif ($id === 'list') {
				$auth = new Authentication_Token;
				if ($auth->isuser()) {
					global $user;
					if (isset($_GET['prjlist'])) {
						$r = [];
						foreach ($dba->getusernames() as $v) {
							$r[] = ['dis' => $v->firstname . ' ' . $v->lastname, 'val' => $v->id];
						}
						return $r;
					} elseif ($user->staff || in_array('userlevel_admin', $user->perms)) {
						if (isset($_GET['offset'])) {
							if (!is_numeric($_GET['offset'])) {
								new Application_Exception(401);
							}
							$r = $dba->getusers(intval($_GET['offset']));
							foreach ($r as &$v) {
								$v->staff = boolval($v->staff);
							}
							return $r;
						} else {
							$r = $dba->getusers();
							foreach ($r as &$v) {
								$v->staff = boolval($v->staff);
							}
							return $r;
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
		} elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
			global $user;
			$auth = new Authentication_Token;
			if ($auth->isuser()) {
				if (is_numeric($id)) {
					$b = json_decode(file_get_contents('php://input'));
					if ($b === null || !isset($b->firstname, $b->lastname, $b->phonenumber, $b->email, $b->dateofbirth)) {
						new Application_Exception(400);
					}
					if ($id == '0') {
						$d = (object)[];
						$d->firstname = $b->firstname;
						$d->lastname = $b->lastname;
						$d->phonenumber = $b->phonenumber;
						$d->email = $b->email;
						$d->title = $b->title;
						if (isset($b->title)) {
							$d->title = $b->title;
						} else {
							$d->title = "";
						}
						if (isset($b->bio)) {
							$d->bio = $b->bio;
						} else {
							$d->bio = "";
						}
						if(isset($b->signature)){
							$d->signature = $b->signature;
						}else{
							$d->signature = "";
						}
						
						
						$d->dateofbirth = $b->dateofbirth;
						return ['success' => $dba->updateuserdata($user->id, $d)];
					} else {
						if (in_array('userlevel_admin', $user->perms)) {
							$d = (object)[];
							$d->firstname = $b->firstname;
							$d->lastname = $b->lastname;
							$d->phonenumber = $b->phonenumber;
							$d->email = $b->email;
							if (isset($b->title)) {
								$d->title = $b->title;
							} else {
								$d->title = "";
							}
							if (isset($b->bio)) {
								$d->bio = $b->bio;
							} else {
								$d->bio = "";
							}
							if (isset($b->signature)) {
								$d->signature = $b->signature;
							} else {
								$d->signature = "";
							}
							$d->dateofbirth = $b->dateofbirth;
							return ['success' => $dba->updateuserdata($id, $d)];
						} else {
							new Application_Exception(401);
						}
					}
				} else {
					new Application_Exception(400);
				}
			} else {
				new Application_Exception(401);
			}
		} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
			global $user, $app;
			$auth = new Authentication_Token;
			if ($auth->isuser()) {
				if (is_numeric($id)) {
					$b = json_decode(file_get_contents('php://input'));
					if ($id == '0') {
						$adb = new DB_Account;
						$tdb = new DB_Tasks;
						$adb->active($user->id, false);
						$tdb->add('deleteuser', $user->id, time() + 5184000);
						$app->log('user', (object) [
							'user' => $user->id,
							'type' => 'userdeleted',
						]);
					} else {
						if (in_array('userlevel_admin', $user->perms) || $user->staff) {
							$adb = new DB_Account;
							$tdb = new DB_Tasks;
							$adb->active($id, false);
							$tdb->add('deleteuser', $id, time() + 5184000);
							$app->log('user', (object) [
								'user' => $id,
								'type' => 'userdeleted',
								'data' => [
									'by' => $user->id
								],
							]);
						} else {
							new Application_Exception(401);
						}
					}
				} else {
					new Application_Exception(400);
				}
			} else {
				new Application_Exception(401);
			}
		} else {
			new Application_Exception(404);
		}
	}

	public function permissons($id, $a) {
		global $app;
		$app->cors('private');
		$auth = new Authentication_Token;
		$dba = new DB_Account;
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			global $user;
			if ($auth->isuser()) {
				if (is_numeric($id)) {
					if (in_array('userlevel_admin', $user->perms)) {
						return json_decode($dba->getuserperms($id));
					} else {
						new Application_Exception(401);
					}
				} else {
					new Application_Exception(400);
				}
			} else {
				new Application_Exception(401);
			}
		}
		if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
			global $user;
			if ($auth->isuser()) {
				if (is_numeric($id)) {
					if (in_array('userlevel_admin', $user->perms)) {
						$b = file_get_contents('php://input');
						$d[] = $b;
						if (is_array($d)) {
							return ['success' => $dba->updateuserperms(json_encode($b), $id)];
						} else {
							new Application_Exception(400);
						}
					} else {
						new Application_Exception(401);
					}
				} else {
					new Application_Exception(400);
				}
			} else {
				new Application_Exception(401);
			}
		}
	}

	public function notifications($id, $a) {
		global $app, $user;
		$app->cors('private');
		$auth = new Authentication_Token;
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($auth->isuser() && $id === '0') {
				$ndb = new DB_Notification;
				$dba = new DB_Account;
				if (isset($a[0]) && is_numeric($a[0])) {
					$r = $ndb->get($a[0]);
					if ($r->user === $user->id) {
						$r->read = boolval($r->read);
						$r->data = json_decode($r->data);
						if (isset($r->data->by)) {
							$r->data->by = $dba->getusername($r->data->by);
						}
					} else {
						new Application_Exception(401);
					}
				} else {
					if (isset($_GET['all'])) {
						$r = $ndb->getallnotificationsforuser($user->id);
					} else {
						$r = $ndb->getnotificationsforuser($user->id);
					}
					foreach ($r as &$v) {
						$v->read = boolval($v->read);
						$v->data = json_decode($v->data);
						if (isset($v->data->by)) {
							$v->data->by = $dba->getusername($v->data->by);
						}
					}
				}
				return $r;
			} else {
				new Application_Exception(401);
			}
		} else {
			new Application_Exception(404);
		}
	}

	public function info($id, $a) {
		global $app;
		$app->cors('private');
		$auth = new Authentication_Token;
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($auth->isuser() && $id === '0') {
				global $user;
				return (object) [
					'id' => $user->id,
					'firstname' => $user->firstname,
					'lastname' => $user->lastname,
					'email' => $user->email,
					'staff' => boolval($user->staff),
					'profilepicture' => $user->profilepicture
				];
			} else {
				new Application_Exception(401);
			}
		} else {
			new Application_Exception(404);
		}
	}

	public function register($id, $a) {
		global $app, $user;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$auth = new Authentication_Token;
			if ($auth->isuser()) {
				if (is_numeric($id)) {
					if ($user->staff || in_array('userlevel_admin', $user->perms)) {
						$d = json_decode(file_get_contents('php://input'));
						if ($d === null || !isset($d->email, $d->passwd, $d->rpasswd, $d->fname, $d->lname, $d->phonenumber, $d->dob, $d->tl, $d->ul)) {
							new Application_Exception(400);
						}

						$acc = new User_Account;

						$d->email = $d->email;
						$d->teamleader = $d->tl;
						$d->perms = json_encode(['userlevel_' . $d->ul]);
						if (
							!preg_match(Util_Regex::$email, $d->email) ||
							$d->passwd !== $d->rpasswd ||
							!preg_match(Util_Regex::$password, $d->passwd) ||
							!preg_match(Util_Regex::$password, $d->passwd) ||
							!preg_match(Util_Regex::$password, $d->rpasswd) ||
							!preg_match(Util_Regex::$name, $d->fname) ||
							!preg_match(Util_Regex::$name, $d->lname) ||
							!preg_match(Util_Regex::$dob, $d->dob)
						) {
							new Application_Exception(400);
						}

						$r = $acc->register($d);
						if ($r->r === "success") {
							return (object) ['s' => 4];
						} elseif ($r->r === "email_used") {
							return (object) ['s' => 5];
						} else {
							return (object) ['s' => 50];
						}
					} else {
						new Application_Exception(401);
					}
				} else {
					new Application_Exception(400);
				}
			} else {
				new Application_Exception(401);
			}
		} else {
			new Application_Exception(404);
		}
	}

	public function avatar($id, $a) {
		global $app, $user, $config;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($id !== '0') {
				new Application_Exception(404);
			}
			$auth = new Authentication_Token;
			if (!$auth->isuser()) {
				new Application_Exception(401);
			}

			if (
				!isset($_FILES['file_image']['error']) ||
				is_array($_FILES['file_image']['error'])
			) {
				new Application_Exception(400, "image_error");
			}

			switch ($_FILES['file_image']['error']) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					new Application_Exception(400, "file_error");
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					new Application_Exception(400, "size_error");
				default:
					new Application_Exception(400, "unknown_error");
			}

			if ($_FILES['file_image']['size'] > 5242880) {
				new Application_Exception(400, "image too big");
			}

			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mt = $finfo->file($_FILES['file_image']['tmp_name']);
			if (false === $ext = array_search(
				$mt,
				array(
					'jpg' => 'image/jpeg',
					'png' => 'image/png',
					'webp' => 'image/webp',
				),
				true
			)) {
				new Application_Exception(400, "unknown_format");
			}

			$file = tempnam(sys_get_temp_dir(), '');

			if (!move_uploaded_file($_FILES['file_image']['tmp_name'], $file)) {
				unlink($file);
				new Application_Exception(400, "unknown error");
			}

			list($width, $height) = getimagesize($file);
			$r = $width / $height;
			if ($width > $height) {
				$width = ceil($width - ($width * abs($r - 512 / 512)));
			} else {
				$height = ceil($height - ($height * abs($r - 512 / 512)));
			}
			$newwidth = 512;
			$newheight = 512;
			switch ($mt) {
				case 'image/png':
					$src = imagecreatefrompng($file);
					break;
				case 'image/webp':
					$src = imagecreatefromwebp($file);
					break;
				default:
					$src = imagecreatefromjpeg($file);
					break;
			}
			$dst = imagecreatetruecolor($newwidth, $newheight);
			imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			$dba = new DB_Account;
			$id = $app->createrandomstring(32);
			if (!$dba->isavataridused($id)) {
				if (imagepng($dst, $config->server->cdnpath . '/usercontent/avatars/' . $id . '.png', 9)) {
					unlink($file);
					if ($user->profilepicture !== null) {
						unlink($config->server->cdnpath . '/usercontent/avatars/' . $user->profilepicture . '.png');
					}
					$dba->updateavatar($user->id, $id);
					return (object) ['success' => true, 'r' => $id];
				} else {
					return (object) ['success' => false];
				}
			} else {
				return (object) ['success' => false];
			}
		} else {
			new Application_Exception(404);
		}
	}
}
