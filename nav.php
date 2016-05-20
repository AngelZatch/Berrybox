<?php
if(isset($_SESSION["token"])){
	//If the user is connected
	$userDetails = $db->query("SELECT * FROM user u
							JOIN user_preferences up
								ON u.user_token = up.up_user_id
							WHERE user_token='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);
	$followRooms = $db->query("SELECT * FROM rooms
								WHERE room_creator in (SELECT user_followed FROM user_follow uf
								WHERE user_following = '$_SESSION[token]')
								AND room_protection = '1' AND room_active = '1'")->rowCount();
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
<nav class="navbar navbar-static-top">
	<div class="container">
		<div class="hidden-sm hidden-md hidden-lg">
			<div class="navbar-header">
				<a href="home" class="navbar-brand">Berrybox</a>
				<?php if(isset($_SESSION["username"])){ ?>
				<button class="navbar-toggle collapsed no-padding" data-toggle="collapse" data-target="#navbar">
					<div class="small-pp">
						<img src="<?php echo $ppAdresss;?>" alt="" style="width:inherit;">
					</div>
				</button>
				<?php } else { ?>
				<button class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<?php } ?>
			</div>
			<div id="navbar" class="navbar-collapse collapse">
				<ul class="nav navbar">
					<?php if(isset($_SESSION["username"])){ ?>
					<li>
						<a href="follow"><span class="glyphicon glyphicon-heart"></span> <?php echo $lang["following"];?> (<?php echo $followRooms;?>)</a>
					</li>
					<li>
						<a href="profile/settings"><span class="glyphicon glyphicon-cog"></span> <?php echo $lang["my_settings"];?></a>
					</li>
					<li>
						<a href="user/<?php echo $_SESSION["username"];?>"><span class="glyphicon glyphicon-user"></span> <?php echo $lang["my_profile"];?></a>
					</li>
					<li>
						<a href="my/likes"><span class="glyphicon glyphicon-thumbs-up"></span> <?php echo $lang["profile_likes"];?></a>
					</li>
					<li>
						<a href="create" class="btn btn-primary btn-nav"><?php echo $lang["room_create"];?></a>
					</li>
					<li>
						<form action="search" method="post" class="navbar-form" role="search">
							<div class="input-group">
								<span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
								<input type="text" class="form-control search-input" name="search-terms" placeholder="<?php echo $lang["search"];?>...">
							</div>
						</form>
					</li>
					<li>
						<a href="logout.php"><span class="glyphicon glyphicon-off"></span> <?php echo $lang["log_out"];?></a>
					</li>
					<?php } else { ?>
					<li>
						<a href="portal" class="navbar-link"> <span class="glyphicon glyphicon-log-in"></span> <?php echo $lang["log_in"];?></a>
					</li>
					<li>
						<a href="signup" class="navbar-link"><?php echo $lang["sign_up"];?></a>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<div class="visible-sm visible-md visible-lg">
			<div class="navbar-header">
				<a href="home" class="navbar-brand">Berrybox</a>
			</div>
			<ul class="nav navbar-nav navbar-right">
				<form action="search" method="post" class="navbar-form navbar-left" role="search">
					<div class="input-group">
						<span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
						<input type="text" class="form-control search-input" name="search-terms" placeholder="<?php echo $lang["search"];?>...">
					</div>
				</form>
				<?php if(isset($_SESSION["username"])){ ?>
				<li>
					<a href="follow"><span class="glyphicon glyphicon-heart"></span> <?php echo $lang["following"];?> (<?php echo $followRooms;?>)</a>
				</li>
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
							<ul class="popover-menu">
								<a href="profile/settings" class="no-margin"><span class="glyphicon glyphicon-cog"></span> <?php echo $lang["my_settings"];?></a>
								<a href="user/<?php echo $_SESSION["username"];?>" class="no-margin"><span class="glyphicon glyphicon-user"></span> <?php echo $lang["my_profile"];?></a>
								<a href="my/likes" class="no-margin"><span class="glyphicon glyphicon-thumbs-up"></span> <?php echo $lang["profile_likes"];?></a>
							</ul>
						</div>
						<div class="popover-footer">
							<a href="logout.php" class="btn btn-primary no-margin"><span class="glyphicon glyphicon-off"></span> <?php echo $lang["log_out"];?></a>
						</div>
					</div>
				</li>
				<?php } else { ?>
				<li><a href="portal" class="navbar-link"><?php echo $lang["log_in"];?></a></li>
				<li><a href="signup" class="navbar-link"><?php echo $lang["sign_up"];?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
</nav>
