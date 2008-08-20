<?PHP
	class Database
	{
		public $db = false;

		public $dbname;
		public $host;
		public $user;
		public $password;

		public $queries;
		public $result;

		public $dieOnError;
		public $redirect = false;

		function __construct($dbserver = null, $dbuser = null, $dbpass = null, $dbname = null, $die_on_error = false)
		{
			// If no arguments are passed, attempt to pull from our global $Config variable
			if(func_num_args() == 0)
			{
				$this->host       = Config::$dbserver;
				$this->user       = Config::$dbuser;
				$this->password   = Config::$dbpass;
				$this->dbname     = Config::$dbname;
				$this->dieOnError = Config::$dberror;
			}
			else
			{
				$this->host       = $dbserver;
				$this->user       = $dbuser;
				$this->password   = $dbpass;
				$this->dbname     = $dbname;
				$this->dieOnError = $die_on_error;
			}

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

			// Optionally allow extra args which are escaped and inserted in place of ?
			if(func_num_args() > 1)
			{
				$args = func_get_args();
				foreach($args as &$item)
					$item = $this->escape($item);
				$sql = vsprintf(str_replace('?', '%s', $sql), array_slice($args, 1));
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
		function getValues($arg = null)
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
				$rows[] = array_pop($row);
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
			if(!is_resource($this->db)) $this->connect();
			return "'" . $this->escape($var) . "'";
		}

		function escape($var)
		{
			if(!is_resource($this->db)) $this->connect();
			return mysql_real_escape_string($var, $this->db);
		}

		function quoteParam($var) { return $this->quote($_REQUEST[$var]); }
		function numQueries() { return count($this->queries); }
		function lastQuery() { return $this->queries[count($this->queries) - 1]; }

		function notify()
		{
			$err_msg = mysql_error($this->db);
			error_log($err_msg);

			if($this->dieOnError === true)
			{
				echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Database Error:</strong><br/>$err_msg</p>";
				echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Last Query:</strong><br/>" . $this->lastQuery() . "</p>";
				echo "<pre>";
				debug_print_backtrace();
				echo "</pre>";
				exit;
			}

			if(is_string($this->redirect))
			{
				header("Location: {$this->redirect}");
				exit;
			}			
		}
	}
