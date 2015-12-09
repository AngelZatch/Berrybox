<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();

if(isset($_SESSION["token"])){
	$userSettings = $db->query("SELECT *
							FROM user_preferences up
							WHERE up_user_id='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);

	if($userSettings["up_theme"] == "1"){
		$theme = "dark";
	} else {
		$theme = "light";
	}
}

$profileToken = $_GET["id"];

$profileDetails = $db->query("SELECT * FROM user u
							JOIN user_stats us ON u.user_token = us.user_token
							WHERE u.user_token='$profileToken'")->fetch(PDO::FETCH_ASSOC);

?>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $profileDetails["user_pseudo"];?></title>
		<base href="../../">
		<?php include "styles.php";
		if(isset($_SESSION["token"])){ ?>
		<link rel="stylesheet" href="assets/css/<?php echo $theme;?>-theme.css">
		<?php } else { ?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
		<?php } ?>
		<link rel="stylesheet" href="assets/css/fileinput.min.css">
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
			<div class="user-profile-details">
				<div class="user-profile-picture">
					<img src="profile-pictures/<?php echo $profileDetails["user_pp"];?>" class="profile-picture">
				</div>
				<p class="user-profile-name"><?php echo $profileDetails["user_pseudo"];?></p>
				<div class="user-profile-bio">
					<?php echo ($profileDetails["user_bio"])?$profileDetails["user_bio"]:$lang["no_bio"];?>
				</div>
			</div>
			<div class="user-profile-stats">
				<div class="col-lg-4 col-md-4">
					<p class="stats-title"><?php echo $lang["rooms_created"];?></p>
					<p class="stats-value"><?php echo $profileDetails["stat_rooms_created"];?></p>
				</div>
				<div class="col-lg-4 col-md-4">
					<p class="stats-title"><?php echo $lang["songs_submitted"];?></p>
					<p class="stats-value"><?php echo $profileDetails["stat_songs_submitted"];?></p>
				</div>
				<div class="col-lg-4 col-md-4">
					<p class="stats-title"><?php echo $lang["total_views"];?></p>
					<p class="stats-value"><?php echo $profileDetails["stat_visitors"];?></p>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/fileinput.min.js"></script>
	</body>
</html>
