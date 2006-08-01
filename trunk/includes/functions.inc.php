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

	// Creates a random, simple math problem to be used as a CAPTCHA instead of the standard, hard-to-read distorted images.
	// By no means is this method 100% foolproof. Far from it. However it's a faily easy way to weed out bots and still remain
	// accessible to all users.
	// Returns the question as a string and stores the answer in $_SESSION['captcha_answer']
	function math_captcha()
	{
		$num1  = rand(1, 10);
		$num2  = rand(1, 10);
		$op    = rand(1, 3);

		$words   =  array( array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10), array("zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten") );

		if($op == 1)
		{
			$answer   = $num1 + $num2;
			$num1     = $words[rand(0, 1)][$num1];
			$num2     = $words[rand(0, 1)][$num2];
			$question = (rand(1, 2) == 1) ? "What is $num1 plus $num2?" : "What does $num1 + $num2 equal?";
		}
		elseif($op == 2)
		{
			$answer  = $num1 * $num2;
			$num1     = $words[rand(0, 1)][$num1];
			$num2     = $words[rand(0, 1)][$num2];
			$question = (rand(1, 2) == 1) ? "What is $num1 times $num2?" : "What does $num1 * $num2 equal?";
		}
		elseif($op == 3)
		{
			if($num1 < $num2)
			{
				$temp = $num1;
				$num1 = $num2;
				$num2 = $temp;
			}
			$answer   = $num1 - $num2;
			$num1     = $words[rand(0, 1)][$num1];
			$num2     = $words[rand(0, 1)][$num2];
			$question = (rand(1, 2) == 1) ? "What is $num1 minus $num2?" : "What does $num1 - $num2 equal?";
		}
		
		$_SESSION['captcha_question'] = $question;
		$_SESSION['captcha_answer']   = $answer;

		return $question;
	}
?>