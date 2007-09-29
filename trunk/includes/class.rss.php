<?PHP

	/* E X A M P L E -----------------------------------------------
		$feed = new RSS();
		$feed->title       = "RSS Feed Title";
		$feed->link        = "http://website.com";
		$feed->description = "Recent articles on your website.";

		$db->query($query);
		$result = $db->result;
		while($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$item = new RSSItem();
			$item->title = $title;
			$item->link  = $link;
			$item->setPubDate($create_date); 
			$item->description = $html;
			$feed->addItem($item);
		}
		echo $feed->serve();
		
		Or, you can eliminate the while loop above by using the
		built-in loadRecordset() method. It takes a MySQL result
		and the column names you want to use for the title, link,
		description, and pub-date.
	---------------------------------------------------------------- */

	class RSS
	{
		public $title;
		public $link;
		public $description;
		public $language = "en-us";
		public $pubDate;
		public $items;
		public $tags;

		function __construct()
		{
			$this->items = array();
			$this->tags  = array();
		}

		function addItem($item)
		{
			$this->items[] = $item;
		}

		function setPubDate($when)
		{
			if(strtotime($when) == false)
				$this->pubDate = date("D, d M Y H:i:s O", $when);
			else
				$this->pubDate = date("D, d M Y H:i:s O", strtotime($when));
		}

		function getPubDate()
		{
  			if(empty($this->pubDate))
				return date("D, d M Y H:i:s O");
			else
				return $this->pubDate;
		}

		function addTag($tag, $value)
		{
			$this->tags[$tag] = $value;
		}
		
		function loadRecordset($result, $title, $link, $description, $pub_date)
		{
			while($row = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				$item = new RSSItem();
				$item->title       = $row[$title];
				$item->link        = $row[$link];
				$item->description = "<![CDATA[ " . $row[$description] . "]]>";
				$item->setPubDate($row[$pub_date]);
				$this->addItem($item);
			}
		}

		function out()
		{
			$out  = $this->header();
			$out .= "<channel>\n";
			$out .= "<title>" . $this->title . "</title>\n";
			$out .= "<link>" . $this->link . "</link>\n";
			$out .= "<description>" . $this->description . "</description>\n";
			$out .= "<language>" . $this->language . "</language>\n";
			$out .= "<pubDate>" . $this->getPubDate() . "</pubDate>\n";

			foreach($this->tags as $key => $val) $out .= "<$key>$val</$key>\n";
			foreach($this->items as $item) $out .= $item->out();

			$out .= "</channel>\n";
			
			$out .= $this->footer();

			$out = str_replace("&", "&amp;", $out);

			return $out;
		}
		
		function serve($contentType = "application/xml")
		{
			$xml = $this->out();
			header("Content-type: $contentType");
			echo $xml;
		}

		function header()
		{
			$out  = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
			$out .= '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";
			return $out;
		}

		function footer()
		{
			return '</rss>';
		}
	}

	class RSSItem
	{
		var $title;
		var $link;
		var $description;
		var $pubDate;
		var $guid;
		var $tags;
		var $attachment;
		var $length;
		var $type;		

		function RSSItem()
		{ 
			$this->tags = array();
		}

		function setPubDate($when)
		{
			if(strtotime($when) == false)
				$this->pubDate = date("D, d M Y H:i:s O", $when);
			else
				$this->pubDate = date("D, d M Y H:i:s O", strtotime($when));
		}

		function getPubDate()
		{
			if(empty($this->pubDate))
				return date("D, d M Y H:i:s O");
			else
				return $this->pubDate;
		}

		function addTag($tag, $value)
		{
			$this->tags[$tag] = $value;
		}

		function out()
		{
			$out .= "<item>\n";
			$out .= "<title>" . $this->title . "</title>\n";
			$out .= "<link>" . $this->link . "</link>\n";
			$out .= "<description><![CDATA[ " . $this->description . " ]]></description>\n";
			$out .= "<pubDate>" . $this->getPubDate() . "</pubDate>\n";

			if(empty($this->guid)) $this->guid = $this->link;
			$out .= "<guid>" . $this->guid . "</guid>\n";

			if($this->attachment != "")
				$out .= "<enclosure url='{$this->attachment}' length='{$this->length}' type='{$this->type}' />\n";

			foreach($this->tags as $key => $val) $out .= "<$key>$val</$key>\n";
			$out .= "</item>\n";
			return $out;
		}

		function enclosure($url, $type, $length)
		{
			$this->attachment = $url;
			$this->type       = $type;
			$this->length     = $length;
		}
	}