<?PHP
	if(isset($_POST['btnTables']))
	{
		$db = mysql_connect($_POST['server'], $_POST['dbusername'], $_POST['dbpassword']) or die(mysql_error());
		mysql_select_db($_POST['dbname'], $db) or die(mysql_error());
		$sql = file_get_contents("mysql.sql");
		mysql_query($sql, $db) or die(mysql_error());
		echo "<h1>Tables installed!</h1>";
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
		$password = mysql_real_escape_string($_POST['password'], $db);
		mysql_query("INSERT INTO users (username, password) VALUES ('$username', '$password')", $db);
		echo "<h1>User added!</h1>";
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
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<title>untitled</title>
	
</head>

<body>
	<form action="<?PHP echo $_SERVER['PHP_SELF'];?>" method="post">
		<table>
			<caption>MySQL Connection Information</caption>
			<tr>
				<th>Server</th>
				<td><input type="text" name="server" value="<?PHP echo $_POST['server'];?>" id="server" /></td>
			</tr>
			<tr>
				<th>Username</th>
				<td><input type="text" name="dbusername" value="<?PHP echo $_POST['dbusername'];?>" id="dbusername" /></td>
			</tr>
			<tr>
				<th>Password</th>
				<td><input type="text" name="dbpassword" value="<?PHP echo $_POST['dbpassword'];?>" id="dbpassword" /></td>
			</tr>
			<tr>
				<th>Database</th>
				<td><input type="text" name="dbname" value="<?PHP echo $_POST['dbname'];?>" id="dbname" /></td>
			</tr>
			<tr>
				<th></th>
				<td><input type="submit" name="btnTables" value="Install Tables" id="btnTables" /></td>
			</tr>
		</table>
	
		<br/><br/>
	
		<table>
			<caption>Add User</caption>
			<tr>
				<th>Username</th>
				<td><input type="text" name="username" value="" id="username" /></td>
			</tr>
			<tr>
				<th>Password</th>
				<td><input type="text" name="password" value="" id="password" /></td>
			</tr>
			<tr>
				<th></th>
				<td><input type="submit" name="btnAddUser" value="Add User" id="btnuser" /></td>
			</tr>
		</table>
		
		<br/>
		
		<p>
			<input type="submit" name="btnGetTables" value="Get list of tables for DBObject" id="btnGetTables" />
			<input type="text" name="tables" value="<?PHP echo $tables;?>" id="tables" style="width:70%;" />
		</p>

			
		<p><input type="submit" name="btnDBO" value="Create DBObject classes from above tables" id="btnDBO" /></p>
	</form>

	<?PHP if(!empty($out)) { ?>
		<textarea style="width:100%; height:400px;"><?PHP echo $out;?></textarea>
	<?PHP } ?>
</body>
</html>