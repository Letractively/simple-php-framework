<?PHP
	require_once("includes/master.inc.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" href="styles/yui.css" type="text/css" media="screen" title="Style Reset" charset="utf-8" />
	<link rel="stylesheet" href="styles/screen.css" type="text/css" media="screen" title="Screen" charset="utf-8" />
	<title>index</title>	
</head>

<body>
	<p>This is your home page.</p>
	<p>You are <?PHP echo $auth->ok() ? "logged in as {$auth->username}. <a href='logout/'>Logout</a>." : "not logged in. <a href='login/'>Login</a>."; ?></p>
</body>
</html>