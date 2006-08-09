<?PHP
	class Form
	{
		var $els;
		var $errorClass  = "";		
		var $errorPrefix = "";
		var $errorSuffix = "";
		var $incomplete  = false;
		var $method      = "";
		var $numErrors;
		var $required    = "";
		
		function Form()
		{
			$this->els = array();
		}
		
		function add($type, $name, $dvalue = "", $class = "", $extra = "")
		{
			$el = array();
			$el['type']    = $type;
			$el['name']    = $name;
			$el['dvalue']  = $dvalue;
			$el['class']   = $class;
			$el['extra']   = $extra;
			$el['options'] = array();
			$this->els[$name] = $el;
			$this->els[$name]['value'] = $this->getVal($name);
		}
		
		function out($name)
		{
			$dvalue  = $this->els[$name]['dvalue'];
			$value   = $this->getVal($name);
			$class   = $this->els[$name]['class'];
			$extra   = $this->els[$name]['extra'];
			$options = $this->els[$name]['options'];
			$checked = $this->els[$name]['checked'];
			
			if(isset($this->els[$name]['error']))
			{
				$prefix = $this->errorPrefix;
				$suffix = $this->errorSuffix;
				$class .= " " . $this->errorClass;
			}
			
			// If needed, we could insert a callback function here
			// which checks for an error. If so, it returns an error
			// message to be displayed in $suffix.
			//
			// EXAMPLE:
			// if(function_exists("form_suffix_$name"))
			//   $suffix = call_user_func("form_suffix_$name", $this-els[$name]);
			// 
			// function form_suffix_foo($el) {
			//   if($el['error'] != "")
			//     return "some error message";
			// }

			switch($this->els[$name]['type'])
			{ 
				case "text":
					$out = "$prefix<input type='text' name='$name' value='$value' id='$name' class='$class' $extra/>$suffix";
					break;

				case "password":
					$out = "$prefix<input type='password' name='$name' value='$value' id='$name' class='$class' $extra/>$suffix";
					break;
				
				case "hidden":
					$out = "$prefix<input type='hidden' name='$name' value='$value' id='$name' class='$class' $extra/>$suffix";
					break;
				
				case "submit":
					$out = "$prefix<input type='submit' name='$name' value='$dvalue' id='$name' class='$class' $extra/>$suffix";
					break;
				
				case "button":
					$out = "$prefix<input type='button' name='$name' value='$dvalue' id='$name' class='$class' $extra/>$suffix";
					break;
				
				case "checkbox":
					if($checked == true) 
						$checked = "checked='checked'";
					if($this->method != "")
						$checked = ($value == $dvalue) ? "checked='checked'" : "";
					$out = "$prefix<input type='checkbox' name='$name' value='$dvalue' id='$name' class='$class' $extra $checked/>$suffix";
					break;
				
				case "file":
					$out = "$prefix<input type='file' name='$name' value='$value' id='$name' class='$class' $extra/>$suffix";
					break;
				
				case "textarea":
					$out = "$prefix<textarea name='$name' id='$name' class='$class' $extra/>$value</textarea>$suffix";
					break;
				
				case "select":
					$out = "$prefix<select name='$name' id='$name' class='$class' $extra>";
					foreach($options as $key => $val)
					{
						$selected = ($key == $value) ? "selected='selected'" : "";
						$out     .= "<option value='$key' $selected>$val</option>\n";
					}
					$out .= "</select>$suffix";
					break;
				
				case "radio":
				 	// $out = "<input type='radio' name='$name' value='$value' id='$name' class='$class' $extra/>";
				 	break;
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
				$this->incomplete = true;
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
			foreach($this->els as $el)
				if($el['error'] != "")
					$errors[] = $el['error'];
			return $errors;
		}
	}
?>