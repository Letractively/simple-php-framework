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
		ini_set('error_reporting', E_ALL ^ E_NOTICE);
	}
	elseif(in_array($_SERVER['SERVER_NAME'], $local_servers))
	{
		// Local (testing)
		$dbserver = "localhost";
		$dbname   = "";
		$dbuser   = "root";
		$dbpass   = "";
		$on_error = "die";

		ini_set('display_errors', "1");
		ini_set('error_reporting', E_ALL ^ E_NOTICE);
	}
	else
		die("Where am I? (You need to setup your server names in master.inc.php)");

	session_start();

	// Determine our absolute document root
	$docroot = realpath(dirname(__FILE__) . "/../");

	// Global include files
	require $docroot . '/includes/functions.inc.php'; // __autoload() is contained in this file
	require $docroot . '/includes/class.objects.php';

	// Connect to database
	$db = new Database($dbserver, $dbuser, $dbpass, $dbname);
	$db->onError = $on_error;
	$db->connect();
	unset($dbserver, $dbname, $dbuser, $dbpass, $on_error);

	// Initialize current user
	$auth_salt = "nFSD76n9234A34%@9"; // Pick any random string of characters
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