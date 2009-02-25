<?PHP
    class DBObject
    {
        public $id;
        public $searchCols;

        protected $idName;
        protected $tableName;
        protected $columns = array();

        public function __construct($table_name, $id_name, $columns, $id = '')
        {
            $this->tableName = $table_name;
            $this->idName    = $id_name;

            foreach($columns as $key)
                $this->columns[$key] = '';

            if($id != '')
                $this->select($id);
        }

        public function __get($key)
        {
            if(substr($key, 0, 2) == '__')
                return htmlspecialchars($this->columns[substr($key, 2)]);
            elseif(array_key_exists($key, $this->columns))
                return $this->columns[$key];
        }

        public function __set($key, $value)
        {
            if(array_key_exists($key, $this->columns))
            {
                $this->columns[$key] = $value;
                return true;
            }
            return false;
        }

        public function select($id, $column = '')
        {
            global $db;
            if($column == '') $column = $this->idName;

            $id = mysql_real_escape_string($id, $db->db);
            $column = mysql_real_escape_string($column, $db->db);

            $db->query("SELECT * FROM {$this->tableName} WHERE `$column` = ?", $id);

            if(mysql_num_rows($db->result) == 0)
                return false;
            else
            {
                $row = mysql_fetch_array($db->result, MYSQL_ASSOC);
                $this->id = $row[$this->idName];
                foreach($row as $key => $val)
                    $this->columns[$key] = $val;
            }
        }

        public function replace()
        {
            return $this->insert('REPLACE INTO');
        }

        public function insert($cmd = 'INSERT INTO')
        {
            global $db;

            if(count($this->columns) > 0)
            {
                unset($this->columns[$this->idName]);

                $columns = "`" . join("`, `", array_keys($this->columns)) . "`";
                $values  = join(",", $this->quoteColumnVals());

                $db->query("$cmd " . $this->tableName . " ($columns) VALUES ($values)");

                $this->id = mysql_insert_id($db->db);
                return $this->id;
            }
        }

        public function update()
        {
            global $db;

            $arrStuff = array();
            unset($this->columns[$this->idName]);
            foreach($this->quoteColumnVals() as $key => $val)
                $arrStuff[] = "`$key` = $val";
            $stuff = implode(", ", $arrStuff);

            $id = mysql_real_escape_string($this->id, $db->db);

            $db->query('UPDATE ' . $this->tableName . " SET $stuff WHERE " . $this->idName . " = '" . $id . "'");
            return mysql_affected_rows($db->db); // Not always correct due to mysql update bug/feature
        }

        public function delete($id = null)
        {
            global $db;

            if(is_null($id)) $id = $this->id;

            $id = mysql_real_escape_string($id, $db->db);
            $db->query('DELETE FROM ' . $this->tableName . " WHERE `" . $this->idName . "` = '" . $id . "'");
            return mysql_affected_rows($db->db);
        }

        // Grab a large block of instantiated objects from the database using only one query.
        public function glob($sql = null)
        {
            global $db;

            if(is_null($sql))
                $sql = "SELECT * FROM {$this->tableName}";

            $objs = array();
            $rows = $db->getRows($sql);
            $class = get_class($this);
            foreach($rows as $row)
            {
                $o = new $class;
                $o->load($row);
                $o->id = $row[$this->idName];
                $objs[$o->id] = $o;
            }
            return $objs;
        }

        public function postLoad() { $this->load($_POST); }
        public function getLoad()  { $this->load($_GET); }
        public function load($arr)
        {
            if(is_array($arr))
            {
                foreach($arr as $key => $val)
                    if(array_key_exists($key, $this->columns) && $key != $this->idName)
                        $this->columns[$key] = $val;
                return true;
            }

            return false;
        }

        public function quoteColumnVals()
        {
            global $db;

            $vals = array();
            foreach($this->columns  as $key => $val)
                $vals[$key] = is_null($val) ? 'NULL' : $db->quote($val);
            return $vals;
        }

        public function addColumn($key)
        {
            if(!in_array($key, array_keys($this->columns)))
                $this->columns[$key] = '';
        }
    }

    class TaggableDBObject extends DBObject
    {
        protected $tagCol;

        public function __construct($table_name, $id_name, $columns, $id = '')
        {
            parent::__construct($table_name, $id_name, $columns, $id);
            $this->tagCol = strtolower(get_class($this) . '_id');
        }

        public function addTag($name)
        {
            global $db;

            if($this->id == '') return false;

            if(preg_match('/^\s*$/', $name) > 0) return false;

            $t = new Tag($name);
            $db->query("INSERT IGNORE {$this->tableName}2tags ({$this->tagCol}, tag_id) VALUES ('?', '?')", $this->id, $t->id);
        }

        public function removeTag($name)
        {
            global $db;
            if($this->id == '') return false;
            $t = new Tag($name);
            $db->query("DELETE FROM {$this->tableName}2tags WHERE {$this->tagCol} = '?' AND tag_id = '?'", $this->id, $t->id);
        }

        public function clearTags()
        {
            global $db;
            if($this->id == '') return false;
            $db->query("DELETE FROM {$this->tableName}2tags WHERE {$this->tagCol} = '?'", $this->id);
        }

        public function tags()
        {
            global $db;
            if($this->id == '') return false;
            $rows = $db->getRows("SELECT * FROM {$this->tableName}2tags a LEFT JOIN tags t ON a.tag_id = t.id WHERE a.{$this->tagCol} = '{$this->id}'");
            return $rows;
        }

        // Glob by tag name
        public function tagged($tag_name, $sql = '')
        {
            global $db;

            $tag = new Tag($tag_name);
            $objs = array();
            $rows = $db->getRows("SELECT b.* FROM {$this->tableName}2tags a LEFT JOIN {$this->tableName} b ON a.{$this->tagCol} = b.{$this->idName} WHERE a.tag_id = {$tag->id} $sql");
            $class = get_class($this);
            foreach($rows as $row)
            {
                $o = new $class;
                $o->load($row);
                $o->id = $row[$this->idName];
                $objs[] = $o;
            }
            return $objs;
        }
    }