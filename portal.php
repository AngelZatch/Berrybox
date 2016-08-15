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
		<div class="main layer portal-main">
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
		<style>
			.layer{
				background-color: #cf9930;
				height: 100%;
			}
		</style>
		<?php include "scripts.php";?>
	</body>
</html>
