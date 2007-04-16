<?PHP
	require("../includes/master.inc.php");
	if($auth->level != "admin") redirect("../");
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
	<p>If you can read this then you are logged in as an admin user.</p>
</body>
</html>