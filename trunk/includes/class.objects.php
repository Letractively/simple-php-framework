<?PHP
	// Stick your DBOjbect subclasses in here (to help keep things tidy).

	class User extends DBObject
	{
		function __construct($id = '')
		{
			parent::__construct('users', 'user_id', array('username', 'password', 'level', 'email'), $id);
		}
	}