<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_GET["box_token"];

date_default_timezone_set('UTC');
$time = date('Y-m-d H:i:s', time() - 15);

$stmt = $db->query("SELECT * FROM roomUsers_$box_token ru
						JOIN user u ON ru.room_user_token = u.user_token
						WHERE presence_stamp > '$time'
						ORDER BY room_user_state DESC");
$users = array();
while($user = $stmt->fetch(PDO::FETCH_ASSOC)){
	$u = array(
		"token" => $user["room_user_token"],
		"pseudo" => $user["user_pseudo"],
		"power" => $user["room_user_state"]
	);
	array_push($users, $u);
}
echo json_encode($users);
?>
