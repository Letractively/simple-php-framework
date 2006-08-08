<?php

	// 
	// class User extends DBObject
	// {
	// 	function User($id = "")
	// 	{
	// 		parent::DBObject("users", "user_id", array("username", "first", "last", "email"));
	// 	}
	// }
	// overload("User");
	// 

	class DBObject
	{
		var $id;
		var $id_name;
		var $table_name;
		var $columns;

		function DBObject($table_name, $id_name, $columns, $id = "")
		{
			$this->table_name = $table_name;
			$this->id_name    = $id_name;
			$this->columns    = array();

			foreach($columns as $key)
			{
				$this->$key = null;
			}
			
			if($id != "")
				$this->select($id);			
		}

		function iset($key, $value)
		{
			$tmp = $this->columns;
			$tmp[$key] = $value;
			$this->columns = $tmp;
		}

		function __get($key, &$ret)
		{
			$ret = $this->columns[$key];
			return true;
		}

		function __set($key, $value)
		{
			$this->columns[$key] = $value;
			return true;
		}

		function select($id, $column = "")
		{
			global $db;
			if($column == "") $column = $this->id_name;

			$id = mysql_real_escape_string($id, $db->db);
			$column = mysql_real_escape_string($column, $db->db);

			$db->query("SELECT * FROM " . $this->table_name . " WHERE $column = '$id'");
			if(mysql_num_rows($db->result) == 0)
				return false;
			else
			{
				$this->id = $id;
				$row = mysql_fetch_array($db->result, MYSQL_ASSOC);
				foreach($row as $key => $val)
					$this->iset($key, $val);
			}
		}

		function insert()
		{
			global $db;

			$tmp = $this->columns;
			unset($tmp[$this->id_name]);

			$columns = join(", ", array_keys($tmp));
			$values  = "'" . join("', '", $this->quote_column_vals()) . "'";

			$db->query("INSERT INTO " . $this->table_name . " ($columns) VALUES ($values)");

			$this->id = mysql_insert_id($db->db);
			return $this->id;
		}

		function update()
		{
			global $db;

			$arrStuff = array();
			foreach($this->quote_column_vals() as $key => $val)
				$arrStuff[] = "$key = '$val'";
			$stuff = implode(", ", $arrStuff);
		
			$id = mysql_real_escape_string($this->id, $db->db);
	
			$db->query("UPDATE " . $this->table_name . " SET $stuff WHERE " . $this->id_name . " = '" . $id . "'");
			echo $db->lastQuery();
			return mysql_affected_rows($db->db); // Not always correct due to mysql update bug/feature
		}

		function delete()
		{
			global $db;
			$id = mysql_real_escape_string($this->id, $db->db);
			$db->query("DELETE FROM " . $this->table_name . " WHERE " . $this->id_name . " = '" . $id . "'");
			return mysql_affected_rows($db->db);
		}
	
		function postload() { $this->load($_POST); }
		function getload()  { $this->load($_GET); }
		function load($arr)
		{
			if(is_array($arr))
			{
				foreach($arr as $key => $val)
					if(array_key_exists($key, $this->columns) && $key != $this->id_name)
						$this->iset($key, fix_slashes($val));
						// $this->columns[$key] = fix_slashes($val);
				return true;
			}
			else
				return false;
		}
	
		function quote_column_vals()
		{
			global $db;
			$columnVals = array();
			$tmp = $this->columns;
			foreach($tmp as $key => $val)
				if($key != $this->id_name)
					$columnVals[$key] = mysql_real_escape_string($val, $db->db);
			return $columnVals;
		}
	}
?>