<?php
class Application_Router {
	function __construct() {
		global $route;
		$cn = 'Route_' . $route[0] . '_' . ucfirst($route[1]);
		if (class_exists($cn)) {
			$c = new $cn();
			if (isset($c->product) && $c->product) {
				if (!isset($route[2])) {
					new Application_Exception(404);
				}
				$pcn = 'Route_' . $route[0] . '_' . ucfirst($route[1]) . '_' . ucfirst($route[2]);
				$pc = new $pcn();
				if ($pc->offset) {
					if (!isset($route[3])) {
						new Application_Exception(404);
					}
					if (!isset($route[4]) || $route[4] === "") {
						$f = 'index';
					} else {
						$f = $route[4];
					}
					$args = array_slice($route, 5);
					if (is_callable(array($pc, $f))) {
						header('Content-Type: application/json');
						echo json_encode($pc->$f($route[3], $args));
					} else {
						new Application_Exception(404);
					}
				} else {
					if (!isset($route[3]) || $route[3] === "") {
						$f = 'index';
					} else {
						$f = $route[3];
					}
					$args = array_slice($route, 4);
					if (is_callable(array($pc, $f))) {
						header('Content-Type: application/json');
						echo json_encode($pc->$f($args));
					} else {
						new Application_Exception(404);
					}
				}
			} else {
				if ($c->offset) {
					if (!isset($route[3]) || $route[3] === "") {
						$f = 'index';
					} else {
						$f = $route[3];
					}
					
					$args = array_slice($route, 4);
					if (is_callable(array($c, $f))) {
						header('Content-Type: application/json');
						if (isset($route[2])) {
							echo json_encode($c->$f($route[2], $args));
						} else {
							echo json_encode($c->$f($route[1], $args));
						}
					} else {
						new Application_Exception(404);
					}
				} else {
					if (!isset($route[2]) || $route[2] === "") {
						$f = 'index';
					} else {
						$f = $route[2];
					}
					$args = array_slice($route, 3);
					if (is_callable(array($c, $f))) {
						header('Content-Type: application/json');
						echo json_encode($c->$f($args));
					} else {
						new Application_Exception(404);
					}
				}
			}
		} else {
			new Application_Exception(404);
		}
	}
}