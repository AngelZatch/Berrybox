<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
if(isset($_SESSION["token"])){
	$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_active = 1 AND (room_protection != 3 OR (room_protection = 3 AND room_creator = '$_SESSION[token]'))");
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
								WHERE room_active = 1 AND room_protection != 3");
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Berrybox</title>
		<meta content="Berrybox is an app to share and watch YouTube videos together. Users can share their favorite videos, chat and react about them in real time.">
		<meta content="Berrybox" property="og:site_name">
		<meta content="Berrybox" property="og:title">
		<meta content="Berrybox is an app to share, watch and react to YouTube videos together." property="og:description">
		<meta content="http://berrybox.tv/home" property="og:url">
		<meta content="website" property="og:type">
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
			<legend><?php echo $lang["active_room"];?></legend>
			<div class="container-fluid">
				<?php while($activeRooms = $queryActiveRooms->fetch(PDO::FETCH_ASSOC)){ ?>
				<div class="col-lg-4">
					<div class="panel" onClick="window.location='box/<?php echo $activeRooms["room_token"];?>'">
						<div class="panel-body box-entry">
							<p class="col-lg-12 room-name"><?php echo $activeRooms["room_name"];?></p>
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
									<span class="label label-warning"><?php echo $lang["password"];?></span>
									<?php } ?>
									<span class="label label-lang"><?php echo $lang["lang_".$activeRooms["room_lang"]];?></span>
								</p>
							</div>
							<p class="col-lg-12 room-description"><?php echo $activeRooms["room_description"];?></p>
							<div class="col-lg-12">
								<?php if($activeRooms["room_protection"] == 2 && (!isset($_SESSION["token"]) || (isset($_SESSION["token"]) && $_SESSION["token"] != $activeRooms["room_creator"]))){?>
								<p class="error-password" style="display:none;"><?php echo $lang["wrong_password"];?></p>
								<input type="password" class="form-control password-input" placeholder="<?php echo $lang["password"];?>" name="password" id="password-<?php echo $activeRooms["room_token"];?>" style="display:none;">
								<a class="btn btn-primary btn-block password-protected"><?php echo $lang["room_join"];?></a>
								<?php } else { ?>
								<a href="box/<?php echo $activeRooms["room_token"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
								<?php } ?>
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
		</div>
		<div class="col-lg-12 social-space">
			<div class="col-lg-6 col-lg-offset-3">
				<p><?php echo $lang["follow_us"];?></p>
				<a href="http://twitter.com/AngelZatch" target="_blank" class="btn btn-primary"><?php echo $lang["twitter"];?></a>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				$(".password-protected").click(function(){
					var joinButton = $(this);
					joinButton.hide('200');
					var passwordInput = $(this).prev();
					passwordInput.show('200');
					passwordInput.focus();
				})
				$('.password-input').on('focus',function(){
					$(this).keyup(function(event){
						if(event.keyCode == 27){
							$(this).hide('200');
							$(this).next().show('200');
						}
						if(event.keyCode == 13){
							var password = $(this).val();
							var roomToken = $(this).attr('id').substr(9);
							$.post("functions/submit_password.php", {password : password, roomToken : roomToken}).success(function(data){
								if(data == 1){
									window.location.replace("box/"+roomToken);
								} else {
									$("#password-"+roomToken).val('');
									$("#password-"+roomToken).prev().show();
								}
							})
						}
					})
				}).on('blur', function(){
					$(this).hide('200');
					$(this).next().show('200');
				})
			})
		</script>
	</body>
</html>
