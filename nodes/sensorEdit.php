<script type="text/javascript">
$(function() {
	$("#formSensorSubmit").click(function() {
		var sensorUID = $("input#formSensorUID").val();
		var name = $("input#formSensorName").val();
		var locationUID = $("select#formLocationUID").val();
		
		if($('#formSensorEnabled').is(':checked')) {
			enabled = "1";
    	} else {
    		enabled = "0";
    	}
    	
		var status = $("select#formSensorStatus").val();
		var defaultObservation = $("input#formSensorDefaultObservation").val();
						
		// url we're going to send the data to
		var url = "actions/updateSensor.php";

		$.post(url,{
			sensorUID: sensorUID,
			name: name,
			locationUID: locationUID,
			enabled: enabled,
			status: status,
			defaultObservation: defaultObservation
		}, function(data){
			alert("done");
			$("#response_added").append(data);
			

		},'html');
		
		// stop the page refreshing, this is all handled in jQuery so we don't need a proper submit
		return false;
	});
});		
</script>

<?php
$sensor = new Sensor();
$sensor->uid = $_GET['uid'];
$sensor = $sensor->find_by_uid();

$locations = new Location();
$locations = $locations->allLocations();

?>

<h2>Edit Sensor (Serial: <?php echo $sensor->serial; ?>)</h2>

<form>
<label>Sensor Name: </label>
<input type="input" id="formSensorName" value="<?php echo $sensor->name; ?>" />
<div class="clear"></div>

<label>Location: </label>
<select id="formLocationUID">
	<?php
	echo optionDropdown("", "", $sensor->locationUID);
	
	foreach ($locations AS $location) {
		echo optionDropdown($location->uid, $location->name, $sensor->locationUID);
	}
	?>
</select>
<div class="clear"></div>

<label>Registered Date: </label>
<label id="formSensorDateRegistered"><?php echo $sensor->datetime_entered; ?></label>
<div class="clear"></div>

<label>IP Address: </label>
<label id="formSensorDateRegistered"><?php echo $sensor->ip; ?></label>
<div class="clear"></div>

<label>Enabled: </label>
<?php
if ($sensor->enabled == 1) {
	$checkedValue = "checked";
} else {
	$checkedValue = "";
}
?>
<input type="checkbox" id="formSensorEnabled" <?php echo $checkedValue; ?> />
<div class="clear"></div>

<label>Status: </label>
<select id="formSensorStatus">
	<?php
	echo optionDropdown("registered", "Registered", $sensor->status);
	echo optionDropdown("unknown", "Unknown", $sensor->status);
	echo optionDropdown("error", "Error", $sensor->status);
	echo optionDropdown("missing", "Missing", $sensor->status);
	?>
</select>
<div class="clear"></div>

<label>Default Observation: </label>
<input type="input" id="formSensorDefaultObservation" value="<?php echo $sensor->defaultObservation; ?>" /> <i> e.g. 'tagIn' or 'tagOut'</i>
<div class="clear"></div>

<label></label>
<input type="submit" id="formSensorSubmit" value="Update Sensor" />
<input type="hidden" id="formSensorUID" value="<?php echo $sensor->uid; ?>" />
<div class="clear"></div>
<div id="response_added"></div>

</form>