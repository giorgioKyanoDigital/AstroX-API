<?php
class Authentication_Password {
	public static function generate($password) {
		$hasher = new Authentication_PasswordHash(8, false);
		$hash = $hasher->HashPassword($password);
		return $hash;
	}
	public static function check($password, $stored_hash) {
		$hasher = new Authentication_PasswordHash(8, false);
		// Check that the password is correct, returns a boolean
		$check = $hasher->CheckPassword($password, $stored_hash);
		if ($check) {
			return true;
		} else {
			return false;
		}
	}
}
