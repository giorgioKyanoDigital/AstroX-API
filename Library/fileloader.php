<?php

function fileloader() {
	require_once './Library/DB/Main.php';
	require_once './Library/Application/Exception.php';
	require_once './Library/Application/Router.php';
	require_once './Library/Application/Util.php';
	require_once './Library/Authentication/Password.php';
	require_once './Library/Authentication/PasswordHash.php';
	require_once './Library/Authentication/Hotp.php';
	require_once './Library/Authentication/Totp.php';
	require_once './Library/Authentication/Token.php';
	require_once './Library/DB/Account.php';	
	require_once './Library/DB/Template.php';
	require_once './Library/DB/Deckbuilder.php';
	require_once './Library/DB/Inventory.php';
	require_once './Library/DB/Session.php';
	require_once './Library/DB/Matches.php';
	require_once './Library/DB/User.php';
	require_once './Library/DB/Gameserver.php';
	require_once './Library/DB/Shop.php';
	require_once './Library/DB/Seasonpass.php';
	require_once './Library/DB/Tournament.php';
	require_once './Library/User/Account.php';
	require_once './Library/User/Session.php';
	require_once './Library/Util/Base32.php';
	require_once './Library/Util/Regex.php';
	$loaddir = function ($dir, $ldir) {
		if (is_dir($dir)) {
			$scan = scandir($dir);
			unset($scan[0], $scan[1]);
			foreach ($scan as $file) {
				if (is_dir("$dir/$file")) {
					$ldir("$dir/$file", $ldir);
				} else {
					if (strpos($file, '.php') !== false) {
						require_once "$dir/$file";
					}
				}
			}
		}
	};
	$loaddir('./Library/Route', $loaddir);
}
