<?php
$sql  = "SELECT * FROM users WHERE tagUID IN (SELECT tagUID FROM observations ";
$sql .= "WHERE DATE(datetime_service) = DATE(NOW()) ";
$sql .= "AND sensorUID IN (21, 22) ";
$sql .= "GROUP BY tagUID) ";
$sql .= "ORDER BY lastname ASC, firstname ASC";
		
$allUsers = new User();
$allUsers = $allUsers->find_by_sql($sql);


?>

<h2><?php echo count($allUsers); ?> Users that have signed in as studying today</h2>
<?php
foreach ($allUsers AS $user) {
	echo $user->displayBlock();
}
?>