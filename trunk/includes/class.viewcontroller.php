<?PHP
	/* C A L L  B A C K S */
	// vc_sort_<sort>(ASC | DESC)
	// vc_tr(table, row)
	// vc_extra(table, col, row)
	// vc_format_<column>(table, val, row)

	// Examples:
	// function vc_format_phone($table, $val, $row) { return format_phone($val); }
	// function vc_format_email($table, $val, $row) { return ($val) ? "<a href='mailto:$val'>$val</a>" : ""; }

	/* P A G I N G */
	/* <p class="paging"><?PHP echo $vc->prev() . " <strong>Page {$vc->page} of {$vc->numPages}</strong> " . $vc->next(); ?></p> */

	// This is a rather unfortunate name and has nothing to do with the
	// traditional MVC "V" or "C" meaning.
	class ViewController
	{
		public $bodySQL;   // Override the default SELECT query
		public $caption;   // Table caption
		public $columns;   // Array of column names and labels
		public $countSQL;  // Overrride the default COUNT(*) query
		public $direction;
		public $dontSort;  // Array of column names to not sort on
		public $html;      // Final output
		public $numPages;
		public $page;
		public $perPage;
		public $searchCols; // Array of columns to search on. Defaults to $columns
		public $sort;       // "column_name,desc"
		public $table;
		public $tableAttr;  // Inserted into <table> tag. Let's you set ID and class
		public $tfoot;      // HTML to insert into <tfoot></tfoot>
		public $url;
		public $where;      // Custom WHERE clause appended to default SELECT and COUNT(*) queries

		function __construct($table = "", $columns = "")
		{
			$this->table      = $table;
			$this->columns    = is_array($columns) ? $columns : array();
			$this->searchCols = $this->columns;
			$this->url        = $_SERVER['PHP_SELF'];
			$this->perPage    = 25;
			$this->dontSort   = array();
			$this->tableAttr  = "";

			// Load any previous settings...
			$this->load();

			// Then overwrite them with new ones if given...
			if(isset($_GET['perPage'])) $this->perPage = intval($_GET['perPage']);
			if(isset($_GET['page']))    $this->page = intval($_GET['page']);
			if(isset($_GET['sort']))
			{
				list($this->sort, $this->direction) = explode(",", $_GET['sort']);
				$this->sort = key_exists($this->sort, $this->columns) ? $this->sort : '';
				$this->direction = (strtolower($this->direction) != "asc") ? 'desc' : 'asc';
			}

			// Save the settings
			$this->save();
		}

		function out()
		{
			$this->html = "";

			$this->tableHead();
			$this->tableBody();
			$this->tableFoot();

			return $this->html;
		}

		function tableHead()
		{
			$this->html .= "<table {$this->tableAttr}>";
			
			if($this->caption != "") $this->html .= "<caption>" . $this->caption . "</caption>";
			$this->html .= "<thead>";
			$this->html .= "<tr>";

			foreach($this->columns as $key => $val)
			{
				$this->html .= "<th>";

				if(substr($key, 0, 6) == "extra_" || in_array($key, $this->dontSort))
				{
					$this->html .= $val;
				}
				else
				{
					if($this->sort == $key)
					{
						$d     = ($this->direction == "" || $this->direction == "desc") ? "asc" : "desc";
						$arrow = ($this->direction == "" || $this->direction == "desc") ? "&darr;" : "&uarr;";
						$this->html .= "<a href='{$this->url}?sort=$key,$d'>$val</a><span class='arrow'>$arrow</span>";
					}
					else
					{
						$d  = ($this->direction == "desc") ? "desc" : "asc";
						$this->html .= "<a href='{$this->url}?sort=$key,$d'>$val</a>";
					}
				}

				$this->html .= "</th>";
			}

			$this->html .= "</tr>";
			$this->html .= "</thead>";
		}

		function tableFoot()
		{
			$this->html .= "<tfoot>{$this->tfoot}</tfoot></table>";
		}

		function tableBody()
		{
			global $db;

			$this->html .= "<tbody>";
			
			// Do the paging...
			$result = $this->queryCount("SELECT COUNT(*) FROM {$this->table} a {$this->where}");
			$numRecords     = mysql_result($result, 0, 0);
			$this->numPages = ceil($numRecords / $this->perPage);
			if($this->page > $this->numPages) $this->page = $this->numPages;
			if($this->page < 1) $this->page = 1;
			$start = $this->perPage * ($this->page - 1);
			if($this->numPages < 1) $this->numPages = 1;

			// Order by...
			if(function_exists("vc_sort_{$this->sort}"))
				$order = " ORDER BY " . call_user_func("vc_sort_{$this->sort}", $this->direction) . " ";
			elseif($this->sort != "")
				$order = " ORDER BY " . mysql_real_escape_string($this->sort, $db->db) . " "  . mysql_real_escape_string($this->direction, $db->db);
			else
				$order = "";

			// Grab the data...
			$result = $this->queryBody("SELECT * FROM {$this->table} a {$this->where} $order LIMIT $start, {$this->perPage}");

			// And build the table...
			while($row = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				$trAttr = function_exists("vc_tr") ? call_user_func("vc_tr", $this->table, $row) : "";

				$this->html .= "<tr$trAttr>";
				foreach(array_keys($this->columns) as $col)
				{
					if(substr($col, 0, 6) == "extra_")
					{
						$extra = function_exists("vc_extra") ? call_user_func("vc_extra", $this->table, $col, $row) : "&nbsp;";
						$this->html .= "<td>$extra</td>";
					}
					elseif(function_exists("vc_format_$col"))
					{
						$this->html .= "<td>" . call_user_func("vc_format_$col", $this->table, $row[$col], $row) . "</td>";
					}
					else
					{
						$this->html .= "<td>" . $row[$col] . "</td>";
					}
				}
				$this->html .= "</tr>";
			}
			$this->html .= "</tbody>";
		}

		function save()
		{
			$arr = array("perPage"   => $this->perPage,
						 "page"      => $this->page,
						 "sort"      => $this->sort,
						 "direction" => $this->direction);

			$var = "vc" . md5($this->url . $this->table);
			$_SESSION[$var] = serialize($arr);
		}

		function load()
		{
			$var = "vc" . md5($this->url . $this->table);
			$arr = unserialize($_SESSION[$var]);

			if($arr['perPage'] != "")   $this->perPage   = $arr['perPage'];
			if($arr['page'] != "")      $this->page      = $arr['page'];
			if($arr['sort'] != "")      $this->sort      = $arr['sort'];
			if($arr['direction'] != "") $this->direction = $arr['direction'];
		}

		function prev()
		{
			$page = $this->page - 1;
			if($this->page > 1)
				return "<a href='{$this->url}?page=$page'>&#171; Previous</a>";
			else
				return "&#171; Previous";
		}

		function next()
		{
			$page = $this->page + 1;
			if($this->page < $this->numPages)
				return "<a href='{$this->url}?page=$page'>Next &#187;</a>";
			else
				return "Next &#187;";
		}

		function queryCount($sql)
		{
			global $db;
			$query = ($this->countSQL == "") ? $sql : $this->countSQL;
			$db->query($query);
			return $db->result;
		}

		function queryBody($sql)
		{
			global $db;
			$query = ($this->bodySQL == "") ? $sql : $this->bodySQL;
			$db->query($query);
			return $db->result;
		}
	}