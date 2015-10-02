<?php
session_start();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Strawberry Music Streamer</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main">
			<div id="large-block">
				<?php include "base_page.php";?>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
	<script>
		$(document).ready(function(){
			$.post("functions/active_rooms.php").done(function(data){
				var rooms = JSON.parse(data);
				var listOfRooms = "";
				for(var i = 0; i < rooms.length; i++){
					listOfRooms += "<div class='col-lg-3'>";
					listOfRooms += "<div class='thumbnail'>";
					listOfRooms += "<img src='assets/Binboda.Momiji.full.1184759.jpg' style='height:100px;'>";
					listOfRooms += "<div class='caption'>";
					listOfRooms += "<p>"+rooms[i].name+"</p>";
					listOfRooms += "<p>"+rooms[i].creator_name+"</p>";
					listOfRooms += "<p><button class='btn btn-primary btn-block join-room' value="+rooms[i].token+">Rejoindre</button></p>";
					listOfRooms += "</div>";
					listOfRooms += "</div>";
					listOfRooms += "</div>";
				}
				$("#large-block").append(listOfRooms);
			})
		}).on('click', '#create-room', function(){
			$("#large-block").empty();
			$("#large-block").load("create_room.php");
		}).on('click', '.join-room', function(){
			var roomToken = $(this).val();
			var userToken = "<?php echo $_SESSION["token"];?>";
			$.post("functions/join_room.php", {roomToken : roomToken, userToken : userToken}).done(function(data){
				$("#large-block").empty();
				$("#large-block").load("room.php", {roomToken : roomToken});
			})
		}).on('click', '[name=createRoom]', function(){
			var roomName = $('[name=roomName]').val();
			var user = "<?php echo $_SESSION["token"];?>";
			$.post("functions/room_create.php", {roomName : roomName, creator : user}).done(function(data){
				/** Unique token of the room **/
				sessionStorage.setItem("created-room", data);
				window.uniqueToken = data;
				/** Once the room is created **/
				$("#large-block").empty();
				/** Bring the player of the room **/
				$("#large-block").load("includes/room.php");
			})
		})
		$("#create-room").on('click', function(){
			$("#large-block").empty();
			$("#large-block").load("create_room.php");
		})
	</script>
</html>
