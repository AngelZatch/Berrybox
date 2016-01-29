<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();

if(isset($_SESSION["token"])){
	$profileToken = $_SESSION["token"];
	$userSettings = $db->query("SELECT *
							FROM user_preferences up
							WHERE up_user_id='$profileToken'")->fetch(PDO::FETCH_ASSOC);

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
		<link rel="stylesheet" href="assets/css/fileinput.min.css">
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main col-lg-12">
			<div class="col-sm-offset-2 col-sm-8 page-title">
				<p id="profile-title"><?php echo $lang["profile_history"];?></p>
				<span class="tip"><?php echo $lang["profile_history_tip"];?></span>
				<ul class="nav nav-tabs" id="profile-menu">
					<li role="presentation"><a href="profile/settings"><?php echo $lang["my_settings"];?></a></li>
					<li role="presentation" class="active"><a href="profile/history"><?php echo $lang["profile_history"];?></a></li>
					<li role="presentation"><a href="profile/security"><?php echo $lang["profile_security"];?></a></li>
				</ul>
			</div>
			<div class="user-rooms col-sm-offset-2 col-sm-8">
				<?php while($historyRooms = $queryHistoryRooms->fetch(PDO::FETCH_ASSOC)){?>
				<div class="panel box-entry" id="panel-room-<?php echo $historyRooms["room_token"];?>" onClick="window.location='box/<?php echo $historyRooms["room_token"];?>'">
					<div class="panel-body room-details">
						<p class="col-lg-5"><?php echo $historyRooms["room_name"];?></p>
						<p class="col-lg-3 room-type room-label">
							<span class="label label-info"><?php echo $lang[$historyRooms["type"]];?></span>
							<?php if($historyRooms["room_protection"] == '1') { ?>
							<span class="label label-success"><?php echo $lang["level_public"];?></span>
							<?php } else { ?>
							<span class="label label-warning"><?php echo $lang["password"];?></span>
							<?php } ?>
							<span class="label label-lang"><?php echo $lang["lang_".$historyRooms["room_lang"]];?></span>
						</p>
						<?php if($historyRooms["room_active"] == '1'){ ?>
						<p class="col-lg-1 label-status">
							<span class="label label-success"><span class="glyphicon glyphicon-signal"></span> <?php echo $lang["status_open"];?></span>
						</p>
						<div class="col-lg-3">
							<a href="box/<?php echo $historyRooms["room_token"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
						</div>
						<?php } else { ?>
						<p class="col-lg-1 label-status">
							<span class="label label-danger"><span class="glyphicon glyphicon-off"></span> <?php echo $lang["status_closed"];?></span>
						</p>
						<div class="col-lg-3">
							<a class="btn btn-primary" onClick="openRoom('<?php echo $historyRooms["room_token"];?>')"><?php echo $lang["room_reopen"];?></a>
							<a class="btn btn-danger" onClick="deleteRoom('<?php echo $historyRooms["room_token"];?>')"><?php echo $lang["room_delete"];?></a>
						</div>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/fileinput.min.js"></script>
		<script>
			function openRoom(roomToken){
				$.post("functions/reopen_room.php", {roomToken : roomToken}).done(function(data){
					window.location.replace("box/"+roomToken);
				})
			}
			function deleteRoom(roomToken){
				var panel = $("#panel-room-"+roomToken);
				console.log(panel);
				$.post("functions/delete_room.php", {roomToken : roomToken}).done(function(data){
					panel.hide("500", function(){ panel.remove(); });
				})
			}
		</script>
	</body>
</html>
