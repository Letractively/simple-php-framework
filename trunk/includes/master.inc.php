<?PHP
	// Add your server names to the appropriate arrays.
	$production_servers = array('production.server.com');
	$staging_servers    = array('staging.server.com');
	$local_servers      = array('local.server.site');

	// Pick appropriate settings based on which server we're running on
	if(in_array($_SERVER['SERVER_NAME'], $production_servers))
	{
		// Production servers
		define('WEB_ROOT', '/');

		$dbserver = '';
		$dbname   = '';
		$dbuser   = '';
		$dbpass   = '';
		$on_error = '';

		ini_set('display_errors', '0');
	}
	elseif(in_array($_SERVER['SERVER_NAME'], $staging_servers))
	{
		// Staging servers
		define('WEB_ROOT', '/');

		$dbserver = '';
		$dbname   = '';
		$dbuser   = '';
		$dbpass   = '';
		$on_error = 'die';

		ini_set('display_errors', '1');
		ini_set('error_reporting', E_ALL);
	}
	elseif(in_array($_SERVER['SERVER_NAME'], $local_servers))
	{
		// Local (testing) servers
		define('WEB_ROOT', '/');

		$dbserver = 'localhost';
		$dbname   = '';
		$dbuser   = '';
		$dbpass   = '';
		$on_error = 'die';

		ini_set('display_errors', '1');
		ini_set('error_reporting', E_ALL);
	}
	else
		die('Where am I? (You need to setup your server names in master.inc.php) You might want to read our <a href="_masters/overview.html">quick overview</a> to get started.');

	// Determine our absolute document root
	define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));

	// Global include files
	require DOC_ROOT . '/includes/functions.inc.php'; // __autoload() is contained in this file
	require DOC_ROOT . '/includes/class.objects.php';

	// Connect to database (does not actually open the connection until it's needed)
	$db = new Database($dbserver, $dbuser, $dbpass, $dbname, $on_error);
	$db->connect();

	// Initialize our session...
	// DBSession::register(); // Uncomment this line to store sessions in the database
	session_start();
	
	// Initialize current user
	define('AUTH_SALT', '697845hjkSDF9687');; // Pick any random string of characters
	$auth = new Auth();

	// Object for tracking and displaying error messages
	$Error = new Error();

	// Fix magic quotes
	if(get_magic_quotes_gpc())
	{
		$_POST    = fix_slashes($_POST);
		$_GET     = fix_slashes($_GET);
		$_REQUEST = fix_slashes($_REQUEST);
	}
	
	// Clean up the global namespace
	unset($dbserver, $dbname, $dbuser, $dbpass, $on_error, $local_servers, $staging_servers, $production_servers);