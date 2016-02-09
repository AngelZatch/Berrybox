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
				<?php while($historyRooms = $queryHistoryRooms->fetch(PDO::FETCH_ASSOC)){
	$roomInfo = $db->query("SELECT link, video_name, video_status FROM roomHistory_$historyRooms[room_token] rh
												JOIN song_base sb ON sb.song_base_id = rh.video_index
												WHERE video_status = 1 OR video_status = 2 ORDER BY room_history_id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);?>
				<div class="col-lg-6" id="panel-room-<?php echo $historyRooms["room_token"];?>">
					<div class="panel panel-box">
						<div class="panel-body box-entry" onClick="window.location='box/<?php echo $historyRooms["room_token"];?>'">
							<p class="col-lg-12 room-name"><?php echo $historyRooms["room_name"];?></p>
							<div class="col-lg-12" style="text-align:center">
								<?php if($historyRooms["room_active"] == 0){ ?>
								<p class="label-status">
									<span class="label label-danger label-block"><span class="glyphicon glyphicon-off"></span> <?php echo $lang["status_closed"];?></span>
								</p>
								<?php } else { ?>
								<p class="label-status">
									<span class="label label-success label-block"><span class="glyphicon glyphicon-signal"></span> <?php echo $lang["status_open"];?></span>
								</p>
								<?php } ?>
							</div>
							<div class="col-lg-12 room-thumbnail">
								<img src="http://img.youtube.com/vi/<?php echo $roomInfo["link"];?>/0.jpg" alt="">
								<?php if($roomInfo["video_status"] == 1){ ?>
								<p id="current-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["now_playing_home"].stripslashes($roomInfo["video_name"]);?></p>
								<?php } else {?>
								<p id="previous-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["recently_played"].stripslashes($roomInfo["video_name"]);?></p>
								<?php } ?>
							</div>
							<div class="room-pp">
								<img src="profile-pictures/<?php echo $profileDetails["user_pp"];?>" alt="<?php echo $profileDetails["user_pseudo"];?>" style="width:inherit;">
							</div>
							<div class="room-details">
								<p><span class="room-creator"><a href="user/<?php echo $profileDetails["user_pseudo"];?>"><?php echo $profileDetails["user_pseudo"];?></a></span></p>
								<p class="room-type room-label">
									<span class="label label-info"><?php echo $lang[$historyRooms["type"]];?></span>
									<?php if($historyRooms["room_protection"] == '1') { ?>
									<span class="label label-success"><?php echo $lang["level_public"];?></span>
									<?php } else { ?>
									<span class="label label-danger"><?php echo $lang["level_private"];?></span>
									<?php } ?>
									<span class="label label-lang"><?php echo $lang["lang_".$historyRooms["room_lang"]];?></span>
								</p>
							</div>
							<p class="col-lg-12 room-description"><?php echo $historyRooms["room_description"];?></p>
						</div>
						<div class="panel panel-footer">
							<div class="col-lg-12">
								<?php if($historyRooms["room_active"] == '1'){ ?>
								<a href="box/<?php echo $historyRooms["room_token"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
								<?php } else { ?>
								<div class="col-lg-6">
									<a class="btn btn-primary btn-block" onClick="openRoom('<?php echo $historyRooms["room_token"];?>')"><?php echo $lang["room_reopen"];?></a>
								</div>
								<div class="col-lg-6">
									<a class="btn btn-danger btn-block" onClick="deleteRoom('<?php echo $historyRooms["room_token"];?>')"><?php echo $lang["room_delete"];?></a>
								</div>
								<?php } ?>
							</div>
						</div>
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
				$.post("functions/delete_room.php", {roomToken : roomToken}).done(function(data){
					panel.hide("500", function(){ panel.remove(); });
				})
			}
		</script>
	</body>
</html>
