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
			<div class="form-group has-feedback" id="username-form-group">
				<label for="username" class="control-label"><?php echo $lang["username"];?></label>
				<input type="text" placeholder="Username" class="form-control" name="username">
			</div>
			<div class="form-group">
				<label for="login_pwd" class="control-label"><?php echo $lang["password"];?></label>
				<input type="password" class="form-control" name="login_pwd">
			</div>
			<div class="row">
				<input type="submit" class="btn btn-primary col-lg-6" name="login" value="<?php echo $lang["log_in"];?>">
				<input type="submit" class="btn btn-primary col-lg-6" name="signup" value="<?php echo $lang["sign_up"];?>">
			</div>
		</form>
	</div>
</div>
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
				if(string != ""){
					//console.log("timeout expired. Search with '"+string+"' query.");
					$.post("functions/compare_user_string.php", {string : string}).done(function(data){
						if(data == 1){
							//console.log("Success");
							applySuccessFeedback(elementId);
							$(":regex(name,signup)").removeClass("disabled");
							$(":regex(name,signup)").removeAttr("disabled");
						} else {
							//console.log("This username already exists");
							applyWarningFeedback(elementId);
							$(":regex(name,signup)").addClass("disabled");
							$(":regex(name,signup)").attr("disabled", "disabled");
						}
					})
				}
			}, 1000);
		})
	})
</script>
