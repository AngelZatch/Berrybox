<?php
session_start();
?>
<div id="create-room">
	<div class="form-group">
		<label for="roomName">Room's name</label>
		<input type="text" placeholder="Room's name" class="form-control" name="roomName">
	</div>
	<div class="form-group">
		<div class="radio"><label for=""><input type="radio" name="protection">Public</label></div>
		<div class="radio"><label for=""><input type="radio" name="protection">Locked</label></div>
		<div class="radio"><label for=""><input type="radio" name="protection">Private</label></div>
	</div>
	<button name="createRoom" class="btn btn-primary btn-block">Create the room</button>
</div>
<script>
	$(document).on('click', '[name=createRoom]', function(){
		var roomName = $('[name=roomName]').val();
		var user = "<?php echo $_SESSION["token"];?>";
		$.post("functions/room_create.php", {roomName : roomName, creator : user}).done(function(data){
			/** Unique token of the room **/
			window.uniqueToken = data;
			console.log("Room token : "+window.uniqueToken);
			/** Once the room is created **/
			$("#large-block").empty();
			/** Bring the player of the room **/
			$("#large-block").load("includes.page_player.php");

			/** Load the chat of the room **/
		})
	})
</script>
