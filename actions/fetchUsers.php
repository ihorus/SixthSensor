<?php
require_once("../engine/initialise.php");

// debug/test
//$_GET['year'] = "Entry 2011 (7)";

if (isset($_GET['year']) && $_GET['year'] <> "") {
	$baseDN1 = ("OU=" . $_GET['year'] . ",OU=Students,OU=Wallingford Users,DC=wsnet,DC=local");
	$students = $session->findUsers($baseDN1);
}

$usersArray[] = "\"\": \"\"";

foreach ($students AS $student) {
	if ($student['sn'][0] <> "") {
		$studentName = stripslashes($student['sn'][0] . ", " . $student['givenname'][0]);
		$username = stripslashes($student['samaccountname'][0]);
		
		$usersArray[] = "\"" . $username . "\": \"" . $studentName . "\"";
	}
}

echo "{" ;
echo implode(", ", $usersArray);
echo "}";
?>