<?php
session_start();
require_once "functions/db_connect.php";
$db = PDOFactory::getConnection();
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
			<div class="col-lg-8" id="large-block">
				<?php include "includes/page-player.php";?>
			</div>
			<?php include "includes/chat.php";?>
		</div>
		<?php include "includes/player.php";?>
		<?php include "scripts.php";?>
	</body>
	<script>
		$("#create-room").on('click', function(){
			$("#large-block").empty();
			$("#large-block").load("includes/create_room.php");
		})
	</script>
</html>
