<?PHP
	require '../includes/master.inc.php';

	// Kick out user if already logged in.
	if($auth->ok()) redirect(WEB_ROOT);

	// Try to log in...
	if(!empty($_POST['username']))
	{
		$auth->login($_POST['username'], $_POST['password']);
		if($auth->ok())
			redirect(WEB_ROOT);
		else
			$Error->add("We're sorry, you have entered an incorrect username and password. Please try again.", 'username');
	}

	$username = isset($_POST['username']) ? $_POST['username'] : "";
	$username = htmlspecialchars($username);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Login</title>
	<!-- <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/reset-fonts-grids/reset-fonts-grids.css"> -->
	<!-- <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/base/base-min.css"> -->
	<link rel="stylesheet" href="<?PHP WEBROOT();?>styles/screen.css" type="text/css" media="screen" title="Screen" charset="utf-8" />
	<?PHP $Error->css(); ?>
</head>

<body>
	<form action="<?PHP WEBROOT();?>login/" method="post">
		<?PHP echo $Error; ?>
		<p><label for="username">Username:</label> <input type="text" name="username" value="<?PHP echo $username;?>" id="username" /></p>
		<p><label for="password">Password:</label> <input type="password" name="password" value="" id="password" /></p>
		<p><input type="submit" name="btnlogin" value="Login" id="btnlogin" /></p>
	</form>
</body>
</html>