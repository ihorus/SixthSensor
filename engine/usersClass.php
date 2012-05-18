<?php
class User {
	public $uid;
	public $username;
	public $tagUID;
	public $form;
	public $firstname;
	public $lastname;
	public $email;
	public $type;
	public $active;
	protected static $table_name = "users";
	
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
	
	function allUsers() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ORDER BY lastname ASC, firstname ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function allUsersByForm() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE form = '" . $this->form . "' ";
		$sql .= "ORDER BY lastname ASC, firstname ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function allTutorGroups() {
		global $database;
		
		$sql  = "SELECT form FROM " . self::$table_name . " ";
		$sql .= "GROUP BY form ";
		$sql .= "ORDER BY form DESC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function findByUID() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE uid = '" . $this->uid . "' ";
		$sql .= "LIMIT 1";
		
		$result_array = self::find_by_sql($sql);
		
		return !empty($result_array) ? array_shift($result_array) : false;
	}
	
	function findByTagUID() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE tagUID = '" . $this->tagUID . "' ";
		$sql .= "LIMIT 1";
		
		$result_array = self::find_by_sql($sql);
		
		return !empty($result_array) ? array_shift($result_array) : false;
	}
	
	function usersWithTags() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE tagUID IS NOT NULL ";
		$sql .= "ORDER BY lastname ASC, firstname ASC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
	}
	
	function formalName() {
		$firstname = ucwords($this->firstname);
		$lastname = ucwords($this->lastname);
		
		if ($firstname) {
			$formalName = $lastname . ", " . $firstname;
		} else {
			$formalName = $lastname;
		}
		
		return $formalName;
	}
	
	function tutorGroup() {
		$tutorGroup = ucwords($this->form);
				
		if (!$tutorGroup) {
			$tutorGroup = false;
		}
		
		return $tutorGroup;
	}
	
	function displayBlock() {
		$content = "";
		// check if user has an assigned tag, if so, display it
		if ($this->tagUID) {
			$tag = new Tag();
			$tag ->uid = $this->tagUID;
			$tag = $tag->findbyUID();
			
			$content .= $tag->displayBlock();
		}
		
		$observations = new Observations();
		$observations->username = $this->username;
		$content.= $observations->displayUserLogsByUsername();
					
		if ($this->lastObservedDate()) {
			// we have a date stamp, display it with it's age
			$meta = "Last Observed: " . dateDisplay($this->lastObservedDate(), TRUE);
		} else {
			// we don't have a date stamp
			$meta = "";
		}
		
		if ($this->tutorGroup()) {
			$tutorGroup = " (" . $this->tutorGroup() . ")";
		} else {
			$tutorGroup = "";
		}
		
		return makeBlock("user", $this->formalName() . $tutorGroup, $meta, $content);
	}
	
	function create() {
		global $database;
		global $session;
		
		// lookup user details from LDAP
		$ldapUser = $session->findUser($this->username);

		// user doesn't exist in database yet, create them
		$sql  = "INSERT INTO " . self::$table_name . " ";
		$sql .= "(username, tagUID, form, firstname, lastname, email, type, active";
		$sql .= ") VALUES ('";
		$sql .= $database->escape_value($this->username) . "', '";
		$sql .= $database->escape_value($this->tagUID) . "', '";
		
		// catch for empty form groups
		if (!isset($ldapUser['facsimiletelephonenumber'][0])) {
			$ldapUser['facsimiletelephonenumber'][0] = "";
		}
		
		$sql .= $database->escape_value($ldapUser['facsimiletelephonenumber'][0]) . "', '";
		$sql .= $database->escape_value($ldapUser['givenname'][0]) . "', '";
		$sql .= $database->escape_value($ldapUser['sn'][0]) . "', '";
		
		// catch for empty form e-mail
		if (!isset($ldapUser['mail'][0])) {
			$ldapUser['mail'][0] = "";
		}
		
		$sql .= $database->escape_value($ldapUser['mail'][0]) . "', '";
		$sql .= $database->escape_value("User") . "', '";
		$sql .= $database->escape_value($this->active) . "')";
		
		if ($database->query($sql)) {
			$observation = new Observations();
			$observation->username = $session->serverUsername();
			$observation->logType = "logInfo";
			$observation->observationType = "system";
			$observation->description = $session->serverUsername() . " created '" . $this->username . "' for the first time";
			$observation->create();
			
			return true;
		} else {
			$observation = new Observations();
			$observation->username = $session->serverUsername();
			$observation->logType = "logError";
			$observation->observationType = "system";
			$observation->description = $session->serverUsername() . " attempted to create '" . $this->username . "' for the first time";
			$observation->create();
			
			return false;
		}
	}
	
	function lastObservedDate() {
		$tag = new Tag();
		$tag->uid = $this->tagUID;
		
		if ($tag->lastObservedDate()) {
			$date = $tag->lastObservedDate();
		
			return $date;
		} else {
			return FALSE;
		}
	}
	
	function offSiteUsers() {
		$sql  = "SELECT * FROM (SELECT * FROM (SELECT * FROM observations WHERE observationType = 'tagIn' ORDER BY tagUID DESC, datetime_service DESC) AS tmpTable ";
		$sql .= "GROUP BY tagUID) AS tmpTable2, users ";
		$sql .= "WHERE tmpTable2.tagUID = users.tagUID ";
		$sql .= "AND sensorUID = '19' ";
		$sql .= "ORDER BY users.form DESC, users.lastname DESC, users.firstname DESC";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
		


	}
	
	function todaysOffSiteUsers() {
		$sql  = "SELECT * FROM observations ";
		$sql .= "WHERE uid IN (SELECT uid FROM observations WHERE DATE(datetime_service) = DATE(NOW()) AND observationType = 'tagIn' GROUP BY tagUID ORDER BY datetime_service ASC) ";
		$sql .= "AND sensorUID = '19'";
		
		$result_array = self::find_by_sql($sql);
		
		return $result_array;
		
	}
} // end of userClass
?>