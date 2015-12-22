<?php
include "../db_connect.php";
$db = PDOFactory::getConnection();

/** This script searches and deletes all rooms that have been closed.
 Evolution : implement a date of creation / closure of each room
so this script deletes only rooms closed for more than 14 days.

For now, this script runs once every week; every sunday at 1am.
cron line : 0 1 * * 7 /usr/bin/php /var/www/Strawberry/functions/schedules/delete_old_rooms.php
**/


//$time = date_create('now', new datetimezone('UTC'))->format('Y-m-d H:i:s');

$queryInactive = $db->query("SELECT room_token FROM rooms WHERE room_active = 0");

while($inactive = $queryInactive->fetch(PDO::FETCH_ASSOC)){
	$inactiveToken = $inactive["room_token"];

	try{
		$delete = $db->query("DROP TABLE roomChat_$inactiveToken");
		$delete = $db->query("DROP TABLE roomHistory_$inactiveToken");
		$delete = $db->query("DROP TABLE roomUsers_$inactiveToken");
	} catch(PDOException $e){
		// Silently dropping the exception. If the tables are not found, it means they've been dropped manually, which should never happen. The row is still deleted even if tables are already non-existent, so that end result stays clean.
	}
	try{
		$delete = $db->query("DELETE FROM rooms WHERE room_token = '$inactiveToken'");
	} catch(PDOException $e){
		// Once again, silently dropping the exception to ensure even manual operation doesn't disrupte the scheduled operation. If the row has been deleted by hand, the script won't fall into error and continue on.
	}
}

?>
