<?PHP
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
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Disposition: attachment; filename = $filename");
		header("Content-Length: " . filesize($pathto . $filename));
		header("Content-Type: $mimetype");
		echo file_get_contents($pathto . $filename);
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
	function geturl($url)
	{
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$html = curl_exec($ch);
		curl_close($ch);
		return $html;
	}
?>