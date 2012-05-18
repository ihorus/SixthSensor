<?php
require_once("../engine/initialise.php");

if (isset($_POST['tagUID'])) {
	$assign = new Tag();
	$assign->uid = $_POST['tagUID'];
	$assign->registerTag();
}
?>