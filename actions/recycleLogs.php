<?php
require_once("../engine/initialise.php");

global $database;

$systemAgeRecycle = 180; 	// AGE IN DAYS TO KEEP 'system' LOGS
$tagAgeRecycle = 365;		// AGE IN DAYS TO KEEP 'tag' LOGS

// DELETE 'system' LOGS OLDER THAN $systemAgeRecycle DAYS
$observationTypes = array("'system'");
$sql  = "DELETE FROM observations ";
$sql .= "WHERE DATE(datetime_service) < DATE_SUB(NOW(), INTERVAL " . $systemAgeRecycle . " DAY) ";
$sql .= "AND observationType IN (" . implode(",", $observationTypes) . ")";

echo "Deleting " . implode(",", $observationTypes) . " logs..." . "<br />";
$database->query($sql);
echo "Deleted " . $database->affected_rows() . autoPluralise(" row", " rows", $database->affected_rows()) . " from the database that matched the following query: " . $sql . "<br /><br />";

// DELETE 'tagIn'/'tagOut'/'tagAssign' LOGS OLDER THAN $tagAgeRecycle DAYS
$observationTypes = array("'tagIn'", "'tagOut'", "'assignTag'", "'tagCreate'");
$sql  = "DELETE FROM observations ";
$sql .= "WHERE DATE(datetime_service) < DATE_SUB(NOW(), INTERVAL " . $tagAgeRecycle . " DAY) ";
$sql .= "AND observationType IN (" . implode(",", $observationTypes) . ")";

echo "Deleting " . implode(",", $observationTypes) . " logs..." . "<br />";
$database->query($sql);

echo "Deleted " . $database->affected_rows() . autoPluralise(" row", " rows", $database->affected_rows()) . " from the database that matched the following query: " . $sql . "<br />";
?>