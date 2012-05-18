<script type="text/javascript">
$(document).ready(function() {
	$("#selectAll").click(function() {
		
		$("input").each(function() {
			this.checked = "checked";
		});
	});
});

$(function() {
	$("#formSubmit").click(function() {
		// build a long string of all selected users UIDs		
		var checkedInputs = $("input:checked");
		var userUIDS = "";
		$.each(checkedInputs, function(i, val) {
			userUIDS += val.value+",";
		});
		userUIDS = userUIDS.substring(0,(userUIDS.length-1));
		    											
		// url we're going to send the data to
		var url = "actions/signUsersIn.php";

		$.post(url,{
			userUIDS: userUIDS
		}, function(data){
			alert(userUIDS.length + " user(s) signed back in");
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
?>
<h2>Manually Override Offsite Users</h2>

<form>
<?php
$table  = "<table class='tabularData'>";
$table .= "<tr>";
$table .= "<th>" . "Select" . "</th>";
$table .= "<th>" . "Form" . "</th>";
$table .= "<th>" . "Student Name" . "</th>";
$table .= "<th>" . "Sign Out Date/Time" . "</th>";
$table .= "</tr>";

foreach ($offSiteUsers AS $offsiteUser) {
	$user = new User();
	$user->tagUID = $offsiteUser->tagUID;
	$user = $user->findByTagUID();
		
	$table .= "<tr>";
	$table .= "<td>" . "<input type=\"checkbox\" value=\"" . $user->uid . "\" name=\"paradigm\" />" . "</td>";
	$table .= "<td>" . $user->form . "</td>";
	$table .= "<td>" . $user->formalName() . "</td>";
	$table .= "<td>" . dateDisplay($user->lastObservedDate()) . "</td>";
	$table .= "</tr>";
}
$table .= "</table>";

echo $table;
?>
<div id="selectAll">Select All Users</div>
<input type="button" id="formSubmit" value="Sign Users Back In" />
</form>

<div id="response_added"></div>
<div class="clear"></div>