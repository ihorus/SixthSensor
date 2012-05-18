<?php
class Chart extends Observations {
	
	public $dateFrom;
	public $dateTo;
	public $totalJobs;
	
	public static function find_by_sql($sql="") {
		global $database;
		
		$result_set = $database->query($sql);
		$object_array = array();
		while ($row = $database->fetch_array($result_set)) {
			global $database;
			$object_array[] = self::instantiate($row);
		}
		return $object_array;
	}


	private static function instantiate($record) {
		
	$object = new self;
		foreach ($record as $attribute=>$value) {
			if ($object->has_attribute($attribute)) {
				$object->$attribute = $value;
			}
		}
		return $object;
	}
	
	private function has_attribute($attribute) {
		// get_object_vars returns as associative array with all attributes
		// (incl. private ones!) as the keys and their current values as the value
		$object_vars = $this->attributes($this) ;
		
		// we don't care about the value, we just want to know if the key exists
		// will return true or false
		return array_key_exists($attribute, $object_vars);
	}
	
	private function attributes($attribute) {
		return get_object_vars($this);
	}
	
	/* --------------------------------------------- */
	
	function recentActivityTotals() {
		global $database;
		
		// select the last 30 days of mySQL events
		$sql  = "SELECT count(*) AS totalJobs, datetime_service FROM " . self::$table_name . " ";
		$sql .= "WHERE datetime_service BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()+1 ";
		$sql .= "GROUP BY DAY(datetime_service) ";
		$sql .= "ORDER BY datetime_service DESC";
		
		$observations = self::find_by_sql($sql);
		
		// build the results into an array
		if (count($observations) > 0) {
			foreach ($observations AS $observation) {
				$date = "'" . date('d-M', strtotime($observation->datetime_service)) . "'";			
				$totalJobsByDay[] = $observation->totalJobs;
				$dates[] = $date;
			}
			krsort($totalJobsByDay);
			krsort($dates);
						
			$output  = "<script type=\"text/javascript\">" . "\r";
			$output .= "var chart;" . "\r";
			$output .= "$(document).ready(function() {" . "\r";
			$output .= 		"chart = new Highcharts.Chart({" . "\r";
			$output .= 			"chart: {" . "\r";
			$output .= 				"renderTo: 'recentActivity_container'," . "\r";
			$output .= 				"defaultSeriesType: 'column'" . "\r";
			$output .= 			"}," . "\r";
			
			$output .= 			"credits: {" . "\r";
        	$output .= 				"enabled: false" . "\r";
        	$output .= 			"}," . "\r";
			$output .= 			"title: {" . "\r";
			$output .= 				"text: 'Activity Over The Last 30 Days'" . "\r";
			$output .= 			"}," . "\r";
			$output .= 			"xAxis: {" . "\r";
			$output .= 				"categories: [" . implode(",", $dates) . "]" . "\r";
			$output .= 			"}," . "\r";
			$output .= 			"yAxis: {" . "\r"; // PRIMARY AXIS
			$output .= 				"title: {" . "\r";
			$output .= 					"text: 'Observations'" . "\r";
			$output .= 				"}," . "\r";
			$output .= 				"min: 0" . "\r";
			$output .= 			"}," . "\r";
			$output .= 			"tooltip: {" . "\r";
			$output .= 				"formatter: function() {" . "\r";
			$output .= 					"return '<b>('+this.x+')</b> Observations:' + this.y" . "\r";
			$output .= 				"}" . "\r";
			$output .= 			"}," . "\r";
			$output .= 			"legend: {" . "\r";
			$output .= 				"enabled: false" . "\r";
			$output .= 			"}," . "\r";
			$output .= 			"plotOptions: {" . "\r";
			$output .= 				"pointPadding: 0.2," . "\r";
			$output .= 				"borderWidth: 0" . "\r";
			$output .= 			"}," . "\r";
			
			$output .= 			"series: [{" . "\r";
			$output	.=				"name: 'Totals'," . "\r";
			$output .=				"data: [ " . implode(",", $totalJobsByDay) . " ]" . "\r";
					
			$output .= 			"}]" . "\r";
			$output .= 		"});" . "\r";
			$output .= "});" . "\r";
			$output .= "</script>" . "\r";
			
			$output .= "<div id=\"recentActivity_container\" style=\"width: 700px; height: 280px; margin: 0 auto\"></div>";
			
			return $output;
		} else {
			return FALSE;
		}
	}
} // end class Users
?>