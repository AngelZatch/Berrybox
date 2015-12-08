<?php
require_once "functions/db_connect.php";
include "functions/tools.php";
include "functions/login.php";
if(isset($_SESSION["token"]) && isset($_SESSION["lang"])){
	header("Location:home.php?lang=$_SESSION[lang]");
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Strawberry Music Streamer</title>
		<?php include "styles.php";?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
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
						<label for="login_pwd" class="control-label"><?php echo $lang["password"];?></label>
						<input type="password" class="form-control" name="login_pwd">
					</div>
					<input type="submit" class="btn btn-primary btn-block" name="login" value="<?php echo $lang["log_in"];?>">
				</form>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
