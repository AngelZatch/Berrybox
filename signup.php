<?php
require_once "functions/db_connect.php";
include "functions/tools.php";

if(isset($_POST["signup"])){
	$db = PDOFactory::getConnection();

	$token = generateUserToken();
	$color = "000000";

	try{
		$newUser = $db->prepare("INSERT INTO user(user_token, user_pseudo, user_pwd) VALUES(:token, :pseudo, :pwd)");
		$newUser->bindParam(':pseudo', $_POST["username"]);
		$newUser->bindParam(':pwd', $_POST["password"]);
		$newUser->bindParam(':token', $token);
		$newUser->execute();

		$newPref = $db->prepare("INSERT INTO user_preferences(up_user_id, up_color)
								VALUES(:token, :color)");
		$newPref->bindParam(':token', $token);
		$newPref->bindParam(':color', $color);
		$newPref->execute();
		header('Location: home.php');
	} catch(PDOException $e){
		echo $e->getMessage();
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
						<label for="username" class="control-label">Username</label>
						<input type="text" placeholder="Username" class="form-control" name="username">
					</div>
					<div class="form-group">
						<label for="password" class="control-label">Password</label>
						<input type="password" class="form-control" name="password">
					</div>
					<div class="form-group">
						<label for="password-confirm" class="control-label">Please confirm your password</label>
						<input type="password" class="form-control">
					</div>
					<input type="submit" class="btn btn-primary btn-block" name="signup" value="Register">
				</form>
			</div>
		</div>
		<?php include "player.php";?>
		<?php include "scripts.php";?>
	</body>
</html>
