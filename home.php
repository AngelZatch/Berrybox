<?php
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
<?php include "includes/page-player.php";?>
<?php include "includes/chat.php";?>
</div>
<?php include "includes/player.php";?>
<?php include "scripts.php";?>
</body>
</html>
