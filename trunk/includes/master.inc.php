<?PHP
	if(preg_match('/\.com|\.net|\.org/', $_SERVER['SERVER_NAME']) === 0)
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

	require_once($docroot . "/includes/class.dbobject.php");
	require_once($docroot . "/includes/class.objects.php");
	require_once($docroot . "/includes/class.misc.php");
	require_once($docroot . "/includes/class.database.php");
	require_once($docroot . "/includes/class.auth.php");
	require_once($docroot . "/includes/functions.inc.php");

	$db = new Database($dbserver, $dbuser, $dbpass, $dbname);
	$db->onError = $onError;
	$db->connect();
	
	$auth = new Auth();
?>