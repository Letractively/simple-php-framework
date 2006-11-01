<?PHP
	if(strpos($_SERVER['DOCUMENT_ROOT'], ".com") === false)
	{
		// Testing
		$dbserver = "localhost";
		$dbname   = "";
		$dbuser   = "root";
		$dbpass   = "";
		$onError  = "die";

		$docroot = $_SERVER['DOCUMENT_ROOT'] . "/";

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
		$onError  = "email";

		$docroot = $_SERVER['DOCUMENT_ROOT'] . "/";

		ini_set('display_errors', "0");
	}

	session_start();
	
	require_once($docroot . "/includes/class.dbobject.php");
	require_once($docroot . "/includes/class.misc.php");
	require_once($docroot . "/includes/class.database.php");
	require_once($docroot . "/includes/class.auth.php");
	require_once($docroot . "/includes/class.vc.php");
	require_once($docroot . "/includes/functions.inc.php");

	$db = new Database($dbserver, $dbuser, $dbpass, $dbname);
	$db->onError = $onError;
	$db->connect();
	
	$auth = new Auth();
?>