<?php
session_start();
require_once "functions/db_connect.php";
$db = PDOFactory::getConnection();
if(isset($_SESSION["token"]) && isset($_SESSION["user_lang"])){
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
			$_SESSION["user_lang"] = $credentials["user_lang"];
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
		<div class="main layer portal-main">
			<div class="col-lg-4 col-lg-offset-4 col-md-offset-3 col-md-6 login-space">
				<legend><?php echo $lang["log_in"];?></legend>
				<div class="login-option">
					<button class="btn btn-facebook btn-block" onclick="javascript:login()">Log in with Facebook</button>
					<div id="status"></div>
				</div>
				<div class="login-separator">
					<p class="sub-legend">Log in with username</p>
				</div>
				<div class="login-option">
					<form action="" method="post">
						<div class="form-group form-group-lg">
							<input type="text" placeholder="<?php echo $lang["username"];?>" class="form-control form-control-portal" name="username">
						</div>
						<div class="form-group form-group-lg">
							<input type="password" placeholder="<?php echo $lang["password"];?>" class="form-control form-control-portal" name="login_pwd">
						</div>
						<?php if(isset($_GET["box-token"])){ ?>
						<input type="hidden" name="box-token-redirect" value="<?php echo $_GET["box-token"];?>">
						<?php } ?>
						<input type="submit" class="btn btn-primary btn-block" name="login" value="<?php echo $lang["log_in"];?>">
					</form>
				</div>
				<p class="sign-up-option" style="text-align: center"><?php echo $lang["no_account"];?> <a href="signup"><?php echo $lang["sing_up_here"];?></a></p>
			</div>
		</div>
		<style>
			.layer{
				background-color: #cf9930;
				height: 100%;
			}
			.login-separator{
				text-align: center;
				margin-bottom: 14px;
				font-size: 0.75em;
			}
			.login-option{
				margin-bottom: 20px;
				text-align: center;
			}
			.sign-up-option{
				margin-top: 50px;
			}
		</style>
		<?php include "scripts.php";?>
		<script src="assets/js/facebook.min.js"></script>
	</body>
</html>
