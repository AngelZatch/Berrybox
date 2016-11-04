<?php
session_start();
require "functions/db_connect.php";
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
			$box_token = "33VJQZR2EMF9I63";

			$queue = $db->query("SELECT room_history_id FROM roomHistory_$box_token WHERE video_status = 0 ORDER BY room_history_id ASC")->fetchAll();
			$order = $db->query("SELECT playlist_order FROM roomHistory_$box_token WHERE video_status = 0 ORDER BY room_history_id ASC")->fetchAll(PDO::FETCH_COLUMN);
			?>

			<pre>
				<?php
print_r($order);
$numbers = range(min($order), max($order));

shuffle($numbers);
$shuffled_array = []; $i = 0;
foreach($numbers as $number){
	$current_id = $queue[$i]["room_history_id"];

	echo "UPDATE SET playlist_order = $number WHERE room_history_id = $current_id";
	echo "<br>";
	$i++;
}
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
