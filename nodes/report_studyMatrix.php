<?php
$css = file_get_contents("../css/style01.css");

require_once("../engine/initialise.php");

function makeRow($columns = NULL) {
	$output  = "<tr>";
	
	foreach ($columns AS $col) {
		if ($col == "active") {
			$output .= "<td class=\"active\">" . displayTickMark() . "</td>";
		} else {
			$output .= "<td>" . $col . "</td>";
		}
	}
	
	$output .= "</tr>";
	
	return $output;
}

function displayTickMark() {
	$output  = "<span style=\"font-family: Arial Unicode MS, Lucida Grande\">";
	$output .= "&#10004;";
	$output .= "</span>";
	
	return $output;
}

$sql  = "SELECT * FROM users WHERE tagUID IN (SELECT tagUID FROM observations ";
$sql .= "WHERE DATE(datetime_service) = DATE(NOW()) ";
$sql .= "AND sensorUID IN (21, 22) ";
$sql .= "GROUP BY tagUID) ";
$sql .= "ORDER BY lastname ASC, firstname ASC";

$allUsers = new User();
$allUsers = $allUsers->find_by_sql($sql);



$title = "<h2>" . count($allUsers) . " Users that have signed in as studying today</h2>";

$table  = "<table class=\"tabularData\">";
$table .= "<tr>";
$table .= "<th></th>";
$table .= "<th>M1</th>";
$table .= "<th>P1</th>";
$table .= "<th>P2</th>";
$table .= "<th>B1</th>";
$table .= "<th>P3</th>";
$table .= "<th>P4</th>";
$table .= "<th>L1</th>";
$table .= "<th>P5</th>";
$table .= "<th>P6</th>";
$table .= "</tr>";

foreach ($allUsers AS $user) {
	// reset some stuff
	$morning = "";
	$p1 = "";
	$p2 = "";
	$break = "";
	$p3 = "";
	$p4 = "";
	$lunch = "";
	$p5 = "";
	$p6 = "";
	unset($array);
	
	// fetch a list of this $user's observations today
	$sql  = "SELECT * FROM observations ";
	$sql .= "WHERE date(datetime_service) = '" . date('Y-m-d') . "' ";
	$sql .= "AND tagUID = '" . $user->tagUID . "' ";
	$sql .= "AND observationType = 'tagIn'";
	
	$observations = Observations::find_by_sql($sql);
	
	foreach ($observations AS $observation) {
		$time = date('H:i', strtotime($observation->datetime_service));
		
		if ($time >= date('H:i', strtotime("08:40:00")) && $time < date('H:i', strtotime("08:55:00"))) {
			$morning = "active";
		} elseif ($time >= date('H:i', strtotime("08:55:00")) && $time < date('H:i', strtotime("09:45:00"))) {
			$p1 = "active";
		} elseif ($time >= date('H:i', strtotime("09:45:00")) && $time < date('H:i', strtotime("10:35:00"))) {
			$p2 = "active";
		} elseif ($time >= date('H:i', strtotime("10:35:00")) && $time < date('H:i', strtotime("10:55:00"))) {
			$break = "active";
		} elseif ($time >= date('H:i', strtotime("10:55:00")) && $time < date('H:i', strtotime("11:45:00"))) {
			$p3 = "active";
		} elseif ($time >= date('H:i', strtotime("11:45:00")) && $time < date('H:i', strtotime("12:35:00"))) {
			$p4 = "active";
		} elseif ($time >= date('H:i', strtotime("12:35:00")) && $time < date('H:i', strtotime("13:35:00"))) {
			$lunch = "active";
		} elseif ($time >= date('H:i', strtotime("13:35:00")) && $time < date('H:i', strtotime("14:25:00"))) {
			$p5 = "active";
		} elseif ($time >= date('H:i', strtotime("14:25:00")) && $time < date('H:i', strtotime("15:15:00"))) {
			$p6 = "active";
		} else {
		}
	}
	
	$array[] = $user->formalName();
	$array[] = $morning;
	$array[] = $p1;
	$array[] = $p2;
	$array[] = $break;
	$array[] = $p3;
	$array[] = $p4;
	$array[] = $lunch;
	$array[] = $p5;
	$array[] = $p6;
	
	$table .= makeRow($array);
}
$table .= "</table>";

$message  = "<style>" . $css . "</style>";
$message .= $title;
$message .= $table;
$subject = "Users Studying Today";

if (count($allUsers) > 0) {
	sendMail("haddockk@wallingfordschool.com", $subject, $message);
	sendMail("breakspeara@wallingfordschool.com", $subject, $message);
	echo $message;
}
?>
