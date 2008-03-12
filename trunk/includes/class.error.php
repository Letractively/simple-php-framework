<?PHP
	class Error
	{
		public $errors;
		public $style;
		
		function __construct($style = "border:1px solid red;")
		{
			$this->errors = array();
			$this->style = $style;
		}
		
		function __tostring()
		{
			return $this->ul("warn");
		}

		function ok()
		{
			return (count($this->errors) == 0);
		}
		
		function add($id, $msg)
		{
			if($id != "")
			{
				if(isset($this->errors[$id]) && !is_array($this->errors[$id]))
					$this->errors[$id] = array($msg);
				else
					$this->errors[$id][] = $msg;
			}
		}
		
		function delete($id)
		{
			unset($this->errors[$id]);
		}
		
		function msg($id)
		{
			echo $this->errors[$id];
		}
		
		function css($header = true)
		{
			$out = "";
			if(count($this->errors) > 0)
			{
				if($header) $out .= '<style type="text/css" media="screen">';
				$out .= "#" . implode(", #", array_keys($this->errors)) . " { {$this->style} }";
				if($header) $out .= '</style>';
			}
			echo $out;
		}

		function ul($class = "warn")
		{
			if(count($this->errors) == 0) return "";

			$out = "<ul class='$class'>";
			foreach($this->errors as $error)
			 	$out .= "<li>" . implode("</li><li>", $error) . "</li>";
			$out .= "</ul>";

			return $out;
		}
		
		// Variable tests
		
		function blank($val, $id, $name = null)
		{
			if(trim($val) == "")
			{
				if(is_null($name)) $name = ucwords($id);
				$this->add($id, "$name cannot be left blank.");
				return false;
			}
			
			return true;
		}
		
		function range($val, $id, $lower, $upper, $name = null)
		{
			if($val < $lower || $val > $upper)
			{
				if(is_null($name)) $name = ucwords($id);
				$this->add($id, "$name must be between $lower and $upper.");
				return false;
			}
			
			return true;
		}

		function length($val, $id, $lower, $upper, $name = null)
		{
			if(strlen($val) < $lower)
			{
				if(is_null($name)) $name = ucwords($id);
				$this->add($id, "$name must be at least $lower characters.");
				return false;
			}
			elseif(strlen($val) > $upper)
			{
				if(is_null($name)) $name = ucwords($id);
				$this->add($id, "$name cannot be more than $upper characters long.");
				return false;
			}
			
			return true;
		}
		
		function passwords($id, $pass1, $pass2)
		{
			if($pass1 !== $pass2)
			{
				$this->add($id, "The passwords you entered do not match.");
				return false;
			}
			
			return true;
		}
		
		function email($val, $id)
		{
			if(!eregi("^([_a-z0-9+-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $val))
			{
				$this->add($id, "The email address you entered is not valid.");
				return false;
			}
			
			return true;
		}

		function date($val, $id)
		{
			if(strtotime($val) === false)
			{
				$this->add($id, "Please enter a valid date");
				return false;
			}
			
			return true;
		}

		function phone($val, $id)
		{
			$val = preg_replace("/[^0-9]/", "", $val);
			if(strlen($val) != 7 && strlen($val) != 10)
			{
				$this->add($id, "Please enter a valid 7 or 10 digit phone number.");
				return false;
			}
			
			return true;
		}

		function upload($val, $id)
		{
			if(!is_uploaded_file($val['tmp_name']) || !is_readable($val['tmp_name']))
			{
				$this->add($id, "Your file was not uploaded successfully. Please try again.");
				return false;
			}
			
			return true;
		}

		function zip($val, $id, $name = null)
		{
			if(preg_match('/^[0-9]{5}$/', $val) == 0)
			{
				if(is_null($name)) $name = ucwords($id);
				$this->add($id, "Please enter a valid, 5-digit zip code.");
				return false;
			}
			return true;			
		}

		function nan($val, $id, $name = null)
		{
			if(preg_match('/^-?[0-9]*\.?[0-9]*$/', $val) == 0)
			{
				if(is_null($name)) $name = ucwords($id);
				$this->add($id, "$name must be a number.");
				return false;
			}
			return true;
		}

		function url($val, $id, $name = null)
		{
			$info = @parse_url($val);
			if(($info === false) || ($info['scheme'] != "http" && $info['scheme'] != "https") || ($info['host'] == ""))
			{
				if(is_null($name)) $name = ucwords($id);
				$this->add($id, "$name is not a valid URL.");
				return false;
			}
			return true;
		}
	}