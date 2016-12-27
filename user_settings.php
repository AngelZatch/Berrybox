<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();

if(isset($_SESSION["token"])){
	$user_token = $_SESSION["token"];
	$userSettings = $db->query("SELECT *
							FROM user_preferences up
							WHERE user_token='$user_token'")->fetch(PDO::FETCH_ASSOC);

	if($userSettings["up_theme"] == "1"){
		$theme = "dark";
	} else {
		$theme = "light";
	}
}

if(isset($_POST["submit"])){
	$newPseudo = addslashes($_POST["username"]);
	$newMail = $_POST["mail"];
	$newBio = addslashes($_POST["bio"]);
	$newLang = $_POST["default-lang"];
	$newTheme = $_POST["default-theme"];
	$badge_alert = $_POST["badge-alert"];
	$edit = $db->query("UPDATE user
							SET user_pseudo = '$newPseudo',
							user_mail = '$newMail',
							user_bio = '$newBio',
							user_lang = '$newLang'
							WHERE user_token = '$user_token'");
	$editSettings = $db->query("UPDATE user_preferences
									SET up_theme = '$newTheme',
									badge_alert = '$badge_alert'
									WHERE user_token = '$user_token'");
	unset($_SESSION["user_lang"]);
	$_SESSION["user_lang"] = $newLang;
	unset($_SESSION["username"]);
	$_SESSION["username"] = $newPseudo;
}
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
		<link rel="stylesheet" href="assets/css/croppie.css">
		<?php include "scripts.php";?>
		<script src="assets/js/croppie.min.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main col-lg-12">
			<div class="col-lg-offset-2 col-lg-8 col-sm-12 page-title">
				<legend id="profile-title"><span class="glyphicon glyphicon-cog"></span> <?php echo $lang["my_settings"];?></legend>
				<span class="tip"><?php echo $lang["profile_settings_tip"];?></span>
				<ul class="nav nav-tabs" id="profile-menu">
					<li role="presentation" class="active"><a href="profile/settings"><span class="glyphicon glyphicon-cog"></span> <?php echo $lang["my_settings"];?></a></li>
					<li role="presentation"><a href="profile/security"><span class="glyphicon glyphicon-lock"></span> <?php echo $lang["profile_security"];?></a></li>
				</ul>
			</div>
			<form action="profile/settings" class="form-horizontal" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<label for="username" class="col-sm-3 control-label"><?php echo $lang["display_name"];?></label>
					<div class="col-sm-9 col-lg-4 has-feedback" id="username-form-group">
						<input type="text" name="username" class="form-control" aria-describedby="username-tip" value="<?php echo stripslashes($userDetails["user_pseudo"]);?>">
						<span class="tip" id="username-tip"><?php echo $lang["display_name_tip"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="mail" class="col-sm-3 control-label"><?php echo $lang["display_mail"];?></label>
					<div class="col-sm-9 col-lg-4">
						<input type="mail" name="mail" class="form-control" value="<?php echo $userDetails["user_mail"];?>">
					</div>
				</div>
				<div class="form-group">
					<label for="avatar" class="col-sm-3 control-label"><?php echo $lang["profile_picture"];?></label>
					<div class="col-sm-9 col-lg-7">
						<div class="pp-input btn btn-primary">
							<span><?php echo $lang["pick_image"];?></span>
							<input type="file" id="upload" accept="image/jpeg, image/x-png">
						</div>
						<span class="tip" id="username-tip"><?php echo $lang["profile_picture_formats"];?></span>
						<!--<p class="help-block">Formats JPEG ou PNG et de taille inférieurs à 1 Mo.</p>-->
						<div class="crop-step">
							<div id="upload-demo"></div>
							<input type="hidden" id="imagebase64">
							<span class="btn btn-primary btn-block upload-result">Mettre à jour</span>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="bio" class="col-sm-3 control-label"><?php echo $lang["bio"];?></label>
					<div class="col-sm-9 col-lg-4">
						<textarea rows="5" maxlength="400" name="bio" class="form-control" aria-describedby="bio-tip"><?php echo stripslashes($userDetails["user_bio"]);?></textarea>
						<span class="tip" id="bio-tip"><?php echo $lang["bio_tip"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="default-lang" class="col-sm-3 control-label"><?php echo $lang["default_lang"];?></label>
					<div class="col-sm-9 col-lg-4">
						<select name="default-lang" id="" class="form-control">
							<option value="en" <?php if($userDetails["user_lang"]=="en") echo "selected='selected'";?>>English</option>
							<option value="fr" <?php if($userDetails["user_lang"]=="fr") echo "selected='selected'";?>>Français</option>
							<option value="jp" <?php if($userDetails["user_lang"]=="jp") echo "selected='selected'";?>>日本語</option>
						</select>
						<span class="tip" id="lang-tip"><?php echo $lang["lang_tip"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="default-theme" class="col-sm-3 control-label"><?php echo $lang["user_theme"];?></label>
					<div class="col-sm-9 col-lg-4">
						<select name="default-theme" id="" class="form-control">
							<option value="0" <?php if($userSettings["up_theme"]=="0") echo "selected='selected'";?>><?php echo $lang["light"];?></option>
							<option value="1" <?php if($userSettings["up_theme"]=="1") echo "selected='selected'";?>><?php echo $lang["dark"];?></option>
						</select>
						<span class="tip"><?php echo $lang["theme_tip"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="badge-alert" class="col-sm-3 control-label"><?php echo $lang["badge_alert"];?></label>
					<div class="col-sm-9 col-lg-4">
						<select name="badge-alert" id="" class="form-control">
							<option value="1" <?php if($userSettings["badge_alert"]=="1") echo "selected='selected'";?>><?php echo $lang["badge_alert_large"];?></option>
							<option value="0" <?php if($userSettings["badge_alert"]=="0") echo "selected='selected'";?>><?php echo $lang["badge_alert_small"];?></option>
						</select>
						<span class="tip"><?php echo $lang["badge_alert_tip"];?></span>
					</div>
				</div>
				<div class="col-lg-offset-2 col-lg-8">
					<input type="submit" class="btn btn-primary btn-block" name="submit" value="<?php echo $lang["save_changes"];?>">
				</div>
			</form>
		</div>
		<style>
			.profile-picture{
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
			.form-group{
				padding: 20px 0;
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
					}
				}

				$uploadCrop = $('#upload-demo').croppie({
					viewport: {
						width: 200,
						height: 200,
						type: 'circle'
					},
					boundary: {
						width: 300,
						height: 300
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

				var patternUserReg = /^<?php echo $userDetails['user_pseudo'];?>$/i;
				$(":regex(name,username)").on('keydown', function(e){
					if(e.which === 32) return false;
				}).on('keyup blur', function(e){
					var modifiedUsername = $(this).val();
					var elementId = "#username-form-group";
					removeFeedback(elementId);
					if(patternUserReg.exec(modifiedUsername) !== null){
						//console.log("Match");
						applySuccessFeedback(elementId);
						$(":regex(name,submit)").removeClass("disabled");
						$(":regex(name,submit)").removeAttr("disabled");
					} else {
						//console.log("Not match...");
						applyErrorFeedback(elementId);
						$(":regex(name,submit)").addClass("disabled");
						$(":regex(name,submit)").attr("disabled", "disabled");
					}
				})
			}).on('click', '.upload-result', function(){
				var picture_value = $("#imagebase64").val();
				$.when($.get("functions/fetch_session_details.php")).done(function(data){
					var session_details = JSON.parse(data);
					var user_token = session_details.token;
					$.post("functions/update_picture.php", {picture_value : picture_value, user_token : user_token, picture_type : "picture"}).done(function(data){
						var d = new Date();
						$(".small-pp>img").attr("src", "profile-pictures/"+data+"?"+d.getTime());
						$(".crop-step").hide();
					})
				});
			})
		</script>
	</body>
</html>
