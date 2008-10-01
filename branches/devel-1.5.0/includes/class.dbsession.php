<?PHP
    class DBSession
    {
        private static $db = null;

        public static function register()
        {
            ini_set('session.save_handler', 'user');
            session_set_save_handler(array('DBSession', 'open'), array('DBSession', 'close'), array('DBSession', 'read'), array('DBSession', 'write'), array('DBSession', 'destroy'), array('DBSession', 'gc'));
        }

        public static function open()
        {
			self::$db = Database::getDatabase(true);
			return self::$db->isConnected();
        }

        public static function close()
        {
            return true;
        }

        public static function read($id)
        {
			self::$db->query("SELECT `data` FROM `sessions` WHERE `id` = '?'", $id);
			return self::$db->hasRows() ? self::$db->getValue() : '';
        }

        public static function write($id, $data)
        {
			self::$db->query("REPLACE INTO `sessions` (`id`, `data`, `updated_on`) VALUES ('?', '?', '?')", $id, $data, time());
            return (mysql_affected_rows(self::$db->db) == 1);
        }

        public static function destroy($id)
        {
            self::$db->query("DELETE FROM `sessions` WHERE `id` = '?'", $id);
            return (mysql_affected_rows(self::$db->db) == 1);
        }

        public static function gc($max)
        {
            self::$db->query("DELETE FROM `sessions` WHERE `updated_on` < '?'", time() - $max);
            return true;
        }
    }