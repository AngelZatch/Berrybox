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
			<div class="user-rooms col-xs-12">
				<?php while($historyRooms = $queryHistoryRooms->fetch(PDO::FETCH_ASSOC)){
	$roomInfo = $db->query("SELECT link, video_name, video_status FROM roomHistory_$historyRooms[box_token] rh
												JOIN song_base sb ON sb.song_base_id = rh.video_index
												WHERE video_status = 1 OR video_status = 2 ORDER BY playlist_order DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);?>
				<div class="col-md-6 col-lg-3 panel-box-container panel-box-active" id="panel-room-<?php echo $historyRooms["box_token"];?>">
					<?php if($historyRooms["room_active"] == 0){ ?>
					<div class="panel panel-box inactive">
						<?php } else { ?>
						<div class="panel panel-box active">
							<?php } ?>
							<div class="panel-body box-entry" onClick="window.location='box/<?php echo $historyRooms["box_token"];?>'">
								<p class="col-xs-12 room-name"><?php echo $historyRooms["room_name"];?></p>
								<div class="col-xs-12 room-details">
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
								<div class="col-lg-12 room-thumbnail">
									<img src="http://img.youtube.com/vi/<?php echo $roomInfo["link"];?>/0.jpg" alt="">
									<?php if($roomInfo["video_status"] == 1){ ?>
									<p id="current-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["now_playing_home"].stripslashes($roomInfo["video_name"]);?></p>
									<?php } else {?>
									<p id="previous-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["recently_played"].stripslashes($roomInfo["video_name"]);?></p>
									<?php } ?>
								</div>
								<p class="col-lg-12 room-description"><?php echo $historyRooms["room_description"];?></p>
							</div>
							<div class="panel panel-footer">
								<div class="container-fluid">
									<div class="col-lg-12">
										<?php if($historyRooms["room_active"] == '1'){ ?>
										<a href="box/<?php echo $historyRooms["box_token"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
										<?php } else { ?>
										<div class="col-lg-6">
											<a class="btn btn-primary btn-block" href="box/<?php echo $historyRooms["box_token"];?>"><?php echo $lang["room_join"];?></a>
										</div>
										<div class="col-lg-6">
											<a class="btn btn-danger btn-block" onClick="deleteRoom('<?php echo $historyRooms["box_token"];?>')"><?php echo $lang["room_delete"];?></a>
										</div>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
			<?php include "scripts.php";?>
			<script>
				function deleteRoom(roomToken){
					var panel = $("#panel-room-"+roomToken);
					$.post("functions/delete_room.php", {roomToken : roomToken}).done(function(data){
						panel.hide("500", function(){ panel.remove(); });
					})
				}
			</script>
			</body>
		</html>
