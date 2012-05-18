<?php
require_once("../engine/initialise.php");
printArray($_POST);

if (isset($_POST['userUIDS'])) {
	//$userUIDSArray = sanitise_array(explode(",", $_POST['userUIDS']));
	$userUIDSArray = (explode(",", $_POST['userUIDS']));
	printArray($userUIDSArray);
	if (count($userUIDSArray) > 0) {
		foreach ($userUIDSArray AS $userUID) {
			$user = new User();
			$user->uid = $userUID;
			$user = $user->findByUID();
			
			$observation = new Observations();
			$observation->username = $session->serverUsername();
			$observation->tagUID = $user->tagUID;
			$observation->observationType = "tagIn";
			$observation->description = $session->serverUsername() . " manually signed " . $user->username . " back in";
			$observation->logType = "logInfo";
			
			$observation->create();
		}
	}
}
?>