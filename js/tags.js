$(document).ready(function() {
	// prevent ajax results from being cached
	$.ajaxSetup({ cache: false });
	
	// hide the 'active scan' feature for the tags
	var refreshId;
	$("#scanningLoadingSpinner").hide();
	$("#activeScanContainer").hide();
	$(".editIcon").hide();
	
	// hide all the extras for tags/users - but allow them to be clickable
	$('.content').hide();
	$('.meta').click(function(){
		$(this).next().slideToggle("slow");
		$(this).find('img').fadeToggle();
	});
	
	// active scan
	$("#activeScanToggle").click(function() {
		
		
		// if the active scan is currently hidden, then this it needs to be enabled
		if ($("#activeScanContainer").is(":hidden")) {
		
			// show the scanning results
			$("#scanningLoadingSpinner").fadeIn();
			$("#activeScanContainer").slideDown();
			
			// update the button so that the user can stop the scan
			$('#sensorUID').attr("disabled", true);
			$("#activeScanToggle").val("Stop scanning");
			
			// begin the scanning
			refreshId = setInterval("fetchActiveScanResults()", 3000);
			
			
		} else {
			// the active scan is currently visable, so disable it
			
			// update the button so that the user can re-start the scan
			$('#sensorUID').attr("disabled", false);
			$("#activeScanToggle").val("Start scanning");
						
			// stop the scanning
			clearInterval(refreshId);
			
			// hide the scanning results
			$("#activeScanContainer").slideUp();
			$("#scanningLoadingSpinner").fadeOut();
		}
	});

});


function fetchActiveScanResults() {
	// url we're going to send the data to
	var url = "views/tagsSubset.php";
	
	// the sensor UID we want to active scan
	var sensorUID = $("select#sensorUID").val();
	
	$.post(url,{
		sensorUID: sensorUID
	}, function(data){
		$("#activeScanResultsContainer").prepend(data);
	},'html');		
}

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
	$(".registerTagButton").click(function() {
		var tagUID = $(this).attr('id');		
		
		// url we're going to send the data to
		var url = "actions/registerTag.php";

		$.post(url,{
			tagUID: tagUID
		}, function(data){
			alert("Tag registered - please refresh this page to assign it");
			//$("#response_added").append(data);
		},'html');
		return false;
		
	});
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
			centerPopup();
			loadPopup();
			disablePopup();
		},'html');
		return false;
	});
});
