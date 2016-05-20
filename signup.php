<?php
require_once "functions/db_connect.php";
include "functions/tools.php";

if(isset($_POST["signup"])){
	session_start();
	$db = PDOFactory::getConnection();
	$token = generateReference(6);
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
		if(isset($_POST["box-token-redirect"])){
			header("Location: box/".$_POST["box-token-redirect"]);
		} else {
			header("Location: home");
		}
	} catch(PDOException $e){
		$db->rollBack();
		echo $e->getMessage();
	}
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Sign up</title>
		<?php include "styles.php";?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="portal-main">
			<div class="img-main">
				<img src="" alt="YOUTUUUUBE" id="portal-main">
			</div>
			<div class="main layer">
				<div class="col-lg-4 col-lg-offset-4 col-md-offset-3 col-md-6 login-space">
					<legend><?php echo $lang["sign_up"];?></legend>
					<form action="" method="post">
						<div class="form-group form-group-lg has-feedback" id="username-form-group">
							<input type="text" placeholder="<?php echo $lang["username"];?>" class="form-control form-control-portal" name="username">
						</div>
						<div class="form-group form-group-lg">
							<input type="password" placeholder="<?php echo $lang["password"];?>" class="form-control form-control-portal" name="password">
						</div>
						<div class="form-group form-group-lg has-feedback" id="password-confirm-form-group">
							<input type="password" placeholder="<?php echo $lang["pwd_confirm"];?>" class="form-control form-control-portal" name="password-confirm">
						</div>
						<?php if(isset($_POST["box-token"])){ ?>
						<input type="hidden" name="box-token-redirect" value="<?php echo $_POST["box-token"];?>">
						<?php } ?>
						<input type="submit" class="btn btn-primary btn-block" name="signup" value="<?php echo $lang["sign_up"];?>">
					</form>
					<p style="text-align: center"><?php echo $lang["already_account"];?> <a href="portal"><?php echo $lang["log_in_here"];?></a></p>
				</div>
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
				}).on('keydown', function(e){
					if(e.which === 32) return false;
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
				// Get a random video picture from a song in the song base
				$.get("functions/get_background_image.php").done(function(data){
					// Put it as background for the portal-main class
					console.log(data);
					var picture = "https://i.ytimg.com/vi/"+data+"/maxresdefault.jpg";
					$("#portal-main").attr("src", picture);
				})
			})
		</script>
	</body>
</html>
