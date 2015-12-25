<?php
require_once "functions/db_connect.php";
include "functions/tools.php";

if(isset($_GET["lang"])){
	$lang = $_GET["lang"];
	$_SESSION["lang"] = $lang;

	include_once "languages/lang.".$lang.".php";
} else {
	header("Location:en/portal");
}

if(isset($_POST["signup"])){
	session_start();
	$db = PDOFactory::getConnection();
	$token = generateUserToken();
	$colorID = rand(1,20);
	$color = $db->query("SELECT color_value FROM name_colors WHERE number = $colorID")->fetch(PDO::FETCH_ASSOC);

	try{
		$db->beginTransaction();
		$newUser = $db->prepare("INSERT INTO user(user_token, user_pseudo, user_pwd) VALUES(:token, :pseudo, :pwd)");
		$newUser->bindParam(':pseudo', $_POST["username"]);
		$newUser->bindParam(':pwd', $_POST["password"]);
		$newUser->bindParam(':token', $token);
		$newUser->execute();

		$newPref = $db->prepare("INSERT INTO user_preferences(up_user_id, up_color)
								VALUES(:token, :color)");
		$newPref->bindParam(':token', $token);
		$newPref->bindParam(':color', $color["color_value"]);
		$newPref->execute();

		$newStats = $db->prepare("INSERT INTO user_stats(user_token) VALUES(:token)");
		$newStats->bindParam(':token', $token);
		$newStats->execute();

		$db->commit();
		$_SESSION["username"] = $_POST["username"];
		$_SESSION["power"] = "0";
		$_SESSION["token"] = $token;
		$_SESSION["lang"] = "en";
		header('Location: ../'.$_GET["lang"].'/home');
	} catch(PDOException $e){
		$db->rollBack();
		echo $e->getMessage();
	}
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Strawberry Music Streamer</title>
		<base href="../">
		<?php include "styles.php";?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main">
			<div class="col-lg-7 col-lg-offset-2">
				<form action="" method="post">
					<div class="form-group has-feedback" id="username-form-group">
						<label for="username" class="control-label"><?php echo $lang["username"];?></label>
						<input type="text" placeholder="Username" class="form-control" name="username">
					</div>
					<div class="form-group">
						<label for="password" class="control-label"><?php echo $lang["password"];?></label>
						<input type="password" class="form-control" name="password">
					</div>
					<div class="form-group has-feedback" id="password-confirm-form-group">
						<label for="password-confirm" class="control-label"><?php echo $lang["pwd_confirm"];?></label>
						<input type="password" class="form-control" name="password-confirm">
					</div>
					<input type="submit" class="btn btn-primary btn-block" name="signup" value="<?php echo $lang["sign_up"];?>">
				</form>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				var compare;
				$(":regex(name,username)").on('keyup blur', function(){
					var box = $(this);
					var elementId = "#username-form-group";
					removeFeedback(elementId);
					//console.log("Letter typed");
					if(compare){
						clearTimeout(compare);
						//console.log("There is a timeout. Clearing...");
					}
					compare = setTimeout(function(){
						var string = box.val();
						//console.log("timeout expired. Search with '"+string+"' query.");
						$.post("functions/compare_user_string.php", {string : string}).done(function(data){
							if(data == 1){
								console.log("Success, you can use this username");
								applySuccessFeedback(elementId);
								$(":regex(name,signup)").removeClass("disabled");
								$(":regex(name,signup)").removeAttr("disabled");
							} else {
								console.log("This username already exists");
								applyErrorFeedback(elementId);
								$(":regex(name,signup)").addClass("disabled");
								$(":regex(name,signup)").attr("disabled", "disabled");
							}
						})
					}, 1000);
				})
				$(":regex(name,password-confirm)").on('keyup blur', function(){
					var box = $(this);
					var elementId = "#password-confirm-form-group";
					removeFeedback(elementId);
					if(compare){
						clearTimeout(compare);
					}
					compare = setTimeout(function(){
						var string = box.val();
						if(string != ""){
							if(string == $(":regex(name,password)").val()){
								applySuccessFeedback(elementId);
							} else {
								applyErrorFeedback(elementId);
							}
						}
					}, 500);
				})
			})
		</script>
	</body>
</html>
