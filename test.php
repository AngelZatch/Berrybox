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
			<div class="form-group">
				<div class="input-group">
					<input type="text" placeholder="Write your youtube link here" class="form-control url-box">
					<span class="input-group-btn">
						<button class="btn btn-primary btn-block play-url" data-toggle="modal">Submit</button>
					</span>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(".play-url").click(function(){
				var string = $(".url-box").val();

				/** The ID of the video must be 11 characters long. So we have to find the only string of 11 characters not interrupted by any special character. **/
				/** Example : https://www.youtube.com/watch?v=Akw-b0P4mKY **/
				var reg = new RegExp(/\?v=([a-z0-9\-]+)\&?/i); // works for all youtube links except youtu.be type

				/** First, we test the string for the most common type, using the regMatch **/
				var res = reg.exec(string);
				if(res == null){
					var alt = new RegExp(/\.be\/([a-z0-9\-]+)\&?/i); // works for youtu.be type links
					res = alt.exec(string);
				}
				alert(res[1]);
			});
		</script>
	</body>
</html>
