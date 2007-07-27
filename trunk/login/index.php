<?PHP
	require "../includes/master.inc.php";

	// Kick out user if already logged in
	if($auth->ok()) redirect("/");

	// Try to log in...
	$alert = "";
	if(!empty($_POST['username']))
	{
		$auth->login($_POST['username'], $_POST['password']);
		if($auth->ok())
			redirect("../");
		else
			$alert = "<div class='alert'>We're sorry, you have entered an incorrect username and password. Please try again.</div>";
	}

	$username = isset($_POST['username']) ? $_POST['username'] : "";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" href="../styles/screen.css" type="text/css" media="screen" title="Screen" charset="utf-8" />
	<title>index</title>	
</head>

<body>
	<form action="" method="post">
		<?PHP echo $alert;?>
		<p><label for="username">Username:</label> <input type="text" name="username" value="<?PHP echo $username;?>" id="username" /></p>
		<p><label for="password">Password:</label> <input type="password" name="password" value="" id="password" /></p>
		<p><input type="submit" name="btnlogin" value="Login" id="btnlogin" /></p>
	</form>
</body>
</html>