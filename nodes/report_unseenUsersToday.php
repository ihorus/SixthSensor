<?php
$sql  = "SELECT * FROM users WHERE tagUID NOT IN (SELECT tagUID FROM observations ";
$sql .= "WHERE DATE(datetime_service) = DATE(NOW()) ";
$sql .= "GROUP BY tagUID) ";
$sql .= "ORDER BY lastname ASC, firstname ASC";
		
$allUsers = new User();
$allUsers = $allUsers->find_by_sql($sql);

?>

<h2><?php echo count($allUsers); ?> Users that haven't signed in/out today</h2>
<?php
foreach ($allUsers AS $user) {
	echo $user->displayBlock();
}
?>