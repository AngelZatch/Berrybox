<?php
require_once "/var/www/Strawberry/functions/db_connect.php";
$db = PDOFactory::getConnection();

/** This script searches and deletes all rooms that have been inactive for more than 24 hours.

This script runs every hour
cron line : 0 * / 1 * * * /usr/bin/php /var/www/Strawberry/functions/schedules/delete_old_rooms.php
**/


//$time = date_create('now', new datetimezone('UTC'))->format('Y-m-d H:i:s');
date_default_timezone_set('UTC');
$limitDate = date('Y-m-d H:i:s', time() - 86400);

$queryInactive = $db->query("SELECT box_token FROM rooms WHERE room_active = 0 AND last_active_date < '$limitDate'");

while($inactive = $queryInactive->fetch()){
	$inactiveToken = $inactive["box_token"];

	try{
		$delete = $db->query("DROP TABLE roomChat_$inactiveToken");
		$delete = $db->query("DROP TABLE roomHistory_$inactiveToken");
		$delete = $db->query("DROP TABLE roomUsers_$inactiveToken");
	} catch(PDOException $e){
		// Silently dropping the exception. If the tables are not found, it means they've been dropped manually, which should never happen. The row is still deleted even if tables are already non-existent, so that end result stays clean.
	}
	try{
		$delete = $db->query("DELETE FROM rooms WHERE box_token = '$inactiveToken'");
	} catch(PDOException $e){
		// Once again, silently dropping the exception to ensure even manual operation doesn't disrupte the scheduled operation. If the row has been deleted by hand, the script won't fall into error and continue on.
	}
}

?>
