<?PHP
	class Auth
	{
		var $user_id;
		var $username;
		var $password;
		var $level;
		var $salt = "^&DS(*F9876SDF&*(#(W*FSD_bs8d7f";
		var $domain = "";
		var $user;

		function Auth()
		{
			$this->user_id  = 0;
			$this->username = "Guest";

			if(class_exists("DBObject") && class_exists("User"))
				$this->user = new User();

			if(!$this->check_session()) $this->check_cookie();
			return $this->ok();
		}
	
		function check_session()
		{
			if(!empty($_SESSION['auth_username']) && !empty($_SESSION['auth_password']))
				return $this->check($_SESSION['auth_username'], $_SESSION['auth_password']);
		}

		function check_cookie()
		{
			if(!empty($_COOKIE['auth_username']) && !empty($_COOKIE['auth_password']))
				return $this->check($_COOKIE['auth_username'], $_COOKIE['auth_password']);
		}
	
		function check($username, $password)
		{
			global $db;
			$username = mysql_real_escape_string($username, $db->db);
			$password = mysql_real_escape_string($password, $db->db);
			$db->query("SELECT * FROM users WHERE username = '$username' AND password = '$password'");

			if(mysql_num_rows($db->result) == 1)
			{
				$row = mysql_fetch_array($db->result, MYSQL_ASSOC);
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

		function login($username, $password)
		{
			global $db;
			$username = mysql_real_escape_string($username, $db->db);
			$md5pw    = md5($password . $this->salt);
			$db->query("SELECT * FROM users WHERE username = '$username'");
			if(mysql_num_rows($db->result) == 1)
			{
				$row = mysql_fetch_array($db->result, MYSQL_ASSOC);
				if($row['password'] == $md5pw)
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

					$_SESSION['auth_username'] = $username;
					$_SESSION['auth_password'] = $md5pw;
					setcookie("auth_username", $username, time()+60*60*24*30, "/", $this->domain);
					setcookie("auth_password", $md5pw, time()+60*60*24*30, "/", $this->domain);
					
					return true;
				}
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
			return md5($pw . $this->salt);
		}
	}
?>