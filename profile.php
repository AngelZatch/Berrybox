<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								WHERE room_active = 1");

$userToken = $_GET["id"];

if(isset($_GET["lang"])){
	$lang = $_GET["lang"];
	$_SESSION["lang"] = $lang;

	include_once "languages/lang.".$lang.".php";
} else {
	header("Location:home.php?lang=en");
}

if(isset($_POST["submit"])){
	// Uploading the profile picture on the folder
	if($_FILES["profile-picture"]["name"]){
		$picture = $_SESSION["token"].".".pathinfo($_FILES["profile-picture"]["name"], PATHINFO_EXTENSION);
		move_uploaded_file($_FILES["profile-picture"]["tmp_name"], "profile-pictures/".$picture);
	}

	//Writing in the table the modifications
	$newPseudo = $_POST["username"];
	$newBio = $_POST["bio"];
	$edit = $db->query("UPDATE user
						SET user_pseudo = '$newPseudo',
							user_bio = '$newBio',
							user_pp = '$picture'
						WHERE user_token = '$userToken'");
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>My Profile</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main col-lg-12">
			<form action="" class="form-horizontal" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<label for="username" class="col-sm-3 control-label"><?php echo $lang["display_name"];?></label>
					<div class="col-sm-6">
						<input type="text" name="username" class="form-control" aria-describedby="username-tip" value="<?php echo $userDetails["user_pseudo"];?>">
						<span class="help-block" id="username-tip"><?php echo $lang["display_name_tip"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="profile-picture" class="col-sm-3 control-label"><?php echo $lang["profile_picture"];?></label>
					<div class="col-sm-6">
						<div id="profile-picture-sample">
							<img src="<?php echo $ppAdresss;?>" alt="" style="width:inherit;">
						</div>
						<input type="file" name="profile-picture" class="control-label">
						<span class="help-block" id="username-tip"><?php echo $lang["profile_picture_formats"];?></span>
					</div>
				</div>
				<div class="form-group">
					<label for="bio" class="col-lg-3 control-label"><?php echo $lang["bio"];?></label>
					<div class="col-lg-6">
						<textarea rows="5" name="bio" class="form-control" aria-describedby="bio-tip" style="background-color:inherit; border:2px #444 solid;"><?php echo $userDetails["user_bio"];?></textarea>
						<span class="help-block" id="bio-tip"><?php echo $lang["bio_tip"];?></span>
					</div>
				</div>
				<div class="col-lg-offset-3">
					<input type="submit" class="btn btn-primary" name="submit" value="<?php echo $lang["save_changes"];?>">
				</div>
			</form>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
