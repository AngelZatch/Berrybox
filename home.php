<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
if(isset($_SESSION["token"])){
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
	$userSettings = $db->query("SELECT up_theme, up_lang FROM user_preferences up
								WHERE user_token='$_SESSION[token]'")->fetch();

	if($userSettings["up_theme"] == "1"){
		$theme = "dark";
	} else {
		$theme = "light";
	}
} else {
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
			<!--			<div class="alert alert-danger">
<p class="alert-message"><?php echo $lang["maintenance"];?></p>
</div>-->
			<?php if(isset($_SESSION["token"]) && $userDetails["user_new"] == 1){ ?>
			<div class="jumbotron jumbotron-home jumbotron-welcome">
				<h1><?php echo $lang["hello"];?></h1>
				<h2><?php echo $lang["settings_prompt"];?></h2>
				<p><a href="profile/settings" class="btn btn-primary btn-inverted btn-lg"><?php echo $lang["my_settings"];?></a></p>
				<span class="small-link" id="dispel-jumbotron-welcome"><?php echo $lang["dispel"];?></span>
			</div>
			<?php } ?>
			<div class="container-fluid">
				<legend><?php echo $lang["active_room"];?></legend>
				<p class="custom-tip"><?php echo $lang["active_room_tip"];?></p>
				<div class="container-fluid featured-boxes col-lg-8 col-lg-offset-2">
				</div>
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
					<legend><?php echo $lang["follow_us"];?></legend>
					<a href="https://www.facebook.com/berryboxapp/" target="_blank" class="btn btn-primary">Facebook</a>
					<a href="http://twitter.com/BerryboxTV" target="_blank" class="btn btn-primary"><?php echo $lang["twitter"];?></a>
				</div>
			</div>
			<div class="jumbotron jumbotron-tip">
			<?php $rand = rand(1, 10);?>
				<p class="jumbo-tip-title">Berrytip #<?php echo $rand;?></p>
				<p><?php echo $lang["tip_".$rand];?></p>
			</div>
		</div>
		<?php include "footer.php";?>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				<?php if(isset($_SESSION["token"])){ ?>
				window.user_token = <?php echo json_encode($_SESSION["token"]);?>;
					<?php } ?>
				$.when(getUserLang()).done(function(data){
					window.language_tokens = JSON.parse(data);
					window.lang = language_tokens.user_lang;
					fetchBoxes($(".featured-boxes"), "featured");
					setInterval(fetchBoxes, 60000, $(".featured-boxes"), "featured");
				})
			}).on('click', '#dispel-jumbotron-welcome', function(){
				$(".jumbotron-welcome").hide();
				var update = {user_new : 0};
				$.when(updateEntry("user", $.param(update), user_token)).done(function(data){
					console.log(data);
				});
			})
		</script>
	</body>
</html>
