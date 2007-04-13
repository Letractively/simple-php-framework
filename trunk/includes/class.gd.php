<?PHP
	// GD class handles opening images from disk, saving or outputting to
	// the browser, and resizing them. Can work with jpeg, png, or gif.

	class GD
	{
		public $img;
		public $width;
		public $height;
		
		function __construct($data = null, $ext = null)
		{
			if(is_resource($data))
				$this->loadResource($data);
			elseif(file_exists($data))
				$this->loadFile($data, $ext);
		}
		
		function loadResource($img)
		{	
			$this->img = $img;
			$this->width = imagesx($this->img);
			$this->height = imagesy($this->img);
		}
		
		// You can pass in the extension in case the filename doesn't have one
		// (as is the case for PHP file uploads)
		function loadFile($filename, $ext = null)
		{
			if(!file_exists($filename) || !is_readable($filename)) return false;
			
			if(is_null($ext))
				$ext = array_pop(explode(".", $filename));

			$ext = strtolower($ext);

			if(($ext == "jpg" || $ext == "jpeg") && (imagetypes() & IMG_JPG)) $func = "imagecreatefromjpeg";
			elseif($ext == "png" && (imagetypes() & IMG_PNG)) $func = "imagecreatefrompng";
			elseif($ext == "gif" && (imagetypes() & IMG_GIF)) $func = "imagecreatefromgif";
			else return false;
			
			$img = call_user_func($func, $filename);
			if(!is_resource($img)) return false;

			$this->img = $img;
			$this->width = imagesx($this->img);
			$this->height = imagesy($this->img);

			return true;
		}
		
		function saveAs($filename, $type = "jpg")
		{
			if($type == "jpg" && (imagetypes() & IMG_JPG))
				return imagejpeg($this->img, $filename);
			elseif($type == "png" && (imagetypes() & IMG_PNG))
				return imagepng($this->img, $filename);
			elseif($type == "gif" && (imagetypes() & IMG_GIF))
				return imagegif($this->img, $filename);
			else
				return false;
		}

		// Output file to browser
		function output($type = "jpg")
		{
			if($type == "jpg" && (imagetypes() & IMG_JPG))
			{
				header("Content-Type: image/jpeg");
				imagejpeg($this->img);
			}
			elseif($type == "png" && (imagetypes() & IMG_PNG))
			{
				header("Content-Type: image/png");
				imagepng($this->img);
			}
			elseif($type == "gif" && (imagetypes() & IMG_GIF))
			{
				header("Content-Type: image/gif");
				imagegif($this->img);
			}			
			else
				return false;
		}

		// Resizes an image and maintains aspect ratio. By default,
		// it scales to the largest side, but you can ovveride that
		// by setting $force to "x" or "y".
		function scale($new_width, $new_height, $force = "")
		{
			if(($this->width >= $this->height && $force == "") || ($force == "x"))
				$new_height = $new_width * ($this->height / $this->width);
			else
				$new_width = ($this->width / $this->height) * $new_height;

			return $this->resize($new_width, $new_height);
		}
		
		// Resizes an image to an exact size
		function resize($width, $height)
		{
			if(!is_resource($this->img)) return false;

			$dest = imagecreatetruecolor($width, $height);
			if(imagecopyresized($dest, $this->img, 0, 0, 0, 0, $width, $height, $this->width, $this->height))
			{
				$this->img = $dest;
				return true;
			}
			else
				return false;
		}		
	}
?>