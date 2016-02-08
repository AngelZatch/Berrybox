<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
if(isset($_SESSION["token"])){
	$queryFollowing = $db->query("SELECT * FROM user_follow uf
								JOIN user u ON uf.user_followed = u.user_token
								WHERE user_following = '$_SESSION[token]'");
	$userSettings = $db->query("SELECT * FROM user_preferences up
							WHERE up_user_id='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);
	$listFollowed = array();

	if($userSettings["up_theme"] == "1"){
		$theme = "dark";
	} else {
		$theme = "light";
	}
}
?>
<html>
	<head>
		<meta charset="UTF-8">
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
		<div class="main">
			<legend><?php echo $lang["following"];?></legend>
			<div class="container-fluid">
				<h1><?php echo $lang["opened_rooms"];?></h1>
				<?php while($followedUser = $queryFollowing->fetch(PDO::FETCH_ASSOC)){
	$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_active = 1 AND room_protection != 2 AND room_creator = '$followedUser[user_token]'");
	array_push($listFollowed, $followedUser);
	while($activeRooms = $queryActiveRooms->fetch(PDO::FETCH_ASSOC)){
		$roomInfo = $db->query("SELECT link, video_name, video_status FROM roomHistory_$activeRooms[room_token] rh
												JOIN song_base sb ON sb.song_base_id = rh.video_index
												WHERE video_status = 1 OR video_status = 2 ORDER BY room_history_id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
				?>
				<div class="col-lg-4">
					<div class="panel panel-box" onClick="window.location='box/<?php echo $activeRooms["room_token"];?>'">
						<div class="panel-body box-entry">
							<p class="col-lg-12 room-name"><?php echo $activeRooms["room_name"];?></p>
							<div class="col-lg-12 room-thumbnail">
								<img src="http://img.youtube.com/vi/<?php echo $roomInfo["link"];?>/0.jpg" alt="">
								<?php if($roomInfo["video_status"] == 1){ ?>
								<p id="current-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["now_playing_home"].stripslashes($roomInfo["video_name"]);?></p>
								<?php } else {?>
								<p id="previous-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["recently_played"].stripslashes($roomInfo["video_name"]);?></p>
								<?php } ?>
							</div>
							<div class="room-pp">
								<img src="profile-pictures/<?php echo $activeRooms["user_pp"];?>" alt="<?php echo $activeRooms["user_pseudo"];?>" style="width:inherit;">
							</div>
							<div class="room-details">
								<p><span class="room-creator"><a href="user/<?php echo $activeRooms["user_pseudo"];?>"><?php echo $activeRooms["user_pseudo"];?></a></span></p>
								<p class="room-type room-label">
									<span class="label label-info"><?php echo $lang[$activeRooms["type"]];?></span>
									<?php if($activeRooms["room_protection"] == '1') { ?>
									<span class="label label-success"><?php echo $lang["level_public"];?></span>
									<?php } else { ?>
									<span class="label label-danger"><?php echo $lang["level_private"];?></span>
									<?php } ?>
									<span class="label label-lang"><?php echo $lang["lang_".$activeRooms["room_lang"]];?></span>
								</p>
							</div>
							<p class="col-lg-12 room-description"><?php echo $activeRooms["room_description"];?></p>
							<div class="col-lg-12">
								<a href="box/<?php echo $activeRooms["room_token"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
							</div>
						</div>
					</div>
				</div>
			<?php } } ?>
			</div>
			<div class="container-fluid">
				<h1><?php echo $lang["following"];?></h1>
				<?php foreach($listFollowed as $matchingUsers){ ?>
				<div class="col-lg-2">
					<div class="panel">
						<div class="panel-body user-entry">
							<a href="user/<?php echo $matchingUsers["user_pseudo"];?>">
								<div class="search-user-pp">
									<img src="profile-pictures/<?php echo $matchingUsers["user_pp"];?>" class="profile-picture">
								</div>
								<p class="user-profile-name"><?php echo $matchingUsers["user_pseudo"];?></p>
								<div class="user-profile-bio search-bio">
									<?php echo ($matchingUsers["user_bio"])?$matchingUsers["user_bio"]:$lang["no_bio"];?>
								</div>
								<button class="btn btn-primary btn-block"><?php echo $lang["goto_user"];?></button>
							</a>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				var autorefresh = setTimeout(function(){location.reload();}, 60000);
				document.onmousemove = function(){
					clearTimeout(autorefresh);
					autorefresh = setTimeout(function(){location.reload();}, 60000);
				}
			})
		</script>
	</body>
</html>
