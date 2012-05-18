<?php
$logs = new Observations();
$logs = $logs->allObservations();

if (count($logs) > 0) {
	$logsOutput  = "<div class=\"logsContainer\">";
	
	foreach ($logs AS $log) {
		$logsOutput .= $log->makeLogRow();
	}
	
	$logsOutput .= "</div>";
}

echo $logsOutput;

?>