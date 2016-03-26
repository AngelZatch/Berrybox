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

$allLikes = $db->query("SELECT * FROM votes v
						JOIN song_base sb ON v.video_index = sb.song_base_id
						WHERE user_token = '$profileToken'
						ORDER BY vote_mood ASC");

?>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $_SESSION["username"];?></title>
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
				<legend id="profile-title"><?php echo $lang["profile_likes"];?></legend>
				<span class="tip"><?php echo $lang["profile_likes_tip"];?></span>
			</div>
			<div class="user-rooms col-sm-offset-2 col-sm-8">
				<ul class="likes-list">
					<?php
					$currentMood = ""; $moodLegend = "";
					while($like = $allLikes->fetch(PDO::FETCH_ASSOC)){
						if($currentMood !== $like["vote_mood"]){
							switch($like["vote_mood"]){
								case '1':
									$keyMood = "like";
									$icon = "thumbs-up";
									break;

								case '2':
									$keyMood = "cry";
									$icon = "tint";
									break;

								case '3':
									$keyMood = "love";
									$icon = "heart";
									break;

								case '4':
									$keyMood = "energy";
									$icon = "eye-open";
									break;

								case '5':
									$keyMood = "calm";
									$icon = "bed";
									break;

								case '6':
									$keyMood = "fear";
									$icon = "flash";
									break;
							} ?>
					<p class="sub-legend emotion-<?php echo $keyMood;?>"><span class="glyphicon glyphicon-<?php echo $icon;?>"></span> <?php echo $lang[$keyMood];?></p>
					<?php } ?>
					<li class="vote-singleton vote-<?php echo $keyMood;?>">
						<div class="video-thumbnail">
							<img src="http://img.youtube.com/vi/<?php echo $like["link"];?>/0.jpg" alt="">
						</div>
						<div>
							<p class="vote-name"><?php echo $like["video_name"];?></p>
							<a href="https://www.youtube.com/watch?v=<?php echo $like["link"];?>" target="_blank"><span class="glyphicon glyphicon-share"></span> <?php echo $lang["go_to_video"];?></a>
						</div>
					</li>
					<?php $currentMood = $like["vote_mood"];
					} ?>
				</ul>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
