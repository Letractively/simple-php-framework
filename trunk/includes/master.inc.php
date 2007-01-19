<?PHP
	if(strpos($_SERVER['DOCUMENT_ROOT'], ".com") === false)
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
		$onError  = "email";

		ini_set('display_errors', "0");
	}

	session_start();

	$docroot = dirname(__FILE__);
	
	require_once($docroot . "/class.dbobject.php");
	require_once($docroot . "/class.misc.php");
	require_once($docroot . "/class.database.php");
	require_once($docroot . "/class.auth.php");
	require_once($docroot . "/functions.inc.php");

	$db = new Database($dbserver, $dbuser, $dbpass, $dbname);
	$db->onError = $onError;
	$db->connect();
	
	$auth = new Auth();
?>