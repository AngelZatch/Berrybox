<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
if(isset($_SESSION["token"])){
	$userDetails = $db->query("SELECT * FROM user_preferences up
							WHERE up_user_id='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);
}

$queryTypes = $db->query("SELECT * FROM room_types");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Create a room</title>
		<base href="../">
		<?php include "styles.php";
		if(isset($_SESSION["token"])){
			if($userDetails["up_theme"] == '1'){?>
		<link rel="stylesheet" href="assets/css/dark-theme.css">
		<?php } else { ?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
		<?php }
		} else { ?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
		<?php } ?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main col-lg-8 col-lg-offset-2">
			<legend><?php echo $lang["room_create"];?></legend>
			<form class="form-horizontal">
				<div class="form-group">
					<label for="roomName" class="col-sm-3 control-label"><?php echo $lang["room_name"];?></label>
					<div class="col-lg-9">
						<input type="text" placeholder="<?php echo $lang["room_name"];?>" class="form-control" name="roomName">
					</div>
				</div>
				<div class="form-group">
					<label for="" class="col-sm-3 control-label"><?php echo $lang["room_protection"];?></label>
					<div class="col-lg-9">
						<span class="btn btn-primary btn-switch disabled" id="select-private" title="<?php echo $lang["private_tip"];?>"><span class="glyphicon glyphicon-headphones"></span> <?php echo $lang["level_private"];?></span>
						<span class="btn btn-primary btn-switch disabled" id="select-locked" role="button" title="<?php echo $lang["locked_tip"];?>"><span class="glyphicon glyphicon-eye-open"></span> <?php echo $lang["level_locked"];?></span>
						<span class="btn btn-primary btn-switch btn-disabled" id="select-public" title="<?php echo $lang["public_tip"];?>"><span class="glyphicon glyphicon-volume-up"></span> <?php echo $lang["level_public"];?></span>
						<input type="hidden" id="protect-value" value="1">
					</div>
				</div>
				<div class="form-group" id="password-form" style="display:none;">
					<label for="password" class="col-sm-3 control-label"><?php echo $lang["password"];?></label>
					<div class="col-lg-9">
						<input type="text" placeholder="<?php echo $lang["password"];?>" class="form-control" name="password" id="password">
					</div>
				</div>
				<div class="form-group">
					<label for="roomType" class="col-sm-3 control-label"><?php echo $lang["room_type"];?></label>
					<div class="col-lg-9">
						<select name="roomType" id="" class="form-control">
							<?php while($type = $queryTypes->fetch(PDO::FETCH_ASSOC)) { ?>
							<option value="<?php echo $type["id"];?>"><?php echo $lang[$type["type"]];?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="speakLang" class="col-lg-3 control-label"><?php echo $lang["speak_lang"];?></label>
					<div class="col-lg-9">
						<select name="speakLang" id="" class="form-control">
							<option value="en" <?php if($userDetails["user_lang"]=="en") echo "selected='selected'";?>>English</option>
							<option value="fr" <?php if($userDetails["user_lang"]=="fr") echo "selected='selected'";?>>Français</option>
							<option value="jp" <?php if($userDetails["user_lang"]=="jp") echo "selected='selected'";?>>日本語</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="description" class="col-sm-3 control-label"><?php echo $lang["description_limit"];?></label>
					<div class="col-lg-9">
						<textarea name="description" id="description" cols="30" rows="5" class="form-control"></textarea>
					</div>
				</div>
				<span name="createRoom" class="btn btn-primary btn-block"><?php echo $lang["room_create"];?></span>
				<a href="<?php echo $_GET["lang"];?>/home" class="btn btn-default btn-block"><?php echo $lang["cancel"];?></a>
			</form>
		</div>
		<?php include "scripts.php";?>
		<script>
			$("#select-locked").click(function(){
				$(this).toggleClass("disabled");
				$("#select-private").addClass("disabled");
				$("#select-public").addClass("disabled");
				$("#password-form").toggle('600');
				$("#protect-value").val(2);
			})
			$("#select-private").click(function(){
				$(this).toggleClass("disabled");
				$("#select-locked").addClass("disabled");
				$("#select-public").addClass("disabled");
				$("#password-form").hide('600');
				$("#protect-value").val(3);
			})
			$("#select-public").click(function(){
				$(this).toggleClass("disabled");
				$("#select-locked").addClass("disabled");
				$("#select-private").addClass("disabled");
				$("#password-form").hide('600');
				$("#protect-value").val(1);
			})
			$('[name=createRoom]').click(function(){
				var roomName = $('[name=roomName]').val();
				var user = "<?php echo $_SESSION["token"];?>";
				var protect = $("#protect-value").val();
				var password = $("#password").val();
				var type = $('[name=roomType]').val();
				var language = $('[name=speakLang]').val();
				var description = $("#description").val();
				console.log(description);
				$.post("functions/room_create.php", {roomName : roomName, creator : user, protect : protect, password : password, type : type, language : language, description : description}).done(function(data){
					window.location.replace("<?php echo $_GET["lang"];?>/room/"+data);
				})
			})
		</script>
	</body>
</html>
