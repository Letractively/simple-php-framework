<?PHP
	class Database
	{
		var $onError   = "die"; // die, email, or nothing
		var $errorTo   = "email@domain.com";
		var $errorFrom = "errors@domain.com";
		var $errorPage = "http://domain.com/database-error.php";

		var $db;
		var $dbname;
		var $host;
		var $password;
		var $queries;
		var $result;
		var $user;
		var $redirect = false;

		function Database($host, $user, $password, $dbname)
		{
			$this->host     = $host;
			$this->user     = $user;
			$this->password = $password;
			$this->dbname   = $dbname;			
			$this->queries  = array();
		}
		
		function connect()
		{
			$this->db = mysql_connect($this->host, $this->user, $this->password) or $this->notify();
			mysql_select_db($this->dbname, $this->db) or $this->notify();
		}

		function query($sql)
		{
			$this->queries[] = $sql;
			$this->result = mysql_query($sql, $this->db) or $this->notify();
			return $this->result;
		}

		function getRows($result = null, $returnObjects = false)
		{
			$rows = array();
			if(is_null($result)) $result = $this->result;

			if($this->isValid($result))
			{
				mysql_data_seek($result, 0);
				if($returnObjects)
					while($row = mysql_fetch_object($result))
						$rows[] = $row;
				else
					while($row = mysql_fetch_array($result, MYSQL_ASSOC))
						$rows[] = $row;
			}

			return $rows;
		}

        function selectValue($sql = null)
        {
			if(!is_null($sql)) $this->query($sql);
			if(!$this->isValid($this->result)) return false;
			return(mysql_result($this->result, 0, 0));
        }

		function selectRow($sql = null)
		{
			if(!is_null($sql)) $this->query($sql);
			if(!$this->isValid($this->result)) return false;
			return mysql_fetch_array($this->result, MYSQL_ASSOC);
		}

		function selectObject($sql = null)
		{
			if(!is_null($sql)) $this->query($sql);
			if(!$this->isValid($this->result)) return false;
			return mysql_fetch_object($this->result);
		}

		// Only makes sense for results with two columns
        function mapping($sql = null)
        {
            $result = array();
			if(!is_null($sql)) $this->query($sql);

            while(list($key, $value) = mysql_fetch_array($this->result))
                $result[$key] = $value;

            return $result;
        }

		function isValid($result = null)
		{
			if(is_null($result)) $result = $this->result;
			return is_resource($result) && (mysql_num_rows($result) > 0);
		}

		function quote($var) { return "'" . mysql_real_escape_string($var, $this->db) . "'"; }
		function quoteParam($var) { return $this->quote(fix_slashes($_REQUEST[$param])); }
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
					echo $err_msg . "<br/><br/>" . $this->lastQuery() . "<br/><br/>\n\n";
					debug_print_backtrace();
					die();
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
			
			if($redirect === true)
			{
				header("Location: {$this->errorPage}");
				exit();
			}			
		}
	}
?>