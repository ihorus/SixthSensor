<h2>Active Scan</h2>
<img src="images/loading32.gif" id="scanningLoadingSpinner" style="float: right;" align="right"/>
<select id="sensorUID">
<?php
$sensors = new Sensor();
$sensors = $sensors->allSensors();

foreach ($sensors AS $sensor) {
	echo optionDropDown($sensor->uid, $sensor->sensorName(), "");
}	
?>
</select>

<input type="submit" id="activeScanToggle" value="Start Scanning" />

<div id="activeScanContainer">
	<div id="activeScanResultsContainer"></div>	
</div>
<div class="clear"></div>
<?php
$registeredTags = Tag::registeredTags();
$unregisteredTags = Tag::unregisteredTags();
$missingTags = Tag::missingTags();

echo "<div id=\"response_added\"></div>";

// show any tags that are on the system, but unregistered
if (count($unregisteredTags) > 0) {
	$singleMessage = "There is " . count($unregisteredTags) . " unregistered tag on the system";
	$pluralMessage = "There are " . count($unregisteredTags) . " unregistered tags on the system";
	echo "<h2>" . autoPluralise($singleMessage, $pluralMessage, count($unregisteredTags)) . "</h2>";
	
	foreach ($unregisteredTags AS $tag) {
		echo $tag->displayBlock();
	}
	echo "<br /><hr />";
}

echo "<h2>Registered Tags</h2>";
foreach ($registeredTags AS $tag) {
	echo $tag->displayBlock();
}

// show any tags that are on the system, but missing
if (count($missingTags) > 0) {
	$singleMessage = "There is " . count($missingTags) . " missing (lost) tag on the system";
	$pluralMessage = "There are " . count($missingTags) . " missing (lost) tags on the system";
	echo "<h2>" . autoPluralise($singleMessage, $pluralMessage, count($missingTags)) . "</h2>";
	
	foreach ($missingTags AS $tag) {
		echo $tag->displayBlock();
	}
	echo "<br /><hr />";
}
?>

