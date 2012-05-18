<?php
$tabClass = "inactive";
$tab2Class = "inactive";
$tab3Class = "inactive";
$tab4Class = "inactive";
$tab5Class = "inactive";

	if (isset($_GET['node'])) {
		if ($_GET['node'] == "users.php") {
			$tab2Class = "active";
		} elseif ($_GET['node'] == "tags.php") {
			$tab3Class = "active";
		} elseif ($_GET['node'] == "search.php") {
			$tab4Class = "active";
		} elseif ($_GET['node'] == "admin.php") {
			$tab5Class = "active";
		}
	} else {
		$tabClass = "active";
	}
	
	
?>

<ul id="toolbar">
	<li id="new-tab" class="<?php echo $tabClass; ?>"><a href="index.php" title="Home">Home</a></li>
	<li id="new-tab2" class="<?php echo $tab2Class; ?>"><a href="index.php?node=users.php" title="Users">Users</a></li>
	<li id="new-tab3" class="<?php echo $tab3Class; ?>"><a href="index.php?node=tags.php" title="Tags">Tags</a></li>
	<li id="new-tab4" class="<?php echo $tab4Class; ?>"><a href="index.php?node=search.php" title="Search">Search</a></li>
	<li id="new-tab5" class="<?php echo $tab5Class; ?>"><a href="index.php?node=admin.php" title="Admin">Admin</a></li>
</ul>