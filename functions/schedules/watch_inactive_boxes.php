<?php
require_once "/var/www/Strawberry/functions/db_connect.php";
$db = PDOFactory::getConnection();

/** This script closes boxes inactive for more than an hour

This script has to run every hour. (* / 1 arguemnt is all attached)

cron line : 0 * / 1 * * * /usr/bin/php /var/www/Strawberry/functions/schedules/watch_inactive_boxes.php
**/

date_default_timezone_set('UTC');
$limitDate = date('Y-m-d H:i:s', time() - 3600);

$db->query("UPDATE rooms SET room_active = '0' WHERE room_active = '1' AND last_active_date < '$limitDate'");
?>
