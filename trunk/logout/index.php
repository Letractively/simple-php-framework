<?PHP
	require "../includes/master.inc.php";
	$auth = new Auth();
	$auth->logout();
	redirect("../");