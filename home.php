<?php
session_start();
require_once "functions/db_connect.php";

/** LOAD CHAT **/
?>
<html>
<head>
	<meta charset="UTF-8">
	<title>Strawberry Music Streamer</title>
	<?php include "styles.php";?>
</head>
<body>
<?php include "includes/nav.php";?>
<div class="main">
<div class="col-lg-8">
	<button class="btn btn-primary">Create a room</button>
</div>
<?php include "includes/chat.php";?>
</div>
<?php include "includes/player.php";?>
<?php include "scripts.php";?>
</body>
</html>
