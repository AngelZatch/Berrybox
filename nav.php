<?php
if(isset($_SESSION["token"])){
	//If the user is connected
	$userDetails = $db->query("SELECT * FROM user u
							JOIN user_preferences up
								ON u.user_token = up.up_user_id
							WHERE user_token='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);
	$ppAdresss = "profile-pictures/".$userDetails["user_pp"];
	$userLang = $userDetails["user_lang"];
	if($userLang == ""){
		$userLang = "en";
	}
	include "languages/lang.".$userLang.".php";
} else {
	//If there's no user, the default display language is 'en'
	$userLang = "en";
	include_once "languages/lang.".$userLang.".php";
}
?>
<nav class="navbar navbar-fixed-top">
	<div class="container-fluid">
		<a href="home" class="navbar-brand">Berrybox</a>
		<!--<form class="navbar-form navbar-left" role="search">
<div class="form-group">
<input type="text" class="form-control" name="search_terms" placeholder="Search user, room...">
</div>
<button type="submit" class="btn btn-default">Rechercher</button>
</form>-->
		<ul class="nav navbar-nav navbar-right">
			<?php if(isset($_SESSION["username"])){ ?>
			<li>
				<a href="create" class="btn btn-primary btn-nav"><?php echo $lang["room_create"];?></a>
			</li>
			<li>
				<a class="popover-trigger" data-toggle="popover-x" data-target="#user-menu" role="button" data-trigger="focus" data-placement="bottom bottom-right">
					<div class="small-pp">
						<img src="<?php echo $ppAdresss;?>" alt="" style="width:inherit;">
					</div>
				</a>
				<div id="user-menu" class="popover popover-default popover-md menu-popover">
					<div class="arrow"></div>
					<div class="popover-content">
						<div class="medium-pp">
							<img src="<?php echo $ppAdresss;?>" alt="" style="width:inherit;">
						</div>
						<p class="user-menu-name"><?php echo $userDetails["user_pseudo"];?></p>
						<a href="profile/settings" class="btn btn-primary no-margin"><?php echo $lang["my_profile"];?></a>
						<a href="user/<?php echo $_SESSION["username"];?>" class="btn btn-primary no-margin"><?php echo $lang["public_profile"];?></a>
					</div>
					<div class="popover-footer">
						<a href="logout.php" class="btn btn-primary no-margin"><?php echo $lang["log_out"];?></a>
					</div>
				</div>
			</li>
			<?php } else { ?>
			<li><a href="portal" class="navbar-link"><?php echo $lang["log_in"];?></a></li>
			<li><a href="signup" class="navbar-link"><?php echo $lang["sign_up"];?></a></li>
			<!--<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <?php echo $lang["language_name"];?> <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li><a href="/home"><?php echo $lang["lang_en"];?></a></li>
					<li><a href="/home"><?php echo $lang["lang_jp"];?></a></li>
					<li><a href="/home"><?php echo $lang["lang_fr"];?></a></li>
				</ul>
			</li>-->
			<?php } ?>
		</ul>
	</div>
</nav>
