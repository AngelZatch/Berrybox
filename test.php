<?php
session_start();
require "functions/db_connect.php";
include "functions/tools.php";
$db = PDOFactory::getConnection();

?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Strawberry Music Streamer</title>
		<?php include "styles.php";?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
	</head>
	<body>
		<div class="main">
			<?php
			?>

			<pre>
				<?php
date_default_timezone_set('UTC');
$limitDate = date('Y-m-d H:i:s', time() - 3600);
?>
			</pre>
			<?php
			?>
		</div>
		<?php include "scripts.php";?>
		<style>
			pre{
				color: black;
			}
		</style>
	</body>
</html>
