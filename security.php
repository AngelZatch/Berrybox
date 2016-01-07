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

$profileDetails = $db->query("SELECT * FROM user u
							JOIN user_stats us ON u.user_token = us.user_token
							WHERE u.user_token='$profileToken'")->fetch(PDO::FETCH_ASSOC);

if(isset($_POST["submit"])){
	$newPassword = $_POST["newPassword"];
	$edit = $db->query("UPDATE user
							SET user_pwd = '$newPassword'
							WHERE user_token = '$profileToken'");
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
		<div class="main col-lg-12">
			<div class="col-sm-offset-2 col-sm-8 page-title">
				<p id="profile-title"><?php echo $lang["profile_security"];?></p>
				<span class="tip"><?php echo $lang["profile_security_tip"];?></span>
				<ul class="nav nav-tabs" id="profile-menu">
					<li role="presentation"><a href="profile/settings"><?php echo $lang["profile_settings"];?></a></li>
					<li role="presentation"><a href="profile/history"><?php echo $lang["profile_history"];?></a></li>
					<li role="presentation" class="active"><a href="profile/security"><?php echo $lang["profile_security"];?></a></li>
				</ul>
			</div>
			<form action="profile/security" class="form-horizontal" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<label for="currentPassword" class="col-sm-3 control-label"><?php echo $lang["old_password"];?></label>
					<div class="col-sm-6 has-feedback" id="current-password-form-group">
						<input type="password" name="currentPassword" class="form-control" aria-describedby="username-tip">
					</div>
				</div>
				<div class="form-group">
					<label for="newPassword" class="col-sm-3 control-label"><?php echo $lang["new_password"];?></label>
					<div class="col-sm-6">
						<input type="password" name="newPassword" class="form-control" aria-describedby="username-tip">
					</div>
				</div>
				<div class="form-group">
					<label for="confirmNewPassword" class="col-sm-3 control-label"><?php echo $lang["confirm_new_password"];?></label>
					<div class="col-sm-6 has-feedback" id="password-confirm-form-group">
						<input type="password" name="confirmNewPassword" class="form-control" aria-describedby="username-tip">
					</div>
				</div>
				<div class="col-lg-offset-2 col-lg-8">
					<input type="submit" class="btn btn-primary btn-block" name="submit" value="<?php echo $lang["save_changes"];?>" disabled="disabled">
				</div>
			</form>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				var compare;
				$(":regex(name,currentPassword)").on('keyup blur', function(){
					var box = $(this);
					var elementId = "#current-password-form-group";
					removeFeedback(elementId);
					//console.log("Letter typed");
					if(compare){
						clearTimeout(compare);
						//console.log("There is a timeout. Clearing...");
					}
					compare = setTimeout(function(){
						var string = box.val();
						//console.log("timeout expired. Search with '"+string+"' query.");
						$.post("functions/compare_password.php", {string : string, userToken : "<?php echo $profileToken;?>"}).done(function(data){
							if(data == 1){
								applySuccessFeedback(elementId);
							} else {
								applyErrorFeedback(elementId);
							}
						})
					}, 1000);
				})
				$(":regex(name,confirmNewPassword)").on('keyup blur', function(){
					var box = $(this);
					var elementId = "#password-confirm-form-group";
					removeFeedback(elementId);
					if(compare){
						clearTimeout(compare);
					}
					compare = setTimeout(function(){
						var string = box.val();
						if(string != ""){
							if(string == $(":regex(name,newPassword)").val()){
								applySuccessFeedback(elementId);
								$(":regex(name,submit)").removeClass("disabled");
								$(":regex(name,submit)").removeAttr("disabled");
							} else {
								applyErrorFeedback(elementId);
								$(":regex(name,submit)").addClass("disabled");
								$(":regex(name,submit)").attr("disabled", "disabled");
							}
						}
					}, 500);
				}).on('focus', function(){
					$(":regex(name,signup)").addClass("disabled");
					$(":regex(name,signup)").attr("disabled", "disabled");
				})
			})
		</script>
	</body>
</html>
