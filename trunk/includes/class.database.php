<?PHP
	class Database
	{
		var $db;
		var $result;
		var $queries;

		var $notifyOnError; // 1 = die($error), 2 = email($error) & continue, 3 = do nothing & continue
		var $notifyOnLongQuery; // 0 = no, else = email($query) if query time > $notifyOnLongQuery

		var $errorTo = "email@domain.com";
		var $errorFrom = "errors@domain.com";

		function Database($host, $user, $password, $dbname, $notifyOnError = 1)
		{
			$this->notifyOnError     = $notifyOnError;
			$this->notifyOnLongQuery = 0;
			
			$this->db = mysql_connect($host, $user, $password) or $this->notify(mysql_error());
			mysql_select_db($dbname, $this->db) or $this->notify(mysql_error());
			$this->queries = array();
		}

		function query($sql)
		{
			$this->queries[] = $sql;

			$start = microtime();
			$this->result = mysql_query($sql, $this->db) or $this->notify(mysql_error());
			$stop = microtime();

			$delta = $stop - $start;
			
			if(($notifyOnLongQuery != 0) && ($delta > $notifyOnLongQuery))
			{
				$msg  = $_SERVER['PHP_SELF'] . " @ " . date("Y-m-d H:ia") . "\n\n";
				$msg .= "The following query took $delta to complete:\n\n";
				$msg .= $this->lastQuery() . "\n\n";
				$msg .= $this->queries() . "\n\n";
				mail($this->errorTo, "Long Query " . $_SERVER['PHP_SELF'], $msg, "From: {$this->errorFrom}");
			}
		}

		function numQueries() { return count($this->queries); }
		function lastQuery()  {	return $this->queries[count($this->queries) - 1]; }
		function queries()    { return implode("\n", $this->queries); }
		function isValid()    { return isset($this->result) && (mysql_num_rows($this->result) > 0); }

		function notify($err)
		{
			if($this->notifyOnError == 1)
				die($err . "<br/><br/>" . $this->lastQuery());
			elseif($this->notifyOnError == 2)
			{
				global $auth;
				$msg  = $_SERVER['PHP_SELF'] . " @ " . date("Y-m-d H:ia") . "\n\n";
				$msg .= $err . "\n\n";
				$msg .= $this->queries() . "\n\n";

				$msg .= "CURRENT USER\n============\n"     . var_export($auth, true)  . "\n" . $_SERVER['REMOTE_ADDR'] . "\n\n";
				$msg .= "POST VARIABLES\n==============\n" . var_export($_POST, true) . "\n\n";
				$msg .= "GET VARIABLES\n=============\n"   . var_export($_GET, true)  . "\n\n";

				mail($this->errorTo, $_SERVER['PHP_SELF'], $msg, "From: {$this->errorFrom}");
			}
		}
	}
?>