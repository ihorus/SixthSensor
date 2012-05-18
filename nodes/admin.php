<?php
$registeredSensors = Sensor::registeredSensors();
$unregisteredSensors = Sensor::unregisteredSensors();
?>

<ul id="sections">
	<li id="section-tasks"><h2>Tasks</h2>
	<p><a href="index.php?node=overrideSignout.php">Manually sign users back in</a></p>
	<p><a href="index.php?node=report_unseenUsersToday.php">Users that haven't signed in/out today</a></p>
	<p><a href="index.php?node=report_studiedUsersToday.php">Users that have signed in as studying today</a></p>
	<p><a href="index.php?node=studyMatrix.php">Study Matrix Today</a></p>
	</li>
	<li id="section-sensors"><h2>Sensors</h2>
	<?php
	foreach ($registeredSensors AS $sensor) {
		echo $sensor->displaySensorTitle();
	}
	?>
	<?php
	foreach ($unregisteredSensors AS $sensor) {
		echo $sensor->displaySensorTitle();
	}
	?>
	</li>
	<li id="section-logs"><h2>Logs</h2><a href="index.php?node=logs_export_html.php">HTML</a> <a href="nodes/logs_export_csv.php" target="_blank">CSV</a></li>
</ul>