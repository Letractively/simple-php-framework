<?PHP
	require "../../includes/master.inc.php";

	$server     = isset($_POST['server']) ? $_POST['server'] : "";
	$dbname     = isset($_POST['dbname']) ? $_POST['dbname'] : "";
	$dbusername = isset($_POST['dbusername']) ? $_POST['dbusername'] : "";
	$dbpassword = isset($_POST['dbpassword']) ? $_POST['dbpassword'] : "";
	$tables     = "";
	$msg        = "";

	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		$db = mysql_connect($_POST['server'], $_POST['dbusername'], $_POST['dbpassword']) or die(mysql_error());
		mysql_select_db($_POST['dbname'], $db) or die(mysql_error());

		// Install SQL template
		if(isset($_POST['btnTables']))
		{
			$sql = file_get_contents("mysql.sql");
			mysql_query($sql, $db) or die(mysql_error());
			echo "<p class='alert'>Tables installed!</p>";
		}

		// Add new user
		if(isset($_POST['btnAddUser']))
		{
			$username = mysql_real_escape_string($_POST['username'], $db);
			$password = $auth->makePassword($_POST['password']);
			$password = mysql_real_escape_string($password, $db);
			$level = mysql_real_escape_string($_POST['level'], $db);
			$result = mysql_query("INSERT INTO users (username, password, level) VALUES ('$username', '$password', '$level')", $db);
			$msg = (mysql_affected_rows($db) == 1) ? "<p class='alert'>User added!</p>" : "<p class='warn'>User was not added! Does that user already exist?</p>";
		}

		// Get list of tables
		if(isset($_POST['btnGetTables']))
		{
			$arrTables = array();
			$result = mysql_query("SHOW TABLES") or die(mysql_error());
			while($row = mysql_fetch_array($result)) $arrTables[] = $row[0];
			$tables = implode(", ", $arrTables);
		}

		// Create DBObject code from table names
		if(isset($_POST['btnDBO']))
		{
			$out = "";
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

				$out .= "class $uctable extends DBObject\n";
				$out .= "{\n";
				$out .= "	function __construct(\$id = \"\")\n";
				$out .= "	{\n";
				$out .= "		parent::__construct('$table', '$id_field', array($fields), \$id);\n";
				$out .= "	}\n";
				$out .= "}\n";
				$out .= "\n\n";
		
				unset($id_field);
			}
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Simple PHP Framework Install Helper</title>
	<style type="text/css" media="screen">
		#help { position:absolute; right:5px; top:5px; font-weight:bold; }
		.alert { color:green; font-weight:bold; }
		.warn { color:red; font-weight:bold; }
	</style>

	<?PHP if(isset($_POST['btnDBO'])) : ?>
	<script type="text/javascript" charset="utf-8">
		window.onload = function() {
			document.getElementById("dbo").focus();
			document.getElementById("dbo").select();
		}
	</script>
	<?PHP endif; ?>
</head>

<body>
	<?PHP echo $msg;?>
	<form action="" method="post">
		<h1>First...</h1>
		<p>Fill in your database information.</p>
		<table>
			<tr>
				<th>Server</th>
				<td><input type="text" name="server" value="<?PHP echo $server;?>" id="server" /></td>
			</tr>
			<tr>
				<th>Database</th>
				<td><input type="text" name="dbname" value="<?PHP echo $dbname;?>" id="dbname" /> <em>This database must already exist!</em></td>
			</tr>
			<tr>
				<th>Username</th>
				<td><input type="text" name="dbusername" value="<?PHP echo $dbusername;?>" id="dbusername" /></td>
			</tr>
			<tr>
				<th>Password</th>
				<td><input type="text" name="dbpassword" value="<?PHP echo $dbpassword;?>" id="dbpassword" /></td>
			</tr>
		</table>
		
		<h1>Then...</h1>
		<p>Install the database template. This will load the users table structure into the database. You can see the SQL we're using <a href='mysql.sql' target="_blank">here</a>.</p>
		<input type="submit" name="btnTables" value="Install SQL Template" id="btnTables" />
	

		<h1>Next...</h1>
		<p>You can add a new user.</p>
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
				<th>Type</th>
				<td>
					<input type="radio" name="level" value="admin" id="level_admin" checked="checked"/> <label for="level_admin">Admin</label>
					<input type="radio" name="level" value="user" id="level_user"/> <label for="level_user">User</label>
				</td>
			</tr>
			<?PHP if($auth->useHash) : ?>
			<tr>
				<th>Auth Salt</th>
				<td><input type="text" name="salt" value="<?PHP echo AUTH_SALT;?>" id="salt" /> <em>This should be set in master.inc.php</td>
			</tr>
			<?PHP endif; ?>
			<tr>
				<th></th>
				<td><input type="submit" name="btnAddUser" value="Add User" id="btnuser" /></td>
			</tr>
		</table>
		
		<h1>You can also...</h1>
		<p>
			Get a list of tables to generate DBObjects from.<br/>
			<input type="submit" name="btnGetTables" value="Get List" id="btnGetTables" />
			<input type="text" name="tables" value="<?PHP echo $tables;?>" id="tables" style="width:70%;" />
		</p>
			
		<p>
			Once the text box has the tables you want, click this button to generate the skeleton code which you can place into <em>class.objects.php</em><br/>
			<input type="submit" name="btnDBO" value="Create DBObject classes from above tables" id="btnDBO" />
		</p>
	</form>

	<?PHP if(!empty($out)) { ?>
		<textarea style="width:100%; height:400px;" name="dbo" id="dbo"><?PHP echo $out;?></textarea>
	<?PHP } ?>
	
	<div id="help">
		<a href='http://code.google.com/p/simple-php-framework/' target="_blank">Help!</a>
	</div>
</body>
</html>