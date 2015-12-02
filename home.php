<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
if(isset($_SESSION["token"])){
	$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
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
								WHERE room_active = 1 AND room_protection != 3");
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
		<div class="main row">
			<?php if(!isset($_SESSION["token"])) { ?>
			<div class="container">
				<div class="jumbotron">
					<h1><?php echo $lang["hello"];?></h1>
					<p><?php echo $lang["berrybox_description"];?></p>
					<p><a href="signup.php" class="btn btn-primary btn-block btn-lg"><?php echo $lang["get_started"];?></a></p>
				</div>
			</div>
			<?php } ?>
			<div id="large-block">
				<p id="active-rooms-title"><?php echo $lang["active_room"];?></p>
				<div class="container-fluid">
					<?php while($activeRooms = $queryActiveRooms->fetch(PDO::FETCH_ASSOC)){ ?>
					<div class="panel panel-active-room">
						<div class="panel-body">
							<p class="col-lg-3"><?php echo $activeRooms["room_name"];?></p>
							<p class="col-lg-3"><a href="user.php?id=<?php echo $activeRooms["user_token"];?>&lang=<?php echo $_GET["lang"];?>"><?php echo $activeRooms["user_pseudo"];?></a></p>
							<div class="col-lg-6">
								<?php if($activeRooms["room_protection"] == 2 && (!isset($_SESSION["token"]) || (isset($_SESSION["token"]) && $_SESSION["token"] != $activeRooms["room_creator"]))){?>
								<p class="error-password" style="display:none;"><?php echo $lang["wrong_password"];?></p>
								<input type="password" class="form-control password-input" placeholder="<?php echo $lang["password"];?>" name="password" id="password-<?php echo $activeRooms["room_token"];?>" style="display:none;">
								<a class="btn btn-primary btn-block password-protected"><?php echo $lang["room_join"];?></a>
								<?php } else { ?>
								<a href="room.php?id=<?php echo $activeRooms["room_token"];?>&lang=<?php echo $_GET["lang"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php } ?>
					<a href="create_room.php" class="btn btn-primary btn-block btn-lg"><?php echo $lang["home_create_room"];?></a>
				</div>
			</div>
			<div class="col-lg-12 social-space">
			<div class="col-lg-12 social-space">
				<div class="col-lg-6 col-lg-offset-3">
					<p><?php echo $lang["follow_us"];?></p>
					<a href="http://twitter.com/AngelZatch" target="_blank" class="btn btn-primary btn-style-default"><?php echo $lang["twitter"];?></a>
				</div>
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
									window.location.replace("room.php?id="+roomToken+"&lang=<?php echo $_GET["lang"];?>");
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
