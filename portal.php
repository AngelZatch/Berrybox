<?php
require_once "functions/db_connect.php";
$db = PDOFactory::getConnection();
if(isset($_SESSION["token"]) && isset($_SESSION["lang"])){
	header("Location: home");
} else {
	if(isset($_POST["login"])){
		$username = $_POST["username"];
		$password = $_POST["login_pwd"];

		$checkCredentials = $db->prepare("SELECT * FROM user WHERE user_pseudo=? AND user_pwd=?");
		$checkCredentials->bindParam(1, $username);
		$checkCredentials->bindParam(2, $password);
		$checkCredentials->execute();

		if($checkCredentials->rowCount() == 1){
			$credentials = $checkCredentials->fetch(PDO::FETCH_ASSOC);
			session_start();
			$_SESSION["username"] = $credentials["user_pseudo"];
			$_SESSION["power"] = $credentials["user_power"];
			$_SESSION["token"] = $credentials["user_token"];
			$_SESSION["lang"] = $credentials["user_lang"];
			if(isset($_POST["box-token-redirect"])){
				header("Location: box/".$_POST["box-token-redirect"]);
			} else {
				header("Location: home");
			}
		}
	}
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Log in</title>
		<?php include "styles.php";?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="portal-main">
			<div class="img-main">
				<img src="" alt="" id="portal-main">
			</div>
			<div class="main layer">
				<div class="col-lg-4 col-lg-offset-4 col-md-offset-3 col-md-6 login-space">
					<legend><?php echo $lang["log_in"];?></legend>
					<form action="" method="post">
						<div class="form-group form-group-lg">
							<input type="text" placeholder="<?php echo $lang["username"];?>" class="form-control form-control-portal" name="username">
						</div>
						<div class="form-group form-group-lg">
							<input type="password" placeholder="<?php echo $lang["password"];?>" class="form-control form-control-portal" name="login_pwd">
						</div>
						<?php if(isset($_POST["box-token"])){ ?>
						<input type="hidden" name="box-token-redirect" value="<?php echo $_POST["box-token"];?>">
						<?php } ?>
						<input type="submit" class="btn btn-primary btn-block" name="login" value="<?php echo $lang["log_in"];?>">
					</form>
					<p style="text-align: center"><?php echo $lang["no_account"];?> <a href="signup"><?php echo $lang["sing_up_here"];?></a></p>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
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
