<?PHP
	require_once("../includes/master.inc.php");	
	$auth = new Auth();
	$auth->logout();
	redirect("/");
?>