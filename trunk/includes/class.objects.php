<?PHP
	class Users extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('users', 'user_id', array('username', 'password', 'level', 'email'), $id);
		}
	}
?>