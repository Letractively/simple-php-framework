<?PHP
	# Currently, this code is shaky at best. It's really just an exercise to see
	# how easy/hard it is to write a feed parser. I'm doing this because Magpie
	# is bloated and LastRSS has bugs. This implementation is aimed at RSS 2.0.

	# Example:
	# $fp = new FeedParser("http://domain.com/rss.xml");
	# print_r($fp->channels); // Data will be stored in channels array
	#
	# foreach($fp as $item) // You can also iterate over the (0th channel's) items
	# 	print_r($item);

	# TODO: I need a better way to handle tag attributes.
	# TODO: Modularize the parsing so it can handle different feed types.
	# TODO: Support namespaces.
	# TODO: Ahem. Testing!

	class FeedParser implements Iterator
	{
		public $url;
		public $xml;
		public $username;
		public $password;
		public $channels;
		public $channel_tags;
		public $item_tags;
		public $no_attrs = true; // Don't return tag attributes

		function __construct($url = null, $username = null, $password = null)
		{
			$this->url      = $url;
			$this->username = $username;
			$this->password = $password;

			$this->channel_tags = array("title", "link", "description", "language", "copyright", "managingEditor", "pubDate", "lastBuildDate", "category", "generator", "docs", "cloud", "ttl", "image", "rating", "skipHours", "skipDays");
			$this->item_tags    = array("title", "link", "description", "author", "category", "comments", "enclosure", "guid", "pubDate", "source");
			
			if(isset($this->url))
				$this->parse();
		}

		public function parse($url = null, $username = null, $password = null)
		{
			if(isset($url)) $this->url = $url;
			if(isset($username)) $this->username = $username;
			if(isset($password)) $this->password = $password;

			$this->channels = array();

			// Grab the xml
			$this->xml = $this->geturl($this->url, $this->username, $this->password);
			if($this->xml === false) return false;

			// Grab the channels
			preg_match_all('@<channel.*?>(.*?)</channel>@ms', $this->xml, $matches);
			$channels = $matches[1];

			// Parse each channel
			foreach($channels as $channelXML)
				$this->channels[] = $this->parseChannel($channelXML);
			
			reset($this->channels[0]["items"]);
			
			return $this->channels;
		}

		private function parseChannel($xml)
		{
			$channel = array("items" => array());
			preg_match_all('@<([\w:]+)(.*?)>(.*?)</\1>@ms', $xml, $matches);
			for($i = 0; $i < count($matches[1]); $i++)
			{
				// Get the channel's tags
				if(in_array($matches[1][$i], $this->channel_tags))
					$channel[$matches[1][$i]] = $this->parseTag($matches[0][$i], $matches[1][$i]);

				// Get the items
				if($matches[1][$i] == "item")
					$channel["items"][] = $this->parseItem($matches[0][$i], "item");
			}
			return $channel;
		}

		private function parseItem($xml)
		{
			$foo  = $this->parseTag($xml, "item");
			$item = array("attrs" => $foo['attrs']);

			if(!$this->no_attrs) $foo = $foo["value"];
			
			foreach($this->item_tags as $tag)
			{
				if(preg_match("@<$tag(.*?)>(.*?)</$tag>@ms", $foo, $matches) == 1)
					$item[$tag] = $this->parseTag($matches[0], $tag);
			}
			
			if($this->no_attrs) unset($item["attrs"]);

			return $item;
		}

		private function parseTag($xml, $tag)
		{
			if(preg_match("@<$tag(.*?)>(.*?)</$tag>@ms", $xml, $matches) == 1)
			{
				$out = array("value" => $matches[2], "attrs" => array());

				preg_match_all('@(\w+=\'.*?\')@ms', $matches[1], $attrs);
				for($j = 0; $j < count($attrs[0]); $j++)
				{
					list($key, $val) = explode("=", $attrs[1][$j]);
					$out["attrs"][$key] = trim($val, "'");
				}

				preg_match_all('@(\w+=".*?")@ms', $matches[1], $attrs);
				for($j = 0; $j < count($attrs[0]); $j++)
				{
					list($key, $val) = explode("=", $attrs[1][$j]);
					$out["attrs"][$key] = trim($val, '"');
				}
			}
			
			if($this->no_attrs) $out = $out["value"];
			
			return $out;
		}

		private function geturl($url, $username = "", $password = "")
		{
			if(function_exists("curl_init"))
			{
				$ch = curl_init();
				if(!empty($username) && !empty($password))
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' .  base64_encode("$username:$password")));
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				$html = curl_exec($ch);
				curl_close($ch);
				return $html;
			}
			elseif(ini_get("allow_url_fopen") == true)
			{
				if(!empty($username) && !empty($password))
					$url = str_replace("://", "://$username:$password@", $url);
				$html = file_get_contents($url);
				return $html;
			}
			else
				return false;

		}

		// These functions handle the Iterator implementation
		public function rewind()
		{
			reset($this->channels[0]["items"]);
		}
		
		public function current()
		{
			return current($this->channels[0]["items"]);
		}
		
		public function key()
		{
			return key($this->channels[0]["items"]);
		}
		
		public function next()
		{
			return next($this->channels[0]["items"]);
		}
		
		public function valid()
		{
			return $this->current() !== false;
		}
	}

	$fp = new FeedParser($_GET['url']);
	print_r($fp->channels);
	// foreach($fp as $item)
	// {
	// 	$item['description'] = str_replace(array("<![CDATA[", "]]>"), "", $item['description']);
	// 	echo "<h3>{$item['title']}</h3>";
	// 	echo "<p>{$item['description']}</p>";
	// }
?>