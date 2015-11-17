<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();
$queryActiveRooms = $db->query("SELECT * FROM rooms r
								JOIN user u ON r.room_creator = u.user_token
								WHERE room_active = 1 AND room_protection != 3 OR (room_protection = 3 AND room_creator = '$_SESSION[token]')");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Berrybox</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main">
			<div id="large-block">
				<p id="active-rooms-title"><?php echo $lang["active_room"];?></p>
				<div class="container-fluid">
					<?php while($activeRooms = $queryActiveRooms->fetch(PDO::FETCH_ASSOC)){ ?>
					<div class="panel panel-active-room">
						<div class="panel-body">
							<p class="col-lg-3"><?php echo $activeRooms["room_name"];?></p>
							<p class="col-lg-3"><a href="user.php?id=<?php echo $activeRooms["user_token"];?>&lang=<?php echo $_GET["lang"];?>"><?php echo $activeRooms["user_pseudo"];?></a></p>
							<div class="col-lg-6">
								<?php if($activeRooms["room_protection"] == 2 && (!isset($_SESSION["token"]) || (isset($_SESSION["token"]) && $_SESSION["token"] != $activeRooms["room_creator"]))){?>
								<p class="error-password" style="display:none;"><?php echo $lang["wrong_password"];?></p>
								<input type="password" class="form-control password-input" placeholder="<?php echo $lang["password"];?>" name="password" id="password-<?php echo $activeRooms["room_token"];?>" style="display:none;">
								<a class="btn btn-primary btn-block password-protected"><?php echo $lang["room_join"];?></a>
								<?php } else { ?>
								<a href="room.php?id=<?php echo $activeRooms["room_token"];?>&lang=<?php echo $_GET["lang"];?>" class="btn btn-primary btn-block"><?php echo $lang["room_join"];?></a>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				$(".password-protected").click(function(){
					var joinButton = $(this);
					joinButton.hide('200');
					var passwordInput = $(this).prev();
					passwordInput.show('200');
					passwordInput.focus();
				})
				$('.password-input').on('focus',function(){
					$(this).keyup(function(event){
						if(event.keyCode == 27){
							$(this).hide('200');
							$(this).next().show('200');
						}
						if(event.keyCode == 13){
							var password = $(this).val();
							var roomToken = $(this).attr('id').substr(9);
							$.post("functions/submit_password.php", {password : password, roomToken : roomToken}).success(function(data){
								if(data == 1){
									window.location.replace("room.php?id="+roomToken+"&lang=<?php echo $_GET["lang"];?>");
								} else {
									$("#password-"+roomToken).val('');
									$("#password-"+roomToken).prev().show();
								}
							})
						}
					})
				}).on('blur', function(){
					$(this).hide('200');
					$(this).next().show('200');
				})
			})
		</script>
	</body>
</html>
