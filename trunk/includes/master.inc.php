<?PHP
	// Determine our absolute document root
	define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));

	// Global include files
	require DOC_ROOT . '/includes/functions.inc.php'; // __autoload() is contained in this file
	require DOC_ROOT . '/includes/class.objects.php';
	
	// Load our config settings
	Config::load();

	// Connect to database (does not actually open the connection until it's needed)
	$db = new Database();

	// Initialize our session...
	if(Config::$useDBSessions === true)
		DBSession::register();
	session_start();

	// Initialize current user
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