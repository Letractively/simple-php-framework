<?PHP
	$local_servers = array("local.server.site", "another.server.site");
	$staging_servers = array("staging.server.site");
	$production_servers = array("production.server.site");

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
		// Testing
		$dbserver = "localhost";
		$dbname   = "framework";
		$dbuser   = "root";
		$dbpass   = "";
		$on_error = "die";

		ini_set('display_errors', "1");
		ini_set('error_reporting', E_ALL ^ E_NOTICE);
	}
	else
		die("Where am I? (You need to setup your server names in master.inc.php)");

	session_start();

	$docroot = realpath(dirname(__FILE__) . "/../");

	include $docroot . "/includes/class.dbobject.php";
	include $docroot . "/includes/class.objects.php";
	include $docroot . "/includes/class.misc.php";
	include $docroot . "/includes/class.database.php";
	include $docroot . "/includes/class.auth.php";
	include $docroot . "/includes/class.error.php";
	// include $docroot . "/includes/class.gd.php";
	// include $docroot . "/includes/class.vc.php";
	// include $docroot . "/includes/class.pager.php";
	// include $docroot . "/includes/class.rss.php";
	include $docroot . "/includes/functions.inc.php";

	$db = new Database($dbserver, $dbuser, $dbpass, $dbname);
	$db->onError = $on_error;
	$db->connect();
	unset($dbserver, $dbname, $dbuser, $dbpass, $on_error);

	$auth_salt = "nFSD76n9234A34%@9"; // Pick any random string of characters
	$auth = new Auth();

	$Error = new Error();

	if(get_magic_quotes_gpc())
	{
		$_POST    = fix_slashes($_POST);
		$_GET     = fix_slashes($_GET);
		$_REQUEST = fix_slashes($_REQUEST);
	}