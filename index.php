<?php
$route = explode('/', explode('?', $_SERVER['REQUEST_URI'], 2)[0]);
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	header("HTTP/1.1 200 OK");
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Access-Control-Allow-Methods: *');
	die;
}

include_once './Library/config.php';
$route = array_slice($route, $config->urloffset + 1);
include_once './Library/fileloader.php';
fileloader();

set_exception_handler(function ($e) {
	global $app;
	if ($app === null) {
		$app = new Application_Util;
	}
	$app->log('server', (object) [
		'type' => get_class($e),
		'message' => $e->getMessage(),
		'file' => $e->getFile(),
		'line' => $e->getLine(),
		'stacktrace' => $e->getTrace(),
	]);
	new Application_Exception(500);
});

$db = new DB_Main;
$app = new Application_Util;

header('Content-Type: application/json');

new Application_Router;
