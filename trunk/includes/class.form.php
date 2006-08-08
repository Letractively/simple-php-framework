<?PHP
	class Form
	{
		var $els;
		var $errorClass  = "";		
		var $errorPrefix = "";
		var $method      = "";
		var $numErrors;
		var $required    = "";
		
		function Form($method = "post")
		{
			$this->els      = array();
			$this->method  = $method;
		}
		
		function add($type, $name, $value = "", $class = "", $extra = "")
		{
			$el = array();
			$el['type']   = $type;
			$el['name']   = $name;
			$el['dvalue'] = $value;
			$el['class']  = $class;
			$el['extra']  = $extra;
			$this->els[$name] = $el;
			$this->els[$name]['value'] = $this->getVal($name);
		}
		
		function out($name)
		{
			$value = $this->getVal($name);
			$class = $this->els[$name]['class'];
			$extra = $this->els[$name]['extra'];
			
			if(isset($this->els[$name]['error']))
			{
				$prefix = $this->errorPrefix;
				$class .= " " . $this->errorClass;
			}

			switch($this->els[$name]['type'])
			{ 
				case "text":
					$out = "$prefix<input type='text' name='$name' value='$value' id='$name' class='$class' $extra/>";
					break;

				case "password":
					$out = "$prefix<input type='password' name='$name' value='$value' id='$name' class='$class' $extra/>";
					break;
				
				case "hidden":
					$out = "$prefix<input type='hidden' name='$name' value='$value' id='$name' class='$class' $extra/>";
					break;
				
				case "submit":
					$out = "$prefix<input type='submit' name='$name' value='$value' id='$name' class='$class' $extra/>";
					break;
				
				case "button":
					$out = "$prefix<input type='button' name='$name' value='$value' id='$name' class='$class' $extra/>";
					break;
				
				case "checkbox":
					$out = "$prefix<input type='checkbox' name='$name' value='$value' id='$name' class='$class' $extra/>";
					break;
				
				case "file":
					$out = "$prefix<input type='file' name='$name' value='$value' id='$name' class='$class' $extra/>";
					break;
				
				case "textarea":
					$out = "$prefix<textarea name='$name' id='$name' class='$class' $extra/>$value</textarea>";
					break;

				// case "radio":
				// 	$out = "<input type='radio' name='$name' value='$value' id='$name' class='$class' $extra/>";
				// 	break;
			}
			echo $out;
		}
		
		function getVal($name)
		{
			if($this->method == "post")
				return $_POST[$name];
			elseif($this->method == "get")
				return $_GET[$name];
			else
				return $this->els[$name]['dvalue'];
		}
		
		function validate()
		{
			$this->numErrors = 0;
			foreach(array_keys($this->els) as $name)
			{
				if($this->check($name) == false) $this->numErrors++;
			}
			return ($this->numErrors == 0);
		}
		
		function check($name)
		{
			$required = explode(",", $this->required);
			if(in_array($name, $required) && ($this->getVal($name) == ""))
			{
				$this->els[$name]['error'] = "required";
				return false;
			}
			
			if(function_exists("form_check_$name"))
			{
				$this->els[$name]['error'] = call_user_func("form_check_$name", $this->els[$name]);
				if($this->els[$name]['error'] != "")
					return false;
			}
			
			return true;
		}
		
		function errors()
		{
			$errors = array();
			foreach($this->els as $el) $errors[] = $el['error'];
			return $errors;
		}
	}
?>