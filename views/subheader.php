<?php
$offSiteUsers = new User();
$offSiteUsers = $offSiteUsers->offSiteUsers();

$singular = "There is currently " . count($offSiteUsers) . " user off-site";
$plural = "There are currently " . count($offSiteUsers) . " users off-site";
?>
<p><a href="index?node=report_offsite.php"><?php echo autoPluralise($singular, $plural, count($offSiteUsers)); ?></a></p>

<!--<p class="tagline">-->
<!--<a href="index?node=report_offsite.php">-->
<?php
//if (count($offSiteUsers) > 0) {
//	foreach ($offSiteUsers AS $userUID => $formalName) {
//		echo $formalName . " | ";
//	}
//}
?>
<!--</a>-->
<!--</p>-->