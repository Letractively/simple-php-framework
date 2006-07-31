<?PHP
	class Database
	{
		var $db;
		var $result;
		var $queries;
		var $debug;
		var $errorTo = "tylerhall@gmail.com";
		var $errorFrom = "errors@website.com";

		function Database($host, $user, $password, $dbname, $debug = true)
		{
			$this->debug = $debug;
			$this->db = mysql_connect($host, $user, $password) or $this->notify(mysql_error());
			mysql_select_db($dbname, $this->db) or $this->notify(mysql_error());
			$this->queries = array();
		}

		function query($sql)
		{
			$this->queries[] = $sql;
			$this->result = mysql_query($sql, $this->db) or $this->notify(mysql_error());
		}

		function numQueries() { return count($this->queries); }
		function lastQuery()  {	return $this->queries[count($this->queries) - 1]; }
		function queries()    { return implode("\n", $this->queries); }
		function isValid()    { return isset($this->result) && (mysql_num_rows($this->result) > 0); }

		function notify($err)
		{
			if($this->debug) die($err . "<br/><br/>" . $this->lastQuery());

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
?>