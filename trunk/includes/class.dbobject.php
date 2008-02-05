<?PHP
	// Example sub class
	//
	// class User extends DBObject
	// {
	//	function __construct($id = "")
	// 	{                        table    primary_key      column names                             [load record with this id]
	// 		parent::__construct('users', 'user_id', array('username', 'password', 'level', 'email'), $id);
	// 	}
	// }
	//

	class DBObject
	{
		public $id;
		public $searchCols;
		
		private $id_name;
		private $table_name;
		private $columns = array();

		function __construct($table_name, $id_name, $columns, $id = "")
		{
			$this->table_name = $table_name;
			$this->id_name = $id_name;

			foreach($columns as $key)
				$this->columns[$key] = null;
				
			if($id != "")
				$this->select($id);
		}

		function __get($key)
		{
			if(substr($key, 0, 2) == "__")
				return htmlspecialchars($this->columns[substr($key, 2)]);
			else
				return $this->columns[$key];
		}

		function __set($key, $value)
		{
			if(array_key_exists($key, $this->columns))
			{
				$this->columns[$key] = $value;
				return true;
			}
			return false;
		}

		function select($id, $column = "")
		{
			global $db;
			
			if($column == "") $column = $this->id_name;

			$id = mysql_real_escape_string($id, $db->db);
			$column = mysql_real_escape_string($column, $db->db);

			$db->query("SELECT * FROM " . $this->table_name . " WHERE `$column` = '$id'");
			if(mysql_num_rows($db->result) == 0)
				return false;
			else
			{
				$row = mysql_fetch_array($db->result, MYSQL_ASSOC);
				$this->id = $row[$this->id_name];
				foreach($row as $key => $val)
					$this->columns[$key] = $val;
			}
		}

		function replace()
		{
			return $this->insert("REPLACE INTO");
		}

		function insert($cmd = "INSERT INTO")
		{
			global $db;
			
			if(count($this->columns) > 0)
			{
				unset($this->columns[$this->id_name]);

				$columns = "`" . join("`, `", array_keys($this->columns)) . "`";
				$values  = "'" . join("', '", $this->quoteColumnVals()) . "'";

				$db->query("$cmd " . $this->table_name . " ($columns) VALUES ($values)");

				$this->id = mysql_insert_id($db->db);
				return $this->id;
			}
		}

		function update()
		{
			global $db;

			$arrStuff = array();
			unset($this->columns[$this->id_name]);
			foreach($this->quoteColumnVals() as $key => $val)
				$arrStuff[] = "`$key` = '$val'";
			$stuff = implode(", ", $arrStuff);
			
			$id = mysql_real_escape_string($this->id, $db->db);
		
			$db->query("UPDATE " . $this->table_name . " SET $stuff WHERE " . $this->id_name . " = '" . $id . "'");
			return mysql_affected_rows($db->db); // Not always correct due to mysql update bug/feature
		}

		function delete()
		{
			global $db;
			$id = mysql_real_escape_string($this->id, $db->db);
			$db->query("DELETE FROM " . $this->table_name . " WHERE `" . $this->id_name . "` = '" . $id . "'");
			return mysql_affected_rows($db->db);
		}

		// Glob is still being *tested*. Returns an array of pre-initialized dbobjects.
		// Basically, lets you grab a large block of instantiated objects from the database using
		// only one query.
		function glob($str_args = "")
		{
			global $db;

			parse_str($str_args, $args);
			$order = isset($args['order']) ? "ORDER BY {$args['order']}" : "";			

			$where = "";
			if($this->id != "")
				$where .= " {$this->id_name} <> '{$this->id}' AND ";
			
			$objs = array();
			$rows = $db->getRows("SELECT * FROM {$this->table_name} WHERE $where 1 $order");
			$class = get_class($this);
			foreach($rows as $row)
			{
				$o = new $class;
				$o->load($row);
				$o->id = $row[$this->id_name];
				$objs[] = $o;
			}
			return $objs;
		}

		function postLoad() { $this->load($_POST); }
		function getLoad()  { $this->load($_GET); }
		function load($arr)
		{
			if(is_array($arr))
			{
				foreach($arr as $key => $val)
					if(array_key_exists($key, $this->columns) && $key != $this->id_name)
						$this->columns[$key] = $val;
				return true;
			}
			else
				return false;
		}
		
		function quoteColumnVals()
		{
			global $db;
			$columnVals = array();
			foreach($this->columns  as $key => $val)
				$columnVals[$key] = mysql_real_escape_string($val, $db->db);
			return $columnVals;
		}
	}