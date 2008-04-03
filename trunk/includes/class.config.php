<?PHP
	class Config
	{
		// Add your server names to the appropriate arrays.
		private $__productionServers = array('production.server.com');
		private $__stagingServers    = array('staging.server.com');
		private $__localServers      = array('local.server.site');

		// No need to mess with this
		private $values = array();

		// Add code to be run on all servers
		public function everywhere()
		{
			$this->values['useDBSessions'] = false;
			$this->values['auth_hash']     = false; // Stored hashed password in database? (versus plain-text)
			$this->values['auth_domain']   = ''; // Domain to set in Auth cookie
			$this->values['auth_salt']     = '^&ASDF5678dfsaghjdkfghkj~'; // Pick any random string of characters
		}

		// Add code/variables to be run only on production servers
		public function production()
		{
			define('WEB_ROOT', '/');
			ini_set('display_errors', '0');

			$this->values['dbserver'] = '';
			$this->values['dbname']   = '';
			$this->values['dbuser']   = '';
			$this->values['dbpass']   = '';
			$this->values['dberror']  = '';
		}

		// Add code/variables to be run only on staging servers
		public function staging()
		{
			define('WEB_ROOT', '/');
			ini_set('display_errors', '1');
			ini_set('error_reporting', E_ALL);

			$this->values['dbserver'] = '';
			$this->values['dbname']   = '';
			$this->values['dbuser']   = '';
			$this->values['dbpass']   = '';
			$this->values['dberror']  = 'die';
		}
		
		// Add code/variables to be run only on local (testing) servers
		public function local()
		{
			define('WEB_ROOT', '/');
			ini_set('display_errors', '1');
			ini_set('error_reporting', E_ALL);

			$this->values['dbserver'] = 'localhost';
			$this->values['dbname']   = '';
			$this->values['dbuser']   = '';
			$this->values['dbpass']   = '';
			$this->values['dberror']  = 'die';
		}

		public function __construct()
		{
			$this->everywhere();
			
			if(in_array($_SERVER['SERVER_NAME'], $this->__productionServers))
				$this->production();
			elseif(in_array($_SERVER['SERVER_NAME'], $this->__stagingServers))
				$this->staging();
			elseif(in_array($_SERVER['SERVER_NAME'], $this->__localServers))
				$this->local();
			else
				die('Where am I? (You need to setup your server names in class.config.php) You might want to read our <a href="_masters/overview.html">quick overview</a> to get started.');
		}
		
		public function __get($key)
		{
			return isset($this->values[$key]) ? $this->values[$key] : null;
		}
		
		public function __set($key, $val)
		{
			return ($this->values[$key] = $val);
		}
		
		public function __isset($key)
		{
			return isset($this->values[$key]);
		}
		
		public function __unset($key)
		{
			unset($this->values[$key]);
		}
		
		public function whereAmI()
		{
			if(in_array($_SERVER['SERVER_NAME'], $this->__productionServers))
				return 'production';
			elseif(in_array($_SERVER['SERVER_NAME'], $this->__stagingServers))
				return 'staging';
			elseif(in_array($_SERVER['SERVER_NAME'], $this->__localServers))
				return 'local';
		}
	}