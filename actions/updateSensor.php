<?php
require_once("../engine/initialise.php");

if (isset($_POST['sensorUID'])) {
	$sensor = new Sensor();
	$sensor->uid = $_POST['sensorUID'];
	$sensor->name = $_POST['name'];
	$sensor->locationUID = $_POST['locationUID'];
	$sensor->enabled = $_POST['enabled'];
	$sensor->status = $_POST['status'];
	$sensor->defaultObservation = $_POST['defaultObservation'];
	
	$sensor->update();
}
?>