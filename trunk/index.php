<?PHP
	require 'includes/master.inc.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>index</title>
	<link rel="stylesheet" href="/styles/screen.css" type="text/css" media="screen" title="Screen" charset="utf-8" />
</head>

<body>
	<h1>Simple PHP Framework</h1>
	<p>This is your home page.</p>
	<?PHP if($auth->ok()) : ?>
	<p>You are logged in as <?PHP echo $auth->username; ?>. <a href='/logout/'>Logout</a>.</p>
	<?PHP else : ?>
	<p>You are not logged in. <a href='/login/'>Login</a>.</p>
	<?PHP endif; ?>
</body>
</html>