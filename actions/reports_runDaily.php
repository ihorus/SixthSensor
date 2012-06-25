<?php
require_once("../engine/initialise.php");
require_once("../engine/reportsClass.php");

// fetch all report from the database that are on a 'daily' frequency
$reports = new Reports();
//$reports->frequency = "daily";
$reports = $reports->reportsToRunToday();
$reportCSS = NULL;

/*
// not currently applying CSS to e-mails, but this is left here incase we want to in the future
// fetch the CSS from the site, to include in the e-mail
$cssFile = '../css/style01.css';
$fd = fopen($cssFile,"r");
$reportCSS = "<style>" . fread($fd, filesize($cssFile)) . "</style>";
fclose($fd);
*/

foreach($reports AS $report) {
	$observations = new Observations();
	$observations = $observations->find_by_sql($report->query);
	
	$reportTitle = "<h2>" . $report->title . "</h2>";
	$reportSubTitle = "<h3>" . $report->description . "</h3>";
	
	$reportContent = "<div class=\"logsContainer\">";
	
	$csvFile = ("\"uid\"" . "," . "\"datetime_service\"" . "," . "\"username\"" . "," . "\"sensorUID\"" . "," . "\"tagUID\"" . "," . "\"observationType\"" . "," . "\"description\"" . "," . "\"logType\"");
	$csvFile .= ("\n");
	
	foreach($observations AS $observation) {
		$reportContent .= $observation->makeLogRow();
		
		$csvFile .= ("\"" . $observation->uid . "\"" . ",");
		$csvFile .= ("\"" . date('Y-m-d', strtotime($observation->datetime_service)) . "\"" . ",");	
		$csvFile .= ("\"" . $observation->username . "\"" . ",");	
		$csvFile .= ("\"" . $observation->sensorUID . "\"" . ",");	
		$csvFile .= ("\"" . $observation->tagUID . "\"" . ",");	
		$csvFile .= ("\"" . $observation->observationType . "\"" . ",");	
		$csvFile .= ("\"" . $observation->description . "\"" . ",");	
		$csvFile .= ("\"" . $observation->logType . "\"");
		$csvFile .= ("\n");
	}
	$reportContent .= "</div>";
	
	$output = $reportCSS . $reportTitle . $reportSubTitle . $reportContent;
	
	// Only send an e-mail if there were visits today!	
	if (count($observations) > 0) {
		$fileLocation = "tempReports/daily.csv";
		$fh = fopen($fileLocation, "w+");
		if($fh==false) {
			die("<h1>unable to create file '" . $fileLocation . "'</h1>");
		} else {	
			fwrite($fh, $csvFile);
			fclose($fh);
			echo ("<h1>created file '" . $fileLocation . "'</h1>");
		}
		
		$my_file = "daily.csv";
		$my_path = $_SERVER['DOCUMENT_ROOT']."/sixthSensor/actions/tempReports/" . $my_file;
		$my_name = "Sixth Sensor";
		$my_mail = SITE_ADMIN_EMAIL;
		$my_replyto = SITE_ADMIN_EMAIL;
		$my_subject = $report->title;
		$my_message = $output;
		$mailTo = $report->email;
		
		echo "sending mail to " . $report->email . "<br />";
		sendMail($mailTo, $my_subject, $my_message, $my_path);
		
		echo $output;
	}
}
?>