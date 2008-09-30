<?PHP
    class Config
    {
        // Add your server names to the appropriate arrays.
        static private $__productionServers = array('production.server.com');
        static private $__stagingServers    = array('staging.server.com');
        static private $__localServers      = array('simple-php-framework.site');

        // Define any config settings you want to use here, and then set them in the appropriate
        // location functions below (everywhere, production, staging, and local).

        static public $auth_domain; // Domain to set for the cookie
        static public $auth_hash;   // Store hashed passwords in database? (versus plain-text)
        static public $auth_salt;   // Can be any random string of characters

        static public $dbserver; // Database server
        static public $dbname;   // Database name
        static public $dbuser;   // Database username
        static public $dbpass;   // Database password
        static public $dberror;  // What do do on a database error (see class.database.php for details)

        static public $useDBSessions; // Set to true to store sessions in the database


        // Add code to be run on all servers
        static public function everywhere()
        {
            // Store sesions in the database?
            self::$useDBSessions = false;

            // Settings for the Auth class
            self::$auth_domain   = '';
            self::$auth_hash     = false;
            self::$auth_salt     = '6h67467859$%^&A2';
        }

        // Add code/variables to be run only on production servers
        static public function production()
        {
            define('WEB_ROOT', '/');

            self::$dbserver = '';
            self::$dbname   = '';
            self::$dbuser   = '';
            self::$dbpass   = '';
            self::$dberror  = '';

            ini_set('display_errors', '0');
        }

        // Add code/variables to be run only on staging servers
        static public function staging()
        {
            define('WEB_ROOT', '/');

            self::$dbserver = '';
            self::$dbname   = '';
            self::$dbuser   = '';
            self::$dbpass   = '';
            self::$dberror  = 'die';

            ini_set('display_errors', '1');
            ini_set('error_reporting', E_ALL);
        }

        // Add code/variables to be run only on local (testing) servers
        static public function local()
        {
            define('WEB_ROOT', '/');

            self::$dbserver = 'localhost';
            self::$dbname   = 'simple-php-framework';
            self::$dbuser   = 'root';
            self::$dbpass   = '';
            self::$dberror  = 'die';

            ini_set('display_errors', '1');
            ini_set('error_reporting', E_ALL);
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
                die('Where am I? (You need to setup your server names in class.config.php) $_SERVER[\'SERVER_NAME\'] reported: ' . $_SERVER['SERVER_NAME']);
        }

        static public function whereAmI()
        {
            if(in_array($_SERVER['SERVER_NAME'], self::$__productionServers))
                return 'production';
            elseif(in_array($_SERVER['SERVER_NAME'], self::$__stagingServers))
                return 'staging';
            elseif(in_array($_SERVER['SERVER_NAME'], self::$__localServers))
                return 'local';
            else
                return false;
        }
    }