<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								WHERE room_active = 1");

?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Strawberry Music Streamer</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<div class="main">
			<span class="timer"></span><br>
			<span class="presses"></span><br>
			<span class="mean"></span><br><br>
			<span class="letters"></span>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				var timer = new Date;
				setInterval(function(){
					$(".timer").text((new Date - timer) / 1000);
					$(".mean").html(presses / $(".timer").text());
				}, 1000);
				var presses = 0;
				$(document).keypress(function(event){
					presses++;
					$(".presses").html(presses);
					$(".mean").html(presses / $(".timer").text());
					$(".letters").append(event.keyCode+", ");
				})
			})
		</script>
	</body>
</html>
