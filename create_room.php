<?php
session_start();
?>
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
	<button name="createRoom" class="btn btn-primary btn-block">Create the room</button>
</div>
