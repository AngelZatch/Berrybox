<nav class="navbar navbar-fixed-top">
	<div class="container-fluid">
		<a href="home.php?lang=<?php echo $_GET["lang"];?>" class="navbar-brand">Strawberry beta</a>
		<!--<form class="navbar-form navbar-left" role="search">
<div class="form-group">
<input type="text" class="form-control" name="search_terms" placeholder="Chercher une piste, un album, un artiste...">
</div>
<button type="submit" class="btn btn-default">Rechercher</button>
</form>-->
		<ul class="nav navbar-nav navbar-right">
			<?php if(isset($_SESSION["username"])){
	$userDetails = $db->query("SELECT * FROM user u
							WHERE user_token='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);
	$ppAdresss = "profile-pictures/".$userDetails["user_pp"];?>
			<li>
				<a href="create_room.php?lang=<?php echo $_GET["lang"];?>" class="btn btn-primary btn-nav"><?php echo $lang["room_create"];?></a>
			</li>
			<li class="dropdown">
				<a href="#" class="dropdown-toggle dropdown-profile" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
					<div class="small-pp">
						<img src="<?php echo $ppAdresss;?>" alt="" style="width:inherit;">
					</div>
				</a>
				<ul class="dropdown-menu">
					<li><a href="profile.php?id=<?php echo $_SESSION["token"];?>&lang=<?php echo $_GET["lang"];?>"><?php echo $lang["my_profile"];?></a></li>
					<li><a href="logout.php"><?php echo $lang["log_out"];?></a></li>
				</ul>
			</li>
			<?php } else { ?>
			<li><a href="portal.php?lang=<?php echo $_GET["lang"];?>" class="navbar-link"><?php echo $lang["log_in"];?></a></li>
			<li><a href="signup.php?lang=<?php echo $_GET["lang"];?>" class="navbar-link"><?php echo $lang["sign_up"];?></a></li>
			<?php } ?>
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <?php echo $lang["language_name"];?> <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li><a href="home.php?lang=en"><?php echo $lang["lang_en"];?></a></li>
					<li><a href="home.php?lang=jp"><?php echo $lang["lang_jp"];?></a></li>
					<li><a href="home.php?lang=fr"><?php echo $lang["lang_fr"];?></a></li>
				</ul>
			</li>
		</ul>
	</div>
</nav>
