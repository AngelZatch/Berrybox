<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();

$profileToken = $_GET["id"];

$checkUserExistence = $db->query("SELECT * FROM user u
							JOIN user_stats us ON u.user_token = us.user_token
							WHERE u.user_pseudo='$profileToken'");

if($checkUserExistence->rowCount() != "0"){ // Check for box existence.
	$profileDetails = $checkUserExistence->fetch(PDO::FETCH_ASSOC);
} else {
	header('Location: ../404');
}

if(isset($_SESSION["token"])){
	$queryactiveRooms = $db->query("SELECT * FROM rooms r
							JOIN room_types rt ON r.room_type = rt.id
							WHERE r.room_creator = '$profileDetails[user_token]' AND room_active = '1'");

	$userSettings = $db->query("SELECT *
							FROM user_preferences up
							WHERE up_user_id='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);

	if($userSettings["up_theme"] == "1"){
		$theme = "dark";
	} else {
		$theme = "light";
	}

	if(isset($_POST["submit"])){
		if($_FILES["profile-banner"]["name"]){
			$picture = $_SESSION["token"].".".pathinfo($_FILES["profile-banner"]["name"], PATHINFO_EXTENSION);
			move_uploaded_file($_FILES["profile-banner"]["tmp_name"], "profile-banners/".$picture);
			//Writing in the table the modifications
			$edit = $db->query("UPDATE user SET	user_banner = '$picture' WHERE user_token = '$_SESSION[token]'");
		}
	}
	$userFollow = $db->query("SELECT * FROM user_follow uf
								WHERE user_following = '$_SESSION[token]'
								AND user_followed = '$profileDetails[user_token]'")->rowCount();
} else {
	$queryactiveRooms = $db->query("SELECT * FROM rooms r
							JOIN room_types rt ON r.room_type = rt.id
							WHERE r.room_creator = '$profileDetails[user_token]' AND room_active = '1' AND room_protection != '3'");
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $profileDetails["user_pseudo"];?></title>
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
		<div class="main">
			<div class="banner-container">
				<?php if(isset($_SESSION["username"]) && $profileToken == $_SESSION["username"]){ ?>
				<form action="user/<?php echo $profileToken;?>" method="post" enctype="multipart/form-data">
					<div id="banner">
					</div>
					<?php } else { ?>
					<div id="banner">
						<img src="profile-banners/<?php echo $profileDetails['user_banner'];?>">
					</div>
					<?php } ?>
					<div class="user-profile-container">
						<div class="user-profile-details col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
							<div class="user-profile-picture">
								<img src="profile-pictures/<?php echo $profileDetails["user_pp"];?>" class="profile-picture">
							</div>
							<div class="user-actions">
								<?php if(isset($_SESSION["username"])){
	if($_SESSION["username"] != $profileToken){ ?>
								<?php if($userFollow == 1){ ?>
								<button class="btn btn-primary btn-active btn-unfollow" id="user-page-unfollow" value="<?php echo $profileToken;?>"><span class="glyphicon glyphicon-heart"></span> <?php echo $lang['following'];?></button>
								<?php } else { ?>
								<button class="btn btn-primary btn-follow" id="user-page-follow" value="<?php echo $profileToken;?>"><span class="glyphicon glyphicon-heart"></span> <?php echo $lang['follow'];?></button>
								<?php } } else { ?>
								<input type="file" id="banner-input" name="profile-banner" class="file-loading">
								<input type="submit" class="btn btn-success btn-block" name="submit" value="<?php echo $lang["save_changes"];?>">
								<?php } } else { ?>
								<a href="signup" class="btn btn-primary">Register to follow this user</a>
								<?php } ?>
							</div>
							<p class="user-profile-name"><?php echo $profileDetails["user_pseudo"];?></p>
							<div class="user-profile-bio">
								<?php echo ($profileDetails["user_bio"])?$profileDetails["user_bio"]:$lang["no_bio"];?>
							</div>
						</div>
					</div>
					<?php if($profileToken == $_SESSION["username"]){ ?>
				</form>
				<?php } ?>
			</div>
			<div class="user-profile-stats col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
				<div class="col-lg-3 col-md-3">
					<p class="stats-title"><?php echo $lang["rooms_created"];?></p>
					<p class="stats-value"><?php echo $profileDetails["stat_rooms_created"];?></p>
				</div>
				<div class="col-lg-3 col-md-3">
					<p class="stats-title"><?php echo $lang["songs_submitted"];?></p>
					<p class="stats-value"><?php echo $profileDetails["stat_songs_submitted"];?></p>
				</div>
				<div class="col-lg-3 col-md-3">
					<p class="stats-title"><?php echo $lang["total_views"];?></p>
					<p class="stats-value"><?php echo $profileDetails["stat_visitors"];?></p>
				</div>
				<div class="col-lg-3 col-md-3">
					<p class="stats-title"><?php echo $lang["total_followers"];?></p>
					<p class="stats-value"><?php echo $profileDetails["stat_followers"];?></p>
				</div>
			</div>
			<div class="user-rooms col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
				<p id="profile-title"><?php echo $lang["opened_rooms"];?></p>
				<?php while($activeRooms = $queryactiveRooms->fetch(PDO::FETCH_ASSOC)){ ?>
				<div class="panel box-entry" onClick="window.location='box/<?php echo $activeRooms["room_token"];?>'">
					<div class="panel-body">
						<p class="col-lg-5"><?php echo $activeRooms["room_name"];?></p>
						<div class="room-details col-lg-3">
							<p class="room-type room-label">
								<span class="label label-info"><?php echo $lang[$activeRooms["type"]];?></span>
								<?php if($activeRooms["room_protection"] == '1') { ?>
								<span class="label label-success"><?php echo $lang["level_public"];?></span>
								<?php } else if($activeRooms["room_protection"] == '1') { ?>
								<span class="label label-warning"><?php echo $lang["password"];?></span>
								<?php } else { ?>
								<span class="label label-danger"><?php echo $lang["level_private"];?></span>
								<?php } ?>
								<span class="label label-lang"><?php echo $lang["lang_".$activeRooms["room_lang"]];?></span>
							</p>
						</div>
						<div class="col-lg-4">
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
				<?php } ?>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/fileinput.min.js"></script>
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
				$("#banner-input").fileinput({
					overwriteInitial: true,
					defaultPreviewContent: '<img src="profile-banners/<?php echo $profileDetails["user_banner"];?>">',
					showClose: false,
					showCaption: false,
					initialPreviewShowDelete: true,
					browseIcon: '',
					browseLabel: '<?php echo $lang["change_banner"];?>',
					removeLabel: '<?php echo $lang["cancel"];?>',
					removeClass: 'btn btn-danger',
					elPreviewImage: '#banner',
					layoutTemplates: {main2: '{browse} {remove}'},
					allowedFileExtensions: ["jpg", "png", "jpeg"]
				})
			}).on('mouseenter', '#user-page-unfollow', function(){
				var text = "<span class='glyphicon glyphicon-minus'></span> <?php echo $lang['unfollow'];?>";
				$("#user-page-unfollow").html(text);
				$("#user-page-unfollow").removeClass("btn-active");
				$("#user-page-unfollow").addClass("btn-danger");
			}).on('mouseleave', '#user-page-unfollow', function(){
				var text = "<span class='glyphicon glyphicon-heart'></span> <?php echo $lang['following'];?>";
				$("#user-page-unfollow").html(text);
				$("#user-page-unfollow").removeClass("btn-danger");
				$("#user-page-unfollow").addClass("btn-active");
			})
		</script>
		<?php if(isset($_SESSION["token"])){ ?>
		<script>
			$(document).on('click', '#user-page-unfollow', function(){
				$.post("functions/unfollow_user.php", {userFollowing : '<?php echo $_SESSION["token"];?>', userFollowed : '<?php echo $profileToken;?>'}).done(function(data){
					$("#user-page-unfollow").removeClass("btn-active");
					var text = "<span class='glyphicon glyphicon-heart'></span> <?php echo $lang['follow'];?>";
					$("#user-page-unfollow").html(text);
					$("#user-page-unfollow").removeClass("btn-danger");
					$("#user-page-unfollow").removeClass("btn-unfollow");
					$("#user-page-unfollow").addClass("btn-follow");
					$("#user-page-unfollow").attr("id", "#user-page-follow");
				})
			}).on('click', '#user-page-follow', function(){
				$.post("functions/follow_user.php", {userFollowing : '<?php echo $_SESSION["token"];?>', userFollowed : '<?php echo $profileToken;?>'}).done(function(data){
					$("#user-page-follow").addClass("btn-active");
					var text = "<span class='glyphicon glyphicon-heart'></span> <?php echo $lang['following'];?>";
					$("#user-page-follow").html(text);
					$("#user-page-follow").removeClass("btn-follow");
					$("#user-page-follow").addClass("btn-unfollow");
					$("#user-page-follow").attr("id", "#user-page-unfollow");
				})
			})
		</script>
		<?php } ?>
	</body>
</html>
