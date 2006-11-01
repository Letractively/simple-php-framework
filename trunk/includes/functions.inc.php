<?PHP
	function map_index($index, $array)
	{
	    $out = array();
	    foreach($array as $row)
	        $out[] = $row[$index];
	    return $out;
	}

	// Creates a list of <option>s from the given database table
	function get_options($table, $val, $text, $default = "", $where = "", $order = "")
	{
		global $db;
		if($where != "") $where = "WHERE $where";
		if($order != "") $order = "ORDER BY $order";
		$db->query("SELECT * FROM `$table` $where $order");
		while($row = mysql_fetch_array($db->result, MYSQL_ASSOC))
		{
			if($row[$val] == $default)
				$out .= "<option value=\"" . $row[$val] . "\" selected=\"selected\">" . $row[$text] . "</option>";
			else
				$out .= "<option value=\"" . $row[$val] . "\">" . $row[$text] . "</option>";
		}
		return $out;
	}

	// Converts a date/timestamp into the specified format
	function dater($format = "", $date = "")
	{
		if($format == "") $format = "Y-m-d H:i:s";
		if($date == "") $date = time();

		if(strtotime($date) === false)
			return date($format, $date);
		else
			return date($format, strtotime($date));
	}

	// Formats a phone number as (xxx) xxx-xxxx or xxx-xxxx depending on the length.
	function format_phone($phone)
	{
		$phone = preg_replace("/[^0-9]/", "", $phone);

		if(strlen($phone) == 7)
			return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
		elseif(strlen($phone) == 10)
			return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
		else
			return $phone;
	}

	// Outputs hour, minute, am/pm dropdown boxes
	function hourmin($hid = "hour", $mid = "minute", $pid = "pm", $hval = "", $mval = "", $pval = "")
	{
		if(empty($hval)) $hval = date("h");
		if(empty($mval)) $mval = date("i");
		if(empty($pval)) $pval = date("a");

		$hours = array(12, 1, 2, 3, 4, 5, 6, 7, 9, 10, 11);
		$out = "<select name='$hid' id='$hid'>";
		foreach($hours as $hour)
			if(intval($hval) == intval($hour)) $out .= "<option value='$hour' selected>$hour</option>";
			else $out .= "<option value='$hour'>$hour</option>";
		$out .= "</select>";

		$minutes = array("00", 15, 30, 45);
		$out .= "<select name='$mid' id='$mid'>";
		foreach($minutes as $minute)
			if(intval($mval) == intval($minute)) $out .= "<option value='$minute' selected>$minute</option>";
			else $out .= "<option value='$minute'>$minute</option>";
		$out .= "</select>";
		
		$out .= "<select name='$pid' id='$pid'>";
		$out .= "<option value='am'>am</option>";
		if($pval == "pm") $out .= "<option value='pm' selected>pm</option>";
		else $out .= "<option value='pm'>pm</option>";
		
		return $out;
	}

	// Outputs month, day, and year dropdown boxes with default values and custom id/names
	function mdy($mid = "month", $did = "day", $yid = "year", $mval = "", $dval = "", $yval = "")
	{
		if(empty($mval)) $mval = date("m");
		if(empty($dval)) $dval = date("d");
		if(empty($yval)) $yval = date("Y");
		
		$months = array(1 => "January", 2 => "February", 3 => "March", 4 => "April", 5 => "May", 6 => "June", 7 => "July", 8 => "August", 9 => "September", 10 => "October", 11 => "November", 12 => "December");
		$out = "<select name='$mid' id='$mid'>";
		foreach($months as $val => $text)
			if($val == $mval) $out .= "<option value='$val' selected>$text</option>";
			else $out .= "<option value='$val'>$text</option>";
		$out .= "</select> ";

		$out .= "<select name='$did' id='$did'>";
		for($i = 1; $i <= 31; $i++)
			if($i == $dval) $out .= "<option value='$i' selected>$i</option>";
			else $out .= "<option value='$i'>$i</option>";
		$out .= "</select> ";

		$out .= "<select name='$yid' id='$yid'>";
		for($i = date("Y"); $i <= date("Y") + 2; $i++)
			if($i == $yval) $out.= "<option value='$i' selected>$i</option>";
			else $out.= "<option value='$i'>$i</option>";
		$out .= "</select>";
		
		return $out;
	}

	// Redirects user to $url
	function redirect($url = "")
	{
		if($url == "") $url = $_SERVER['PHP_SELF'];
		header("Location: $url");
		exit();
	}

	// Fixes MAGIC_QUOTES
	function fix_slashes($arr = "")
	{
		if(empty($arr))
			return;
		elseif(get_magic_quotes_gpc())
			if(!is_array($arr))
				return stripslashes($arr);
			else
				return array_map('fix_slashes', $arr);
		else
			return $arr;
	}

	// Returns the first $num words of $str
	function max_words($str, $num)
	{
		$words = explode(" ", $str);
		if(count($words) < $num)
			return $str;
		else
		{
			for($i = 0; $i < $num; $i++) $out .= $words[$i] . " ";
			return $out;
		}
	}

	// Serves an external document for download as an HTTP attachment.
	function download_document($filename, $path = "", $mimetype = "application/octet-stream")
	{
		if(substr($path, -1, 1) != "/") $path .= "/";
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Disposition: attachment; filename = $filename");
		header("Content-Length: " . filesize($path . $filename));
		header("Content-Type: $mimetype");
		echo file_get_contents($path . $filename);
	}
	
	// Creates a thumbnail from an existing image.
	// $filename is the original filename, while $tmpname is the actual
	// filesystem name (for example, the temporary filename used in a PHP upload).
	// Returns an image resource which you can then output to the browser, or
	// save to a file using imagejpg(), imagepng(), etc.
	function resize_image($filename, $tmpname, $xmax, $ymax)
	{
		$ext = explode(".", $filename);
		$ext = $ext[count($ext)-1];

		if($ext == "jpg" || $ext == "jpeg")
			$im = imagecreatefromjpeg($tmpname);
		elseif($ext == "png")
			$im = imagecreatefrompng($tmpname);
		elseif($ext == "gif")
			$im = imagecreatefromgif($tmpname);

		$x = imagesx($im);
		$y = imagesy($im);

		if($x <= $xmax && $y <= $ymax)
			return $im;

		if($x >= $y) {
			$newx = $xmax;
			$newy = $newx * $y / $x;
		}
		else {
			$newy = $ymax;
			$newx = $x / $y * $newy;
		}

		$im2 = imagecreatetruecolor($newx, $newy);
		imagecopyresized($im2, $im, 0, 0, 0, 0, floor($newx), floor($newy), $x, $y);
		return $im2;
	}	

	// Retrieves the filesize of a remote file.
	function remote_filesize($url, $user = "", $pw = "")
	{
		ob_start();
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);

		if(!empty($user) && !empty($pw))
		{
			$headers = array('Authorization: Basic ' .  base64_encode("$user:$pw"));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$ok = curl_exec($ch);
		curl_close($ch);
		$head = ob_get_contents();
		ob_end_clean();

		$regex = '/Content-Length:\s([0-9].+?)\s/';
		$count = preg_match($regex, $head, $matches);

		return isset($matches[1]) ? $matches[1] : "unknown";
	}	

	// Outputs a filesize in human readable format.
	function human_readable($val, $thousands = 0)
	{
		if($val >= 1000)
			$val = human_readable($val / 1024, ++$thousands);
		else
		{
			$unit = array('','K','M','T','P','E','Z','Y');
			$val  = round($val, 2) . $unit[$thousands] . 'B';
		}
		return $val;
	}
	
	// Tests for a valid email address and optionally tests for valid MX records, too.
	function valid_email($email, $test_mx = false)
	{
		if(eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email))
			if($test_mx)
			{
				list($username, $domain) = split("@", $email);
				return getmxrr($domain, $mxrecords);
			}
			else
				return true;
		else
			return false;
	}

	// Grabs a remote file using curl since file(http) doesn't work on all systems.
	function geturl($url, $username = "", $password = "")
	{
		if(function_exists("curl_init"))
		{
			$ch = curl_init();
			if(!empty($username) && !empty($password)) curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' .  base64_encode("$username:$password")));
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
			{
				$url = str_replace("http://", "http://$username:$password@", $url);
				$url = str_replace("https://", "https://$username:$password@", $url);
			}
			$html = file_get_contents($url);
			return $html;
		}
		else
		{
			// Cannot open url. Either install curl-php or set allow_url_fopen = true in php.ini
			return false;
		}
	}

	// Returns the user's browser info.
	// browscap.ini must be available for this to work.
	// See the PHP manual for more details.
	function browser_info()
	{
		$info    = get_browser(null, true);
		$browser = $info['browser'] . " " . $info['version'];
		$os      = $info['platform'];	
		$ip      = $_SERVER['REMOTE_ADDR'];		
		return array( "ip" => $ip, "browser" => $browser, "os" => $os );
	}

	// Sends an HTML formatted email
	function send_html_mail($to, $subject, $msg, $from = "", $plaintext = "")
	{
		if(!is_array($to)) $to = array($to);
		
		foreach($to as $address)
		{
			$boundary = uniqid(rand(), true);

			$headers  = "From: $from\n";
			$headers .= "MIME-Version: 1.0\n"; 
			$headers .= "Content-Type: multipart/alternative; boundary = $boundary\n";
			$headers .= "This is a MIME encoded message.\n\n"; 
			$headers .= "--$boundary\n" . 
			   			"Content-Type: text/plain; charset=ISO-8859-1\n" .
			   			"Content-Transfer-Encoding: base64\n\n"; 
			$headers .= chunk_split(base64_encode($plaintext)); 
			$headers .= "--$boundary\n" . 
			   			"Content-Type: text/html; charset=ISO-8859-1\n" . 
			   			"Content-Transfer-Encoding: base64\n\n";
			$headers .= chunk_split(base64_encode($msg));
			$headers .= "--$boundary--\n" . 

			mail($address, $subject, "", $headers);
		}		
	}
?>