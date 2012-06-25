<?php
unset($includes);


$includes[] = "engine/config.php";
$includes[] = "engine/globalFunctions.php";
$includes[] = "engine/database.php";
$includes[] = "engine/ssoSession.php";
$includes[] = "engine/usersClass.php";
$includes[] = "engine/observationsClass.php";
$includes[] = "engine/tagsClass.php";
$includes[] = "engine/sensorsClass.php";
$includes[] = "engine/locationClass.php";
$includes[] = "engine/chartsClass.php";
$includes[] = "engine/phpmailer.inc.php";

foreach ($includes as $include) {
	if (file_exists($include)) {
		require_once($include);
	} else {
		if (file_exists("../" . $include)) {
			require_once("../" . $include);
		} else {
			echo ("Error in initialisation. Could not load the file '" . $include . "'");
			echo ("<br />");
		}
	}
}
?>