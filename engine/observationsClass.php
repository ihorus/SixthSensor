<?php
class Observations {
	public $uid;
	public $datetime_service;
	public $username;
	public $sensorUID;
	public $tagUID;
	public $observationType;
	public $description;
	public $logType;
	
	protected static $table_name = "observations";
	
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
	
	/* --------------------------------------------- */
	
	function allObservations() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ORDER BY datetime_service DESC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function subsetOfObservations() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " WHERE ";
		$sql .= "uid = uid ";
		
		if (isset($this->sensorUID)) {
			if (is_array($this->sensorUID)) {
				$sql .= "AND sensorUID IN (" . implode(",", $this->sensorUID) . ") ";
			} else {
				$sql .= "AND sensorUID = '" . $this->sensorUID . "' ";
			}
		}
		
		if (isset($this->observationType)) {
			$sql .= "AND observationType IN (" . implode(", ", $this->observationType) . ") ";
		}
		
		$sql .= "ORDER BY datetime_service DESC";
				
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function activeScanTags($ageSeconds = 0) {
		global $database;
		
		$currentUnixDate = date('U');
		$dateFrom = $currentUnixDate - $ageSeconds;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE UNIX_TIMESTAMP(datetime_service) > '" . date('U', $dateFrom) . "' ";
		$sql .= "AND UNIX_TIMESTAMP(datetime_service) < '" . $currentUnixDate . "' ";
		
		if (isset($this->sensorUID)) {
			$sql .= "AND (sensorUID = 0 OR sensorUID ='" . $this->sensorUID . "') ";
		}
		
		$sql .= "AND observationType = 'tagIn' ";
		$sql .= "ORDER BY datetime_service DESC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function todaysSignOuts() {
		// fetch a list of users that have signed out today
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE date(datetime_service) = date(NOW()) ";
		$sql .= "AND observationType = 'tagOut'";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function todaysOffsiteUsers() {
		// fetch the sensor associated with the location 'Offsite'
		$offsiteSensor = new Sensor();
		$offsiteSensor = $offsiteSensor->offSiteSensors();
		
		// fetch a list of users that have signed out with that sesnor today
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE date(datetime_service) = date(NOW()) ";
		$sql .= "AND sensorUID = '" . $offsiteSensor->uid . "' ";
		$sql .= "AND observationType = 'tagIn'";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function lastObservedDateByTag() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE tagUID = '" . $this->tagUID . "' ";
		$sql .= "AND observationType IN ('tagIn', 'tagOut') ";
		$sql .= "ORDER BY datetime_service DESC ";
		$sql .= "LIMIT 1";
				
		$result_array = self::find_by_sql($sql);
		
		$result = !empty($result_array) ? array_shift($result_array) : false;
		
		if ($result) {
			return $result->datetime_service;
		} else {
			return FALSE;
		}
	}
	
	function lastAssignedDateByTag() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE tagUID = '" . $this->tagUID . "' ";
		$sql .= "AND observationType = 'tagAssign' ";
		$sql .= "ORDER BY datetime_service DESC ";
		$sql .= "LIMIT 1";
				
		$result_array = self::find_by_sql($sql);
		
		$result = !empty($result_array) ? array_shift($result_array) : false;

		return $result->datetime_service;
	}
	
	function displayUserLogsByUsername() {
		global $database;
		$logsOutput = "";
		
		$sql  = "SELECT * FROM observations ";
		$sql .= "WHERE description LIKE '%" . $this->username . "%' ";
		$sql .= "AND observationType IN ('tagIn', 'tagOut') ";
		$sql .= "ORDER BY datetime_service DESC ";
		$sql .= "LIMIT 10";
		
		$observations = self::find_by_sql($sql);
		
		if (count($observations) > 0) {
			$logsOutput .= "<div class=\"logsContainer\">";
			foreach ($observations AS $observation) {
				$logsOutput .= $observation->makeLogRow();
			}
		$logsOutput .= "</div>";
		}
		return $logsOutput;
	}
	
	function displayTagLogsByTag() {
		global $database;
		
		$sql  = "SELECT * FROM observations ";
		$sql .= "WHERE tagUID = '" . $this->tagUID . "' ";
		$sql .= "AND observationType IN ('tagAssign') ";
		$sql .= "ORDER BY datetime_service DESC ";
		$sql .= "LIMIT 10";
		
		$observations = self::find_by_sql($sql);
		
		if (count($observations) > 0) {
			$logsOutput = "<div class=\"logsContainer\">";
			
			foreach ($observations AS $observation) {
				$logsOutput .= $observation->makeLogRow();
			}
			
			$logsOutput .= "</div>";
		} else {
			$logsOutput = FALSE;
		}
		return $logsOutput;
	}
	
	function create() {
		global $database;
				
		// build the SQL string
		$sql  = "INSERT INTO " . self::$table_name . " (";
		$sql .= "datetime_service, username, sensorUID, tagUID, observationType, description, logType";
		$sql .= ") VALUES ('";
		$sql .= date('Y-m-d H:i:s') . "', '";
		$sql .= $database->escape_value($this->username) . "', '";
		$sql .= $database->escape_value($this->sensorUID) . "', '";
		$sql .= $database->escape_value($this->tagUID) . "', '";
		$sql .= $database->escape_value($this->observationType) . "', '";
		$sql .= $database->escape_value($this->description) . "', '";
		$sql .= $database->escape_value($this->logType) . "')";
					
		if ($database->query($sql)) {
			return true;
		} else {
			return false;
		}
	}
	
	function makeLogRow() {
		$knownTypes = array("system", "tagCreate", "tagIn", "tagOut", "tagAssign", "sensor");
		
		$class = $this->logType;
		$output  = "<div class=\"" . $class . "\">";
		$output .= "(" . $this->datetime_service . ") ";
		
		// build the log type
		if (in_array($this->observationType, $knownTypes)) {
			$output .= $this->description;
		} else {
			$output .= "{unknown log type: " . $this->observationType . "} " . "Username: " . $this->username . " -  " . $this->description;
		}
		
		$output .= "</div>";
		
		return $output;

	}
	
	function lastInOutObservationType() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE tagUID = '" . $this->tagUID . "' ";
		$sql .= "AND (observationType = 'tagIn' OR observationType = 'tagOut') ";
		$sql .= "ORDER BY datetime_service DESC ";
		$sql .= "LIMIT 1";
		
		$result_array = self::find_by_sql($sql);
		$result = !empty($result_array) ? array_shift($result_array) : false;
		
		return $result->observationType;
	}
	
	function lastObservationSensorUID() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE tagUID = '" . $this->tagUID . "' ";
		$sql .= "AND observationType = 'tagIn' ";
		$sql .= "ORDER BY datetime_service DESC ";
		$sql .= "LIMIT 1";
		
		$result_array = self::find_by_sql($sql);
				
		$result = !empty($result_array) ? array_shift($result_array) : false;
		
		return $result->sensorUID;
	}
	
	function lastTagOutObservationDate() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE tagUID = '" . $this->tagUID . "' ";
		$sql .= "AND observationType = 'tagOut' ";
		$sql .= "ORDER BY datetime_service DESC ";
		$sql .= "LIMIT 1";
		
		$result_array = self::find_by_sql($sql);
		$result = !empty($result_array) ? array_shift($result_array) : false;
		
		return $result->datetime_service;
	}
} // end of userClass
?>