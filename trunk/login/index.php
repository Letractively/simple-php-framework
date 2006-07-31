<?PHP
	require_once("../includes/master.inc.php");

	// Kick out user if already logged in
	if($auth->ok()) redirect("/");

	$alert = "";

	// Try to log in...
	if(!empty($_POST['username']))
	{
		$auth->login($_POST['username'], $_POST['password']);
		if($auth->ok())
			redirect("/");
		else
			$alert = "<div class='alert'>We're sorry, you have entered an incorrect username and password combination. Please try again.</div>";
	}
?>

<form action="/login/" method="post">
	<table>
		<tr>
			<td>Username:</td>
			<td><input type="text" name="username" value="<?PHP echo $_POST['username'];?>" id="username" /></td>
		</tr>
		<tr>
			<td>Password:</td>
			<td><input type="password" name="password" value="" id="password" /></td>
		</tr>
		<tr>
			<td colspan="2" class="right"><input type="submit" name="btnlogin" value="Login" id="btnlogin" /></td>
		</tr>
	</table>
</form>