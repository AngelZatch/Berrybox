<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
if(isset($_SESSION["token"])){
	$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_active = 1 AND (room_protection != 3 OR (room_protection = 3 AND room_creator = '$_SESSION[token]'))");
	$userSettings = $db->query("SELECT * FROM user_preferences up
							WHERE up_user_id='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);

	if($userSettings["up_theme"] == "1"){
		$theme = "dark";
	} else {
		$theme = "light";
	}
} else {
	$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_active = 1 AND room_protection != 3");
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>:(</title>
		<title>Berrybox</title>
		<?php include "styles.php";
		if(isset($_SESSION["token"])){ ?>
		<link rel="stylesheet" href="assets/css/<?php echo $theme;?>-theme.css">
		<?php } else { ?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
		<?php } ?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main" style="text-align: center">
			<div class="container-fluid">
				<div class="col-lg-3">
					<p id="not-found-title">:(</p>
				</div>
				<div class="col-lg-9">
					<p id="not-found-message"><?php echo $lang["404"];?></p>
				</div>
			</div>
			<a href="home" class="btn btn-primary btn-lg"><?php echo $lang["leave_404"];?></a>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
