<?php
class Sensor {
	public $uid;
	public $datetime_entered;
	public $locationUID;
	public $name;
	public $serial;
	public $ip;
	public $enabled;
	public $status;
	public $defaultObservation;

	public $username;
	protected static $table_name = "sensors";
	
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
	
	function allSensors() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "ORDER BY name ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function registeredSensors() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE status = 'registered' ";
		$sql .= "ORDER BY name ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function unregisteredSensors() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE status != 'registered' ";
		$sql .= "ORDER BY name ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function find_by_uid() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE uid = '" . $database->escape_value($this->uid) . "' ";
		$sql .= "ORDER BY serial ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return !empty($result_array) ? array_shift($result_array) : false;
	}
	
	function findByLocationUID() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE locationUID = '" . $database->escape_value($this->locationUID) . "' ";
		$sql .= "ORDER BY serial ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return !empty($result_array) ? array_shift($result_array) : false;
	}
	
	function find_by_serial() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE serial = '" . $database->escape_value($this->serial) . "' ";
		$sql .= "ORDER BY serial ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return !empty($result_array) ? array_shift($result_array) : false;
	}
	
	function isRegistered() {
		// locate if the sensor is registered on the system, if not
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE serial = '" . $database->escape_value($this->serial) . "' ";
		$sql .= "LIMIT 1";
				
		$result_array = self::find_by_sql($sql);
		$result = !empty($result_array) ? array_shift($result_array) : false;
		
		if ($result) {
			if ($result->status == "registered") {
				// check if the recorded IP is the same as the current IP
				if ($_SERVER['REMOTE_ADDR'] != $result->ip) {
					$result->find_by_sql("UPDATE sensors SET ip ='" . $_SERVER['REMOTE_ADDR'] . "' WHERE uid = " . $result->uid);
					$observation = new Observations();
					$observation->observationType = "sensor";
					$observation->logType = "logAlert";
					$observation->description = "Sensor '" . $this->serial ."' updated its IP address from " . $result->ip . " to " . $_SERVER['REMOTE_ADDR'];
					$observation->create();
				}
						
				$returnValue = TRUE;
			} else {
				$observation = new Observations();
				$observation->observationType = "sensor";
				$observation->logType = "logAlert";
				$observation->description = "disallowed sensor '" . $this->serial ."' attempted to perform observation";
				$observation->create();
				
				$returnValue = FALSE;
			}
		} else {
			$this->createUnregistered();
			
			$returnValue = FALSE;
		}
		
		return $returnValue;
	}
	
	function createUnregistered() {
		global $database;
		
		$observation = new Observations();
		$observation->observationType = "sensor";
		
		// build the SQL string
		$sql  = "INSERT INTO " . self::$table_name . " (";
		$sql .= "datetime_entered, serial, ip, enabled, status";
		$sql .= ") VALUES ('";
		$sql .= date('Y-m-d H:i:s') . "', '";
		$sql .= $database->escape_value($this->serial) . "', '";
		$sql .= $database->escape_value($this->ip) . "', '";
		$sql .= "0', '";
		$sql .= "unknown')";
					
		if ($database->query($sql)) {
			$observation->logType = "logInfo";
			$observation->description = "sensor '" . $this->serial . "' added in an unregistered state to the database";
			$observation->create();
	
			return true;
		} else {
			$observation->logType = "logError";
			$observation->description = "error when adding sensor to database";
			$observation->create();

			return false;
		}
	}
	
	function sensorName() {
		if (isset($this->name) && $this->name != "") {
			$sensorName = $this->name;
		} else {
			$sensorName = "Unknown";
		}
		
		if ($this->locationUID <> "") {
			$location = new Location();
			$location->uid = $this->locationUID;
			$location = $location->findByUID();
			
			$sensorName .= " in " . $location->name;
		}
		
		if ($this->status == "unknown") {
			$sensorName .= " (UN-REGISTERED)";
		}
		
		return $sensorName;
	}
	
	function locationName() {
		return "test";
	}
	
	function displaySensorTitle() {
		if ($this->enabled) {
			$css = "sensorTitle";
		} else {
			$css = "sensorTitleUnregistered";
		}
		
		
		$output  = "<div class=\"" . $css . "\">";
		$output .= "<a href =\"index.php?node=sensorEdit.php&uid=" . $this->uid . "\">";
		$output .= $this->sensorName();
		$output .= " (Serial: " . $this->serial . ")";
		$output .= "</a>";
		
		$output .= "</div>";
		
		return $output;
	}
	
	function update() {
		global $database;
		global $session;
		
		$sqlUpdate  = "UPDATE " . self::$table_name . " set ";
		$sqlUpdate .= "name = '" . $this->name . "', ";
		$sqlUpdate .= "locationUID = '" . $this->locationUID . "', ";
		$sqlUpdate .= "enabled = '" . $this->enabled . "', ";
		$sqlUpdate .= "status = '" . $this->status . "', ";
		$sqlUpdate .= "defaultObservation = '" . $this->defaultObservation . "' ";
		$sqlUpdate .= "WHERE uid = '" . $database->escape_value($this->uid) . "'";
		
		echo $sqlUpdate;
		
		// insert the record to the database
		if ($database->query($sqlUpdate)) {
			$observation = new Observations();
			$observation->username = $session->serverUsername();
			$observation->sensorUID = $this->uid;
			$observation->logType = "logInfo";
			$observation->observationType = "sensor";
			$observation->description = $session->serverUsername() . " updated sensor " . $this->uid;
			$observation->create();
		}
	}
	
	function offSiteSensors() {
		global $database;
		
		// fetch the location with a name of 'Offsite'
		$offsiteLocation = new Location();
		$offsiteLocation = $offsiteLocation->offsiteLocation();
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE locationUID = '" . $database->escape_value($offsiteLocation->uid) . "' ";
		$sql .= "ORDER BY serial ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return !empty($result_array) ? array_shift($result_array) : false;
	}
} // end of sensorsClass
?>