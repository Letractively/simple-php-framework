<?PHP
	class Tag extends DBObject
	{
		function __construct($id = '')
		{
			if($id == '' || ctype_digit($id))
				parent::__construct('tags', 'id', array('name'), $id);
			else
			{
				parent::__construct('tags', 'id', array('name'), '');
				$this->select($id, 'name');
				if($this->id == '')
				{
					$this->name = $id;
					$this->insert();
				}
			}
		}
	}