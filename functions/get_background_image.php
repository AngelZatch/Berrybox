<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$max = $db->query("SELECT COUNT(*) FROM song_base")->fetch(PDO::FETCH_COLUMN);

getRandomImage($db, $max);

function getRandomImage($db, $max){

	$id = rand(1, $max);

	$get = $db->query("SELECT link FROM song_base WHERE song_base_id = '$id'")->fetch(PDO::FETCH_COLUMN);

	$headers = get_headers("https://i.ytimg.com/vi/".$get."/maxresdefault.jpg");
	$code = substr($headers[0], 9, 3);
	if($code != 404){
		echo $get;
	} else {
		getRandomImage($db, $max);
	}
}
?>
