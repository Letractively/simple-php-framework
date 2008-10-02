<?PHP
    class Database
    {
		// Singleton object. Leave $me alone.
		private static $me;
		
        public $db;
        public $host;
        public $name;
        public $username;
        public $password;
        public $dieOnError;
        public $queries;
        public $result;

        public $redirect = false;

		// Singleton constructor
        private function __construct($connect = false)
        {
			$Config = Config::getConfig();
			
            $this->host       = $Config->dbHost;
            $this->name       = $Config->dbName;
            $this->username   = $Config->dbUsername;
            $this->password   = $Config->dbPassword;
            $this->dieOnError = $Config->dbDieOnError;

			$this->db = false;
            $this->queries = array();

			if($connect === true)
				$this->connect();
        }

		// Get Singleton object
		public static function getDatabase($connect = false)
		{
			if(is_null(self::$me))
				self::$me = new Database($connect);
			return self::$me;			
		}

		// Do we have a valid database connection?
		public function isConnected()
		{
			return is_resource($this->db) && get_resource_type($this->db) == 'mysql link';
		}
		
		// Do we have a valid database connection and have we selected a database?
		public function databaseSelected()
		{
			if(!$this->isConnected()) return false;
			$result = mysql_list_tables($this->name, $this->db);
			return is_resource($result);
		}

        public function connect()
        {
            $this->db = mysql_connect($this->host, $this->username, $this->password) or $this->notify();
            if($this->db === false) return false;
            mysql_select_db($this->name, $this->db) or $this->notify();
			return $this->isConnected();
        }

        public function query($sql)
        {
			if(!$this->isConnected()) $this->connect();

            // Optionally allow extra args which are escaped and inserted in place of '?'.
            if(func_num_args() > 1)
            {
				$args = array_slice(func_get_args(), 1);
				for($i = 0; $i < count($args); $i++)
					$args[$i] = $this->escape($args[$i]);
				$sql = vsprintf(str_replace('?', '%s', $sql), $args);
            }

            $this->queries[] = $sql;
            $this->result = mysql_query($sql, $this->db) or $this->notify();
            return $this->result;
        }

		// Returns the number of rows.
        // You can pass in nothing, a string, or a db result
        public function numRows($arg = null)
        {
            if(is_null($arg) && is_resource($this->result))
                return mysql_num_rows($this->result);
            elseif(is_resource($arg))
                return mysql_num_rows($arg);
            elseif(is_string($arg))
            {
                $this->query($arg);
                if(is_resource($this->result))
                    return mysql_num_rows($this->result);
            }
            return false;
        }

        public function hasRows($result = null)
        {
            if(is_null($result)) $result = $this->result;
            return is_resource($result) && (mysql_num_rows($result) > 0);
        }

		// Returns a single value.
        // You can pass in nothing, a string, or a db result
        public function getValue($arg = null)
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
            }
            return false;
        }

		// Returns an array of the first value in each row.
        // You can pass in nothing, a string, or a db result
        public function getValues($arg = null)
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

            $values = array();
            mysql_data_seek($result, 0);
            while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                $values[] = array_pop($row);
            return $values;
        }

		// Returns the first row.
        // You can pass in nothing, a string, or a db result
        public function getRow($arg = null)
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

		// Returns an array of all the rows.
        // You can pass in nothing, a string, or a db result
        public function getRows($arg = null)
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

		// Escapes a value and wraps it in single quotes.
        public function quote($var)
        {
            if(!$this->isConnected()) $this->connect();
            return "'" . $this->escape($var) . "'";
        }

		// Escapes a value.
        public function escape($var)
        {
            if(!$this->isConnected()) $this->connect();
            return mysql_real_escape_string($var, $this->db);
        }

        function quoteParam($var) { return $this->quote($_REQUEST[$var]); }
        function numQueries() { return count($this->queries); }

        function lastQuery()
		{
			if($this->numQueries() > 0)
				return $this->queries[$this->numQueries() - 1];
			else
				return false;
		}

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