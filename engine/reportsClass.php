<?php
class Reports {
	public $uid;
	public $username;
	public $email;
	public $title;
	public $description;
	public $query;
	public $type;
	public $frequency;
	public $csv;
		
	protected static $table_name = "reports";
	
	public static function find_by_sql($sql="") {
		global $database;
		
		$result_set = $database->query($sql);
		$object_array = array();
		while ($row = $database->fetch_array($result_set)) {
			global $database;
			$object_array[] = self::instantiate($row);
		}
		return $object_array;
	}


	private static function instantiate($record) {
		
	$object = new self;
		foreach ($record as $attribute=>$value) {
			if ($object->has_attribute($attribute)) {
				$object->$attribute = $value;
			}
		}
		return $object;
	}
	
	private function has_attribute($attribute) {
		// get_object_vars returns as associative array with all attributes
		// (incl. private ones!) as the keys and their current values as the value
		$object_vars = $this->attributes($this) ;
		
		// we don't care about the value, we just want to know if the key exists
		// will return true or false
		return array_key_exists($attribute, $object_vars);
	}
	
	private function attributes($attribute) {
		return get_object_vars($this);
	}
	
	public function fetchColumns() {
		$sql = "SHOW COLUMNS FROM " . self::$table_name;
		
		$result_array = self::find_by_sql($sql);
		
		foreach ($result_array AS $result) {
			$columns[] = $result->Field;
		}
		return $columns;
	}
	
	/* --------------------------------------------- */
	
	function userReports() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE username = '" . $this->username . "' ";
		$sql .= "ORDER BY uid DESC";
		
		$result_array = self::find_by_sql($sql);
		
		return !empty($result_array) ? array_shift($result_array) : false;
	}
	
	function allReportsByFrequency() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE frequency = '" . $this->frequency . "' ";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function reportsToRunToday() {
		global $database;
		
		$dayOfWeek = DATE('N');
		//$dayOfWeek = 4;
		
		echo $dayOfWeek;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE frequency = 'daily' ";
		
		if ($dayOfWeek == '5') {
			$sql .= "OR frequency = 'weekly' ";
		}
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function reasonName($strUID) {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " WHERE uid = " . $strUID . " LIMIT 1";
		
		$result_array = self::find_by_sql($sql);
		
//		return !empty($result_array) ? array_shift($result_array) : false;
		return $result_array[0];

	}
	
	function create() {
		global $database;
		
		$sqlCheck  = "SELECT * FROM " . self::$table_name . " ";
		$sqlCheck .= "WHERE username = '" . $database->escape_value($this->username) . "' ";
		$sqlCheck .= "AND frequency = '" . $database->escape_value($this->frequency) . "'";
		
		$exisitingReports = self::find_by_sql($sqlCheck);
				
		if (count($exisitingReports) == 1) {
			$sqlUpdate  = "UPDATE " . self::$table_name . " set ";
			$sqlUpdate .= "csv = '" . $database->escape_value($this->csv) . "', ";
			$sqlUpdate .= "graph = '" . $database->escape_value($this->graphs) . "', ";
			$sqlUpdate .= "sqlQuery = '" . $database->escape_value($this->sqlQuery) . "', ";
			$sqlUpdate .= "email = '" . $database->escape_value($this->email) . "' ";
			$sqlUpdate .= "WHERE username = '" . $database->escape_value($this->username) . "' ";
			$sqlUpdate .= "AND frequency = '" . $database->escape_value($this->frequency) . "' ";
			$sqlUpdate .= "LIMIT 1";
									
			$database->query($sqlUpdate);
		} elseif (count($exisitingReports) == 0) {
			$sqlCreate  = "INSERT INTO " . self::$table_name . " (";
			$sqlCreate .= "username, email, frequency, csv, graph, sqlQuery";
			$sqlCreate .= ") VALUES ('";
			$sqlCreate .= $database->escape_value($this->username) . "', '";
			$sqlCreate .= $database->escape_value($this->email) . "', '";
			$sqlCreate .= $database->escape_value($this->frequency) . "', '";
			$sqlCreate .= $database->escape_value($this->csv) . "', '";
			$sqlCreate .= $database->escape_value($this->graph) . "', '";
			$sqlCreate .= $database->escape_value($this->sqlQuery) . "')";
			
			$database->query($sqlCreate);
		} else {
			echo "ERROR - COUNT OF EXISTING REPORTS FOR USER '" . $this->username . "' IS IN DOUBT.";
		}
	}
	
	function display() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE username = '" . $database->escape_value($this->username) . "' ";
		$sql .= "AND frequency = '" . $database->escape_value($this->frequency) . "'";
		
		$report = self::find_by_sql($sql);
		$report = !empty($report) ? array_shift($report) : false;
				
		$output  = "<h2>Your " . ucfirst($report->frequency) . " Report</h2>";
		$output .= "<p>You are sent a " . $report->frequency . " e-mail, containing a list of the students in the query '" . $report->sqlQuery . "'</p>";
		
		if ($report->csv == 1) {
			$output .= "<p>This e-mail contains a CSV file of the same data, so you can view the data in Excel, should you wish.</p>";
		}
		if ($report->graph == 1) {
			$output .= "<p>This e-mail contains graphical charts, displaying various metrics on the data being e-mailed to you.</p>";
		}
		
		return $output;
	}

} // end class Users
?>