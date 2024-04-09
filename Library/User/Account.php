<?php
class User_Account {
	public $adb;

	function __construct() {
		$this->adb = new DB_Account;
	}

	function login($u, $p,$type = 'u') {
		switch ($type) {
			case 'u':
				$ad = $this->adb->getuseridbyusername(strtolower($u));
				break;
			case 'e':
				$ad = $this->adb->getuseridbyemail(strtolower($u));
				break;
		}
		if (!isset($ad->id)) {
			return (object) array('r' => 'invalid');
		}
		
		$sp = $this->adb->getauthdata($ad->id);
		$checkpasswd = Authentication_Password::check($p, $sp->passwd);
		// if ($checkpasswd && $sp->{'2fa_enabled'} == 1) {
		// 	return (object) array('r' => '2fa_required', 'u' => $ad->id);
		//}
		if ($checkpasswd) {
			return (object) array('r' => 'success', 'u' => $ad->id);
		} else {
			return (object) array('r' => 'invalid');
		}
	}

	function register($d) {
		if ($this->adb->isemailused($d->email)) {
			return (object) array('r' => 'email_used');
		}
		$dbr = $this->adb->register($d);
		if ($dbr) {
			return (object) array('r' => 'success');
		} elseif (!$dbr) {
			return (object) array('r' => 'invalid');
		}
	}
}
