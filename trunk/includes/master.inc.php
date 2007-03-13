<?PHP
	if(preg_match('/\.com|\.net|\.org/', $_SERVER['SERVER_NAME']) === 0)
	{
		// Testing (It's important that testing comes first if you're using install.php)
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
	require_once($docroot . "/includes/class.authmd5.php");
	require_once($docroot . "/includes/functions.inc.php");

	$db = new Database($dbserver, $dbuser, $dbpass, $dbname);
	$db->onError = $onError;
	$db->connect();

	$auth_salt = "SDyiyisd2"; // Pick any random string of characters
	$auth = new Auth();
?>