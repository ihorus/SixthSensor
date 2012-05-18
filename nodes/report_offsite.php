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
$offSiteUsers = new User();
$offSiteUsers = $offSiteUsers->offSiteUsers();

function makeRegisterRow($userUID = NULL) {
	
	
	$output  = "<tr>";
	$output .= "<td>" . $user->form . "</td>";
	$output .= "<td>" . $user->formalName() . "</td>";
	$output .= "<td>" . dateDisplay($user->lastObservedDate()) . "</td>";
	$output .= "</tr>";
	
	return $output;
}
?>

<?php
echo "<h2>Offsite Users at " . dateDisplay(date('U')) . "</h2>";

if (count($offSiteUsers) > 0) {
	$table  = "<table class='tabularData'>";
	$table .= "<tr>";
	$table .= "<th>" . "Form" . "</th>";
	$table .= "<th>" . "Student Name" . "</th>";
	$table .= "<th>" . "Sign Out Date/Time" . "</th>";
	$table .= "</tr>";
	
	foreach ($offSiteUsers AS $offsiteUser) {
		$user = new User();
		$user->tagUID = $offsiteUser->tagUID;
		$user = $user->findByTagUID();
			
		$table .= "<tr>";
		$table .= "<td>" . $user->form . "</td>";
		$table .= "<td>" . $user->formalName() . "</td>";
		$table .= "<td>" . dateDisplay($user->lastObservedDate()) . "</td>";
		$table .= "</tr>";
	
	}
	
	$table .= "</table>";

	echo $table;
} else {
	echo "no users offsite";
}

?>
</p>