<?PHP
	// Try to find default values from master.inc.php
	$master   = file_get_contents("../../includes/master.inc.php");
	$dbserver = match('@\$dbserver\s*=\s*("|\')(.*?)\1@ms', $master, 2);
	$dbname   = match('@\$dbname\s*=\s*("|\')(.*?)\1@ms', $master, 2);
	$dbuser   = match('@\$dbuser\s*=\s*("|\')(.*?)\1@ms', $master, 2);
	$salt     = match('@\$auth_salt\s*=\s*("|\')(.*?)\1@ms', $master, 2);

	if(!isset($_POST['server']) && !empty($dbserver)) $_POST['server'] = $dbserver;
	if(!isset($_POST['dbname']) && !empty($dbname)) $_POST['dbname'] = $dbname;
	if(!isset($_POST['dbusername']) && !empty($dbuser)) $_POST['dbusername'] = $dbuser;
	if(!isset($_POST['salt']) && !empty($salt)) $_POST['salt'] = $salt;

	if(!isset($_POST['server'])) $_POST['server'] = "localhost";
	if(!isset($_POST['dbusername'])) $_POST['dbusername'] = "root";

	if(isset($_POST['btnTables']))
	{
		$db = mysql_connect($_POST['server'], $_POST['dbusername'], $_POST['dbpassword']) or die(mysql_error());
		mysql_select_db($_POST['dbname'], $db) or die(mysql_error());
		$sql = file_get_contents("mysql.sql");
		mysql_query($sql, $db) or die(mysql_error());
		echo "<p class='alert'>Tables installed!</p>";
	}

	if(isset($_POST['btnGetTables']))
	{
		$db = mysql_connect($_POST['server'], $_POST['dbusername'], $_POST['dbpassword']) or die(mysql_error());
		mysql_select_db($_POST['dbname'], $db) or die(mysql_error());
		$arrTables = array();
		$result = mysql_query("SHOW TABLES") or die(mysql_error());
		while($row = mysql_fetch_array($result)) $arrTables[] = $row[0];
		$tables = implode(", ", $arrTables);
	}
	
	if(isset($_POST['btnAddUser']))
	{
		$db = mysql_connect($_POST['server'], $_POST['dbusername'], $_POST['dbpassword']) or die(mysql_error());
		mysql_select_db($_POST['dbname'], $db) or die(mysql_error());
		$username = mysql_real_escape_string($_POST['username'], $db);
		$password = ($_POST['type'] == "normal") ? $_POST['password'] : md5($_POST['salt'] . $_POST['password']);
		$password = mysql_real_escape_string($password, $db);
		$result = mysql_query("INSERT INTO users (username, password) VALUES ('$username', '$password')", $db);
		$msg = (mysql_affected_rows($db) == 1) ? "<p class='alert'>User added!</p>" : "<p class='warn'>User was not added! Does that user already exist?</p>";
	}

	if(isset($_POST['btnDBO']))
	{
		$db = mysql_connect($_POST['server'], $_POST['dbusername'], $_POST['dbpassword']) or die(mysql_error());
		mysql_select_db($_POST['dbname'], $db) or die(mysql_error());

		$tables = $_POST['tables'];
		$arrTables = explode(",", $tables);
		foreach($arrTables as $table)
		{
			$table = trim($table);
			$uctable = ucfirst($table);

			$arrFields = array();
			$result = mysql_query("SHOW FIELDS FROM $table", $db);
			while($row = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				if(!isset($id_field))
					$id_field = current($row);
				else
					$arrFields[] = current($row);
			}
			$fields = "'" . implode("', '", $arrFields) . "'";

			$out .= "		class $uctable extends DBObject\n";
			$out .= "		{\n";
			$out .= "			function __construct(\$id = \"\")\n";
			$out .= "			{\n";
			$out .= "				parent::__construct('$table', '$id_field', array($fields), \$id);\n";
			$out .= "			}\n";
			$out .= "		}\n";
			$out .= "\n\n";
		
			unset($id_field);
		}
	}

	function match($regex, $str, $i = 0)
	{
		if(preg_match($regex, $str, $match) == 1)
			return $match[$i];
		else
			return false;
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Framework Install</title>
	<style type="text/css" media="screen">
		.alert { color:green; font-weight:bold; }
		.warn { color:red; font-weight:bold; }
	</style>
	<script type="text/javascript" charset="utf-8">
		window.onload = function() {
			document.getElementById("type_normal").onclick = function() { document.getElementById("salt").disabled = true; };
			document.getElementById("type_md5").onclick = function() { document.getElementById("salt").disabled = false; };
		}
	</script>
</head>

<body>
	<?PHP echo $msg;?>
	<form action="" method="post">
		<h1>First...</h1>
		<p>Fill in your database information.</p>
		<table>
			<tr>
				<th>Server</th>
				<td><input type="text" name="server" value="<?PHP echo $_POST['server'];?>" id="server" /></td>
			</tr>
			<tr>
				<th>Database</th>
				<td><input type="text" name="dbname" value="<?PHP echo $_POST['dbname'];?>" id="dbname" /> <em>This database must already exist!</em></td>
			</tr>
			<tr>
				<th>Username</th>
				<td><input type="text" name="dbusername" value="<?PHP echo $_POST['dbusername'];?>" id="dbusername" /></td>
			</tr>
			<tr>
				<th>Password</th>
				<td><input type="text" name="dbpassword" value="<?PHP echo $_POST['dbpassword'];?>" id="dbpassword" /></td>
			</tr>
		</table>
		
		<h1>Then...</h1>
		<p>Install the database template.</p>
		<input type="submit" name="btnTables" value="Install SQL Template" id="btnTables" />
	

		<h1>Next...</h1>
		<p>You can add a new user</p>
		<table>
			<tr>
				<th>Username</th>
				<td><input type="text" name="username" value="" id="username" /></td>
			</tr>
			<tr>
				<th>Password</th>
				<td><input type="text" name="password" value="" id="password" /></td>
			</tr>
			<tr>
				<th>Auth Type</th>
				<td>
					<input type="radio" name="type" value="normal" id="type_normal" <?PHP if($_POST['type'] == "normal") echo "checked='checked'";?>><label for="type_normal">Normal</label>
					<input type="radio" name="type" value="md5" id="type_md5" <?PHP if($_POST['type'] == "md5" || !isset($_POST['type'])) echo "checked='checked'";?>><label for="type_md5">MD5</label>
				</td>
			</tr>
			<tr>
				<th>Auth Salt</th>
				<td><input type="text" name="salt" value="<?PHP echo $_POST['salt'];?>" id="salt" /></td>
			</tr>
			<tr>
				<th></th>
				<td><input type="submit" name="btnAddUser" value="Add User" id="btnuser" /></td>
			</tr>
		</table>
		
		<h1>Or...</h1>
		<p>
			Get a list of tables to generate DBObjects from.<br/>
			<input type="submit" name="btnGetTables" value="Get List" id="btnGetTables" />
			<input type="text" name="tables" value="<?PHP echo $tables;?>" id="tables" style="width:70%;" />
		</p>
			
		<p>
			Once the text box has the tables you want, click this button to generate the proper code.<br/>
			<input type="submit" name="btnDBO" value="Create DBObject classes from above tables" id="btnDBO" />
		</p>
	</form>

	<?PHP if(!empty($out)) { ?>
		<textarea style="width:100%; height:400px;"><?PHP echo $out;?></textarea>
	<?PHP } ?>
</body>
</html>