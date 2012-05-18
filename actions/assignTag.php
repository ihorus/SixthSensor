<?php
require_once("../engine/initialise.php");
//printArray($_POST);

if (isset($_POST['tagUID'])) {
	$assign = new Tag();
	$assign->uid = $_POST['tagUID'];
	$assign->username = $_POST['assignUser'];
	$assign->assignTag();
}
?>