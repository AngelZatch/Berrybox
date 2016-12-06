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
							JOIN user u ON r.room_creator = u.user_token
							JOIN room_types rt ON r.room_type = rt.id
							WHERE r.room_creator = '$profileDetails[user_token]' AND room_active = '1' AND room_protection = '1'");

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
							JOIN user u ON r.room_creator = u.user_token
							JOIN room_types rt ON r.room_type = rt.id
							WHERE r.room_creator = '$profileDetails[user_token]' AND room_active = 1 AND room_protection != 2");
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
		<link rel="stylesheet" href="assets/css/croppie.css">
		<?php include "scripts.php";?>
		<script src="assets/js/croppie.min.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main">
			<div class="container-fluid no-padding">
				<div class="banner-container">
					<div id="banner">
						<img src="profile-banners/<?php echo $profileDetails['user_banner'];?>">
					</div>
					<div class="user-profile-container">
						<div class="user-profile-details col-lg-8 col-lg-offset-2 col-md-12">
							<div class="user-actions">
								<?php if(isset($_SESSION["username"])){
	if($_SESSION["username"] != $profileToken){ ?>
								<?php if($userFollow == 1){ ?>
								<button class="btn btn-primary btn-active btn-unfollow" id="user-page-unfollow" value="<?php echo $profileToken;?>"><span class="glyphicon glyphicon-heart"></span> <?php echo $lang['following'];?></button>
								<?php } else { ?>
								<button class="btn btn-primary btn-follow" id="user-page-follow" value="<?php echo $profileToken;?>"><span class="glyphicon glyphicon-heart"></span> <?php echo $lang['follow'];?></button>
								<?php } } else { ?>
								<div class="pp-input btn btn-primary">
									<span><?php echo $lang['banner_picture'];?></span>
									<input type="file" id="upload" accept="image/jpeg, image/x-png">
								</div>
								<?php } } else { ?>
								<a href="signup" class="btn btn-primary">Register to follow this user</a>
								<?php } ?>
							</div>
							<div class="user-profile-picture">
								<img src="profile-pictures/<?php echo $profileDetails["user_pp"];?>" class="profile-picture">
							</div>
							<p class="user-profile-name"><?php echo $profileDetails["user_pseudo"];?></p>
							<div class="user-profile-bio">
								<?php echo ($profileDetails["user_bio"])?$profileDetails["user_bio"]:$lang["no_bio"];?>
							</div>
						</div>
					</div>
				</div>
				<div class="user-profile-stats col-lg-8 col-lg-offset-2 col-xs-12">
					<div class="col-md-3 col-xs-6">
						<p class="stats-title"><?php echo $lang["rooms_created"];?></p>
						<p class="stats-value"><?php echo $profileDetails["stat_rooms_created"];?></p>
					</div>
					<div class="col-md-3 col-xs-6">
						<p class="stats-title"><?php echo $lang["songs_submitted"];?></p>
						<p class="stats-value"><?php echo $profileDetails["stat_songs_submitted"];?></p>
					</div>
					<div class="col-md-3 col-xs-6">
						<p class="stats-title"><?php echo $lang["total_views"];?></p>
						<p class="stats-value"><?php echo $profileDetails["stat_visitors"];?></p>
					</div>
					<div class="col-md-3 col-xs-6">
						<p class="stats-title"><?php echo $lang["total_followers"];?></p>
						<p class="stats-value"><?php echo $profileDetails["stat_followers"];?></p>
					</div>
				</div>
				<div class="user-rooms col-xs-12">
					<legend id="profile-title"><?php echo $lang["opened_rooms"];?></legend>
					<?php while($activeRooms = $queryactiveRooms->fetch(PDO::FETCH_ASSOC)){
	$roomInfo = $db->query("SELECT link, video_name, video_status FROM roomHistory_$activeRooms[box_token] rh
													JOIN song_base sb ON sb.song_base_id = rh.video_index
													WHERE video_status = 1 OR video_status = 2 ORDER BY playlist_order DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
					?>
					<div class="col-lg-3 panel-box-container">
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
			</div>
		</div>
		<div class="modal fade" id="edit-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"></h4>
					</div>
					<div class="modal-body container-fluid">
						<div class="edit-form-space"> <!-- Space for the editing form-->
							<div class="crop-step">
								<div id="upload-demo"></div>
								<input type="hidden" id="imagebase64">
								<span class="btn btn-primary btn-block upload-result"><?php echo $lang["update"];?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<style>
			.banner-picture{
				float: left;
				display: none;
			}
			.pp-input{
				cursor: pointer;
				position: relative;
			}
			.pp-input > input{
				position: absolute;
				top: 0;
				left: 0;
				opacity: 0;
				cursor: pointer;
				width: 100%;
				height: 100%;
			}
			.crop-step{
				display: none;
			}
			.user-pp{
				margin-bottom: 10px;
			}
			.croppie-container{
				padding: 0;
			}
			.cr-image{
				opacity: 1 !important;
			}
		</style>
		<script>
			$(document).ready(function(){
				var $uploadCrop;

				function readFile(input) {
					if (input.files && input.files[0]) {
						var reader = new FileReader();
						reader.onload = function (e) {
							$uploadCrop.croppie('bind', {
								url: e.target.result
							});
							$('.upload-demo').addClass('ready');
							$(".crop-step").show();
						}
						reader.readAsDataURL(input.files[0]);
						$("#edit-modal").modal('show');
					}
				}

				$uploadCrop = $('#upload-demo').croppie({
					viewport: {
						width: 800,
						height: 210,
						type: 'square'
					},
					boundary: {
						width: 850,
						height: 240
					}
				});

				$('#upload').on('change', function () { readFile(this); });
				$('.upload-result').on('click', function (ev) {
					$uploadCrop.croppie('result', {
						type: 'canvas',
						size: 'original'
					}).then(function (resp) {
						$('#imagebase64').val(resp);
						$('#form').submit();
					});
				});
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
			}).on('click', '.upload-result', function(){
				var picture_value = $("#imagebase64").val();
				$.when($.get("functions/fetch_session_details.php")).done(function(data){
					var session_details = JSON.parse(data);
					var user_token = session_details.token;
					$.post("functions/update_picture.php", {picture_value : picture_value, user_token : user_token, picture_type : "banner"}).done(function(data){
						var d = new Date();
						$("#banner>img").attr("src", "profile-banners/"+data+"?"+d.getTime());
						$(".crop-step").hide();
						$("#edit-modal").modal('hide');
					})
				});
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
