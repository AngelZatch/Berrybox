<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								WHERE room_active = 1");

if(isset($_GET["lang"])){
	$lang = $_GET["lang"];
	$_SESSION["lang"] = $lang;

	include_once "languages/lang.".$lang.".php";
} else {
	header("Location:home.php?lang=en");
}
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
				<button class="btn btn-primary btn-block" id="create-room"><?php echo $lang["room_create"];?></button>
				<p id="active-rooms-title"><?php echo $lang["active_room"];?></p>
				<div class="container-fluid">
					<?php while($activeRooms = $queryActiveRooms->fetch(PDO::FETCH_ASSOC)){ ?>
					<div class="col-lg-3">
						<div class="thumbnail">
							<img src="assets/Binboda.Momiji.full.1184759.jpg" alt="" style="height:100px;">
							<div class="caption">
								<p><?php echo $activeRooms["room_name"];?></p>
								<p><?php echo $activeRooms["user_pseudo"];?></p>
								<p><a href="room.php?id=<?php echo $activeRooms["room_token"];?>&lang=<?php echo $_GET["lang"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a></p>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
	<script>
		$(document).ready(function(){
			console.log("ready");
		}).on('click', '#create-room', function(){
			$("#large-block").empty();
			$("#large-block").load("create_room.php");
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
