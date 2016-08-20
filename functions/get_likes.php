<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$user_token = $_GET["user_token"];

$allLikes = $db->query("SELECT * FROM votes v
						JOIN song_base sb ON v.video_index = sb.song_base_id
						WHERE user_token = '$user_token'
						ORDER BY vote_mood ASC");

$likes = array();
while($entry = $allLikes->fetch(PDO::FETCH_ASSOC)){
	$l = array();
	$l["like_mood"] = $entry["vote_mood"];
	switch($l["like_mood"]){
		case '1':
			$l["key_mood"] = "like";
			$l["key_icon"] = "thumbs-up";
			break;

		case '2':
			$l["key_mood"] = "cry";
			$l["key_icon"] = "tint";
			break;

		case '3':
			$l["key_mood"] = "love";
			$l["key_icon"] = "heart";
			break;

		case '4':
			$l["key_mood"] = "energy";
			$l["key_icon"] = "eye-open";
			break;

		case '5':
			$l["key_mood"] = "calm";
			$l["key_icon"] = "bed";
			break;

		case '6':
			$l["key_mood"] = "fear";
			$l["key_icon"] = "flash";
			break;
	}
	$l["video_name"] = $entry["video_name"];
	$l["video_id"] = $entry["link"];
	$l["video_link"] = "https://www.youtube.com/watch?v=".$l["video_id"];
	array_push($likes, $l);
}
echo json_encode($likes);
?>
