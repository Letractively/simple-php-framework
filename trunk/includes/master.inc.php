<?PHP
	$local_servers = array();
	$staging_servers = array();

	if(in_array($_SERVER['SERVER_NAME'], $local_servers))
	{
		// Testing
		$dbserver = "localhost";
		$dbname   = "";
		$dbuser   = "root";
		$dbpass   = "";
		$onError  = "die";

		ini_set('display_errors', "1");
		ini_set('error_reporting', E_ALL ^ E_NOTICE);
	}
	elseif(in_array($_SERVER['SERVER_NAME'], $staging_servers))
	{
		// Staging
		$dbserver = "";
		$dbname   = "";
		$dbuser   = "";
		$dbpass   = "";
		$onError  = "die";

		ini_set('display_errors', "1");
		ini_set('error_reporting', E_ALL ^ E_NOTICE);
	}
	else
	{
		// Production
		$dbserver = "";
		$dbname   = "";
		$dbuser   = "";
		$dbpass   = "";
		$onError  = "";

		ini_set('display_errors', "0");
	}

	session_start();

	$docroot = realpath(dirname(__FILE__) . "/../");

	require($docroot . "/includes/class.dbobject.php");
	require($docroot . "/includes/class.objects.php");
	require($docroot . "/includes/class.misc.php");
	require($docroot . "/includes/class.database.php");
	require($docroot . "/includes/class.auth.php");
	require($docroot . "/includes/functions.inc.php");

	$db = new Database($dbserver, $dbuser, $dbpass, $dbname);
	$db->onError = $onError;
	$db->connect();

	$auth_salt = "^AS%FSA%^Ddsfj"; // Pick any random string of characters
	$auth = new Auth();
?>