<?php
require_once "functions/db_connect.php";
$db = PDOFactory::getConnection();

if(!isset($_GET["lang"])){
	include_once "languages/lang.en.php";
} else {
	include_once "languages/lang.".$_GET["lang"].".php";
}
?>
<div class="row">
	<div class="col-lg-10 col-lg-offset-1">
		<p><?php echo $lang["no_credentials"];?></p>
		<form action="" method="post">
			<div class="form-group">
				<label for="login_name" class="control-label"><?php echo $lang["username"];?></label>
				<input type="text" placeholder="Username" class="form-control" name="login_name">
			</div>
			<div class="form-group">
				<label for="login_pwd" class="control-label"><?php echo $lang["password"];?></label>
				<input type="password" class="form-control" name="login_pwd">
			</div>
			<div class="form-group">
				<label for="beta" class="control-label"><?php echo $lang["beta_sign"];?></label>
				<input type="text" class="form-control" name="beta">
			</div>
			<div class="row">
				<input type="submit" class="btn btn-primary col-lg-6" name="login" value="<?php echo $lang["log_in"];?>">
				<input type="submit" class="btn btn-primary col-lg-6" name="signup" value="<?php echo $lang["sign_up"];?>">
			</div>
		</form>
	</div>
</div>
