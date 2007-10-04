<?PHP
	// This class is still being tested
	class Loop
	{
		private $index;
		private $elements;
		private $numElements;

		function __construct()
		{
			$this->index       = 0;
			$this->elements    = func_get_args();
			$this->numElements = func_num_args();
		}

		function __tostring()
		{
			return (string) $this->get();
		}

		function get()
		{
			if($this->numElements == 0) return null;

			$val = $this->elements[$this->index];

			if(++$this->index >= $this->numElements)
				$this->index = 0;

			return $val;
		}
	}

	// Example:
	// $color = new Loop("white", "black");
	// 
	// echo "<tr color='$color'/>";
	// echo "<tr color='$color'/>";
	// echo "<tr color='$color'/>";