<style>
#backgroundPopup{
display:none;
position:fixed;
_position:absolute; /* hack for internet explorer 6*/
height:100%;
width:100%;
top:0;
left:0;
background:#000000;
z-index:1;
}
#popupContact{
display:none;
position:fixed;
_position:absolute; /* hack for internet explorer 6*/
height:225px;
width:300px;
z-index:2;
}


</style>
	

	
	<?php
require_once("engine/initialise.php");

include_once ("views/html_head.php");

if (isset($_POST['loginCheck']) && $_POST['username'] != "") {	
	$session->username = $_POST['username'];
	$session->password = $_POST['password'];

	if (!$session->ldapAuthenticate()) {
			// log the failed logon attempt
			$log = new Observations();
			$log->logType = "logAlert";
			$log->observationType = "system";
			$log->description = $_POST['username'] . " attempted to log on from IP: " . $_SERVER['REMOTE_ADDR'];
			$log->create();
	}
}
?>

<body>	
<div class="container">
	<div id="header"></div>
	<div id="subhead">
		<?php
		if ($session->is_logged_in() == TRUE && $session->is_in_group("Staff")) {
			include_once("views/subheader.php");
		}
		?>
	</div>
	<div id="navigation">
		<?php
				include_once("views/navigation.php");
		?>
	</div>
	<div id="backgroundPopup"></div>
	<div id="popupContact">
		<img src="images/confirm.png" alt="Go to yensdesign.com"/>
	</div>
	<div id="content">
		<?php			
		if ($session->is_logged_in() == TRUE && $session->is_in_group("Staff")) {
			// you're logged in - show the requested content
			if (isset($_GET['node'])){
				$node = $_GET['node'];
			} else {
				$node = "index.php";
			}
			include_once("nodes/" . $node);
		} else {
			// you're not logged in - show the login page
			//echo $session->is_logged_in();
			include_once("nodes/login.php");
		}
		 ?>
	</div>
	<div id="footer">
		<?php include_once("views/footer.php"); ?>
	</div>
</div>
</body>
</html>