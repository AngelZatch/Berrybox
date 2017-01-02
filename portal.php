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
		<script>
			// Facebook
			window.fbAsyncInit = function() {
				FB.init({
					appId		: '1772124869779068',
					cookie		: true, // enable cookies to allow the server to access the session
					xfbml		: true, // parse social plugins on this page
					version		: 'v2.8' // use graph api version 2.8
				});

				// Now that we've initialized the JavaScript SDK, we call FB.getLoginStatus().  This function gets the state of the person visiting this page and can return one of three states to the callback you provide.  They can be:
				// 1. Logged into your app ('connected')
				// 2. Logged into Facebook, but not your app ('not_authorized')
				// 3. Not logged into Facebook and can't tell if they are logged into your app or not.
				// These three cases are handled in the callback function.

				FB.getLoginStatus(function(response) {
					statusChangeCallback(response);
				});
			};

			// Load the SDK asynchronously
			(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/en_US/sdk.js";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));

			// This function is called when someone finishes with the Login Button.  See the onlogin handler attached to it in the sample code below.
			function checkLoginState() {
				FB.getLoginStatus(function(response) {
					statusChangeCallback(response);
				});
			}

			// Here we run a very simple test of the Graph API after login is successful.  See statusChangeCallback() for when this call is made.
			function getUserInfo(response) {
				console.log('Welcome!  Fetching your information.... ');
				FB.api('/me?fields=id,email,name', function(user_data) {
					console.log(user_data);
					$.post("functions/facebook_login.php", {
						facebook_token : response.authResponse.userID,
						access_token : response.authResponse.accessToken,
						user : user_data.name,
						mail : user_data.email}).done(function(data){
						console.log(data);
						console.log('Successful login for: ' + user_data.name + ' ('+ user_data.mail +')');
						window.top.location = "home";
					});
				});
			}

			function login(){
				FB.login(function(response){
					console.log("login");
					getUserInfo(response);
				}, {scope: 'public_profile,email'});
			}

			function statusChangeCallback(response) {
				console.log('statusChangeCallback');
				console.log(response);
				// The response object is returned with a status field that lets the app know the current login status of the person. Full docs on the response object can be found in the documentation for FB.getLoginStatus().
				if (response.status === 'connected') {
					// Logged into your app and Facebook.
					getUserInfo(response);
				} else if (response.status === 'not_authorized') {
					// The person is logged into Facebook, but not your app.
					/*document.getElementById('status').innerHTML = 'Please log ' +
						'into this app.';*/
				} else {
					// The person is not logged into Facebook, so we're not sure if
					// they are logged into this app or not.
					/*document.getElementById('status').innerHTML = 'Please log ' +
						'into Facebook.';*/
				}
			}
		</script>
	</body>
</html>
