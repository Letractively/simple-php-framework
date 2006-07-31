<?PHP
	class Debugger
	{
		var $fp;
		
		function Debugger($logfile)
		{
			$this->fp = fopen($logfile, 'a');
			set_error_handler(array($this, "handler"));
		}
		
		function handler($errno, $errstr, $errfile, $errline, $errcontext)
		{
			if($errno == 2 || $errno == 32)
			{
				global $auth;
				global $db;
			
				$date   = date("Y-m-d H:i:s");
				$custom = array("auth"    => $auth,
				                "post"    => $_POST,
				                "get"     => $_GET,
				                "ip"      => $_SERVER['REMOTE_ADDR'],
				                "last_query" => $db->lastQuery());
				$data = array($date, $errno, $errstr, $errfile, $errline, $custom);

				fwrite($this->fp, serialize($data) . "\n");
			}
		}
	}
?>