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
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main col-lg-12">
			<div class="page-title">
				<legend id="profile-title"><?php echo $lang["profile_likes"];?></legend>
				<span class="tip"><?php echo $lang["profile_likes_tip"];?></span>
			</div>
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
				<?php if($currentMood != ""){;?></div><?php } ?>
				<p class="sub-legend emotion-<?php echo $keyMood;?>"><span class="glyphicon glyphicon-<?php echo $icon;?>"></span> <?php echo $lang[$keyMood];?></p>
				<div>
				<?php } ?>
				<li class="vote-singleton vote-<?php echo $keyMood;?> container-fluid" id="vote-<?php echo $like["vote_id"];?>">
					<div class="video-thumbnail col-xs-12 col-sm-1">
						<img src="http://img.youtube.com/vi/<?php echo $like["link"];?>/0.jpg" alt="" class="img-responsive center-block">
					</div>
					<div class="container-fluid vote-details col-sm-11">
						<p class="vote-name col-xs-12 col-sm-11"><?php echo stripslashes($like["video_name"]);?></p>
						<span class="glyphicon glyphicon-trash glyphicon-button col-xs-2 col-sm-1" id="delete-<?php echo $like["vote_id"];?>" data-vote="<?php echo $like["vote_id"];?>" title="<?php echo $lang["delete"];?>"></span>
						<a href="https://www.youtube.com/watch?v=<?php echo $like["link"];?>" class="col-xs-8" target="_blank"><span class="glyphicon glyphicon-share"></span> <?php echo $lang["go_to_video"];?></a>
					</div>
				</li>
				<?php $currentMood = $like["vote_mood"];
				} ?>
			</ul>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(".glyphicon-trash").click(function(){
				var vote_id = document.getElementById($(this).attr("id")).dataset.vote;
				$.when(deleteEntry("votes", vote_id)).done(function(){
					$("#vote-"+vote_id).remove();
				})
			})
		</script>
	</body>
</html>
