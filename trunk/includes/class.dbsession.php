<?PHP
	class DBSession
	{
		private static $link = null;

		public static function register()
		{
			ini_set('session.save_handler', 'user');
			session_set_save_handler(array('DBSession', 'open'), array('DBSession', 'close'), array('DBSession', 'read'), array('DBSession', 'write'), array('DBSession', 'destroy'), array('DBSession', 'gc'));
		}

		public static function open()
		{
			if(is_null($GLOBALS['db']) || !is_resource($GLOBALS['db']->db))
				return false;

			self::$link = $GLOBALS['db']->db;
			return true;
	    }
	
		public static function close()
		{
			self::$link = null;
			return true;
		}

		public static function read($id)
		{
			$id = mysql_real_escape_string($id, self::$link);
			$result = mysql_query("SELECT `data` FROM `sessions` WHERE `id` = '$id'", self::$link);
			return (mysql_num_rows($result) > 0) ? mysql_result($result, 0, 0) : '';
		}
	
		public static function write($id, $data)
		{
			$id   = mysql_real_escape_string($id, self::$link);
			$data = mysql_real_escape_string($data, self::$link);
			$time = time();
			mysql_query("REPLACE INTO `sessions` (`id`, `data`, `expires_on`) VALUES ('$id', '$data', '$time')", self::$link);
			return true;
		}
	
		public static function destroy($id)
		{
			$id = mysql_real_escape_string($id, self::$link);
			mysql_query("DELETE FROM `sessions` WHERE `id` = '$id'", self::$link);
			return true;
		}
	
		public static function gc($max)
		{
			$time = time() - $max;
			mysql_query("DELETE FROM `sessions` WHERE `expires_on` < '$time'", self::$link);
			return true;
		}
	}