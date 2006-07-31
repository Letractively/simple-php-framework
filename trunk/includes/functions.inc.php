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
	function redirect($url = "/")
	{
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
	function maxwords($str, $num)
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
?>