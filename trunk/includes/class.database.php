<?PHP
	class Database
	{
		public $onError   = "die"; // die, email, or leave blank to simply continue
		public $errorTo   = "email@domain.com";
		public $errorFrom = "errors@domain.com";
		public $errorPage = "database-error.php";

		public $db = false;
		public $dbname;
		public $host;
		public $user;
		public $password;
		public $queries;
		public $result;
		public $redirect = false;

		function __construct($host, $user, $password, $dbname = null, $on_error = null)
		{
			$this->host     = $host;
			$this->user     = $user;
			$this->password = $password;
			if(!is_null($dbname)) $this->dbname = $dbname;
			if(!is_null($on_error)) $this->onError = $on_error;
			$this->queries  = array();
		}
		
		function connect()
		{
			$this->db = mysql_connect($this->host, $this->user, $this->password) or $this->notify();
			if($this->db === false) return false;
			mysql_select_db($this->dbname, $this->db) or $this->notify();
		}

		function query($sql)
		{
			if(!is_resource($this->db))
				$this->connect();

			// Optionally allow extra args which are escaped and inserted
			// in place of their corresponding question mark placeholders.
			// Surely there's a faster way than doing it this way, right?
			if(func_num_args() > 1)
			{
				$args = func_get_args();
				$sql = str_replace("?", "[[qmark]]", $sql);				
				for($i = 1; $i < func_num_args(); $i++)
					$sql = preg_replace('!\[\[qmark\]\]!', $this->quote($args[$i]), $sql, 1);
			}

			$this->queries[] = $sql;
			$this->result = mysql_query($sql, $this->db) or $this->notify();
			return $this->result;
		}

		// You can pass in nothing, a string, or a db result
		function getValue($arg = null)
		{
			if(is_null($arg) && $this->hasRows())
				return mysql_result($this->result, 0, 0);
			elseif(is_resource($arg) && $this->hasRows($arg))
				return mysql_result($arg, 0, 0);
			elseif(is_string($arg))
			{
				$this->query($arg);
				if($this->hasRows())
					return mysql_result($this->result, 0, 0);
				else
					return false;
			}
			return false;
		}

		function numRows($arg = null)
		{
			if(is_null($arg) && is_resource($this->result))
				return mysql_num_rows($this->result);
			elseif(is_resource($arg) && is_resource($arg))
				return mysql_num_rows($arg);
			elseif(is_string($arg))
			{
				$this->query($arg);
				if(is_resource($this->result))
					return mysql_num_rows($this->result);
			}
			return false;
		}

		// You can pass in nothing, a string, or a db result
		function getRow($arg = null)
		{
			if(is_null($arg) && $this->hasRows())
				return mysql_fetch_array($this->result, MYSQL_ASSOC);
			elseif(is_resource($arg) && $this->hasRows($arg))
				return mysql_fetch_array($arg, MYSQL_ASSOC);
			elseif(is_string($arg))
			{
				$this->query($arg);
				if($this->hasRows())
					return mysql_fetch_array($this->result, MYSQL_ASSOC);
			}
			return false;
		}

		// You can pass in nothing, a string, or a db result
		function getRows($arg = null)
		{
			if(is_null($arg) && $this->hasRows())
				$result = $this->result;
			elseif(is_resource($arg) && $this->hasRows($arg))
				$result = $arg;
			elseif(is_string($arg))
			{
				$this->query($arg);
				if($this->hasRows())
					$result = $this->result;
				else
					return array();
			}
			else
				return array();

			$rows = array();
			mysql_data_seek($result, 0);
			while($row = mysql_fetch_array($result, MYSQL_ASSOC))
				$rows[] = $row;
			return $rows;
		}

		// You can pass in nothing, a string, or a db result
		function getObject($arg = null)
		{
			if(is_null($arg) && $this->hasRows())
				return mysql_fetch_object($this->result);
			elseif(is_resource($arg) && $this->hasRows($arg))
				return mysql_fetch_object($arg);
			elseif(is_string($arg))
			{
				$this->query($arg);
				if($this->hasRows())
					return mysql_fetch_object($this->result);
			}
			return false;
		}

		// You can pass in nothing, a string, or a db result
		function getObjects($arg = null)
		{
			if(is_null($arg) && $this->hasRows())
				$result = $this->result;
			elseif(is_resource($arg) && $this->hasRows($arg))
				$result = $arg;
			elseif(is_string($arg))
			{
				$this->query($arg);
				if($this->hasRows())
					$result = $this->result;
				else
					return array();
			}
			else
				return array();

			$objects = array();
			mysql_data_seek($result, 0);
			while($object = mysql_fetch_object($result))
				$objects[] = $object;
			return $objects;
		}

		function hasRows($result = null)
		{
			if(is_null($result)) $result = $this->result;
			return is_resource($result) && (mysql_num_rows($result) > 0);
		}

		function quote($var)
		{
			if(!is_resource($this->db))
				$this->connect();

			return "'" . mysql_real_escape_string($var, $this->db) . "'";
		}

		function quoteParam($var) { return $this->quote($_REQUEST[$var]); }
		function numQueries() { return count($this->queries); }
		function lastQuery() { return $this->queries[count($this->queries) - 1]; }

		function notify()
		{
			global $auth;
			
			$err_msg = mysql_error($this->db);
			error_log($err_msg);

			switch($this->onError)
			{
				case "die":
					echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Database Error:</strong><br/>$err_msg</p>";
					echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Last Query:</strong><br/>" . $this->lastQuery() . "</p>";
					echo "<pre>";
					debug_print_backtrace();
					echo "</pre>";
					die;
					break;
				
				case "email":
					$msg  = $_SERVER['PHP_SELF'] . " @ " . date("Y-m-d H:ia") . "\n";
					$msg .= $err_msg . "\n\n";
					$msg .= implode("\n", $this->queries) . "\n\n";
					$msg .= "CURRENT USER\n============\n"     . var_export($auth, true)  . "\n" . $_SERVER['REMOTE_ADDR'] . "\n\n";
					$msg .= "POST VARIABLES\n==============\n" . var_export($_POST, true) . "\n\n";
					$msg .= "GET VARIABLES\n=============\n"   . var_export($_GET, true)  . "\n\n";
					mail($this->errorTo, $_SERVER['PHP_SELF'], $msg, "From: {$this->errorFrom}");
					break;
			}

			if($this->redirect === true)
			{
				header("Location: {$this->errorPage}");
				exit;
			}			
		}
	}