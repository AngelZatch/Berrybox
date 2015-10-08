<?php
include "db_connect.php";
$token = $_POST["id"];

if($token != null){
	$content = file_get_contents("http://youtube.com/get_video_info?video_id=".$token);
	parse_str($content, $ytarr);
	echo $ytarr['title'];
} else {
	return null;
}
?>
