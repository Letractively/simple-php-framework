<?PHP
	// Stores session variables unique to a given URL
	class PagePref
	{
		var $_id;
		var $_data;

		function __construct()
		{
			$this->_id = md5($_SERVER['PHP_SELF']);
			$this->_data = unserialize($_SESSION[$this->_id]);
		}

		function __get($key)
		{
			return $this->_data[$key];
		}

		function __set($key, $val)
		{
			if(!is_array($this->_data)) $this->_data = array();
			$this->_data[$key] = $val;
			$_SESSION[$this->_id] = serialize($this->_data);
		}

		function clear()
		{
			unset($_SESSION[$this->_id]);
			unset($this->_data);
		}
	}
?>