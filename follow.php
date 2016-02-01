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
				<?php while($followedUsers = $queryFollowing->fetch(PDO::FETCH_ASSOC)){
	$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								JOIN room_types rt ON r.room_type = rt.id
								WHERE room_active = 1 AND room_protection != 2 AND room_creator = '$followedUsers[user_token]'");
				?>
				<?php while($activeRooms = $queryActiveRooms->fetch(PDO::FETCH_ASSOC)){ ?>
				<div class="col-lg-4">
					<div class="panel box-entry">
						<div class="panel-body">
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
				<?php } ?>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
