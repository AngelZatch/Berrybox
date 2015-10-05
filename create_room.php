<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
if(isset($_GET["lang"])){
	$lang = $_GET["lang"];
	$_SESSION["lang"] = $lang;

	include_once "languages/lang.".$lang.".php";
} else {
	header("Location:create_room.php?lang=en");
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Create a room</title>
		<?php include "styles.php";?>
	</head>
	<body>
	<?php include "nav.php";?>
		<div class="main">
			<div id="create-room-form">
				<div class="form-group">
					<label for="roomName">Room's name</label>
					<input type="text" placeholder="Room's name" class="form-control" name="roomName">
				</div>
				<div class="form-group">
					<div class="radio"><label for=""><input type="radio" name="protection">Public</label></div>
					<div class="radio"><label for=""><input type="radio" name="protection">Locked</label></div>
					<div class="radio"><label for=""><input type="radio" name="protection">Private</label></div>
				</div>
				<button name="createRoom" class="btn btn-primary btn-block"><?php echo $lang["room_create"];?></button>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
		$('[name=createRoom]').click(function(){
			var roomName = $('[name=roomName]').val();
			var user = "<?php echo $_SESSION["token"];?>";
			$.post("functions/room_create.php", {roomName : roomName, creator : user}).done(function(data){
				console.log(data);
				window.location.replace("room.php?id="+data+"&lang=<?php echo $_GET["lang"];?>");
			})
		})
		</script>
	</body>
</html>
