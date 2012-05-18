<?php
$allUsers = User::allUsers();

foreach ($allUsers AS $user) {
	echo $user->displayBlock();
}
//
?>

