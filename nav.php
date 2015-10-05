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
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <?php echo $_SESSION["username"];?> <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li><a href="logout.php"><?php echo $lang["log_out"];?></a></li>
				</ul>
			</li>
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
