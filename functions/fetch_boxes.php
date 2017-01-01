<?php
session_start();
require_once "db_connect.php";
require_once "tools.php";
$db = PDOFactory::getConnection();

$user_token = ($_SESSION["token"])?$_SESSION["token"]:-1;

$filter = $_GET["filter"];

if($filter == "featured"){ // Featured boxes
	$query = "SELECT box_token, room_name, user_pseudo, user_pp, type, room_lang, creation_date FROM rooms r
					JOIN user u ON r.room_creator = u.user_token
					JOIN room_types rt ON r.room_type = rt.id";
	if($user_token != -1){
		$query .= " WHERE room_active = 1 AND (room_protection != 2 OR (room_protection = 2 AND room_creator = '$user_token'))";
	} else {
		$query .= " WHERE room_active = 1 AND room_protection != 2";
	}
} else if($filter == "private"){ // "My boxes" page
	$query = "SELECT * FROM rooms r
							JOIN user u ON r.room_creator = u.user_token
							JOIN room_types rt ON r.room_type = rt.id
							WHERE r.room_creator='$user_token'";
} else if (preg_match('/(public)-([\w]*)/i', $filter, $matches)){ // Public profile
	$filter_token = $matches[2];
	$user_token = solveUserFromName($db, $filter_token);
	$query = "SELECT box_token, room_name, user_pseudo, user_pp, type, room_lang, creation_date FROM rooms r
					JOIN user u ON r.room_creator = u.user_token
					JOIN room_types rt ON r.room_type = rt.id
					WHERE r.room_creator = '$user_token' AND room_active = 1 AND room_protection = 1";
} else { // Search
	$filter_token = preg_match('/(search)-([\w]*)/i', $filter, $matches)[2];
	if($user_token != -1){
		$query = "SELECT * FROM rooms r
					JOIN user u ON r.room_creator = u.user_token
					JOIN room_types rt ON r.room_type = rt.id
					WHERE (room_name LIKE '%{$filter_token}%' OR user_pseudo LIKE '%{$filter_token}%') AND room_active = '1' AND (room_protection != 2 OR (room_protection = 2 AND room_creator = '$user_token')) ORDER BY room_name ASC";
	} else {
		$query = "SELECT * FROM rooms r
					JOIN user u ON r.room_creator = u.user_token
					JOIN room_types rt ON r.room_type = rt.id
					WHERE room_name LIKE '%{$filter_token}%' AND room_active = '1' AND room_protection != 2 ORDER BY room_name ASC";
	}
}

$stmt = $db->query($query);

$boxes = array();
while($box = $stmt->fetch()){
	$details = $db->query("SELECT link, video_name, video_status FROM roomHistory_$box[box_token] rh
							JOIN song_base sb ON sb.song_base_id = rh.video_index
							WHERE video_status = 1 OR video_status = 2
							ORDER BY playlist_order DESC LIMIT 1")->fetch();
	$watchers = $db->query("SELECT COUNT(room_user_token) FROM roomUsers_$box[box_token]")->fetch(PDO::FETCH_COLUMN);
	$b = array(
		"token" => $box["box_token"],
		"name" => $box["room_name"],
		"admin" => $box["user_pseudo"],
		"admin_pp" => $box["user_pp"],
		"type" => $box["type"],
		"lang" => "lang_".$box["room_lang"],
		"creation_date" => $box["creation_date"],
		"video_link" => $details["link"],
		"video_name" => ($details["video_name"])?$details["video_name"]:"-",
		"video_status" => $details["video_status"],
		"watchers" => $watchers,
		"filter" => $filter
	);
	array_push($boxes, $b);
}
echo json_encode($boxes);
?>
