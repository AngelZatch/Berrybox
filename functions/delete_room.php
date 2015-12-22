<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$inactiveToken = $_POST["roomToken"];

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

?>
