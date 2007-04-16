<?PHP
	class Auth
	{
		var $user_id;
		var $username;
		var $password;
		var $level;
		var $salt;
		var $domain = ""; // Domain to set in cookie
		var $user;

		function __construct()
		{
			$this->user_id = 0;
			$this->username = "Guest";
			$this->salt = $GLOBALS['auth_salt'];

			if(class_exists("DBObject") && class_exists("User"))
				$this->user = new User();

			if(!$this->checkSession()) $this->checkCookie();
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
			$username = mysql_real_escape_string($username, $db->db);
			$db->query("SELECT * FROM users WHERE username = '$username'");
			if(mysql_num_rows($db->result) == 1)
			{
				$row = mysql_fetch_array($db->result, MYSQL_ASSOC);
				$db_password = $row['password'];
				if($db_password == $password)
				{
					$this->user_id  = $row['user_id'];
					$this->username = $username;
					$this->level    = $row['level'];

					// Load any additional user info if DBObject and User are available
					if(class_exists("DBObject") && class_exists("User"))
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
			$username = mysql_real_escape_string($username, $db->db);
			$password = mysql_real_escape_string($this->makePassword($password), $db->db);
			$db->query("SELECT * FROM users WHERE username = '$username' AND password = '$password'");
			if(mysql_num_rows($db->result) == 1)
			{
				$row = mysql_fetch_array($db->result, MYSQL_ASSOC);
				$this->user_id = $row['user_id'];
				$this->username = $username;
				$this->level = $row['level'];

				// Load any additional user info if DBObject and User are available
				if(class_exists("DBObject") && class_exists("User"))
				{
					$this->user->id = $this->user_id;
					$this->user->load($row);
				}

				$_SESSION['auth_username'] = $username;
				$_SESSION['auth_password'] = $password;
				setcookie("auth_username", $username, time()+60*60*24*30, "/", $this->domain);
				setcookie("auth_password", $password, time()+60*60*24*30, "/", $this->domain);
				
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

		function makePassword($pw)
		{
			return md5($this->salt . $pw);
		}
	}
?>