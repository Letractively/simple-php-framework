<?PHP
	// Simple wrapper class for GD
	class GD
	{
		public $im     = null;
		public $width  = null;
		public $height = null;
		public $type   = null;
		public $mime   = null;

		function __construct($data = null, $ext = null)
		{
			if(is_resource($data) && get_resource_type($data) == 'gd')
				return $this->loadResource($data);
			elseif(file_exists($data) && is_readable($data))
				return $this->loadFile($data);
			else
				return false;
		}

		function loadResource($im)
		{	
			if(!is_resource($im) || !get_resource_type($im) == 'gd') return false;

			$this->im     = $im;
			$this->width  = imagesx($im);
			$this->height = imagesy($im);

			return true;
		}
		
		function loadFile($filename)
		{
			if(!file_exists($filename) || !is_readable($filename)) return false;

			$info = getimagesize($filename);
			$this->width  = $info[0];
			$this->height = $info[1];
			$this->type   = image_type_to_extension($info[2], false);
			$this->mime   = $info['mime'];
			
			if($this->type == 'jpeg' && (imagetypes() & IMG_JPG))
				$im = imagecreatefromjpeg($filename);
			elseif($this->type == 'png' && (imagetypes() & IMG_PNG))
				$im = imagecreatefrompng($filename);
			elseif($this->type == 'gif' && (imagetypes() & IMG_GIF))
				$im = imagecreatefromgif($filename);
			else
				return false;
			return $this->loadResource($im);
		}
		
		function saveAs($filename, $type = 'jpg')
		{
			if($type == 'jpg' && (imagetypes() & IMG_JPG))
				return imagejpeg($this->im, $filename);
			elseif($type == 'png' && (imagetypes() & IMG_PNG))
				return imagepng($this->im, $filename);
			elseif($type == 'gif' && (imagetypes() & IMG_GIF))
				return imagegif($this->im, $filename);
			else
				return false;
		}

		// Output file to browser
		function output($type = 'jpg')
		{
			if($type == 'jpg' && (imagetypes() & IMG_JPG))
			{
				header("Content-Type: image/jpeg");
				imagejpeg($this->im);
				return true;
			}
			elseif($type == 'png' && (imagetypes() & IMG_PNG))
			{
				header("Content-Type: image/png");
				imagepng($this->im);
				return true;
			}
			elseif($type == 'gif' && (imagetypes() & IMG_GIF))
			{
				header("Content-Type: image/gif");
				imagegif($this->im);
				return true;
			}
			else
				return false;			
		}

		// Resizes an image and maintains aspect ratio.
		function scale($new_width = null, $new_height = null)
		{
			if(!is_null($new_width) && is_null($new_height))
				$new_height = $new_width * $this->height / $this->width;
			elseif(is_null($new_width) && !is_null($new_height))
				$new_width = $this->width / $this->height * $new_height;
			elseif(!is_null($new_width) && !is_null($new_height))
			{
				if($this->width > $this->height)
					$new_width = $this->width / $this->height * $new_height;
				else
					$new_height = $new_width * $this->height / $this->width;
			}
			else
				return false;
			
			return $this->resize($new_width, $new_height);
		}
		
		// Resizes an image to an exact size
		function resize($new_width, $new_height)
		{
			$dest = imagecreatetruecolor($new_width, $new_height);

			// Transparency fix contributed by Google Code user 'desfrenes'
			imagealphablending($dest, false);  
			imagesavealpha($dest, true);

			if(imagecopyresampled($dest, $this->im, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height))
			{
				$this->im = $dest;
				$this->width = imagesx($this->im);
				$this->height = imagesy($this->im);
				return true;
			}

			return false;
		}
		
		function crop($x, $y, $w, $h)
		{
			$dest = imagecreatetruecolor($w, $h);
			
			if(imagecopyresampled($dest, $this->im, 0, 0, $x, $y, $w, $h, $w, $h))
			{
				$this->im = $dest;
				$this->width = imagesx($this->im);
				$this->height = imagesy($this->im);
				return true;
			}
			
			return false;
		}
	}