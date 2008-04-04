<?PHP
	class Config
	{
		// Add your server names to the appropriate arrays.
		static private $__productionServers = array('production.server.com');
		static private $__stagingServers    = array('staging.server.com');
		static private $__localServers      = array('local.server.site');
		
		// Define any config settings you want to use here...
		static public $useDBSessions;

		static public $auth_salt;
		static public $auth_hash;
		static public $auth_domain;

		static public $dbserver;
		static public $dbname;
		static public $dbuser;
		static public $dbpass;
		static public $dberror;

		// Add code to be run on all servers
		static public function everywhere()
		{
			self::$useDBSessions = false;
			self::$auth_hash     = false; // Stored hashed password in database? (versus plain-text)
			self::$auth_domain   = ''; // Domain to set in Auth cookie
			self::$auth_salt     = '^&ASDF5678dfsaghjdkfghkj~'; // Pick any random string of characters
		}

		// Add code/variables to be run only on production servers
		static public function production()
		{
			define('WEB_ROOT', '/');
			ini_set('display_errors', '0');

			self::$dbserver = '';
			self::$dbname   = '';
			self::$dbuser   = '';
			self::$dbpass   = '';
			self::$dberror  = '';
		}

		// Add code/variables to be run only on staging servers
		static public function staging()
		{
			define('WEB_ROOT', '/');
			ini_set('display_errors', '1');
			ini_set('error_reporting', E_ALL);

			self::$dbserver = '';
			self::$dbname   = '';
			self::$dbuser   = '';
			self::$dbpass   = '';
			self::$dberror  = 'die';
		}
		
		// Add code/variables to be run only on local (testing) servers
		static public function local()
		{
			define('WEB_ROOT', '/');
			ini_set('display_errors', '1');
			ini_set('error_reporting', E_ALL);

			self::$dbserver = 'localhost';
			self::$dbname   = '';
			self::$dbuser   = '';
			self::$dbpass   = '';
			self::$dberror  = 'die';
		}

		static public function load()
		{
			self::everywhere();
			
			if(in_array($_SERVER['SERVER_NAME'], self::$__productionServers))
				self::production();
			elseif(in_array($_SERVER['SERVER_NAME'], self::$__stagingServers))
				self::staging();
			elseif(in_array($_SERVER['SERVER_NAME'], self::$__localServers))
				self::local();
			else
				die('Where am I? (You need to setup your server names in class.config.php)');
		}
		
		static public function whereAmI()
		{
			if(in_array($_SERVER['SERVER_NAME'], self::$__productionServers))
				return 'production';
			elseif(in_array($_SERVER['SERVER_NAME'], self::$__stagingServers))
				return 'staging';
			elseif(in_array($_SERVER['SERVER_NAME'], self::$__localServers))
				return 'local';
		}
	}