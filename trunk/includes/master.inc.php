<?PHP
	// Add your server names to the appropriate arrays.
	$local_servers      = array("local.server.site");
	$staging_servers    = array("staging.server.com");
	$production_servers = array("production.server.com");

	if(in_array($_SERVER['SERVER_NAME'], $production_servers))
	{
		// Production
		$dbserver = "";
		$dbname   = "";
		$dbuser   = "";
		$dbpass   = "";
		$on_error = "";

		ini_set('display_errors', "0");
	}
	elseif(in_array($_SERVER['SERVER_NAME'], $staging_servers))
	{
		// Staging
		$dbserver = "";
		$dbname   = "";
		$dbuser   = "";
		$dbpass   = "";
		$on_error = "die";

		ini_set('display_errors', "1");
		ini_set('error_reporting', E_ALL);
	}
	elseif(in_array($_SERVER['SERVER_NAME'], $local_servers))
	{
		// Local (testing)
		$dbserver = "localhost";
		$dbname   = "framework";
		$dbuser   = "root";
		$dbpass   = "";
		$on_error = "die";

		ini_set('display_errors', "1");
		ini_set('error_reporting', E_ALL);
	}
	else
		die("Where am I? (You need to setup your server names in master.inc.php) You might want to read our <a href='/_masters/overview.html'>quick overview</a> to get started.");

	session_start();

	// Determine our absolute document root
	define('DOC_ROOT', realpath(dirname(__FILE__) . "/../"));

	// Global include files
	require DOC_ROOT . '/includes/functions.inc.php'; // __autoload() is contained in this file
	require DOC_ROOT . '/includes/class.objects.php';

	// Connect to database
	$db = new Database($dbserver, $dbuser, $dbpass, $dbname);
	$db->onError = $on_error;
	$db->connect();

	// Initialize current user
	define('AUTH_SALT', 'nFSD76n9234A34%@9');; // Pick any random string of characters
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