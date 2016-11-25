<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
if(isset($_SESSION["token"])){
	$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_active = 1 AND (room_protection != 2 OR (room_protection = 2 AND room_creator = '$_SESSION[token]'))");
	$queryMusicRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_type = 1 AND room_active = 1 AND (room_protection != 2 OR (room_protection = 2 AND room_creator = '$_SESSION[token]'))
								ORDER BY room_id DESC LIMIT 6");
	$queryScienceRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_type = 2 AND room_active = 1 AND (room_protection != 2 OR (room_protection = 2 AND room_creator = '$_SESSION[token]'))
								ORDER BY room_id DESC LIMIT 6");
	$queryComedyRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_type = 3 AND room_active = 1 AND (room_protection != 2 OR (room_protection = 2 AND room_creator = '$_SESSION[token]'))
								ORDER BY room_id DESC LIMIT 6");
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
								WHERE room_active = 1 AND room_protection != 2");
	$queryMusicRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_type = 1 AND room_active = 1 AND room_protection != 2
								ORDER BY room_id DESC LIMIT 6");
	$queryScienceRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_type = 2 AND room_active = 1 AND room_protection != 2
								ORDER BY room_id DESC LIMIT 6");
	$queryComedyRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_type = 3 AND room_active = 1 AND room_protection != 2
								ORDER BY room_id DESC LIMIT 6");
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Berrybox</title>
		<meta content="http://berrybox.tv/home" property="og:url">
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
			<?php if(!isset($_SESSION["token"])) { ?>
			<div class="jumbotron jumbotron-home">
				<h1><?php echo $lang["hello"];?></h1>
				<h2><?php echo $lang["berrybox_description"];?></h2>
				<p><a href="signup" class="btn btn-primary btn-lg"><?php echo $lang["get_started"];?></a></p>
			</div>
			<?php } ?>
			<!--<div class="alert alert-danger">
				<p class="alert-message"><?php echo $lang["maintenance"];?></p>
			</div>-->
			<legend><?php echo $lang["active_room"];?></legend>
			<div class="container-fluid">
				<?php while($activeRooms = $queryActiveRooms->fetch(PDO::FETCH_ASSOC)){
	$roomInfo = $db->query("SELECT link, video_name, video_status FROM roomHistory_$activeRooms[box_token] rh
												JOIN song_base sb ON sb.song_base_id = rh.video_index
												WHERE video_status = 1 OR video_status = 2 ORDER BY playlist_order DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
				?>
				<div class="col-lg-3 col-xs-6 panel-box-container">
					<div class="panel panel-box" onClick="window.location='box/<?php echo $activeRooms["box_token"];?>'">
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
									<span class="label label-lang"><?php echo $lang["lang_".$activeRooms["room_lang"]];?></span>
								</p>
							</div>
							<p class="col-lg-12 room-description"><?php echo $activeRooms["room_description"];?></p>
							<div class="col-lg-12">
								<a href="box/<?php echo $activeRooms["box_token"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
			<div class="container-fluid">
				<?php if(!isset($_SESSION["token"])) { ?>
				<a href="signup" class="btn btn-primary btn-block btn-lg"><?php echo $lang["home_create_room"];?></a>
				<?php } else { ?>
				<a href="create" class="btn btn-primary btn-block btn-lg"><?php echo $lang["home_create_room"];?></a>
				<?php } ?>

			</div>
			<div class="container-fluid social-space">
				<div class="col-lg-6 col-lg-offset-3">
					<p><?php echo $lang["follow_us"];?></p>
					<a href="https://www.facebook.com/berryboxapp/" target="_blank" class="btn btn-primary">Facebook</a>
					<a href="http://twitter.com/BerryboxTV" target="_blank" class="btn btn-primary"><?php echo $lang["twitter"];?></a>
				</div>
			</div>
			<?php if($queryMusicRooms->rowCount() != 0){ ?>
			<div class="container-fluid category-display">
				<h1><?php echo $lang["rt_music"];?></h1>
				<?php while($musicRooms = $queryMusicRooms->fetch(PDO::FETCH_ASSOC)){
	$roomInfo = $db->query("SELECT link, video_name, video_status FROM roomHistory_$musicRooms[box_token] rh
												JOIN song_base sb ON sb.song_base_id = rh.video_index
												WHERE video_status = 1 OR video_status = 2 ORDER BY room_history_id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
				?>
				<div class="col-lg-4 col-xs-6 panel-box-container">
					<div class="panel panel-box" onClick="window.location='box/<?php echo $musicRooms["box_token"];?>'">
						<div class="panel-body box-entry">
							<p class="col-lg-12 room-name"><?php echo $musicRooms["room_name"];?></p>
							<div class="col-lg-12 room-thumbnail">
								<img src="http://img.youtube.com/vi/<?php echo $roomInfo["link"];?>/0.jpg" alt="">
								<?php if($roomInfo["video_status"] == 1){ ?>
								<p id="current-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["now_playing_home"].stripslashes($roomInfo["video_name"]);?></p>
								<?php } else {?>
								<p id="previous-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["recently_played"].stripslashes($roomInfo["video_name"]);?></p>
								<?php } ?>
							</div>
							<div class="room-pp">
								<img src="profile-pictures/<?php echo $musicRooms["user_pp"];?>" alt="<?php echo $musicRooms["user_pseudo"];?>" style="width:inherit;">
							</div>
							<div class="room-details">
								<p><span class="room-creator"><a href="user/<?php echo $musicRooms["user_pseudo"];?>"><?php echo $musicRooms["user_pseudo"];?></a></span></p>
								<p class="room-type room-label">
									<span class="label label-info"><?php echo $lang[$musicRooms["type"]];?></span>
									<span class="label label-lang"><?php echo $lang["lang_".$musicRooms["room_lang"]];?></span>
								</p>
							</div>
							<p class="col-lg-12 room-description"><?php echo $musicRooms["room_description"];?></p>
							<div class="col-lg-12">
								<a href="box/<?php echo $musicRooms["box_token"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
			<?php if($queryScienceRooms->rowCount() != 0){ ?>
			<div class="container-fluid category-display">
				<h1><?php echo $lang["rt_science"];?></h1>
				<?php while($scienceRooms = $queryScienceRooms->fetch(PDO::FETCH_ASSOC)){
	$roomInfo = $db->query("SELECT link, video_name, video_status FROM roomHistory_$scienceRooms[box_token] rh
												JOIN song_base sb ON sb.song_base_id = rh.video_index
												WHERE video_status = 1 OR video_status = 2 ORDER BY room_history_id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
				?>
				<div class="col-lg-4 col-xs-6">
					<div class="panel panel-box" onClick="window.location='box/<?php echo $scienceRooms["box_token"];?>'">
						<div class="panel-body box-entry">
							<p class="col-lg-12 room-name"><?php echo $scienceRooms["room_name"];?></p>
							<div class="col-lg-12 room-thumbnail">
								<img src="http://img.youtube.com/vi/<?php echo $roomInfo["link"];?>/0.jpg" alt="">
								<?php if($roomInfo["video_status"] == 1){ ?>
								<p id="current-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["now_playing_home"].stripslashes($roomInfo["video_name"]);?></p>
								<?php } else {?>
								<p id="previous-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["recently_played"].stripslashes($roomInfo["video_name"]);?></p>
								<?php } ?>
							</div>
							<div class="room-pp">
								<img src="profile-pictures/<?php echo $scienceRooms["user_pp"];?>" alt="<?php echo $scienceRooms["user_pseudo"];?>" style="width:inherit;">
							</div>
							<div class="room-details">
								<p><span class="room-creator"><a href="user/<?php echo $scienceRooms["user_pseudo"];?>"><?php echo $scienceRooms["user_pseudo"];?></a></span></p>
								<p class="room-type room-label">
									<span class="label label-info"><?php echo $lang[$scienceRooms["type"]];?></span>
									<span class="label label-lang"><?php echo $lang["lang_".$scienceRooms["room_lang"]];?></span>
								</p>
							</div>
							<p class="col-lg-12 room-description"><?php echo $scienceRooms["room_description"];?></p>
							<div class="col-lg-12">
								<a href="box/<?php echo $scienceRooms["box_token"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
			<?php if($queryComedyRooms->rowCount()) { ?>
			<div class="container-fluid category-display">
				<h1><?php echo $lang["rt_lol"];?></h1>
				<?php while($comedyRooms = $queryComedyRooms->fetch(PDO::FETCH_ASSOC)){
	$roomInfo = $db->query("SELECT link, video_name, video_status FROM roomHistory_$comedyRooms[box_token] rh
												JOIN song_base sb ON sb.song_base_id = rh.video_index
												WHERE video_status = 1 OR video_status = 2 ORDER BY room_history_id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
				?>
				<div class="col-lg-4 col-xs-6">
					<div class="panel panel-box" onClick="window.location='box/<?php echo $comedyRooms["box_token"];?>'">
						<div class="panel-body box-entry">
							<p class="col-lg-12 room-name"><?php echo $comedyRooms["room_name"];?></p>
							<div class="col-lg-12 room-thumbnail">
								<img src="http://img.youtube.com/vi/<?php echo $roomInfo["link"];?>/0.jpg" alt="">
								<?php if($roomInfo["video_status"] == 1){ ?>
								<p id="current-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["now_playing_home"].stripslashes($roomInfo["video_name"]);?></p>
								<?php } else {?>
								<p id="previous-play"><span class="glyphicon glyphicon-play"></span> <?php echo $lang["recently_played"].stripslashes($roomInfo["video_name"]);?></p>
								<?php } ?>
							</div>
							<div class="room-pp">
								<img src="profile-pictures/<?php echo $comedyRooms["user_pp"];?>" alt="<?php echo $comedyRooms["user_pseudo"];?>" style="width:inherit;">
							</div>
							<div class="room-details">
								<p><span class="room-creator"><a href="user/<?php echo $comedyRooms["user_pseudo"];?>"><?php echo $comedyRooms["user_pseudo"];?></a></span></p>
								<p class="room-type room-label">
									<span class="label label-info"><?php echo $lang[$comedyRooms["type"]];?></span>
									<span class="label label-lang"><?php echo $lang["lang_".$comedyRooms["room_lang"]];?></span>
								</p>
							</div>
							<p class="col-lg-12 room-description"><?php echo $comedyRooms["room_description"];?></p>
							<div class="col-lg-12">
								<a href="box/<?php echo $comedyRooms["box_token"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
		<?php include "footer.php";?>
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
