<?php
require_once "functions/db_connect.php";
include "functions/tools.php";

if(isset($_GET["lang"])){
	$lang = $_GET["lang"];
	$_SESSION["lang"] = $lang;

	include_once "languages/lang.".$lang.".php";
} else {
	header("Location:portal.php?lang=en");
}

if(isset($_POST["signup"])){
	$db = PDOFactory::getConnection();

	$betaKey = $_POST["beta"];
	$matchKey = $db->query("SELECT * FROM beta_keys WHERE key_value = '$betaKey' AND key_user IS NULL");
	if($matchKey->rowCount() == 1){
		$token = generateUserToken();
		$color = "000000";
		$access = 1;

		try{
			$newUser = $db->prepare("INSERT INTO user(user_token, user_pseudo, user_pwd, beta_access) VALUES(:token, :pseudo, :pwd, :access)");
			$newUser->bindParam(':pseudo', $_POST["username"]);
			$newUser->bindParam(':pwd', $_POST["password"]);
			$newUser->bindParam(':token', $token);
			$newUser->bindParam(':access', $access);
			$newUser->execute();

			$newPref = $db->prepare("INSERT INTO user_preferences(up_user_id, up_color)
								VALUES(:token, :color)");
			$newPref->bindParam(':token', $token);
			$newPref->bindParam(':color', $color);
			$newPref->execute();

			$useKey = $db->query("UPDATE beta_keys SET key_user='$token' WHERE key_value='$betaKey'");
			header('Location: home.php?lang='.$_GET["lang"]);
		} catch(PDOException $e){
			echo $e->getMessage();
		}
	}
}

?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Strawberry Music Streamer</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main">
			<div class="col-lg-7 col-lg-offset-2">
				<form action="" method="post">
					<div class="form-group">
						<label for="username" class="control-label"><?php echo $lang["username"];?></label>
						<input type="text" placeholder="Username" class="form-control" name="username">
					</div>
					<div class="form-group">
						<label for="password" class="control-label"><?php echo $lang["password"];?></label>
						<input type="password" class="form-control" name="password">
					</div>
					<div class="form-group">
						<label for="password-confirm" class="control-label"><?php echo $lang["pwd_confirm"];?></label>
						<input type="password" class="form-control">
					</div>
					<div class="form-group">
						<label for="beta" class="control-label"><?php echo $lang["beta_key"];?></label>
						<input type="text" class="form-control" name="beta">
					</div>
					<input type="submit" class="btn btn-primary btn-block" name="signup" value="<?php echo $lang["sign_up"];?>">
				</form>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
