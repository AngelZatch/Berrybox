<?php
session_start();
include "db_connect.php";
$db = PDOFactory::getConnection();

$link = $_POST["url"];
$time = date_create('now', new datetimezone('UTC'))->format('Y-m-d H:i:s');
$user = $_SESSION["token"];
$box_token = $_POST["box_token"];
$source = $_POST["source"];

if(strlen($link) == 11){
	$db->beginTransaction();
	// Check song_base
	$checkBase = $db->query("SELECT * FROM song_base WHERE link = '$link'");
	if($checkBase->rowCount() == 0){ // If the video is new:
		// We get the name from youtube.
		try{
			$content = file_get_contents("http://youtube.com/get_video_info?video_id=".$link);
			parse_str($content, $ytarr);
			$title = addslashes($ytarr['title']);
			$pending = "0";
			if($title == ""){ // If the name is unfetchable, we put the video into "need of info"
				$title = "-";
				$pending = "1";
			}
		}catch (Exception $e){
			$title = "-";
			$pending = "1";
		}

		// The video info is then uploaded in the base
		$uploadToBase = $db->prepare("INSERT INTO song_base(link, video_name, pending)
			VALUES(:link, :title, :pending)");
		$uploadToBase->bindParam(':link', $link);
		$uploadToBase->bindParam(':title', $title);
		$uploadToBase->bindParam(':pending', $pending);
		$uploadToBase->execute();

		// We get the entry index, which will be inserted in the playlist
		$baseIndex = $db->lastInsertId();
	} else { // If the video is not new:
		// We get existing details from the video base
		$checkBase = $db->query("SELECT * FROM song_base WHERE link = '$link'")->fetch(PDO::FETCH_ASSOC);
		$baseIndex = $checkBase["song_base_id"];
		if($checkBase["video_name"] != "-"){
			$pending = "0"; // If there's a name, no need to check
		} else {
			$pending = "1"; // If not, then the name has to be filled
		}
	}
	// Once everything is done, we insert the video in the playlist
	// First, we get the index of the previous entry
	include "tools.php";
	$playlist_order = getPlaylistOrdering($db, $box_token);

	$upload = $db->prepare("INSERT INTO roomHistory_$box_token(video_index, playlist_order, history_time, history_user)
		VALUES(:index, :p_order, :time, :user)");
	$upload->bindParam(':index', $baseIndex);
	$upload->bindParam(':p_order', $playlist_order);
	$upload->bindParam(':time', $time);
	$upload->bindParam(':user', $user);
	$upload->execute();

	$db->commit();

	// If info are not missing, result is 1 (success code). If not, it's the entry of the video in the song base (need for info)
	if($pending == "0"){
		echo "ok";
	} else {
		if($source == "playlist")
			echo 'info';
		else
			echo $baseIndex;
	}
} else {
	echo "error"; // Invalid link code
}
?>
