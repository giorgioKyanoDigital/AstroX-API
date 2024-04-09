<?php
class Route_v1_Projects {
	public $offset = true;

	function index($id, $a) {
		global $app, $user, $config;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$auth = new Authentication_Token;
			if (!$auth->isuser()) {
				new Application_Exception(401);
			}
		
		} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
			$auth = new Authentication_Token;
			if ($auth->isuser()) {
				$pdb = new DB_Template;

				$b = (object) [];
				
				return ['success' => true];
			} else {
				new Application_Exception(401);
			}
		} 
		elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
			$auth = new Authentication_Token;
			if ($auth->isuser()) {
				$pdb = new DB_Template;

				$b = (object) [];
				
				return ['success' => true];
			} else {
				new Application_Exception(401);
			}
		}
		elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
			$auth = new Authentication_Token;
			if ($auth->isuser()) {
				$pdb = new DB_Template;
				return ['success' => $pdb->delete($id)];
			} else {
				new Application_Exception(401);
			}
		} else {
			new Application_Exception(404);
		}
	}
function temp($id){
	global $app, $user, $config;
		$app->cors('private');
	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		$auth = new Authentication_Token;
		if (!$auth->isuser()) {
			new Application_Exception(401);
		}
	
	} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
		$auth = new Authentication_Token;
		if ($auth->isuser()) {
			$pdb = new DB_Template;

			$b = (object) [];
			
			return ['success' => true];
		} else {
			new Application_Exception(401);
		}
	} 
	elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
		$auth = new Authentication_Token;
		if ($auth->isuser()) {
			$pdb = new DB_Template;

			$b = (object) [];
			
			return ['success' => true];
		} else {
			new Application_Exception(401);
		}
	}
	elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
		$auth = new Authentication_Token;
		if ($auth->isuser()) {
			$pdb = new DB_Template;
			return ['success' => $pdb->delete($id)];
		} else {
			new Application_Exception(401);
		}
	} else {
		new Application_Exception(404);
	}
}
	public function getall($id) {
		global $app;
		$app->cors('private');
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$auth = new Authentication_Token;
			if ($auth->isuser()) {
				if (is_numeric($id)) {
					$db = new DB_Template;
					return $db->getall($id);
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
	private function _getfileid($pdb) {
		global $app;
		$id = $app->createrandomstring(32);
		if ($pdb->fileexists($id)) {
			$this->_getfileid($pdb);
		} else {
			return $id;
		}
	}

	private function _getassemblyfileid($pdb) {
		// NOTE: Also in order.php
		global $app;
		$id = $app->createrandomstring(32);
		if ($pdb->assemblyfileexists($id)) {
			$this->_getassemblyfileid($pdb);
		} else {
			return $id;
		}
	}
}
