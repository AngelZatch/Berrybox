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
	</head>
	<body>
		<div class="main">
			<?php
			/*$played = $db->query("UPDATE roomHistory_bqtaygnaaxgdr7g
					SET video_status='5'
					WHERE history_link = 'KLqZ4AAypFE'");*/

			$nextVideoState = $db->query("SELECT video_status, room_history_id
											FROM roomHistory_bqtaygnaaxgdr7g
											WHERE room_history_id =
												(SELECT room_history_id
													FROM roomHistory_bqtaygnaaxgdr7g
													WHERE history_link='tzUuqu9vqCs'
													AND video_status='2')+1")->fetch(PDO::FETCH_ASSOC);
			//$played = $db->query("SELECT 26")->fetch(PDO::FETCH_ASSOC);
			echo $nextVideoState["video_status"];
			if($nextVideoState["video_status"] > 2){
				$confirmIgnore = $db->query("UPDATE roomHistory_bqtaygnaaxgdr7g
											SET video_status = '2'
											WHERE room_history_id = '$nextVideoState[room_history_id]'")
			}
			/*echo $played;*/
			?>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
