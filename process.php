<?php
$_GET['sensorSerial'] = "4Hk2XJSobS";
$_GET['tagSerial'] = "4C00AAD78B";

require_once("engine/initialise.php");

$observation = new Observations();

if (isset($_GET['sensorSerial'])) {
	// Sensor Serial number sent
	$sensorSerial = $_GET['sensorSerial'];
	
	$sensorCheck = new Sensor();
	$sensorCheck->serial = $sensorSerial;
	
	if (!$sensorCheck->isRegistered()) {
		exit("Sensor registration failed");
	} else {
		$sensorLookup = new Sensor();
		$sensorLookup->serial = $sensorSerial;
		$sensorLookup = $sensorLookup->find_by_serial();	
		$sensorUID = $sensorLookup->uid;
		$observation->sensorUID = $sensorUID;
	
		if ($sensorLookup->defaultObservation != "") {
			$observation->observationType = $sensorLookup->defaultObservation;
		} else {
			$observation->observationType = "noType";
		}
	}
} else {
	// No Sensor Serial number - error
	$observation->logType = "logError";
	$observation->description = "'process' service called, but no Sensor serial number provided";
}

if (isset($_GET['tagSerial'])) {
	// Tag Serial number sent
	$tagSerial = $_GET['tagSerial'];
	
	$tagLookup = new Tag();
	$tagLookup->serial = $tagSerial;
	
	if (!$tagLookup->isRegistered()) {
		exit("Tag registration failed");
	} else {
		$tagLookup = $tagLookup->find_by_serial();	
		$tagUID = $tagLookup->uid;
		
		$user = new User();
		$user->tagUID = $tagUID;
		$user = $user->findByTagUID();
		
		//printArray($user);
		
		if (!isset($user->username)) {
			$user->username = "Tag '" . $tagLookup->serial . "' with an unknown user";
			$observation->logType = "logAlert";
		} else {
			$observation->logType = "logInfo";
		}
		
		if ($observation->observationType == "tagIn") {
			$location = new Location();
			$location->uid = $sensorLookup->locationUID;
			$location = $location->findByUID();
			
			$observation->description = $user->username . " was observed at location '" . $location->name . " (" . $sensorLookup->name . ")'";
		} else {
			$observation->description = "unknown tag observation type '" . $observation->observationType . "'";
		}
		$observation->tagUID = $tagUID;
		
	}
	
} else {
	// No Tag Serial number - error
	$observation->logType = "logError";
	$observation->description = "'process' service called, but no Tag serial number provided";
}

$observation->create();

printArray($_GET);
?>


<div>
<p>Sensor Serial Number: <?php echo $sensorSerial; ?></p>
<p>Tag Serial Number: <?php echo $tagSerial; ?></p>
<p>DateTime: <?php echo date('r'); ?></p>
<p>Observation Command: <?php echo "no command set"; ?></p>
<p>Client IP: <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
</div>