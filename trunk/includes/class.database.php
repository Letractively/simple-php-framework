<?PHP
	class Database
	{
		var $onError   = 0; // 0 = die($error), 1 = email($error) & continue, 2 = do nothing & continue
		var $longQuery = 0; // 0 = ignore, otherwise email($query) if query time > $longQuery
		var $errorTo   = "email@domain.com";
		var $errorFrom = "errors@domain.com";
		var $errorPage = "http://domain.com/database-error.php";

		var $host;
		var $user;
		var $password;
		var $dbname;
		var $db;
		var $result;
		var $queries;

		function Database($host, $user, $password, $dbname)
		{
			$this->host     = $host;
			$this->user     = $user;
			$this->password = $password;
			$this->dbname   = $dbname;			
		}
		
		function connect($redirect = false)
		{
			$this->queries = array();

			$this->db = mysql_connect($host, $user, $password)
				or $this->notify(mysql_error(), $redirect);

			mysql_select_db($dbname, $this->db)
				or $this->notify(mysql_error(), $redirect);
		}

		function query($sql, $redirect = false)
		{
			$this->queries[] = $sql;

			$start = microtime();
			$this->result = mysql_query($sql, $this->db) or $this->notify(mysql_error());
			$stop = microtime();

			$delta = $stop - $start;
			
			if(($longQuery != 0) && ($delta > $longQuery))
			{
				$msg  = $_SERVER['PHP_SELF'] . " @ " . date("Y-m-d H:ia") . "\n\n";
				$msg .= "The following query took $delta to complete:\n\n";
				$msg .= $this->lastQuery() . "\n\n";
				$msg .= $this->queries() . "\n\n";
				
				mail($this->errorTo, "Long Query " . $_SERVER['PHP_SELF'], $msg, "From: {$this->errorFrom}");
				
				// global $rsslog;
				// $rsslog->log("Long Query " . $_SERVER['PHP_SELF'], $msg);
			}
			
			return $db->result;
		}

		function numQueries()
		{
			return count($this->queries);
		}

		function lastQuery()
		{
			return $this->queries[count($this->queries) - 1];
		}

		function queries()
		{
			return implode("\n", $this->queries);
		}
		
		function isValid() 
		{
			return isset($this->result) && (mysql_num_rows($this->result) > 0);
		}

		function notify($errMsg, $redirect)
		{
			global $auth;

			switch($this->onError)
			{
				case 0:
					die($err . "<br/><br/>" . $this->lastQuery());
					break;
				
				case 1:
					$msg  = $_SERVER['PHP_SELF'] . " @ " . date("Y-m-d H:ia") . "\n";
					$msg .= $errMsg . "\n\n";
					$msg .= $this->queries() . "\n\n";
					$msg .= "CURRENT USER\n============\n"     . var_export($auth, true)  . "\n" . $_SERVER['REMOTE_ADDR'] . "\n\n";
					$msg .= "POST VARIABLES\n==============\n" . var_export($_POST, true) . "\n\n";
					$msg .= "GET VARIABLES\n=============\n"   . var_export($_GET, true)  . "\n\n";
					mail($this->errorTo, $_SERVER['PHP_SELF'], $msg, "From: {$this->errorFrom}");
					// global $rsslog;
					// $rsslog->log("DB Error" . $_SERVER['PHP_SELF'], $msg);
					break;
			}
			
			if($redirect)
			{
				header("Location: {$this->errorPage}");
				exit();
			}			
		}
	}
?>