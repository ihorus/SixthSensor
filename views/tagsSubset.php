<script src="js/popup.js" type="text/javascript"></script>
<script type="text/javascript">
$(function(){
	$(".yearSelect").change(function(){
		var tagUID = $(this).attr('id').replace("_yearSelect", "");
		var yearGroupSelected = $(this).val();
		var assignTagUserElement = "#" + tagUID + "_assignTagUser";
				
		$(assignTagUserElement).empty();
		
		$.getJSON("actions/fetchUsers.php?year=" + yearGroupSelected, function(data) {
			var items = [];
			$.each(data, function(key, val) {
				$("<option>").attr("value", key).text(val).appendTo(assignTagUserElement);
			});
		});
	})
});

$(function() {
	$(".assignTagUser").change(function() {
		var tagUID = $(this).attr('id').replace("_assignTagUser", "");
		var assignUser = $("option:selected",this).val();
				
		// url we're going to send the data to
		var url = "actions/assignTag.php";
		
		$.post(url,{
			tagUID: tagUID,
			assignUser: assignUser
		}, function(data){
			//centering with css
			//centerPopup();
			//loadPopup();
			//disablePopup();
		},'html');
		return false;
	});
});
</script>
<?php
require_once("../engine/initialise.php");

$observations = new Observations();
if (isset($_POST['sensorUID'])) {
	$observations->sensorUID = $_POST['sensorUID'];
}
$observations = $observations->activeScanTags(4);

//printArray($observations);

if (count($observations) > 0) {
	foreach ($observations AS $observation) {
		$tag = new Tag();
		$tag->uid = $observation->tagUID;
		$tag = $tag->findbyUID();
		
		echo $tag->displayBlock(FALSE);
	}
}
?>