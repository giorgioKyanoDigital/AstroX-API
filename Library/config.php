<?php

use function PHPSTORM_META\map;

$config = (object) [
	'urloffset' => 1,
	'sql' => (object) [
		'host' => 'localhost:3306', //:3307',
		'username' => 'root',
		'password' => '',
		'database' => 'astrox_db', //'astrox_api',
	],
	'server' => (object) [
		'secret' => '26B9D434gGDfAYSHZmcS1esmRZFhdX2kFGi68rsjcAySADpfYYrYUlC0HoN29foF',
		'gameserversecret' => 'cDN5TSX6vv2NKtyHQAnSk18G77v1vy7o',
		'domain' => 'localhost',
		'datapath' => '../atxdata'
	],
	'auth' => (object) [
		'accounturl' => 'http://localhost/',
		'allowed_domains' => [
			'localhost',
			'swcenter.nl',
			'astrox.swcenter.nl',
			'api.astrox.swcenter.nl',
		]
	],
	'temp' => (object) [
		'mailgun' => (object) [
			'token' => '17e57963c0321909726493c19d20dcad-102c75d8-29b66152',
			'domain' => 'sandboxb15e4993c59342d9a828c461f19b11a8.mailgun.org'
		]
	],

	'game' => (object) [
		'season' => '1',
	],

];
