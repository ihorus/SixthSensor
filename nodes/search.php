<?php
$sensors = Sensor::allSensors();
?>

<script src="js/selectToUISlider.jQuery.js"></script>

<style type="text/css">
fieldset {
	border:0;
	margin: 40px;
}

#selectable .ui-selecting {
	background: #FECA40;
}
#selectable .ui-selected {
	background: #F39814;
	color: white;
}
#selectable {
	list-style-type: none;
	margin: 0;
	padding: 0;
	width: 60%;
}
#selectable li {
	margin: 3px;
	padding: 0.4em;
	font-size: 1.4em;
	height: 18px;
}
</style>

<script>
$(document).ready(function() {
	// hide the 'loading' spinner
	$('#loading_spinner_placeholder').hide();
	$('#sensors-selected').hide();
	
	// select all the sensors by default (this is just visual, it does not pass the variables though)
	$('#selectable li').addClass("ui-selected");
});

$(function() {
	$( "#selectable" ).selectable({
		stop: function() {
			var result = $("#sensors-selected").empty();
			
			$( ".ui-selected", this ).each(function() {
				var index = $("#selectable li").index( this );
				
				result.append(( index + 1 ) + ",");
			});
			//alert(selectedSensors);
		}
	});
});

$(function(){
	$('select#valueAA, select#valueBB').selectToUISlider({
		labels: 12
	}).hide();
});

$(function() {
	$(".button").click(function() {
		var url = "views/usersSubset.php";
		var sensors = $( "#sensors-selected" ).text();
		var dateFrom = $("#valueAA option:selected").text();
		var dateTo = $("#valueBB option:selected").text();

		$("#loading_spinner_placeholder").show();
		
		$.post(url,{
			selectedSensors: sensors,
			dateFrom: dateFrom,
			dateTo: dateTo
		}, function(data){
			$("#responsecontainer").html(data);
			
			// remove the loading image
			$("#loading_spinner_placeholder").hide();
		},'html');
		return false;
	});
		
	
});
</script>

<form action="#">
<h2>Filter Settings</h2>
	
<h3>Sensors to Include</h3>
<ol id="selectable">
<?php
	foreach ($sensors AS $sensor) {
		echo "<li class=\"ui-widget-content\">" . $sensor->name . " <i>(" . $sensor->locationName() . ")</i></li>";
	}
?>
</ol>

<h3>Date Range To Include</h3>
<?php
echo displayDateSlider();
?>
<input type="submit" name="submit" class="button" id="submit_btn" value="Search" />
</form>
<span id="sensors-selected"></span>
<div class="clear"></div>

<h2>Results</h2>
<div id="loading_spinner_placeholder"><img src="images/searching_animation.gif"></div>

<div id="responsecontainer"></div>