<?php
include_once("../engine/config.php");
include_once("../engine/globalFunctions.php");
include_once("../engine/database.php");
include_once("../engine/ssoSession.php");
include_once("../engine/usersClass.php");
include_once("../engine/observationsClass.php");
include_once("../engine/tagsClass.php");
include_once("../engine/sensorsClass.php");

header('Expires: 0?');
header('Cache-control: private');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0?');
header('Content-Description: File Transfer');
header('Content-Type: text/csv');
header('Content-disposition: attachment; filename="sixth_sensor_logs.csv"');

$logs = new Observations();
$logs = $logs->allObservations();

echo (	"\"uid\"" . "," . 
		"\"Date\"" . "," . 
		"\"Username\"" . "," . 
		"\"Sensor Serial\"" . "," . 
		"\"Tag Serial\"" . "," . 
		"\"Observation Type\"" . "," . 
		"\"Description\"" . "," . 
		"\"Log Type\"");
echo ("\n");

foreach ($logs AS $log) {
	$tag = new Tag();
	$tag->uid = $log->tagUID;
	$tag = $tag->findbyUID();
	if (!$tag) {
		$tag->serial = "";
	}
	
	$sensor = new Sensor();
	$sensor->uid = $log->sensorUID;
	$sensor = $sensor->find_by_uid();
	if (!$sensor) {
		$sensor->serial = "";
	}
	
	echo ("\"" . $log->uid . "\"" . ",");
	echo ("\"" . $log->datetime_service . "\"" . ",");	
	echo ("\"" . $log->username . "\"" . ",");	
	echo ("\"" . $sensor->serial . "\"" . ",");	
	echo ("\"" . $tag->serial . "\"" . ",");	
	echo ("\"" . $log->observationType . "\"" . ",");	
	echo ("\"" . $log->description . "\"" . ",");	
	echo ("\"" . $log->logType . "\"");
	echo ("\n");
}

?>