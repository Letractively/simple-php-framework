<?PHP
	// Stores session variables unique to a given URL
	class PagePref
	{
   		var $_id;
		var $_data;
	
		function PagePref()
		{
			$this->_id = md5($_SERVER['PHP_SELF']);
			$this->_data = unserialize($_SESSION[$this->_id]);
		}
	
		function __get($key, &$ret)
		{
			$tmp = $this->_data;
			$ret = $tmp[$key];
			return true;
		}
	
		function __set($key, $val)
		{
			if(!is_array($this->_data)) $this->_data = array();
			$tmp = $this->_data;
			$tmp[$key] = $val;
			$this->_data = $tmp;
			$_SESSION[$this->_id] = serialize($this->_data);
			return true;
		}
	}
	overload('PagePref');
?>