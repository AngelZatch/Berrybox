<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();

if(isset($_SESSION["token"])){
	$profileToken = $_SESSION["token"];
	$userSettings = $db->query("SELECT *
							FROM user_preferences up
							WHERE user_token='$profileToken'")->fetch(PDO::FETCH_ASSOC);

	if($userSettings["up_theme"] == "1"){
		$theme = "dark";
	} else {
		$theme = "light";
	}
}

$profileDetails = $db->query("SELECT * FROM user u
							JOIN user_stats us ON u.user_token = us.user_token
							WHERE u.user_token='$profileToken'")->fetch(PDO::FETCH_ASSOC);

$queryHistoryRooms = $db->query("SELECT * FROM rooms r
							JOIN room_types rt ON r.room_type = rt.id
							WHERE r.room_creator='$profileToken'");

?>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $profileDetails["user_pseudo"];?></title>
		<base href="../">
		<?php include "styles.php";
		if(isset($_SESSION["token"])){ ?>
		<link rel="stylesheet" href="assets/css/<?php echo $theme;?>-theme.css">
		<?php } else { ?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
		<?php } ?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main col-lg-12">
			<div class="page-title">
				<legend id="profile-title"><?php echo $lang["profile_history"];?></legend>
				<span class="tip"><?php echo $lang["profile_history_tip"];?></span>
			</div>
			<div class="user-rooms col-xs-12"></div>
			<?php include "scripts.php";?>
			<script>
				$(document).ready(function(){
					$.when(getUserLang()).done(function(data){
						window.language_tokens = JSON.parse(data);
						window.lang = language_tokens.user_lang;
						fetchBoxes($(".user-rooms"), "private");
					})
				}).on('click', '.delete-box', function(e){
					e.stopImmediatePropagation();
					var box_token = $(this).data("box");
					deleteRoom(box_token);
				})
				function deleteRoom(box_token){
					var panel = $("#panel-box-"+box_token);
					$.post("functions/delete_room.php", {box_token : box_token}).done(function(data){
						panel.hide("500", function(){ panel.remove(); });
					})
				}
			</script>
			</body>
		</html>
