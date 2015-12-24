<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();

if(isset($_SESSION["token"])){
	$userSettings = $db->query("SELECT *
							FROM user_preferences up
							WHERE up_user_id='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);

	if($userSettings["up_theme"] == "1"){
		$theme = "dark";
	} else {
		$theme = "light";
	}
}

$profileToken = $_GET["id"];

$profileDetails = $db->query("SELECT * FROM user u
							JOIN user_stats us ON u.user_token = us.user_token
							WHERE u.user_token='$profileToken'")->fetch(PDO::FETCH_ASSOC);

$queryactiveRooms = $db->query("SELECT * FROM rooms r
							JOIN room_types rt ON r.room_type = rt.id
							WHERE r.room_creator = '$profileToken' AND room_active = '1' AND room_protection != '3'");

?>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $profileDetails["user_pseudo"];?></title>
		<base href="../../">
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
		<div class="main col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
			<div class="user-profile-details">
				<div class="user-profile-picture">
					<img src="profile-pictures/<?php echo $profileDetails["user_pp"];?>" class="profile-picture">
				</div>
				<p class="user-profile-name"><?php echo $profileDetails["user_pseudo"];?></p>
				<div class="user-profile-bio">
					<?php echo ($profileDetails["user_bio"])?$profileDetails["user_bio"]:$lang["no_bio"];?>
				</div>
			</div>
			<div class="user-profile-stats">
				<div class="col-lg-4 col-md-4">
					<p class="stats-title"><?php echo $lang["rooms_created"];?></p>
					<p class="stats-value"><?php echo $profileDetails["stat_rooms_created"];?></p>
				</div>
				<div class="col-lg-4 col-md-4">
					<p class="stats-title"><?php echo $lang["songs_submitted"];?></p>
					<p class="stats-value"><?php echo $profileDetails["stat_songs_submitted"];?></p>
				</div>
				<div class="col-lg-4 col-md-4">
					<p class="stats-title"><?php echo $lang["total_views"];?></p>
					<p class="stats-value"><?php echo $profileDetails["stat_visitors"];?></p>
				</div>
			</div>
			<div class="user-rooms">
				<p id="profile-title"><?php echo $lang["opened_rooms"];?></p>
				<?php while($activeRooms = $queryactiveRooms->fetch(PDO::FETCH_ASSOC)){ ?>
				<div class="panel panel-active-room">
					<div class="panel-body">
						<p class="col-lg-4"><?php echo $activeRooms["room_name"];?></p>
						<div class="room-details col-lg-2">
							<p class="room-type room-label">
								<span class="label label-info"><?php echo $lang[$activeRooms["type"]];?></span>
								<?php if($activeRooms["room_protection"] == '1') { ?>
								<span class="label label-success"><?php echo $lang["level_public"];?></span>
								<?php } else { ?>
								<span class="label label-warning"><?php echo $lang["password"];?></span>
								<?php } ?>
							</p>
						</div>
						<div class="col-lg-6">
							<?php if($activeRooms["room_protection"] == 2 && (!isset($_SESSION["token"]) || (isset($_SESSION["token"]) && $_SESSION["token"] != $activeRooms["room_creator"]))){?>
							<p class="error-password" style="display:none;"><?php echo $lang["wrong_password"];?></p>
							<input type="password" class="form-control password-input" placeholder="<?php echo $lang["password"];?>" name="password" id="password-<?php echo $activeRooms["room_token"];?>" style="display:none;">
							<a class="btn btn-primary btn-block password-protected"><?php echo $lang["room_join"];?></a>
							<?php } else { ?>
							<a href="<?php echo $_GET["lang"];?>/room/<?php echo $activeRooms["room_token"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php } ?>
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
									window.location.replace("<?php echo $_GET["lang"];?>/room/"+roomToken);
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
