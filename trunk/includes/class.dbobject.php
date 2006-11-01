<?php
	class DBObject
	{
		public $id;
		private $__id_name;
		private $__table_name;
		private $__columns = array();

		function __construct($table_name, $id_name, $columns, $id = "")
		{
			$this->__table_name = $table_name;
			$this->__id_name = $id_name;

			$this->__columns = $columns;
				
			if(!empty($id))
				$this->select($id);
		}

		function select($id)
		{
			global $db;

			$db->query("SELECT ".join(',', $this->__columns)." FROM {$this->__table_name} WHERE {$this->__id_name}='$id'");
			if(mysql_num_rows($db->result) == 0)
				return false;
			else
			{
				$this->id = $id;
				$row = mysql_fetch_array($db->result, MYSQL_ASSOC);
				foreach($row as $key => $val)
					$this->$key = $val;
			}
		}

		function insert()
		{
			global $db;

			unset($this->__columns[$this->__id_name]);
			$columns = join(", ", $this->__columns);
			
			$values = array();
			foreach($this->__columns as $col)
				$values[] = $db->quote($this->$col);
			$values = join(',', $values);

			$db->query("INSERT INTO " . $this->__table_name . " ($columns) VALUES ($values)");

			$this->id = mysql_insert_id($db->db);
			return $this->id;
		}

		function update()
		{
			global $db;

			$arrStuff = array();
			unset($this->__columns[$this->__id_name]);
			foreach($this->__columns as $key)
				$arrStuff[] = $key.'='.$db->quote($this->$key);
			$stuff = join(',', $arrStuff);
		
			$db->query("UPDATE " . $this->__table_name . " SET $stuff WHERE " . $this->__id_name . " = '" . $this->id . "'");
			return mysql_affected_rows($db->db); // Not always correct due to mysql update bug/feature
		}

		function delete()
		{
			global $db;
			$db->query("DELETE FROM " . $this->__table_name . " WHERE " . $this->__id_name . " = '" . $this->id . "'");
			return mysql_affected_rows($db->db);
		}
		
		function postload()
		{
			foreach($_POST as $key => $val)
				$_POST[$key] = fix_slashes($val);
			$this->load($_POST);
		}
		
		function getload()
		{
			foreach($_GET as $key => $val)
				$_GET[$key] = fix_slashes($val);
			$this->load($_GET);
		}

		function load($arr)
		{
			if(is_array($arr))
			{
				foreach($arr as $key => $val)
					if(array_key_exists($key, $this->__columns))
						$this->__columns[$key] = $val;
				return true;
			}
			else
				return false;
		}
		
		function quote_column_vals()
		{
			global $db;
			$columnVals = array();
			foreach($this->__columns as $key)
				$columnVals[$key] = mysql_real_escape_string($this->$key, $db->db);
			return $columnVals;
		}
	}
?>