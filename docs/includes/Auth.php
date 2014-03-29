<?php

/* Class for Authentication handling stuff */
class Auth {

	protected $userId = null;
	protected $isAdmin = null;
	protected $authInfo = null;
	protected $db = null;

	// Make sure the session is already started and save the userId if it exists
	function __construct() {
		global $db;
		$this->db = $db;

		if (session_id() == '') {
			session_start();
		}
		else if(!empty($_SESSION['user_id'])) {
			$this->userId = $_SESSION['user_id'];
		}
	}

	// Get authorization info
	function getAuthInfo() {
		$authInfo = null;

		// Make sure the constructor found a user id
		if (!is_null($this->userId)) {

			// If $this->authInfo doesn't yet exist perform the database query
			if (is_null($this->authInfo)) {
				$sql = "select * from administrator where id=:id";
				$statement = $this->db->prepare($sql);
				$statement->execute(array(':id'=>$this->userId));
				$authInfo = $statement->fetch(PDO::FETCH_ASSOC);
			}
		}
		$this->authInfo = $authInfo;
		return $authInfo;
	}

	// Figure out if this person is an admin
	function isAdmin() {
		$isAdmin = false;

		// Make sure we have the user's id
		if (!is_null($this->userId)) {

			// If $this->authInfo is null, call the getAuthInfo method
			if (is_null($this->authInfo)) {
				$this->getAuthInfo();
			}

			// See if this user is an admin
			if ($this->authInfo["user_type"] == "Super") {
				$isAdmin = true;
			}
		}

		return $isAdmin;
	}

	// Force the user to login
	function forceLogin() {
		$mustLogin = true;

		if ($this->isAdmin()) {
			$mustLogin = false;
		}

		if ($mustLogin) {
			header("Location: /admin-login.php?msg=3");
		}
	}

	// Try to login 
	function login($email, $password, $referer = null) {
		$sql = "select * from administrator where email=:email and password=:password";
		$statement = $this->db->prepare($sql);
		$statement->execute(array(':email'=>$email, ':password'=>$password));
		$userInfo = $statement->fetchAll(PDO::FETCH_ASSOC);

		if (count($userInfo) == 1) {
			$this->userId = $userInfo['0']['id'];

			$_SESSION['user_id'] = $userInfo['0']['id'];
			$_SESSION['admin_email'] = $userInfo['0']['email'];
			$_SESSION['admin_name'] = $userInfo['0']['name'];
			$_SESSION['user_type'] = $userInfo['0']['user_type'];
			$_SESSION['sesid'] = session_id();
			
			if (!is_null($referer)) {
				header("Location: $referer");
			}
			else {
				header("Location: /admin-main.php?msg=1");
			}
		}
	}
}

?>
