<?PHP
	class RSSLog
	{
		var $title;
		var $log_name;
		var $path;
		
		var $link        = "";
		var $description = "";
		var $language    = "en-us";
		
		function __construct($title, $log_name = "")
		{
			$this->title = $title;
			
			if($log_name == "")
			{
				$this->log_name = preg_replace("/[^a-zA-Z0-9_ -]/", "", $this->title);
				$this->log_name = str_replace(" ", "_", $this->log_name);
			}
			else
				$this->log_name = $log_name;
		}
		
		function log($subject, $msg, $link = "")
		{
			if($subject == "") $subject = $_SERVER['PHP_SELF'];

			$out .= "<item>\n";
			$out .= "<title>$subject</title>\n";
			$out .= ($link == "") ? "" : "<link>$link</link>\n";
			$out .= "<description><![CDATA[$msg]]></description>\n";
			$out .= "<pubDate>" . date("D, d M Y H:i:s") . " GMT</pubDate>\n";
			$out .= "<guid>" . md5($msg) . "</guid>\n";
			$out .= "</item>\n";

			$handle = @fopen($this->path . $this->log_name . ".rsslog", 'a');
			if($handle !== false)
			{
				fwrite($handle, $out);
				fclose($handle);
			}
		}
		
		function serve()
		{
			$out  = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
			$out .= '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";
			$out .= "<channel>\n";
			$out .= "<title>" . $this->title . "</title>\n";
			$out .= "<link>" . $this->link . "</link>\n";
			$out .= "<description>" . $this->description . "</description>\n";
			$out .= "<language>" . $this->language . "</language>\n";
			$out .= "<pubDate>" . date("D, d M Y H:i:s") . " GMT</pubDate>\n";
			$out .= file_get_contents($this->path . $this->log_name . ".rsslog");
			$out .= "</channel>\n";
			$out .= "</rss>";

			header("Content-type: $contentType");
			echo $out;
		}
	}
?>