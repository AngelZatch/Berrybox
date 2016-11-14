<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_GET["box_token"];

$queryTypes = $db->query("SELECT * FROM room_types ORDER BY id = (SELECT room_type FROM rooms WHERE box_token = '$box_token') DESC");

$types = array();
while($type = $queryTypes->fetch()){
	$t = array(
		"id" => $type["id"],
		"type" => $type["type"]
	);
	array_push($types, $t);
}
echo json_encode($types);
?>
