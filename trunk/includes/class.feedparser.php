<?PHP
	class FeedParser
	{
		public $url;
		public $xml;
		public $username;
		public $password;
		public $channels;
		public $channel_tags;
		public $item_tags;

		function __construct($url = null, $username = null, $password = null)
		{
			$this->url      = $url;
			$this->username = $username;
			$this->password = $password;

			$this->channel_tags = array("title", "link", "description", "language", "copyright", "managingEditor", "pubDate", "lastBuildDate", "category", "generator", "docs", "cloud", "ttl", "image", "rating", "skipHours", "skipDays");
			$this->item_tags    = array("title", "link", "description", "author", "category", "comments", "enclosure", "guid", "pubDate", "source");
		}

		function parse($url = null)
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
		}

		function parseChannel($xml)
		{
			$channel = array("items" => array());
			preg_match_all('@<(\w+)(.*?)>(.*?)</\1>@ms', $xml, $matches);
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

		function parseItem($xml)
		{
			$foo  = $this->parseTag($xml, "item");
			$item = array("attrs" => $foo['attrs']);

			preg_match_all('@<(\w+)(.*?)>(.*?)</\1>@ms', $foo['value'], $matches);
			for($i = 0; $i < count($matches[1]); $i++)
			{
				if(in_array($matches[1][$i], $this->item_tags))
					$item[$matches[1][$i]] = $this->parseTag($matches[0][$i], $matches[1][$i]);
			}

			return $item;
		}

		function parseTag($xml, $tag)
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
			return $out;
		}

		function geturl($url, $username = "", $password = "")
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
	}
?>
