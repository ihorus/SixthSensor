<?php
require_once("../engine/initialise.php");

if (isset($_POST['tagUID'])) {
	$tag = new Tag();
	$tag->uid = $_POST['tagUID'];
	$tag->serial = $_POST['tagSerial'];
	$tag->enabled = $_POST['enabled'];
	$tag->status = $_POST['status'];
	
	$tag->update();
}
?>