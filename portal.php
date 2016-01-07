<?php
require_once "functions/db_connect.php";
$db = PDOFactory::getConnection();
if(isset($_SESSION["token"]) && isset($_SESSION["lang"])){
	header("Location: ../$_SESSION[lang]/home");
} else {
	if(isset($_POST["login"])){
		session_start();
		$username = $_POST["username"];
		$password = $_POST["login_pwd"];

		$checkCredentials = $db->prepare("SELECT * FROM user WHERE user_pseudo=? AND user_pwd=?");
		$checkCredentials->bindParam(1, $username);
		$checkCredentials->bindParam(2, $password);
		$checkCredentials->execute();

		if($checkCredentials->rowCount() == 1){
			$credentials = $checkCredentials->fetch(PDO::FETCH_ASSOC);
			$_SESSION["username"] = $credentials["user_pseudo"];
			$_SESSION["power"] = $credentials["user_power"];
			$_SESSION["token"] = $credentials["user_token"];
			$_SESSION["lang"] = $credentials["user_lang"];
			header("Location: home");
		}
	}
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Log in | Berrybox</title>
		<?php include "styles.php";?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main">
			<div class="col-lg-4 col-lg-offset-4">
			<legend><?php echo $lang["log_in"];?></legend>
				<form action="" method="post">
					<div class="form-group form-group-lg">
						<input type="text" placeholder="<?php echo $lang["username"];?>" class="form-control" name="username">
					</div>
					<div class="form-group form-group-lg">
						<input type="password" placeholder="<?php echo $lang["password"];?>" class="form-control" name="login_pwd">
					</div>
					<input type="submit" class="btn btn-primary btn-block" name="login" value="<?php echo $lang["log_in"];?>">
				</form>
				<p style="text-align: center"><?php echo $lang["no_account"];?> <a href="signup"><?php echo $lang["sing_up_here"];?></a></p>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
