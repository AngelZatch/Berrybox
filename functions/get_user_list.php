<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];

$queryList = $db->query("SELECT * FROM roomUsers_$roomToken ru
						JOIN user u ON ru.room_user_token = u.user_token
						WHERE room_user_present = 1
						ORDER BY room_user_state DESC");
$userList = array();
while($user = $queryList->fetch(PDO::FETCH_ASSOC)){
	$u = array();
	$u["token"] = $user["room_user_token"];
	$u["pseudo"] = $user["user_pseudo"];
	$u["power"] = $user["room_user_state"];
	array_push($userList, $u);
}
echo json_encode($userList);
?>
