<?php
session_start();
require "functions/db_connect.php";
$db = PDOFactory::getConnection();

if(isset($_SESSION["token"])){
	$userSettings = $db->query("SELECT *
							FROM user_preferences up
							WHERE up_user_id='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);

	if($userSettings["up_theme"] == "1"){
		$theme = "dark";
	} else {
		$theme = "light";
	}
}

$profileToken = $_GET["id"];

$profileDetails = $db->query("SELECT * FROM user u
							JOIN user_stats us ON u.user_token = us.user_token
							WHERE u.user_token='$profileToken'")->fetch(PDO::FETCH_ASSOC);

$queryHistoryRooms = $db->query("SELECT * FROM rooms r
							WHERE r.room_creator='$profileToken'");

?>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $profileDetails["user_pseudo"];?></title>
		<base href="../../">
		<?php include "styles.php";
		if(isset($_SESSION["token"])){ ?>
		<link rel="stylesheet" href="assets/css/<?php echo $theme;?>-theme.css">
		<?php } else { ?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
		<?php } ?>
		<link rel="stylesheet" href="assets/css/fileinput.min.css">
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="main col-lg-12">
			<div class="col-sm-offset-2 col-sm-8 page-title">
				<p id="profile-title"><?php echo $lang["profile_history"];?></p>
				<span class="tip"><?php echo $lang["profile_history_tip"];?></span>
				<ul class="nav nav-tabs" id="profile-menu">
					<li role="presentation"><a href="<?php echo $_SESSION["lang"];?>/profile/<?php echo $_SESSION["token"];?>"><?php echo $lang["profile_settings"];?></a></li>
					<li role="presentation" class="active"><a href="<?php echo $_SESSION["lang"];?>/history/<?php echo $_SESSION["token"];?>"><?php echo $lang["profile_history"];?></a></li>
				</ul>
			</div>
			<div class="user-rooms col-sm-offset-2 col-sm-8">
				<?php while($historyRooms = $queryHistoryRooms->fetch(PDO::FETCH_ASSOC)){?>
				<div class="panel panel-active-room" id="panel-room-<?php echo $historyRooms["room_token"];?>">
					<div class="panel-body">
						<p class="col-lg-3"><?php echo $historyRooms["room_name"];?></p>
						<?php if($historyRooms["room_active"] == '1'){ ?>
						<p class="col-lg-3 label-status">
							<span class="label label-success"><span class="glyphicon glyphicon-signal"></span> <?php echo $lang["status_open"];?></span>
						</p>
						<div class="col-lg-6">
							<a href="<?php echo $_GET["lang"];?>/room/<?php echo $historyRooms["room_token"];?>" class="btn btn-primary"><?php echo $lang["room_join"];?></a>
						</div>
						<?php } else { ?>
						<p class="col-lg-3 label-status">
							<span class="label label-danger"><span class="glyphicon glyphicon-off"></span> <?php echo $lang["status_closed"];?></span>
						</p>
						<div class="col-lg-6">
							<a class="btn btn-primary" onClick="openRoom('<?php echo $historyRooms["room_token"];?>')"><?php echo $lang["room_reopen"];?></a>
							<a class="btn btn-danger" onClick="deleteRoom('<?php echo $historyRooms["room_token"];?>')"><?php echo $lang["room_delete"];?></a>
						</div>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/fileinput.min.js"></script>
		<script>
			function openRoom(roomToken){
				$.post("functions/reopen_room.php", {roomToken : roomToken}).done(function(data){
					window.location.replace("<?php echo $_GET["lang"];?>/room/"+roomToken);
				})
			}
			function deleteRoom(roomToken){
				var panel = $("#panel-room-"+roomToken);
				console.log(panel);
				$.post("functions/delete_room.php", {roomToken : roomToken}).done(function(data){
					panel.hide("500", function(){ panel.remove(); });
				})
			}
		</script>
	</body>
</html>
