<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								WHERE room_active = 1");

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
			$video_id = 1067;
			$moods = $db->query("SELECT vote_mood, COUNT(vote_mood) AS count_mood FROM `votes` WHERE video_index = $video_id GROUP BY vote_mood");
			?>

			<pre>
				<?php
/*print_r($moods->fetchAll(PDO::FETCH_ASSOC));*/
while($mood = $moods->fetch(PDO::FETCH_ASSOC)){
	echo $mood["count_mood"];
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
