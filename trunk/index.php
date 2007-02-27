<?PHP
	require_once("includes/master.inc.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>index</title>
	<link rel="stylesheet" href="styles/screen.css" type="text/css" media="screen" title="Screen" charset="utf-8" />
</head>

<body>
	<p>This is your home page.</p>
	<p>You are <?PHP echo $auth->ok() ? "logged in as {$auth->username}. <a href='logout/'>Logout</a>." : "not logged in. <a href='login/'>Login</a>."; ?></p>
	<?PHP if(!$auth->ok()) : ?>
	<p>If you haven't done so already, you may want to install the users table into the database. You can do so <a href='_masters/install/install.php'>here</a>.</p>
	<?PHP endif; ?>
</body>
</html>