<?php
require_once "/var/www/Strawberry/functions/db_connect.php";
$db = PDOFactory::getConnection();

/** This script looks at boxes to identify the boxes left open but inactive:
- The last video has stopped playing more than 1 hour ago
- No message has been submitted for more than 1 hour

For now, this script will render these boxes inactive.

This script has to run every hour. (* / 1 arguemnt is all attached)
cron line : 0 * / 1 * * * /usr/bin/php /var/www/Strawberry/functions/schedules/watch_inactive_boxes.php
**/

date_default_timezone_set('UTC');
$limitDate = date('Y-m-d H:i:s', time() - 3600);

$queryInactive = $db->query("SELECT * FROM rooms WHERE room_protection = '1' AND room_active = '1'");
$flaggedBoxes = array();

while($inactive = $queryInactive->fetch(PDO::FETCH_ASSOC)){
	$roomToken = $inactive["room_token"];
	// First, we check for time of last sent message
	$conditionTime = $db->query("SELECT message_time FROM roomChat_$roomToken rc
							ORDER BY rc.message_time DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
	if($conditionTime["message_time"] <= $limitDate){ // If message is too old
		// Then we check for last video
		$conditionPlay = $db->query("SELECT history_start, video_status FROM roomHistory_$roomToken rh
							WHERE video_status = '2'
							ORDER BY rh.history_start DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
		if($conditionPlay["history_start"] <= $limitDate){ // More than 1 hour ago : flag
			$updateBox = $db->query("UPDATE rooms SET room_protection = '2' WHERE room_token = '$roomToken'");
		}
	}
}
?>
