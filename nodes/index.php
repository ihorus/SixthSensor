<?php
// build a chart showing recent activity levels
$chart = new Chart();
$chart = $chart->recentActivityTotals();
echo $chart;

// lookup user details from LDAP
$ldapUser = $session->findUser($session->serverUsername());
//printArray($ldapUser);
?>


<p>Welcome <?php echo $_SESSION['cUser']['firstname']; ?></p>