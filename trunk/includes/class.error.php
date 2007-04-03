<?PHP
	// This class has not been tested yet.
	// Tyler - 4/3/07
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
			return $this->ul();
		}

		function ok()
		{
			return (count($this->errors) == 0);
		}
		
		function add($id, $msg)
		{
			$this->errors[$id] = $msg;
		}
		
		function delete($Id)
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
			if(count($this->errors) == 0)
			{
				if($header) $out .= '<style type="text/css" media="screen">';
				$out .= "#" . implode(", #", array_keys($this->errors));
				if($header) $out .= '</style>';
			}
			echo $out;
		}

		function ul($class = "warn", $echo = true)
		{
			if(count($this->errors) == 0) return "";
			$out = "<ul class='$class'><li>" . implode("</li><li>", $this->errors) . "</li>";
			if($echo)
				echo $out;
			else
				return $out;
		}
	}
?>