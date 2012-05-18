<?php
include_once("../engine/config.php");
include_once("../engine/globalFunctions.php");
include_once("../engine/database.php");
include_once("../engine/ssoSession.php");
include_once("../engine/usersClass.php");
include_once("../engine/observationsClass.php");
include_once("../engine/tagsClass.php");
include_once("../engine/sensorsClass.php");

$observations = new Observations();

//printArray($_POST);
if (isset($_POST['selectedSensors']) && $_POST['selectedSensors'] <> "") {
	$sensors = sanitise_array($_POST['selectedSensors']);
	$observations->sensorUID = $sensors;
}


$observations->observationType = array("'tagIn'", "'tagOut'");

$dateFromYear = substr($_POST['dateFrom'], 4, 4);
$dateFromMonth = date('m', strtotime(substr($_POST['dateFrom'], 0, 3)));
$dateFromDay = 1;

$dateToYear = substr($_POST['dateTo'], 4, 4);
$dateToMonth = date('m', strtotime(substr($_POST['dateTo'], 0, 3)));
$dateToDay = cal_days_in_month(CAL_GREGORIAN, $dateToMonth, $dateToYear);

$dateFrom = date('U', mktime(0, 0, 0, $dateFromMonth, $dateFromDay, $dateFromYear));
$dateTo = date('U', mktime(0, 0, 0, $dateToMonth, $dateToDay, $dateToYear));

$observations = $observations->subsetOfObservations();


foreach ($observations AS $observation) {
	$observationUnixDate = date('U', strtotime($observation->datetime_service));
	
	if ($observationUnixDate >= $dateFrom && $observationUnixDate <= $dateTo) {
		echo $observation->makeLogRow();
	}
}


?>