<?php
class Application_Exception {
	function __construct($errcode, $m = null) {
		switch ($errcode) {
			case 400:
				$r = array('status' => 400, 'message' => 'Bad Request');
				header("HTTP/1.1 400 Bad Request");
				break;

			case 401:
				$r = array('status' => 401, 'message' => 'Unauthorized');
				header("HTTP/1.1 401 Unauthorized");
				break;
			case 402:
				$r = array('status' => 402, 'message' => 'Payment Required');
				header("HTTP/1.1 402 Payment Required");
				break;
			case 403:
				$r = array('status' => 403, 'message' => 'Forbidden');
				header("HTTP/1.1 403 Forbidden");
				break;
			case 404:
				$r = array('status' => 404, 'message' => 'Not Found');
				header("HTTP/1.1 404 Not Found");
				break;
			case 409:
				$r = array('status' => 409, 'message' => 'Conflict');
				header("HTTP/1.1 409 Conflict");
				break;
			case 413:
				$r = array('status' => 413, 'message' => 'Payload Too Large ');
				header("HTTP/1.1 413 Payload Too Large ");
				break;
			case 460:
				$r = array('status' => 460);
				header("HTTP/1.1 460 See Response Body");
				break;
			case 500:
				$r = array('status' => 500, 'message' => 'Internal Server Error');
				header("HTTP/1.1 500 Internal Server Error");
				break;
			case 510:
				$r = array('status' => 510, 'message' => 'Not Extended');
				header("HTTP/1.1 510 Not Extended");
				break;
			case 521:
				$r = array('status' => 521, 'message' => 'File API failure');
				header("HTTP/1.1 521 File API failure");
				break;
			default:
				$r = array('status' => 520, 'message' => 'Not sure what happened');
				header("HTTP/1.1 520 Not sure what happened");
				break;
		}
		if ($m) {
			$r['message'] = $m;
		}
		echo json_encode($r);
		die;
	}
}
