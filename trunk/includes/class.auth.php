<?PHP
	class Auth
	{
		public $user_id;
		public $username;
		public $password;
		public $level;           // Admin, User, etc.
		public $salt;            // Used to compute password hash
		public $domain = "";     // Domain to set in cookie
		public $user;            // DBObject User class if available
		public $useHash = false; // Are passwords hashed in the database?
		
		private $loggedIn = false;

		// Call with no arguments to create a guest user (which can then be logged in using $this->login($un, $pw)
		// Or pass a user_id to simply login that user. The $seriously is just a safeguard to be certain you really do
		// want to blindly login a user. Set it to true.
		public function __construct($user_id = null, $seriously = false)
		{
			global $db;

			$this->user_id  = 0;
			$this->username = "Guest";
			$this->salt     = AUTH_SALT; // Defined in master.inc.php

			if(class_exists("User") && (get_parent_class("User") == "DBObject"))
				$this->user = new User();

			// Allow login via user_id passed into constructor
			if(!is_null($user_id) && ($seriously === true))
			{
				$db->query("SELECT * FROM users WHERE user_id = " . $db->quote($user_id));
				if(mysql_num_rows($db->result) == 1)
				{
					$row = mysql_fetch_array($db->result, MYSQL_ASSOC);
					$this->doLogin($row);
					$this->storeSessionData($row['username'], $row['password']);
				}
			} // But normally we login via a session or cookie variable
			elseif($this->checkSession())
				return true;
			elseif($this->checkCookie())
				return true;
			else
				return false;
		}

		// Verify a login from PHP's session store
		private function checkSession()
		{
			if(!empty($_SESSION['auth_username']) && !empty($_SESSION['auth_password']))
				return $this->check($_SESSION['auth_username'], $_SESSION['auth_password']);
		}

		// Verify a login from a cookie
		private function checkCookie()
		{
			if(!empty($_COOKIE['auth_username']) && !empty($_COOKIE['auth_password']))
				return $this->check($_COOKIE['auth_username'], $_COOKIE['auth_password']);
		}

		// Verify a username and password from a previously authenticated session.
		// Basically, it accepts the hashed password rather than the plain text that a user would submit during
		// an active login process.
		private function check($username, $password)
		{
			global $db;

			$db->query("SELECT * FROM users WHERE username = " . $db->quote($username));
			if(mysql_num_rows($db->result) == 1)
			{
				$row = mysql_fetch_array($db->result, MYSQL_ASSOC);

				$db_password = $row['password'];

				// This looks backwards, but it really is correct!
				if($this->useHash == false)
					$db_password = sha1($db_password . $this->salt);

				// If password is ok
				if($db_password == $password)
				{
					$this->doLogin($row);
					return true;
				}
			}

			$this->loggedIn = false;
			return false;
		}

		// Actively login a user
		public function login($username, $password)
		{
			global $db;

			$db_password = $this->makePassword($password);
			$db->query("SELECT * FROM users WHERE username = " . $db->quote($username) . " AND password = " . $db->quote($db_password));
			if(mysql_num_rows($db->result) == 1)
			{
				$row = mysql_fetch_array($db->result, MYSQL_ASSOC);
				$this->doLogin($row);
				$this->storeSessionData($row['username'], $row['password']);
				return true;
			}
			else
			{
				$this->loggedIn = false;
				return false;
			}
		}

		// Once the login is authenticated, setup the $auth object.
		private function doLogin($row)
		{
			// Load the most basic user info
			$this->user_id  = $row['user_id'];
			$this->username = $row['username'];
			$this->level    = $row['level'];

			// Load any additional user info if DBObject and User are available
			if(class_exists("User") && (get_parent_class("User") == "DBObject"))
			{
				$this->user = new User();
				$this->user->id = $this->user_id;
				$this->user->load($row);
			}

			$this->loggedIn = true;
		}
		
		public function impersonate($user)
		{
			global $db;

			if(ctype_digit($user))
				$result = $db->query("SELECT * FROM users WHERE user_id = " . $db->quote($user));
			else
				$result = $db->query("SELECT * FROM users WHERE username = " . $db->quote($user));

			if(mysql_num_rows($result) == 1)
			{
				$row = mysql_fetch_array($result, MYSQL_ASSOC);
				$this->doLogin($row);
				$this->storeSessionData($row['username'], $row['password']);
				return true;
			}
			else
			{
				$this->loggedIn = false;
				return false;
			}
		}

		// Save login in a session and cookie
		private function storeSessionData($username, $password)
		{
			$_SESSION['auth_username'] = $username;
			$_SESSION['auth_password'] = $this->useHash ? $password : sha1($password . $this->salt);
			setcookie("auth_username", $_SESSION['auth_username'], time()+60*60*24*30, "/", $this->domain);
			setcookie("auth_password", $_SESSION['auth_password'], time()+60*60*24*30, "/", $this->domain);
		}

		// Logout the user
		public function logout()
		{
			$this->user_id = 0;
			$this->username = "Guest";
			$this->user = new User();

			$_SESSION['auth_username'] = "";
			$_SESSION['auth_password'] = "";
			setcookie("auth_username", "", time() - 3600, "/", $this->domain);
			setcookie("auth_password", "", time() - 3600, "/", $this->domain);

			$this->loggedIn = false;
		}

		// Is the user (of any level) logged in?
		public function ok()
		{
			return $this->loggedIn;
		}

		// Helper function that redirects away from "admin only" pages
		public function admin($url = null)
		{
			if(is_null($url)) $url = WEB_ROOT . "login/";
			if($this->level != "admin")
				redirect($url);
		}

		// Helper function that redirects away from "member only" pages
		public function user($url = null)
		{
			if(is_null($url)) $url = WEB_ROOT . "login/";
			if($this->ok() === false)
				redirect($url);
		}

		// Returns the hashed version of a password if $this->md5 is turned on
		public function makePassword($pw)
		{
			return $this->useHash ? sha1($pw . $this->salt) : $pw;
		}
	}