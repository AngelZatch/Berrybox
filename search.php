<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();

$searchToken = $_POST["search-terms"];

if(isset($_SESSION["token"])){
	$userSettings = $db->query("SELECT * FROM user_preferences up
							WHERE up_user_id='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);

	if($userSettings["up_theme"] == "1"){
		$theme = "dark";
	} else {
		$theme = "light";
	}
	$userLang = $userSettings["up_lang"];
	if($userLang == ""){
		$userLang = "en";
	}
	include_once "languages/lang.".$userLang.".php";

	//search for boxes matching
	$queryMatchingBoxes = $db->query("SELECT * FROM rooms r
									JOIN user u ON r.room_creator = u.user_token
									JOIN room_types rt ON r.room_type = rt.id
WHERE (room_name LIKE '%{$searchToken}%' OR user_pseudo LIKE '%{$searchToken}%') AND room_active = '1' AND (room_protection != 3 OR (room_protection = 3 AND room_creator = '$_SESSION[token]')) ORDER BY room_name ASC");
} else {
	include_once "languages/lang.en.php";

	//search for boxes matching
	$queryMatchingBoxes = $db->query("SELECT * FROM rooms r
									JOIN user u ON r.room_creator = u.user_token
									JOIN room_types rt ON r.room_type = rt.id
WHERE room_name LIKE '%{$searchToken}%' AND room_active = '1' AND room_protection != '3' ORDER BY room_name ASC");
}

//search for users matching (and their boxes if they have some opened)
$queryMatchingUsers = $db->query("SELECT * FROM user u WHERE user_pseudo LIKE '%{$searchToken}%' ORDER BY user_pseudo ASC");
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
			<legend><?php echo $lang["search"];?></legend>
			<div class="col-lg-offset-2 col-lg-8">
				<p class="search-matching-title"><?php echo $queryMatchingUsers->rowCount()." ".$lang["user_match"];?></p>
				<div class="container-fluid">
					<?php while($matchingUsers = $queryMatchingUsers->fetch(PDO::FETCH_ASSOC)){ ?>
					<div class="col-lg-3">
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
				<p class="search-matching-title"><?php echo $queryMatchingBoxes->rowCount()." ".$lang["box_match"];?></p>
				<div class="container-fluid">
					<?php while ($activeRooms = $queryMatchingBoxes->fetch(PDO::FETCH_ASSOC)){ ?>
					<div class="col-lg-6">
						<div class="panel">
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
			});
		</script>
	</body>
</html>
