<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();

$searchToken = $_GET["q"];

if(isset($_SESSION["token"])){
	$userSettings = $db->query("SELECT * FROM user_preferences up
							WHERE user_token='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);

	if($userSettings["up_theme"] == "1"){
		$theme = "dark";
	} else {
		$theme = "light";
	}
	$userLang = $userSettings["up_lang"];
	if($userLang == ""){
		$userLang = "en";
	}
	include_once "languages/lang.".$userLang.".php";
} else {
	include_once "languages/lang.en.php";
}

//search for users matching (and their boxes if they have some opened)
$queryMatchingUsers = $db->query("SELECT * FROM user u WHERE user_pseudo LIKE '%{$searchToken}%' ORDER BY user_pseudo ASC");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $lang["search"];?></title>
		<?php include "styles.php";
		if(isset($_SESSION["token"])){ ?>
		<link rel="stylesheet" href="assets/css/<?php echo $theme;?>-theme.css">
		<?php } else { ?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
		<?php } ?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main">
			<legend><?php echo $lang["search"];?></legend>
			<div class="col-lg-offset-2 col-lg-8">
				<p class="search-matching-title"><?php echo $lang["user_match"];?></p>
				<div class="container-fluid">
					<?php while($matchingUsers = $queryMatchingUsers->fetch(PDO::FETCH_ASSOC)){ ?>
					<div class="col-lg-3 col-md-4">
						<div class="panel">
							<div class="panel-body user-entry">
								<a href="user/<?php echo $matchingUsers["user_pseudo"];?>">
									<div class="search-user-pp">
										<img src="profile-pictures/<?php echo $matchingUsers["user_pp"];?>" class="profile-picture">
									</div>
									<p class="user-profile-name"><?php echo $matchingUsers["user_pseudo"];?></p>
									<div class="user-profile-bio search-bio">
										<?php echo ($matchingUsers["user_bio"])?$matchingUsers["user_bio"]:$lang["no_bio"];?>
									</div>
									<button class="btn btn-primary btn-block"><?php echo $lang["goto_user"];?></button>
								</a>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
				<p class="search-matching-title"><?php echo $lang["box_match"];?></p>
				<div class="container-fluid matching-boxes"></div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
		$(document).ready(function(){
			$.when(getUserLang()).done(function(data){
				window.language_tokens = JSON.parse(data);
				window.lang = language_tokens.user_lang;
				var search_token = /=([\w]*)/g.exec(top.location.search)[1];
				console.log(search_token);
				fetchBoxes($(".matching-boxes"), "search-"+search_token);
			})
		})
		</script>
	</body>
</html>
