<?php
?>
<nav class="navbar navbar-fixed-top">
	<div class="container-fluid">
		<a class="navbar-brand">Strawberry</a>
		<form class="navbar-form navbar-left" role="search">
			<div class="form-group">
				<input type="text" class="form-control" name="search_terms" placeholder="Search anything...">
			</div>
			<button type="submit" class="btn btn-default">Rechercher</button>
		</form>
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav navbar-right">
				<?php if(!isset($_SESSION["power"])){?>
				<a href="portal.php" class="btn btn-primary navbar-btn">Log in</a>
				<a href="signup.php" role="button" class="btn btn-default navbar-btn">Sign up</a>
				<?php } else { ?>
				<li><a href=""><span class="glyphicon glyphicon-user"></span><?php echo $_SESSION["username"];?></a></li>
				<li><a href="logout.php"><span class="glyphicon glyphicon-off"></span> Sign out</a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
</nav>
