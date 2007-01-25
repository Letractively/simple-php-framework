<?PHP
	// Please read the comments in class.form.php
	require_once("includes/class.form.php");

	// Setup the form
	$f = new Form();
	$f->add("text", "username");
	$f->add("password", "password");
	$f->add("password", "password2");
	$f->add("hidden", "secret", "123");
	$f->add("button", "foobar", "Does Nothing");
	$f->add("checkbox", "agree", 1);
	$f->add("file", "thefile");
	$f->add("textarea", "comments", "Your comments go here.");
	$f->add("select", "gender", "na");
	$f->els['gender']['options'] = array("m" => "Male", "f" => "Female", "o" => "Other");
	$f->add("submit", "btnsubmit", "Create Account");
	$f->required = "username,password,password2";
	$f->errorClass = "error";
	$f->errorPrefix = "!";
	if(isset($_POST['btnsubmit'])) $f->method = "post";
	
	// Callback to perform validation
	function form_check_password2($el)
	{
		global $f;
		if($f->getVal('password') != $f->getVal('password2'))
		{
			$f->els['password']['error'] = "error";
			$_POST['password']  = "";
			$_POST['password2'] = "";
			return "error";
		}
	}
	
	if("post" == $f->method)
	{
		if($f->validate())
			echo "Valid!";
		elseif($f->incomplete)
			echo "Please complete the required fields.";
		elseif($f->els['password']['error'] != "")
			echo "Your passwords do not match.";
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" href="/styles/yui.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<link rel="stylesheet" href="/styles/screen.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<title>index</title>
	<style type="text/css" media="screen">
		.error { background-color:yellow; }
	</style>
</head>

<body>

<form action="<?PHP echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
	Username: <?PHP $f->out("username");?> <br/>
	Password: <?PHP $f->out("password");?> <br/>
	Password again: <?PHP $f->out("password2");?> <br/>
	<?PHP
		$f->out("secret"); echo "<br/>";
		$f->out("foobar"); echo "<br/>";
		$f->out("agree"); echo "<br/>";
		$f->out("thefie"); echo "<br/>";
		$f->out("comments"); echo "<br/>";
		$f->out("gender"); echo "<br/>";
		$f->out("btnsubmit"); echo "<br/>";
	?>
</form>

</body>
</html>