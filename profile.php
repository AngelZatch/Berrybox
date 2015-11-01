<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();

$userToken = $_GET["id"];

if(isset($_POST["submit"])){
	$newPseudo = addslashes($_POST["username"]);
	$newBio = addslashes($_POST["bio"]);
	$newLang = $_POST["default-lang"];
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
									user_bio = '$newBio',
									user_lang = '$newLang'
									WHERE user_token = '$userToken'");
			} else {
				$picture = $_SESSION["token"].".".pathinfo($_FILES["profile-picture"]["name"], PATHINFO_EXTENSION);
				move_uploaded_file($_FILES["profile-picture"]["tmp_name"], "profile-pictures/".$picture);
				//Writing in the table the modifications
				$edit = $db->query("UPDATE user
									SET user_pseudo = '$newPseudo',
									user_bio = '$newBio',
									user_pp = '$picture',
									user_lang = '$newLang'
									WHERE user_token = '$userToken'");
			}
		}
	} else {
		$edit = $db->query("UPDATE user
							SET user_pseudo = '$newPseudo',
							user_bio = '$newBio',
							user_lang = '$newLang'
							WHERE user_token = '$userToken'");
	}
}

$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								WHERE room_active = 1");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>My Profile</title>
		<?php include "styles.php";?>
		<link rel="stylesheet" href="assets/css/fileinput.min.css">
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main col-lg-12">
			<form action="profile.php?id=<?php echo $userToken;?>&lang=<?php echo $_SESSION["lang"];?>" class="form-horizontal" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<label for="username" class="col-sm-3 control-label"><?php echo $lang["display_name"];?></label>
					<div class="col-sm-6">
						<input type="text" name="username" class="form-control" aria-describedby="username-tip" value="<?php echo stripslashes($userDetails["user_pseudo"]);?>">
						<span class="help-block" id="username-tip"><?php echo $lang["display_name_tip"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="profile-picture" class="col-sm-3 control-label"><?php echo $lang["profile_picture"];?></label>
					<div class="col-sm-6">
						<div id="kv-avatar-errors" class="center-block" style="width:800px;display:none;"></div>
						<div class="kv-avatar">
							<input type="file" id="avatar" name="profile-picture" class="file-loading">
						</div>
						<span class="help-block" id="username-tip"><?php echo $lang["profile_picture_formats"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="bio" class="col-lg-3 control-label"><?php echo $lang["bio"];?></label>
					<div class="col-lg-6">
						<textarea rows="5" maxlength="400" name="bio" class="form-control" aria-describedby="bio-tip" style="background-color:inherit; border:2px #444 solid; color:white;"><?php echo stripslashes($userDetails["user_bio"]);?></textarea>
						<span class="help-block" id="bio-tip"><?php echo $lang["bio_tip"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="default-lang" class="col-lg-3 control-label"><?php echo $lang["default_lang"];?></label>
					<div class="col-lg-6">
						<select name="default-lang" id="" class="form-control" style="background-color:inherit; border:2px #444 solid; color:white;">
							<option value="en" <?php if($userDetails["user_lang"]=="en") echo "selected='selected'";?>>English</option>
							<option value="fr" <?php if($userDetails["user_lang"]=="fr") echo "selected='selected'";?>>Français</option>
							<option value="jp" <?php if($userDetails["user_lang"]=="jp") echo "selected='selected'";?>>日本語</option>
						</select>
						<span class="help-block" id="lang-tip"><?php echo $lang["lang_tip"];?></span>
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
				msgErrorClass: 'alert alert-block alert-danger',
				defaultPreviewContent: '<img src="<?php echo $ppAdresss;?>" style="width:118px;">',
				layoutTemplates: {main2: '{preview} {browse}' },
				allowedFileExtensions: ["jpg", "png"]
			});
		</script>
	</body>
</html>