<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								WHERE room_active = 1");
$rooms = array();
while($activeRooms = $queryActiveRooms->fetch(PDO::FETCH_ASSOC)){
	$r = array();
	$r["name"] = $activeRooms["room_name"];
	$r["token"] = $activeRooms["box_token"];
	$r["creator"] = $activeRooms["room_creator"];
	$r["creator_name"] = $activeRooms["user_pseudo"];
	array_push($rooms, $r);
}
echo json_encode($rooms);
?>
