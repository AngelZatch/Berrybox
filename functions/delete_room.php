<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_POST["box_token"];

try{
	$delete = $db->query("DROP TABLE roomChat_$box_token");
	$delete = $db->query("DROP TABLE roomHistory_$box_token");
	$delete = $db->query("DROP TABLE roomUsers_$box_token");
} catch(PDOException $e){
	// Silently dropping the exception. If the tables are not found, it means they've been dropped manually, which should never happen. The row is still deleted even if tables are already non-existent, so that end result stays clean.
}
try{
	$delete = $db->query("DELETE FROM rooms WHERE box_token = '$box_token'");
} catch(PDOException $e){
	// Once again, silently dropping the exception to ensure even manual operation doesn't disrupte the scheduled operation. If the row has been deleted by hand, the script won't fall into error and continue on.
}

?>
