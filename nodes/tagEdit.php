<script type="text/javascript">
$(function() {
	$("#formTagSubmit").click(function() {
		var tagUID = $("input#formTagUID").val();
		var tagSerial = $("input#formTagSerial").val();
		
		if($('#formTagEnabled').is(':checked')) {
			enabled = "1";
    	} else {
    		enabled = "0";
    	}
    	
		var status = $("select#formTagStatus").val();
										
		// url we're going to send the data to
		var url = "actions/updateTag.php";

		$.post(url,{
			tagUID: tagUID,
			tagSerial: tagSerial,
			enabled: enabled,
			status: status
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
$tag = new Tag();
$tag->serial = $_GET['tagSerial'];
$tag = $tag->find_by_serial();

$logs = new Observations();
$logs->tagUID = $tag->uid;
$logs = $logs->displayTagLogsByTag();

?>

<h2>Edit Tag (Serial: <?php echo $tag->serial; ?>)</h2>

<form>
<label>Registered Date: </label>
<label id="formTagDateRegistered"><?php echo $tag->datetime_entered; ?></label>
<div class="clear"></div>

<label>Enabled: </label>
<?php
if ($tag->enabled == 1) {
	$checkedValue = "checked";
} else {
	$checkedValue = "";
}
?>
<input type="checkbox" id="formTagEnabled" <?php echo $checkedValue; ?> />
<div class="clear"></div>

<label>Status: </label>
<select id="formTagStatus">
	<?php
	echo optionDropdown("registered", "Registered", $tag->status);
	echo optionDropdown("unknown", "Unknown", $tag->status);
	echo optionDropdown("error", "Error", $tag->status);
	echo optionDropdown("missing", "Missing", $tag->status);
	?>
</select>
<div class="clear"></div>

<label></label>
<input type="submit" id="formTagSubmit" value="Update Tag" />
<input type="hidden" id="formTagUID" value="<?php echo $tag->uid; ?>" />
<input type="hidden" id="formTagSerial" value="<?php echo $tag->serial; ?>" />
<div class="clear"></div>
<div id="response_added"></div>

</form>
<div class="clear"></div>

<h2>Logs For This Tag:</h2>
<?php
echo $logs;
?>
<div class="clear"></div>
