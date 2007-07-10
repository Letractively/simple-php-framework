<?PHP
	class Auth
	{
		var $user_id;
		var $username;
		var $password;
		var $level;          // Admin, User, etc.
		var $salt;           // Used to compute password hash
		var $domain = "";    // Domain to set in cookie
		var $user;           // DBObject User class if available
		var $useMD5 = false; // Are passwords hashed in the database?

		function __construct()
		{
			$this->user_id  = 0;
			$this->username = "Guest";
			$this->salt     = $GLOBALS['auth_salt']; // So you can set it in master.inc.php

			if(class_exists("User") && (get_parent_class("User") == "DBObject"))
				$this->user = new User();

			if(!$this->checkSession())
				$this->checkCookie();

			return $this->ok();
		}

		function checkSession()
		{
			if(!empty($_SESSION['auth_username']) && !empty($_SESSION['auth_password']))
				return $this->check($_SESSION['auth_username'], $_SESSION['auth_password']);
		}

		function checkCookie()
		{
			if(!empty($_COOKIE['auth_username']) && !empty($_COOKIE['auth_password']))
				return $this->check($_COOKIE['auth_username'], $_COOKIE['auth_password']);
		}

		function check($username, $password)
		{
			global $db;

			$db->query("SELECT * FROM users WHERE username = " . $db->quote($username));
			if(mysql_num_rows($db->result) == 1)
			{
				$row = mysql_fetch_array($db->result, MYSQL_ASSOC);

				$db_password = $row['password'];

				if($this->useMD5 == false)
					$db_password = md5($db_password . $this->salt);

				if($db_password == $password)
				{
					$this->user_id  = $row['user_id'];
					$this->username = $username;
					$this->level    = $row['level'];

					// Load any additional user info if DBObject and User are available
					if(class_exists("User") && (get_parent_class("User") == "DBObject"))
					{
						$this->user->id = $this->user_id;
						$this->user->load($row);
					}

					return true;
				}
			}
		}

		function login($username, $password)
		{
			global $db;

			$db_password = $this->makePassword($password);
			$db->query("SELECT * FROM users WHERE username = " . $db->quote($username) . " AND password = " . $db->quote($db_password));

			if(mysql_num_rows($db->result) == 1)
			{
				$row = mysql_fetch_array($db->result, MYSQL_ASSOC);
				$this->user_id  = $row['user_id'];
				$this->username = $username;
				$this->level    = $row['level'];

				// Load any additional user info if DBObject and User are available
				if(class_exists("User") && (get_parent_class("User") == "DBObject"))
				{
					$this->user->id = $this->user_id;
					$this->user->load($row);
				}

				$hashed_password = md5($password . $this->salt);
				$_SESSION['auth_username'] = $username;
				$_SESSION['auth_password'] = $hashed_password;
				setcookie("auth_username", $username, time()+60*60*24*30, "/", $this->domain);
				setcookie("auth_password", $hashed_password, time()+60*60*24*30, "/", $this->domain);

				return true;
			}

			return false;
		}

		function logout()
		{
			$this->user_id = 0;
			$this->username = "Guest";

			$_SESSION['auth_username'] = "";
			$_SESSION['auth_password'] = "";

			setcookie("auth_username", "", time() - 3600, "/", $this->domain);
			setcookie("auth_password", "", time() - 3600, "/", $this->domain);
		}

		function ok()
		{
			return ($this->username !== "Guest");
		}

		// Helper function that redirects away from "admin only" pages
		function admin($url = "/login/")
		{
			if($this->level != "admin")
				redirect($url);
		}

		function makePassword($pw)
		{
			return $this->useMD5 ? md5($pw . $this->salt) : $pw;
		}
	}