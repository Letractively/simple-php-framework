<?PHP
	class FeedParser
	{
		public $xml;

		public $type;
		public $version;
		
		private $channelMetaInfo;
		private $chennelDescriptor;
		private $itemSelector;
		private $itemMetaInfo;
		private $itemDescriptor;

		function __construct($url)
		{
			$this->xmlstr = $this->getURL($url);
			if($this->xml = new SimpleXMLElement($this->xmlstr))
			{
				$ns = array_shift($this->xml->getNamespaces());
				switch($ns)
				{
					case 'http://purl.org/atom/ns#':
						$this->type = 'atom';
						$this->version = '0_3';
						break;					
		            case 'http://www.w3.org/2005/Atom':
		                $this->type = 'atom';
		                $this->version = '1_0';
		                break;
					case 'http://purl.org/net/rss1.1#':
						$this->type = 'rss';
						$this->version = '1_1';
						break;
					case 'http://www.w3.org/1999/02/22-rdf-syntax-ns#':
						$this->type = 'rss';
						

				}
			}
			else
				return false;
		}

		public function parse()
		{
			print_r($this->xml);
		}

		private function getURL($url, $username = null, $password = null)
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
			{
				return false;
			}
		}
	}

	$foo = new FeedParser("http://www.sitening.com/blog/feed/atom/");
?>