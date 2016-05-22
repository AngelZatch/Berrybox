<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();

if(isset($_SESSION["token"])){
	$userToken = $_SESSION["token"];
	$userSettings = $db->query("SELECT *
							FROM user_preferences up
							WHERE up_user_id='$userToken'")->fetch(PDO::FETCH_ASSOC);

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
	// Uploading the profile picture on the folder
	if($_FILES["profile-picture"]["name"]){
		$pictureExtension = pathinfo($_FILES["profile-picture"]["name"], PATHINFO_EXTENSION);
		if($pictureExtension != "png" && $pictureExtension != "jpg"){
			$message = "Invalid format";
		} else {
			if($_FILES["profile-picture"]["size"] > (3072000)){
				$message = "The size is too big. 3MB max.";
				//Writing in the table the modifications
				$edit = $db->query("UPDATE user
									SET user_pseudo = '$newPseudo',
									user_mail = '$newMail',
									user_bio = '$newBio',
									user_lang = '$newLang'
									WHERE user_token = '$userToken'");
				$editSettings = $db->query("UPDATE user_preferences
											SET up_theme = '$newTheme'
											WHERE up_user_id = '$userToken'");
			} else {
				$picture = $_SESSION["token"].".".pathinfo($_FILES["profile-picture"]["name"], PATHINFO_EXTENSION);
				move_uploaded_file($_FILES["profile-picture"]["tmp_name"], "profile-pictures/".$picture);
				//Writing in the table the modifications
				$edit = $db->query("UPDATE user
									SET user_pseudo = '$newPseudo',
									user_mail = '$newMail',
									user_bio = '$newBio',
									user_pp = '$picture',
									user_lang = '$newLang'
									WHERE user_token = '$userToken'");
				$editSettings = $db->query("UPDATE user_preferences
											SET up_theme = '$newTheme'
											WHERE up_user_id = '$userToken'");
			}
		}
	} else {
		$edit = $db->query("UPDATE user
							SET user_pseudo = '$newPseudo',
							user_mail = '$newMail',
							user_bio = '$newBio',
							user_lang = '$newLang'
							WHERE user_token = '$userToken'");
		$editSettings = $db->query("UPDATE user_preferences
											SET up_theme = '$newTheme'
											WHERE up_user_id = '$userToken'");
	}
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
		<link rel="stylesheet" href="assets/css/fileinput.min.css">
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main col-lg-12">
			<div class="col-lg-offset-2 col-lg-8 col-sm-12 page-title">
				<p id="profile-title"><?php echo $lang["my_settings"];?></p>
				<span class="tip"><?php echo $lang["profile_settings_tip"];?></span>
				<ul class="nav nav-tabs" id="profile-menu">
					<li role="presentation" class="active"><a href="profile/settings"><?php echo $lang["my_settings"];?></a></li>
					<li role="presentation"><a href="profile/history"><?php echo $lang["profile_history"];?></a></li>
					<li role="presentation"><a href="profile/security"><?php echo $lang["profile_security"];?></a></li>
				</ul>
			</div>
			<form action="profile/settings" class="form-horizontal" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<label for="username" class="col-sm-3 control-label"><?php echo $lang["display_name"];?></label>
					<div class="col-sm-9 col-lg-7 has-feedback" id="username-form-group">
						<input type="text" name="username" class="form-control" aria-describedby="username-tip" value="<?php echo stripslashes($userDetails["user_pseudo"]);?>">
						<span class="tip" id="username-tip"><?php echo $lang["display_name_tip"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="mail" class="col-sm-3 control-label"><?php echo $lang["display_mail"];?></label>
					<div class="col-sm-9 col-lg-7">
						<input type="mail" name="mail" class="form-control" value="<?php echo $userDetails["user_mail"];?>">
					</div>
				</div>
				<div class="form-group">
					<label for="profile-picture" class="col-sm-3 control-label"><?php echo $lang["profile_picture"];?></label>
					<div class="col-sm-9 col-lg-7">
						<div id="kv-avatar-errors" class="center-block" style="width:800px;display:none;"></div>
						<div id="avatar-container">
							<input type="file" id="avatar" name="profile-picture" class="file-loading">
						</div>
						<span class="tip" id="username-tip"><?php echo $lang["profile_picture_formats"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="bio" class="col-sm-3 control-label"><?php echo $lang["bio"];?></label>
					<div class="col-sm-9 col-lg-7">
						<textarea rows="5" maxlength="400" name="bio" class="form-control" aria-describedby="bio-tip"><?php echo stripslashes($userDetails["user_bio"]);?></textarea>
						<span class="tip" id="bio-tip"><?php echo $lang["bio_tip"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="default-lang" class="col-sm-3 control-label"><?php echo $lang["default_lang"];?></label>
					<div class="col-sm-9 col-lg-7">
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
					<div class="col-sm-9 col-lg-7">
						<select name="default-theme" id="" class="form-control">
							<option value="0" <?php if($userSettings["up_theme"]=="0") echo "selected='selected'";?>><?php echo $lang["light"];?></option>
							<option value="1" <?php if($userSettings["up_theme"]=="1") echo "selected='selected'";?>><?php echo $lang["dark"];?></option>
						</select>
						<span class="tip"><?php echo $lang["theme_tip"];?></span>
					</div>
				</div>
				<div class="col-lg-offset-2 col-lg-8">
					<input type="submit" class="btn btn-primary btn-block" name="submit" value="<?php echo $lang["save_changes"];?>">
				</div>
			</form>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/fileinput.min.js"></script>
		<script>
			$("#avatar").fileinput({
				overwriteInitial: true,
				maxFileSize: 3000,
				showClose: false,
				showCaption: false,
				browseLabel: '',
				removeLabel: '',
				browseIcon: '<i class="glyphicon glyphicon-folder-open"></i>',
				removeTitle: 'Cancel or reset changes',
				elErrorContainers: '#kv-avatar-errors',
				elPreviewContainer: '#avatar-container',
				msgErrorClass: 'alert alert-block alert-danger',
				defaultPreviewContent: '<img src="<?php echo $ppAdresss;?>" style="width:118px;">',
				layoutTemplates: {main2: '{preview} {browse}' },
				allowedFileExtensions: ["jpg", "png"]
			});
			$(document).ready(function(){
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
			})
		</script>
	</body>
</html>
