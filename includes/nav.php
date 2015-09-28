<?php
session_start();
?>
<nav class="navbar navbar-fixed-top">
	<div class="container-fluid">
		<a href="home.php" class="navbar-brand">Strawberry</a>
		<form class="navbar-form navbar-left" role="search">
			<div class="form-group">
				<input type="text" class="form-control" name="search_terms" placeholder="Search anything...">
			</div>
			<button type="submit" class="btn btn-default">Rechercher</button>
		</form>
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav navbar-right">
				<?php if(!isset($_SESSION["power"])){?>
				<li><button class="btn btn-primary">Log in</button></li>
				<li><a href="signup.php" role="button" class="btn btn-default">Sign up</a></li>
				<?php } else { ?>
				<li><a href=""><span class="glyphicon glyphicon-user"></span></a></li>
				<li><a href=""><span class="glyphicon glyphicon-off"></span> Sign out</a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
</nav>
