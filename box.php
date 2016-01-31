<?php
session_start();
include "functions/db_connect.php";
$db = PDOFactory::getConnection();
$roomToken = $_GET["id"];
$checkRoomExistence = $db->query("SELECT *
							FROM rooms r
							JOIN user u ON r.room_creator = u.user_token
							WHERE room_token = '$roomToken'");

if($checkRoomExistence->rowCount() != "0"){ // Check for box existence.
	$roomDetails = $checkRoomExistence->fetch(PDO::FETCH_ASSOC);
} else {
	header('Location: ../404');
}

$creatorStats = $db->query("SELECT *
							FROM user_stats us
							WHERE user_token = '$roomDetails[room_creator]'")->fetch(PDO::FETCH_ASSOC);
$queryTypes = $db->query("SELECT * FROM room_types");

if(isset($_SESSION["token"])){
	$userDetails = $db->query("SELECT * FROM user u
							JOIN user_preferences up ON up.up_user_id = u.user_token
							WHERE user_token='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);
	if($userDetails["up_theme"] == "1"){ // userDetails only for this page. On all the other rooms, it's userSettings
		$theme = "dark";
	} else {
		$theme = "light";
	}
	$ppAdresss = "profile-pictures/".$userDetails["user_pp"];
	$userLang = $userDetails["user_lang"];
	if($userLang == ""){
		$userLang = "en";
	}
	if($_SESSION["token"] != $roomDetails["user_token"]){
		$userFollow = $db->query("SELECT * FROM user_follow uf
								WHERE user_following = '$_SESSION[token]'
								AND user_followed = '$roomDetails[user_token]'")->rowCount();
	}
	$colorList = $db->query("SELECT * FROM name_colors WHERE color_status <= '$userDetails[user_power]'");
	include_once "languages/lang.".$userLang.".php";
} else {
	include "functions/tools.php";
	include_once "languages/lang.en.php";
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $roomDetails["room_name"];?> | <?php echo $roomDetails["user_pseudo"];?> | Berrybox</title>
		<meta content="Berrybox" name="description">
		<meta content="Berrybox" property="og:site_name">
		<meta content="Berrybox" property="og:title">
		<meta content="Berrybox is an app to share, watch and react to YouTube videos together." property="og:description">
		<meta content="website" property="og:type">
		<base href="../">
		<?php include "styles.php";
		if(isset($_SESSION["token"])){ ?>
		<link rel="stylesheet" href="assets/css/<?php echo $theme;?>-theme.css">
		<?php } else { ?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
		<?php } ?>
		<link rel="stylesheet" href="assets/css/ekko-lightbox.min.css">
	</head>
	<body>
		<div class="col-lg-8 col-md-8" id="room-player">
			<div class="room-info">
				<div class="room-picture">
					<img src="profile-pictures/<?php echo $roomDetails["user_pp"];?>" class="profile-picture" title="<?php echo $roomDetails["user_pseudo"]." (".$lang["room_admin"].")";?>" alt="">
				</div>
				<p id="room-title"><?php echo $roomDetails["room_name"];?></p>
				<p> <a href="user/<?php echo $roomDetails["user_pseudo"];?>" target="_blank"><?php echo $roomDetails["user_pseudo"];?></a> | <span class="glyphicon glyphicon-play" title="<?php echo $lang["now_playing"];?>"></span> <span class="currently-name"></span></p>
				<div class="room-admin">
					<?php
					if(isset($_SESSION["token"])){
						if($_SESSION["token"] != $roomDetails["room_creator"]){?>
					<div class="room-buttons col-lg-2">
						<?php if($userFollow == 1){ ?>
						<button class="btn btn-primary btn-active btn-unfollow" id="box-title-unfollow" value="<?php echo $roomDetails["user_pseudo"];?>"><span class="glyphicon glyphicon-heart"></span> <?php echo $lang['following'];?></button>
						<?php } else { ?>
						<button class="btn btn-primary btn-follow" id="box-title-follow" value="<?php echo $roomDetails["user_pseudo"];?>"><span class="glyphicon glyphicon-heart"></span> <?php echo $lang['follow'];?></button>
						<?php } ?>
					</div>
					<?php } else { ?>
					<div class="room-buttons col-lg-2">
						<button class="btn btn-default btn-admin" onClick="getNext(true)"><span class="glyphicon glyphicon-step-forward"></span> <?php echo $lang["skip"];?></button>
					</div>
					<?php } ?>
					<div class="room-quick-messages col-lg-8"></div>
					<?php } else { ?>
					<div class="room-quick-messages col-lg-10"></div>
					<?php } ?>
					<div class="creator-stats col-lg-2">
						<span class="glyphicon glyphicon-eye-open" title="<?php echo $lang["total_views"];?>"></span> <?php echo $creatorStats["stat_visitors"];?>
						<span class="glyphicon glyphicon-heart"></span> <?php echo $creatorStats["stat_followers"];?>
					</div>
				</div>
			</div>
			<div id="currently-playing">
				<div class="modal-body" id="player"></div>
			</div>
			<div class="row under-video">
				<div class="add-link col-lg-12">
					<?php if(isset($_SESSION["token"])){ ?>
					<div class="input-group">
						<input type="text" placeholder="<?php echo $lang["youtube_message"];?>" class="form-control url-box">
						<span class="input-group-btn">
							<button class="btn btn-primary play-url" data-toggle="modal"><?php echo $lang["submit_link"];?></button>
						</span>
					</div>
					<?php } else { ?>
					<div id="no-credentials">
						<p><?php echo $lang["no_credentials"];?></p>
						<form method="post" action="portal">
							<input type="hidden" name="box-token" value="<?php echo $roomToken;?>">
							<input type="submit" class="btn btn-primary" value="<?php echo $lang["log_in"];?>">
						</form>
						<form method="post" action="signup">
							<input type="hidden" name="box-token" value="<?php echo $roomToken;?>">
							<input type="submit" class="btn btn-primary" value="<?php echo $lang["sign_up"];?>">
						</form>
					</div>
					<?php } ?>
				</div>
				<div class="col-lg-6 mood-selectors">
					<p class="mood-question"><?php echo $lang["mood-question"];?></p>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-like button-glyph" onClick="voteMood('like')">
							<span class="glyphicon glyphicon-thumbs-up"></span>
						</p>
					</div>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-cry button-glyph" onClick="voteMood('cry')">
							<span class="glyphicon glyphicon-tint"></span>
						</p>
					</div>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-love button-glyph" onClick="voteMood('love')">
							<span class="glyphicon glyphicon-heart"></span>
						</p>
					</div>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-intense button-glyph" onClick="voteMood('intense')">
							<span class="glyphicon glyphicon-eye-open"></span>
						</p>
					</div>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-sleep button-glyph" onClick="voteMood('sleep')">
							<span class="glyphicon glyphicon-bed"></span>
						</p>
					</div>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-energy button-glyph" onClick="voteMood('energy')">
							<span class="glyphicon glyphicon-flash"></span>
						</p>
					</div>
				</div>
			</div>
			<p class='alert alert-danger closed-box-text'><?php echo $lang["room_closing"];?></p>
		</div>
		<div class="col-lg-4 col-md-4" id="room-chat">
			<div class="panel panel-default panel-room">
				<div class="panel-heading">
					<div class="chat-options row">
						<div class="col-lg-12 room-brand"><a href="home">Berrybox</a></div>
						<?php if(isset($_SESSION["username"])) { ?>
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-menu-list button-glyph">
							<span class="glyphicon glyphicon-dashboard" title="<?php echo $lang["menu"];?>"></span>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-song-list button-glyph">
							<span class="glyphicon glyphicon-list" title="<?php echo $lang["playlist"];?>"></span>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-user-list button-glyph">
							<span class="glyphicon glyphicon-user" title="<?php echo $lang["watch_count"];?>"></span><span id="watch-count"></span>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-options-list button-glyph">
							<span class="glyphicon glyphicon-cog" title="<?php echo $lang["chat_settings"];?>"></span>
						</div>
						<?php } else { ?>
						<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 toggle-song-list button-glyph">
							<span class="glyphicon glyphicon-list" title="<?php echo $lang["playlist"];?>"></span>
						</div>
						<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 toggle-user-list button-glyph">
							<span class="glyphicon glyphicon-user" title="<?php echo $lang["watch_count"];?>"></span><span id="watch-count"></span>
						</div>
						<?php } ?>
					</div>
				</div>
				<div class="panel-body" id="body-chat"></div>
				<div class="panel-footer">
					<?php if(isset($_SESSION["token"])){ ?>
					<input type="text" class="form-control chatbox" placeholder="<?php echo $lang["chat_placeholder"];?>">
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-md-3 full-panel" id="song-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-list"></span> <?php echo $lang["playlist"];?></div>
				<div class="panel-body panel-section">
					<input type="text" class="form-control" id="playlist-filter" placeholder="Filter the playlist">
				</div>
				<div class="panel-body full-panel-body" id="body-song-list">
				</div>
			</div>
		</div>
		<div class="col-lg-2 col-md-2 full-panel" id="user-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-user"></span><span id="watch-count"></span> <?php echo $lang["watch_count"];?></div>
				<div class="panel-body full-panel-body" id="body-user-list"></div>
			</div>
		</div>
		<div class="col-lg-3 col-md-3 full-panel" id="options-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-cog"></span> <?php echo $lang["chat_settings"];?></div>
				<?php if(isset($_SESSION["username"])){ ?>
				<div class="panel-body" id="body-options-list">
					<div class="room-option">
						<div class="option-title"><?php echo $lang["color_pick"];?></div>
						<span class="tip"><?php echo $lang["color_tip"];?></span><br>
						<div id="colors">
							<?php while($color = $colorList->fetch(PDO::FETCH_ASSOC)){
	$colorValue = $color["color_value"];
	if(strcasecmp($colorValue,$userDetails["up_color"]) == 0){?>
							<div class="color-cube cube-selected" id="color-<?php echo $colorValue;?>" style="background-color:#<?php echo $colorValue;?>"></div>
							<?php } else { ?>
							<div class="color-cube" id="color-<?php echo $colorValue;?>" style="background-color:#<?php echo $colorValue;?>"></div>
							<?php } }?>
						</div>
					</div>
					<div class="room-option">
						<div class="option-title"><?php echo $lang["user_theme"];?>
							<span style="float:right;">
								<input type="checkbox" class="user-option-toggle" name="toggle-theme" <?php echo($userDetails["up_theme"]=='0')?'checked':'unchecked';?>>
							</span>
						</div>
						<span class="tip"><?php echo $lang["theme_tip"];?></span>
					</div>
					<?php if($_SESSION["token"] == $roomDetails["room_creator"]){ ?>
					<div class="room-option">
						<div class="option-title"><?php echo $lang["play_type"];?>
							<span style="float: right;">
								<input type="checkbox" class="admin-option-toggle" name="toggle-autoplay" <?php echo($roomDetails["room_play_type"]=='1')?'unchecked':'checked';?>>
							</span>
						</div>
						<span class="tip"><?php echo $lang["play_type_tip"];?></span>
					</div>
					<div class="room-option">
						<div class="option-title"><?php echo $lang["submit_type"];?>
							<span style="float: right;">
								<input type="checkbox" class="admin-option-toggle" name="toggle-submit" <?php echo($roomDetails["room_submission_rights"]=='1')?'checked':'unchecked';?>>
							</span>
						</div>
						<span class="tip"><?php echo $lang["submit_type_tip"];?></span>
					</div>
					<div class="room-option">
						<span class="option-title"><?php echo $lang["room_params"];?></span><br>
						<span class="tip"><?php echo $lang["room_params_tip"];?></span>
						<form class="form-horizontal">
							<div class="form-group">
								<label for="speakLang" class="col-lg-4 control-label"><?php echo $lang["speak_lang"];?></label>
								<div class="col-lg-8">
									<select name="speakLang" id="" class="form-control">
										<option value="en" <?php if($roomDetails["room_lang"]=="en") echo "selected='selected'";?>>English</option>
										<option value="fr" <?php if($roomDetails["room_lang"]=="fr") echo "selected='selected'";?>>Français</option>
										<option value="jp" <?php if($roomDetails["room_lang"]=="jp") echo "selected='selected'";?>>日本語</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="roomType" class="col-sm-4 control-label"><?php echo $lang["room_type"];?></label>
								<div class="col-lg-8">
									<select name="roomType" id="" class="form-control">
										<?php while($type = $queryTypes->fetch(PDO::FETCH_ASSOC)) {
	if($type["id"] == $roomDetails["room_type"]){?>
										<option value="<?php echo $type["id"];?>" selected="selected"><?php echo $lang[$type["type"]];?></option>
										<?php } else { ?>
										<option value="<?php echo $type["id"];?>"><?php echo $lang[$type["type"]];?></option>
										<?php } } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="description" class="col-sm-4 control-label"><?php echo $lang["description_limit"];?></label>
								<div class="col-lg-8">
									<textarea name="description" id="description" cols="30" rows="5" class="form-control"><?php echo $roomDetails["room_description"];?></textarea>
								</div>
							</div>
						</form>
						<button class="btn btn-primary btn-block" id="save-room-button" onClick="saveRoomChanges('<?php echo $roomToken;?>')"><?php echo $lang["save_changes"];?></button>
					</div>
					<?php if($roomDetails["room_active"] == 1){ ?>
					<div class="room-option" id="close-option" >
						<span class="option-title"><?php echo $lang["close_room"];?></span><br>
						<span class="tip"><?php echo $lang["close_room_tip"];?></span>
						<button class="btn btn-danger btn-admin btn-block" onClick="closeRoom('<?php echo $roomToken;?>')"><span class="glyphicon glyphicon-remove-circle"></span> <?php echo $lang["close_room"];?></button>
					</div>
					<div class="room-option" id="open-option" style="display:none">
						<span class="option-title"><?php echo $lang["open_room"];?></span><br>
						<span class="tip"><?php echo $lang["open_room_tip"];?></span>
						<button class="btn btn-success btn-admin btn-block" onClick="openRoom('<?php echo $roomToken;?>')"><span class="
							glyphicon glyphicon-play-circle"></span> <?php echo $lang["open_room"];?></button>
					</div>
					<?php } else { ?>
					<div class="room-option" id="close-option" style="display:none">
						<span class="option-title"><?php echo $lang["close_room"];?></span><br>
						<span class="tip"><?php echo $lang["close_room_tip"];?></span>
						<button class="btn btn-danger btn-admin btn-block" onClick="closeRoom('<?php echo $roomToken;?>')"><span class="glyphicon glyphicon-remove-circle"></span> <?php echo $lang["close_room"];?></button>
					</div>
					<div class="room-option" id="open-option">
						<span class="option-title"><?php echo $lang["open_room"];?></span><br>
						<span class="tip"><?php echo $lang["open_room_tip"];?></span>
						<button class="btn btn-success btn-admin btn-block" onClick="openRoom('<?php echo $roomToken;?>')"><span class="
							glyphicon glyphicon-play-circle"></span> <?php echo $lang["open_room"];?></button>
					</div>
					<?php } ?>
					<?php } else { ?>
					<div class="room-option">
						<span class="option-title"><?php echo $lang["sync"];?></span><br>
						<span class="tip"><?php echo $lang["sync_tip"];?></span>
						<button class="btn btn-default btn-admin btn-block sync-on" id="btn-synchro"><span class="glyphicon glyphicon-refresh"></span> <?php echo $lang["sync-on"];?></button>
					</div>
					<?php } ?>
				</div>
				<?php } ?>
			</div>
		</div>
		<div class="col-lg-2 col-md-3 full-panel" id="menu-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-dashboard" title=""></span> <?php echo $lang["menu"];?></div>
				<div class="panel-body" style="height: 85vh;">
					<?php if(isset($_SESSION["username"])){ ?>
					<div class="connected-user">
						<div class="menu-pp">
							<img src="<?php echo $ppAdresss;?>" alt="" style="width:inherit">
						</div>
						<p id="user-name"><?php echo $userDetails["user_pseudo"];?></p>
					</div>
					<form action="search" method="post" target="_blank" role="search">
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
							<input type="text" class="form-control search-input" name="search-terms" placeholder="<?php echo $lang["search"];?>...">
						</div>
					</form>
					<div class="menu-options row">
						<ul class="nav nav-pills nav-stacked">
							<li><a href="profile/settings"><span class="glyphicon glyphicon-wrench col-lg-2"></span> <?php echo $lang["my_settings"];?></a></li>
							<li><a href="user/<?php echo $userDetails['user_pseudo'];?>"><span class="glyphicon glyphicon-user col-lg-2"></span> <?php echo $lang["my_profile"];?></a></li>
							<li><a href="follow"><span class="glyphicon glyphicon-heart col-lg-2"></span> <?php echo $lang["following"];?></a></li>
						</ul>
					</div>
					<?php } ?>
				</div>
				<div class="panel-footer no-border">
					<div class="menu-logo">
						<img src="assets/berrybox-logo-grey.png" alt="">
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<!--<script src="assets/js/ekko-lightbox.min.js"></script>-->
		<script>
			/* YOUTUBE PLAYER */
			var tag = document.createElement('script');
			tag.src = "https://www.youtube.com/iframe_api";
			var firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

			var player;
			function onYouTubeIframeAPIReady() {
				player = new YT.Player('player', {
					height: '75%',
					width: '60%',
					videoId: '',
					events: {
						'onReady': onPlayerReady,
						'onStateChange': onPlayerStateChange
					}
				});
			}

			var done = false;
			$(document).ready(function(){
				var roomToken = "<?php echo $roomToken;?>";
				window.roomState = "<?php echo $roomDetails["room_active"];?>";
				/** THINGS TO DO IF THE USER IS LOOGED **/
				<?php if(isset($_SESSION["token"])){ ?>
				window.userToken = "<?php echo $_SESSION["token"];?>";
				// Join the room
				function joinRoom(roomToken, userToken){
					return $.post("functions/join_room.php", {roomToken : roomToken, userToken : userToken});
				}
				joinRoom(roomToken, userToken).done(function(result){
					// Get power of the user
					window.userPower = result;
					// Load the chat
					if(window.userPower == 2){
						$("#body-chat").append("<p class='system-message'><?php echo $lang["welcome_admin"];?></p>");
					} else {
						$("#body-chat").append("<p class='system-message'><?php echo $lang["welcome"];?></p>");
					}
					function fetchEmotes(){
						return $.post("functions/fetch_emotes.php");
					}
					fetchEmotes().done(function(data){
						var emoteList = JSON.parse(data);
						var emotes = [];
						for(var i = 0; i < emoteList.length; i++){
							emotes.push(emoteList[i].emoteText);
						}
						loadChat(roomToken, result, emotes);
					})
					// Load the history of all submitted songs in this room (once, it will be refreshed if the user toggles the panel)
					loadSongHistory(roomToken, result);
					// Load all the active users in the room (once, it will be refreshed if the user toggles the panel)
					loadUsers(roomToken);
					// State of the room
					watchRoom(roomToken);
					// Set global chatHover & sync variables
					window.chatHovered = false;
					window.sync = true;
					// Check if creator
					if(userToken != "<?php echo $roomDetails["room_creator"];?>"){
						// If user is not the creator, check presence of the creator
						$.post("functions/check_creator.php", {roomToken : roomToken}).done(function(presence){
							if(presence == '0'){
								$("#body-chat").append("<p class='system-message system-alert'><?php echo $lang["no_admin"];?></p>");
							}
						})
					} else {
						// If user is the creator, then start autoplay
						if("<?php echo $roomDetails["room_play_type"];?>" == 1){
							window.autoplay = true;
						} else {
							window.autoplay = false;
						}
					}
					// Watch the state of the user and of the room (refresh every 10s)
					setTimeout(userState, 10000, roomToken, userToken);
					// Keep PHP session alive (every 30 mins)
					setInterval(function(){$.post("functions/refresh_session.php");},1800000);
				})
				$(window).on('beforeunload', function(event){
					sessionStorage.removeItem("currently-playing");
					$.post("functions/leave_room.php", {roomToken : "<?php echo $roomToken;?>", userToken : userToken});
				})
				$(":regex(name,toggle-autoplay)").bootstrapSwitch({
					size: 'small',
					onText: '<i class="glyphicon glyphicon-hourglass"></i> <?php echo $lang["manual_play"];?>',
					offText: '<i class="glyphicon glyphicon-play-circle"></i> <?php echo $lang["auto_play"];?>',
					onColor: 'info',
					offColor: 'default',
					onSwitchChange: function(){
						var state = (window.autoplay)?'1':'0';
						$.post("functions/toggle_autoplay.php", {roomToken : "<?php echo $roomToken;?>", state : state}).done(function(data){
							if(data == 0){
								sendMessage("<?php echo $roomToken;?>", 4, 1, "{auto-off}");
								window.autoplay = false;
							} else{
								sendMessage("<?php echo $roomToken;?>", 4, 1, "{auto-on}");
								window.autoplay = true;
							}
						})
					}
				});
				$(":regex(name,toggle-submit)").bootstrapSwitch({
					size: 'small',
					onText: '<i class="glyphicon glyphicon-ok-sign"></i> <?php echo $lang["submit_all"];?>',
					offText: '<i class="glyphicon glyphicon-ok-circle"></i> <?php echo $lang["submit_mod"];?>',
					onColor: 'success',
					offColor: 'warning',
					onSwitchChange: function(){
						var state = "<?php echo $roomDetails["room_submission_rights"];?>";
						/*console.log(state);*/
						$.post("functions/toggle_submission_rights.php", {roomToken : "<?php echo $roomToken;?>", state : state}).done(function(data){
							if(data == 0){
								window.submission = false;
								sendMessage("<?php echo $roomToken;?>", 4, 1, "{submission_mod}");
							} else {
								window.submission = true;
								sendMessage("<?php echo $roomToken;?>", 4, 1, "{submission_all}");
							}
						})
					}
				});
				$(":regex(name,toggle-theme)").bootstrapSwitch({
					size: 'small',
					onText: '<?php echo $lang["light"];?>',
					offText: '<?php echo $lang["dark"];?>',
					onColor: 'light',
					offColor: 'dark',
					onSwitchChange: function(){
						var state = "<?php echo $userDetails["up_theme"];?>";
						$.post("functions/toggle_theme.php", {userToken : "<?php echo $_SESSION["token"];?>", state : state}).done(function(data){
							location.reload();
						})
					}
				});
				<?php } else { ?>
				/** THIS TO DO ONLY IF THE USER IS NOT LOGGED **/
				window.userToken = "-1";
				window.userPower = "1";
				$("#body-chat").append("<p class='system-message'><?php echo $lang["welcome"];?></p>");
				function fetchEmotes(){
					return $.post("functions/fetch_emotes.php");
				}
				fetchEmotes().done(function(data){
					var emoteList = JSON.parse(data);
					var emotes = [];
					for(var i = 0; i < emoteList.length; i++){
						emotes.push(emoteList[i].emoteText);
					}
					loadChat(roomToken, 1, emotes);
				})
				loadSongHistory(roomToken, 1);
				// Load all the active users in the room (once, it will be refreshed if the user toggles the panel)
				loadUsers(roomToken);
				// State of the room
				watchRoom(roomToken);
				// Set global chatHover & sync variables
				window.chatHovered = false;
				window.sync = true;
				<?php } ?>
				// Get the number of people in the room (refresh every 30s)
				getWatchCount(roomToken);
				setInterval(getWatchCount, 30000, roomToken);
				// Dynamic filtering of the playlist
				$('#playlist-filter').on('keyup', function(){
					var $rows = $('.playlist-entry');
					var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
					$rows.show().filter(function(){
						var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
						return !~text.indexOf(val);
					}).hide();
				});
			});

			/** THINGS TO DO ON DOCUMENT ONLY IF THE USER IS LOOGED **/
			<?php if(isset($_SESSION["token"])){ ?>
			$(document).on('click', '.btn-unfollow', function(){
				var followedToken = $(this).attr("value");
				var id = $(this).attr("id");
				$.post("functions/unfollow_user.php", {userFollowing : '<?php echo $_SESSION["token"];?>', userFollowed : followedToken}).done(function(data){
					$("#"+id).removeClass("btn-active");
					var text = "<span class='glyphicon glyphicon-heart'></span> <?php echo $lang['follow'];?>";
					$("#"+id).html(text);
					$("#"+id).removeClass("btn-danger");
					$("#"+id).removeClass("btn-unfollow");
					$("#"+id).addClass("btn-follow");
					$("#"+id).attr("id", id.substr(0, id.length - 6)+"follow");
					if(followedToken == '<?php echo $roomDetails["user_pseudo"];?>'){
						$("#box-title-unfollow").removeClass("btn-active");
						$("#box-title-unfollow").html(text);
						$("#box-title-unfollow").removeClass("btn-danger");
						$("#box-title-unfollow").removeClass("btn-unfollow");
						$("#box-title-unfollow").addClass("btn-follow");
						$("#box-title-unfollow").attr("id", "box-title-follow");
					}
				})
			}).on('click', '.btn-follow', function(){
				var followedToken = $(this).attr("value");
				var id = $(this).attr("id");
				$.post("functions/follow_user.php", {userFollowing : '<?php echo $_SESSION["token"];?>', userFollowed : followedToken}).done(function(data){
					$("#"+id).addClass("btn-active");
					var text = "<span class='glyphicon glyphicon-heart'></span> <?php echo $lang['following'];?>";
					$("#"+id).html(text);
					$("#"+id).removeClass("btn-follow");
					$("#"+id).addClass("btn-unfollow");
					$("#"+id).attr("id", id.substr(0, id.length - 8)+"unfollow");
					if(followedToken == '<?php echo $roomDetails["user_pseudo"];?>'){
						$("#box-title-follow").addClass("btn-active");
						$("#box-title-follow").html(text);
						$("#box-title-follow").removeClass("btn-follow");
						$("#box-title-follow").addClass("btn-unfollow");
						$("#box-title-follow").attr("id", "box-title-unfollow");
					}
				})
			}).on('click','.play-url', function(){
				submitLink();
			}).on('focus', '.url-box', function(){
				$(this).keypress(function(event){
					if(event.which == 13){
						submitLink();
					}
				})
			}).on('click', '.send-info', function(){
				fillInfo();
			}).on('click', '.cancel-info', function(){
				var id = $(this).attr("id").substr(12);
				$("#warning-"+id).remove();
			}).on('focus', '.info-box', function(){
				$(this).keypress(function(event){
					if(event.which == 13){
						fillInfo();
					}
				})
			}).on('focus', '.chatbox', function(){
				$.post("functions/get_user_list.php", {roomToken : "<?php echo $roomToken;?>"}).done(function(data){
					var userList = JSON.parse(data);
					var autocompleteList = [];
					for(var i = 0; i < userList.length; i++){
						autocompleteList.push(userList[i].pseudo);
					}
					$(".chatbox").textcomplete([{
						match: /(^|\b)(\w{2,})$/,
						search: function(term, callback){
							callback($.map(autocompleteList, function(item){
								return item.indexOf(term) === 0 ? item : null;
							}));
						},
						replace: function(item){
							return item;
						}
					}]);
				});
				$(this).keypress(function(event){
					if(event.which == 13){
						sendMessage("<?php echo $roomToken;?>", 1, null, 'chatbox', '');
					}
				})
			}).on('click', '.btn-chat', function(){
				sendMessage("<?php echo $roomToken;?>", 1, null, 'chatbox', '');
			}).on('click', '.color-cube', function(){
				var cube = $(this);
				var color = $(this).attr('id').substr(6,6);
				var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
				$.post("functions/change_color.php", {userToken : userToken, color : color}).done(function(){
					$(".close").click();
					$(".color-cube").removeClass("cube-selected");
					cube.addClass("cube-selected");
				})
			}).on('click', '#btn-synchro', function(){
				var $b = $(this);
				if($b.hasClass("sync-on")){
					$b.removeClass("sync-on");
					$b.empty();
					$b.addClass("sync-off");
					$b.html("<span class='glyphicon glyphicon-repeat'></span> " +"<?php echo $lang["sync-off"];?>");
					window.sync = false;
					$b.blur();
				} else {
					$b.addClass("sync-on");
					$b.empty();
					$b.removeClass("sync-off");
					$b.html("<span class='glyphicon glyphicon-refresh'></span> " +"<?php echo $lang["sync-on"];?>");
					window.sync = true;
					synchronize("<?php echo $roomToken;?>", userPower);
					$b.blur();
				}
			}).on('mouseenter', '.emotion-like', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["like"];?>");
					$(".mood-question").addClass("emotion-like");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseenter', '.emotion-cry', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["cry"];?>");
					$(".mood-question").addClass("emotion-cry");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseenter', '.emotion-love', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["love"];?>");
					$(".mood-question").addClass("emotion-love");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseenter', '.emotion-intense', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["intense"];?>");
					$(".mood-question").addClass("emotion-intense");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseenter', '.emotion-sleep', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["sleep"];?>");
					$(".mood-question").addClass("emotion-sleep");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseenter', '.emotion-energy', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["energy"];?>");
					$(".mood-question").addClass("emotion-energy");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseleave', '.emotion-like', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["mood-question"];?>");
					$(".mood-question").removeClass("emotion-like");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseleave', '.emotion-cry', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["mood-question"];?>");
					$(".mood-question").removeClass("emotion-cry");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseleave', '.emotion-love', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["mood-question"];?>");
					$(".mood-question").removeClass("emotion-love");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseleave', '.emotion-intense', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["mood-question"];?>");
					$(".mood-question").removeClass("emotion-intense");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseleave', '.emotion-sleep', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["mood-question"];?>");
					$(".mood-question").removeClass("emotion-sleep");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseleave', '.emotion-energy', function(){
				$(".mood-question").fadeOut('500', function(){
					$(".mood-question").empty();
					$(".mood-question").html("<?php echo $lang["mood-question"];?>");
					$(".mood-question").removeClass("emotion-energy");
					$(".mood-question").fadeIn('500');
				});
			}).on('mouseenter', '#room-title', function(){
				if(userPower == 2){
					$(this).append(" <span class='glyphicon glyphicon-edit button-glyph' id='edit-title-button'></span>");
				}
			}).on('mouseleave', '#room-title', function(){
				$("#edit-title-button").remove();
			}).on('click', '#edit-title-button', function(){
				var title = $("#room-title").text();
				$("#room-title").replaceWith("<input type='text' class='form-control' style='width:61vw;margin-bottom:5px' id='edit-title-input' value='"+title+"'>");
			}).on('blur', '#edit-title-input', function(){
				var newTitle = $(this).val();
				$.post("functions/retitle_room.php", {title : newTitle, roomToken : "<?php echo $roomToken;?>"});
				$(this).replaceWith("<p id='room-title'>"+newTitle+"</p>");
			}).on('click', '.whisper-action', function(){
				var user = $("#user-card-name").text();
				$(".chatbox").val("/w "+user+" ");
				$(".chatbox").focus();
			}).on('mouseenter', '.btn-unfollow', function(){
				var id = $(this).attr("id");
				var text = "<span class='glyphicon glyphicon-minus'></span> <?php echo $lang['unfollow'];?>";
				$("#"+id).html(text);
				$("#"+id).removeClass("btn-active");
				$("#"+id).addClass("btn-danger");
			}).on('mouseleave', '.btn-unfollow', function(){
				var id = $(this).attr("id");
				var text = "<span class='glyphicon glyphicon-heart'></span> <?php echo $lang['following'];?>";
				$("#"+id).html(text);
				$("#"+id).removeClass("btn-danger");
				$("#"+id).addClass("btn-active");
			})
				<?php } ?>

			/** THING TO DO ON DOCUMENT FOR EVERYONE **/
			$(document).on('click', '.toggle-song-list, .toggle-menu-list, .toggle-user-list, .toggle-options-list', function(){
				var classToken = $(this).attr("class").split(' ')[4].substr(7);
				var position;
				if($("#"+classToken).css("display") == "none"){
					$("#"+classToken).toggle();
					position = "32.5%";
					switch(classToken){
						case "song-list":
							loadSongHistory("<?php echo $roomToken;?>", window.userPower);
							setTimeout((function(){
								$("#user-list").hide();
								$("#menu-list").hide();
								$("#options-list").hide();
							}), 200);
							$("#user-list").animate({
								right : "0px"
							}, 200);
							$("#menu-list").animate({
								right : "0px"
							}, 200);
							$("#options-list").animate({
								right : "0px"
							}, 200);
							break;

						case "user-list":
							loadUsers("<?php echo $roomToken;?>");
							setTimeout((function(){
								$("#song-list").hide();
								$("#menu-list").hide();
								$("#options-list").hide();
							}), 200);
							$("#song-list").animate({
								right : "0px"
							}, 200);
							$("#menu-list").animate({
								right : "0px"
							}, 200);
							$("#options-list").animate({
								right : "0px"
							}, 200);
							break;

						case "menu-list":
							setTimeout((function(){
								$("#song-list").hide();
								$("#user-list").hide();
								$("#options-list").hide();
							}), 200);
							$("#song-list").animate({
								right : "0px"
							}, 200);
							$("#user-list").animate({
								right : "0px"
							}, 200);
							$("#options-list").animate({
								right : "0px"
							}, 200);
							break;

						case "options-list":
							setTimeout((function(){
								$("#song-list").hide();
								$("#user-list").hide();
								$("#menu-list").hide();
							}), 200);
							$("#song-list").animate({
								right : "0px"
							}, 200);
							$("#user-list").animate({
								right : "0px"
							}, 200);
							$("#menu-list").animate({
								right : "0px"
							}, 200);
							break;
					}
				} else {
					$(this).t = setTimeout((function(){
						$("#"+classToken).hide();
					}), 200);
					position = "0px";
				}
				$("#"+classToken).animate({
					right : position
				}, 200);
			}).on('mouseenter', '#body-chat', function(){
				window.chatHovered = true;
			}).on('mouseleave', '#body-chat', function(){
				window.chatHovered = false;
			}).on('click', '.author-linkback', function(){
				var user = $(this).text();
				var currentLine = $(this).parents("p");
				$(".user-card").remove();
				$.post("functions/fetch_user_card.php", {user : user}).done(function(data){
					var details = JSON.parse(data);
					var userCard = "<div class='user-card'>";
					userCard += "<div class='user-card-info'>";
					userCard += "<div class='medium-pp'>";
					userCard += "<img src='"+details.user_pp+"' alt='' style='width:inherit'>";
					userCard += "</div>";
					userCard += "<p id='user-card-name'><a href='user/"+details.user_pseudo+"' target='_blank'>"+details.user_pseudo+"</a><span class='glyphicon glyphicon-remove button-glyph' id='user-card-close'></span></p>";
					userCard += "<div class='row user-card-stats'>";
					userCard += "<div class='col-lg-2'><span class='glyphicon glyphicon-heart' title='<?php echo $lang["total_followers"];?>'></span> "+details.followers+"</div>";
					userCard += "<div class='col-lg-2'><span class='glyphicon glyphicon-eye-open' title='<?php echo $lang["total_views"];?>'></span> "+details.visitors+"</div>";
					userCard += "<div class='col-lg-2'><span class='glyphicon glyphicon-plus' title='<?php echo $lang["rooms_created"];?>'></span> "+details.rooms+"</div>";
					userCard += "<div class='col-lg-2'><span class='glyphicon glyphicon-music' title='<?php echo $lang["songs_submitted"];?>'></span> "+details.songs+"</div>";
					userCard += "</div>"; // user-card-stats
					userCard += "</div>"; // user-card-info
					<?php if(isset($_SESSION["username"])){ ?>
					if(details.user_pseudo != '<?php echo $userDetails["user_pseudo"];?>'){
						userCard += "<div class='user-card-actions'>";
						userCard += "<button class='btn btn-primary whisper-action'><?php echo $lang["whisper"];?></button>"; // whisper action
						if(details.following == 1){
							userCard += "<button class='btn btn-primary btn-active btn-unfollow' id='user-card-unfollow' value='"+details.user_pseudo+"'><span class='glyphicon glyphicon-heart'></span> <?php echo $lang["following"];?></button>";
						} else {
							userCard += "<button class='btn btn-primary btn-follow' id='user-card-follow' value='"+details.user_pseudo+"'><span class='glyphicon glyphicon-heart'></span> <?php echo $lang["follow"];?></button>";
						} // follow action
						userCard += "</div>"; // user-card-actions
					}
					<?php } ?>
					userCard += "</div>"; //user-card
					currentLine.after(userCard);
					$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
				})
			}).on('click', '#user-card-close', function(){
				$(".user-card").remove();
			})
			/** FUNCTIONS TO LOAD ONLY IF USER IS LOGGED **/
				<?php if(isset($_SESSION["username"])){ ?>
			function requeueSong(id){
				$.post("functions/requeue_song.php", {roomToken : "<?php echo $roomToken;?>", id : id, userToken : "<?php echo $_SESSION["token"];?>"}).done(function(data){
					if(data == "1"){
						$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> <?php echo $lang["song_submit_success"];?></p>");
					} else {
						$("#body-chat").append("<p class='system-message system-warning'></span class='glyphicon glyphicon-question-sign'></span> <?php echo $lang["no_fetch"];?></p>");
					}
					$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
				})
			}
			function timeoutUser(userToken){
				var roomToken = "<?php echo $roomToken;?>";
				$.post("functions/time_out.php", {roomToken : roomToken, userToken : userToken}).done(function(data){
					var adminMessage = "<?php echo $lang["timeout_message_admin_first_part"];?>"+data+"<?php echo $lang["timeout_message_admin_second_part"];?>";
					sendMessage("<?php echo $roomToken;?>", 3, null, adminMessage);
					sendMessage("<?php echo $roomToken;?>", 5, null, "<?php echo $lang["timeout_message_user"];?>", userToken);
				})
			}
			function banUser(userToken){

			}
			function promoteUser(userToken){
				$.post("functions/promote_user.php", {roomToken : "<?php echo $roomToken;?>", adminToken : "<?php echo $_SESSION["token"];?>", userToken : userToken}).done(function(data){
					var message = "{user_promoted}"+data;
					// System message to everyone to alert the new mod
					sendMessage("<?php echo $roomToken;?>", 4, 1, message);
					// System message to the new mod only
					sendMessage("<?php echo $roomToken;?>", 5, null, "{you_promoted}", userToken);
				})
			}
			function demoteUser(userToken){
				$.post("functions/demote_user.php", {roomToken : "<?php echo $roomToken;?>", adminToken : "<?php echo $_SESSION["token"];?>", userToken : userToken}).done(function(data){
					var message = "{user_demoted}"+data;
					// System message to everyone to alert of the demote
					sendMessage("<?php echo $roomToken;?>", 4, 1, message);
					// System message to the affected user only
					sendMessage("<?php echo $roomToken;?>", 5, null, "{you_demoted}", userToken);
				});
			}
			function closeRoom(roomToken){
				sendMessage(roomToken, 4, 4, "{close_room}");
				$.post("functions/close_room.php", {roomToken : roomToken, userToken : userToken});
			}
			function openRoom(roomToken){
				sendMessage(roomToken, 4, 7, "{reopen_room}");
				$.post("functions/reopen_room.php", {roomToken : roomToken, userToken : userToken});
			}
			function ignoreSong(id){
				$.post("functions/ignore_song.php", {roomToken : "<?php echo $roomToken;?>", id : id}).done(function(data){
					var message = "{song_ignored}"+data;
					sendMessage("<?php echo $roomToken;?>", 4, 5, message);
				})
			}
			function reinstateSong(id){
				$.post("functions/reinstate_song.php", {roomToken : "<?php echo $roomToken;?>", id : id}).done(function(data){
					var message = "{song_reinstated}"+data;
					sendMessage("<?php echo $roomToken;?>", 4, 6, message);
				})
			}
			function showMoodSelectors(){
				$(".add-link").animate({
					'width': '50%'
				}, 300);
				$(".mood-selectors").show('900');
			}
			function voteMood(mood){
				$.post("functions/select_mood.php", {mood : mood, id : sessionStorage.getItem("currently-playing")}).done(function(){
					hideMoodSelectors();
				})
			}
			function hideMoodSelectors(){
				$(".mood-selectors").hide('900');
				$(".add-link").delay('900').animate({
					'width': '100%'
				}, 400);
			}
			function fillInfo(){
				var name = $(".info-box").val();
				var id = $(".info-box").attr("id").substr(5);
				$.post("functions/fill_info.php", {index : id, name : name}).done(function(data){
					$("#warning-"+id).remove();
					$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> <?php echo $lang["info_fill_success"];?></p>");
				})
			}
			function sendMessage(roomToken, scope, type, message, destination){
				if(message == 'chatbox' && scope == 1){
					var fullString = $(".chatbox").val();
					var actionToken = $(".chatbox").val().substr(0,1);
					if(actionToken == '/'){ // Detection de macros
						var action = $(".chatbox").val().substr(1).split(" ");
						if(action[0] == 'w'){
							scope = 6;
							destination = action[1];
							message = "";
							for(var i = 2; i < action.length; i++){
								message += action[i];
								if(i != action.length-1){
									message += " ";
								}
							}
							$(".chatbox").val('');
							$.post("functions/post_chat.php", {message : message, token : roomToken, scope : scope, destination : destination, solveDestination : destination});
						}
					} else {
						var message = $(".chatbox").val();
						$(".chatbox").val('');
						$.post("functions/post_chat.php", {message : message, token : roomToken, scope : scope, destination : destination});
					}
				} else {
					$.post("functions/post_chat.php", {message : message, token : roomToken, scope : scope, type: type, destination : destination});
				}
			}
			function saveRoomChanges(roomToken){
				var language = $('[name=speakLang]').val();
				var description = $("#description").val();
				var type = $('[name=roomType]').val();
				$.post("functions/edit_room.php", {type : type, language : language, description : description, roomToken : roomToken}).done(function(){
					$("#save-room-button").blur();
					$("#save-room-button").text("<?php echo $lang["save_changes_feedback"];?>");
					$("#save-room-button").switchClass("btn-primary", "btn-success feedback", 200, "easeOutBack");
					setTimeout(function(){
						$("#save-room-button").switchClass("btn-success feedback", "btn-primary", 1000, "easeInQuad")
						$("#save-room-button").text("<?php echo $lang["save_changes"];?>");
					}, 1500);
				});
			}
			<?php } ?>
			/** FUNCTION TO LOAD FOR EVERYONE **/
			function onPlayerReady(event){
				sessionStorage.setItem("currently-playing", "");
				synchronize("<?php echo $roomToken;?>", userPower);
			}
			function onPlayerStateChange(event) {
				/*console.log(window.autoplay);*/
				if(window.sync == true && window.autoplay != false){
					if (event.data == YT.PlayerState.ENDED) {
						getNext(false);
					}
				}
				if(event.data == YT.PlayerState.PLAYING){
					var moodTimer = player.getDuration() * 1000;
					<?php if(isset($_SESSION["username"])){ ?>
					setTimeout(showMoodSelectors, moodTimer * 0.3);
					setTimeout(hideMoodSelectors, moodTimer - 10000);
					<?php } ?>
				}
			}
			function getNext(skip){
				if(skip == true){
					var message = "{skip}";
					sendMessage("<?php echo $roomToken;?>", 4, 3, message);
				}
				if(userPower == 2){
					$.post("functions/get_next.php", {roomToken : "<?php echo $roomToken;?>", userPower : window.userPower, lastPlayed : sessionStorage.getItem("currently-playing")}).done(function(data){
						if(data != ""){
							var songInfo = JSON.parse(data);
							if(songInfo.link != null){
								playSong(songInfo.index, songInfo.link, songInfo.title, 0);
							}
						} else {
							synchronize("<?php echo $roomToken;?>", userPower);
						}
					});
				} else {
					synchronize("<?php echo $roomToken;?>", userPower);
					$(".room-quick-messages").append("<div class='system-message' id='message-synchro'><span class='glyphicon glyphicon-refresh'></span> <?php echo $lang["synchronizing"];?></div>");
				}
			}
			function userState(roomToken, userToken){
				$.post("functions/get_user_state.php", {roomToken : roomToken, userToken : userToken}).done(function(data){
					if(data == 1){
						setTimeout(userState, 10000, roomToken, userToken);
					} else {
						$("#body-chat").append("<p class='system-message system-alert'><?php echo $lang["room_closing"];?></p>");
						setTimeout(function(){
							window.location.replace("home");
						}, 3000);
					}
				})
			}
			function watchRoom(roomToken){
				$.post("functions/get_room_state.php", {roomToken : roomToken}).done(function(data){
					var states = JSON.parse(data);
					window.roomState = states.room_active;

					// Name of the room
					if($("#room-title").text() != states.room_name){
						$("#room-title").text(states.room_name);
					}
					if(document.title != states.room_name){
						document.title = states.room_name+" | <?php echo $roomDetails["user_pseudo"];?> | Berrybox";
					}

					// Submission of videos
					if(states.room_submission_rights == '0'){
						if(window.userPower == 1){
							window.submission = false;
							$(".add-link").hide();
						} else {
							window.submission = true;
						}
					} else {
						window.submission = true;
						$(".add-link").show();
					}

					// State of the autoplay
					var autoplayWatch = states.room_play_type;

					// State active of the room
					if(window.roomState == 0){
						$(".under-video").hide('1000');
						$(".closed-box-text").show('500');
						if(userPower == 2){
							$("#close-option").hide();
							$("#open-option").show();
						}
					} else {
						$(".closed-box-text").hide('500');
						$(".under-video").show('1000');
						if(userPower == 2){
							$("#close-option").show();
							$("#open-option").hide();
						}
					}
					// Watch the state of the room every 10 seconds
					setTimeout(watchRoom, 10000, roomToken);
				})
			}
			function synchronize(roomToken, userPower){
				/* This function synchronizes the current video for everyone */
				$.post("functions/load_current.php", {roomToken : roomToken, userPower : userPower}).done(function(data){
					var songInfo = JSON.parse(data);
					if(songInfo.link != null){
						if(songInfo.index != sessionStorage.getItem("currently-playing")){
							playSong(songInfo.index, songInfo.link, songInfo.title, songInfo.timestart);
						} else {
							window.videoPending = setTimeout(synchronize, 3000, "<?php echo $roomToken;?>", userPower);
						}
					} else {
						window.videoPending = setTimeout(synchronize, 3000, "<?php echo $roomToken;?>", userPower);
					}
				})
			}
			function playSong(index, id, title, timestart){
				if(timestart != 0){
					//console.log("timestamp : "+timestart);
					var sTime = moment.utc(timestart).add(4, 's');
					//console.log("start of video fetched from DB : "+sTime);
					var sLocalTime = moment(sTime).local();
					//console.log("formatted : "+sLocalTime);
					var timeDelta = Math.round(moment().diff(sLocalTime)/1000);
					//console.log("TIME DELTA : "+timeDelta);
					player.loadVideoById(id, timeDelta);
				} else {
					player.loadVideoById(id);
				}
				$("#message-synchro").remove();
				sessionStorage.setItem("currently-playing", index);
				$(".currently-name").empty();
				$(".currently-name").html(title);
				var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
				if(userToken == "<?php echo $roomDetails["room_creator"];?>" && (timestart == 0 || timeDelta <= 3)){
					var message = "{now_playing}"+title;
					sendMessage("<?php echo $roomToken;?>", 4, 2, message);
					$.post("functions/register_song.php", {index : index});
				}
			}
			function loadSongHistory(roomToken, userPower){
				if($("#song-list").css("display") != "none"){
					// Gets the whole history of the room
					$.post("functions/get_history.php", {roomToken : roomToken}).done(function(data){
						var songList = JSON.parse(data);
						var uVideos = 0, pVideos = 0;
						$("#body-song-list").empty();
						var previousSongState = -1;
						for(var i = 0; i < songList.length; i++){
							var message = "";
							var songName = songList[i].videoName.replace(/'/g, "\&#39");
							if(previousSongState != songList[i].videoStatus){
								switch(songList[i].videoStatus){
									case '0':
										if(previousSongState != 3){
											var messageRank = "<p class='list-rank' id='list-upcoming'><?php echo $lang["sl_upcoming"];?></p>";
											$("#body-song-list").append(messageRank);
										}
										break;
									case '1':
										message += "<p class='list-rank'><?php echo $lang["now_playing"];?></p>";
										break;
									case '2':
										message += "<p class='list-rank' id='list-played'><?php echo $lang["sl_played"];?></p>";
										break;
								}
							}
							if(songList[i].videoStatus == 2){
								message += "<div class='row playlist-entry song-played'>";
								message += "<div class='col-lg-10'>";
								pVideos++;
							} else if(songList[i].videoStatus == 1){
								message += "<div class='row playlist-entry song-playing'>";
								message += "<div class='col-lg-12'>";
							} else if(songList[i].videoStatus == 3){
								message += "<div class='row playlist-entry song-ignored'>";
								message += "<div class='col-lg-10'>";
							} else {
								var message = "<div class='row playlist-entry song-upcoming'>";
								message += "<div class='col-lg-10'>";
								uVideos++;
							}
							message += "<p class='song-list-line'><a href='https://www.youtube.com/watch?v="+songList[i].videoLink+"' target='_blank' title='"+songName+"'>"+songList[i].videoName+"</a></p></div>";
							if(window.roomState == 1){
								if(userPower == 2 || userPower == 3){
									if(songList[i].videoStatus == 0){
										message += "<div class='col-lg-1'>";
										message += "<span class='glyphicon glyphicon-ban-circle button-glyph' onClick=ignoreSong("+songList[i].entry+")></span>";
										message += "</div>";
										/*message += "<div class='col-lg-1'>";
						message += "<span class='glyphicon glyphicon-arrow-up'></span>";
						message += "</div>";
						message += "<div class='col-lg-1'>";
						message += "<span class='glyphicon glyphicon-arrow-down'></span>";
						message += "</div>";*/
									} else if(songList[i].videoStatus == 3){
										message += "<div class='col-lg-1'>";
										message += "<span class='glyphicon glyphicon-ok-circle button-glyph' onClick=reinstateSong("+songList[i].entry+")></span>";
										message += "</div>";
									}
								}
								if(songList[i].videoStatus == 2 && userToken != -1){
									message += "<div class='col-lg-1'><span class='glyphicon glyphicon-repeat button-glyph' onClick=requeueSong("+songList[i].entry+")></span></div>";
								}
							}
							message += "</div>";
							previousSongState = songList[i].videoStatus;
							$("#body-song-list").append(message);
						}
						$("#list-upcoming").append(" ("+uVideos+")");
						$("#list-played").append(" ("+pVideos+")");
					})
					setTimeout(loadSongHistory, 8000, roomToken, userPower);
				}
			}
			function loadUsers(roomToken){
				if($("#user-list").css("display") != "none"){
					$.post("functions/get_user_list.php", {roomToken : roomToken}).done(function(data){
						var userList = JSON.parse(data);
						$("#body-user-list").empty();
						var previousRank = -1;
						for(var i = 0; i < userList.length; i++){
							var message = "";
							if(previousRank != userList[i].power){
								switch(userList[i].power){
									case '1':
										message += "<p class='list-rank'><?php echo $lang["ul_users"];?></p>";
										break;
									case '2':
										message += "<p class='list-rank'><?php echo $lang["ul_admin"];?></p>";
										break;
									case '3':
										message += "<p class='list-rank'><?php echo $lang["ul_mods"];?></p>";
										break;
								}
							}
							message += "<p>";
							message += userList[i].pseudo;
							message += "</p>";
							previousRank = userList[i].power;
							$("#body-user-list").append(message);
						}
					})
					setTimeout(loadSongHistory, 8000, roomToken);
				}
			}
			function submitLink(){
				if(window.roomState == 1){
					// Get room token
					var roomToken = "<?php echo $roomToken;?>";

					// Get URL
					var src = $(".url-box").val();
					if(src != ''){
						// get ID of video
						var reg = new RegExp(/\?v=([a-z0-9\-\_]+)\&?/i); // works for all youtube links except youtu.be type
						var res = reg.exec(src);
						if(res == null){
							var alt = new RegExp(/\.be\/([a-z0-9\-\_]+)\&?/i); // works for youtu.be type links
							res = alt.exec(src);
						}
						var id = res[1];

						// Post URL into room history
						$.post("functions/post_history.php", {url : id, roomToken : roomToken}).done(function(code){
							console.log(code);
							switch(code){
								case 'ok': // success code
									$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> <?php echo $lang["song_submit_success"];?></p>");
									break;

								case 'error': // Invalid link code
									$("#body-chat").append("<p class='system-message system-alert'><span class='glyphicon glyphicon-exclamation-sign'></span> <?php echo $lang["invalid_link"];?></p>");
									break;

								default: // success code but the info are incomplete
									$("#body-chat").append("<div id='warning-"+code+"'><p class='system-message system-warning'><span class='glyphicon glyphicon-question-sign'></span> <?php echo $lang["no_fetch"];?><div class='input-group info-box-group'><input type='text' placeholder='<?php echo $lang["fill_placeholder"];?>' class='form-control info-box' id='info-"+code+"'><span class='input-group-btn'><button class='btn btn-primary send-info'><?php echo $lang["fill_missing"];?></button><button class='btn btn-danger cancel-info' id='cancel-info-"+code+"'>Cancel</button></div></div>");
									break;
							}
							$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
						});
						// Empty URL box
						$(".url-box").val('');
					}
				}
			}
			function loadChat(roomToken, userPower, emotes){
				var lang = "<?php echo (isset($userLang))?$userLang:"en";?>";
				if(!window.lastID){
					window.lastID = 0;
				}
				$.post("functions/load_chat.php", {token : roomToken, lang : lang, lastMessageID : window.lastID}).done(function(data){
					var messageList = JSON.parse(data);
					for(var i = 0; i < messageList.length; i++){
						var mTime = moment.utc(messageList[i].timestamp);
						var messageTime = moment(mTime).local().format("HH:mm");
						if(messageList[i].id != window.lastID){
							window.lastID = messageList[i].id;
							//Emotes
							var splitMessage = messageList[i].content.split(' ');
							//console.log(splitMessage);
							for(var j = 0; j < emotes.length; j++){
								for(var k = 0; k < splitMessage.length; k++){
									if(splitMessage[k] == emotes[j]){
										var emoteMessage = splitMessage[k].replace(splitMessage[k], "<img src='assets/emotes/"+emotes[j]+".png' class='chat-emote' title='"+emotes[j]+"'>");
										splitMessage[k] = emoteMessage;
									}
								}
							}
							//console.log(splitMessage);
							messageList[i].content = splitMessage.join(" ");
							//Display
							if(messageList[i].scope == 6){
								// Whispers
								if(messageList[i].destinationToken == userToken){
									var message = "<p class='whisper'>";
									message += "<span class='message-time'>"+messageTime+"</span> ";
									message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#"+messageList[i].authorColor+";'>";
									message += messageList[i].author;
									message += "</span></a>";
									message += "<span class='glyphicon glyphicon-chevron-right'></span> ";
									message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#"+messageList[i].destinationColor+";'>";
									message += messageList[i].destination;
									message += "</span></a> : ";
									message += messageList[i].content;
									message += "</p>";
								} else if(messageList[i].authorToken == userToken){
									var message = "<p class='whisper'>";
									message += "<span class='message-time'>"+messageTime+"</span> ";
									message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#"+messageList[i].authorColor+";'>";
									message += messageList[i].author;
									message += "</span></a>";
									message += "<span class='glyphicon glyphicon-chevron-right'></span> ";
									message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#"+messageList[i].destinationColor+";'>";
									message += messageList[i].destination;
									message += "</span></a> : ";
									message += messageList[i].content;
									message += "</p>";
								} else {
									var message = ""; // Clear message if whisper has nowhere to go
								}
							} else if(messageList[i].scope == 5){
								// System messages viewable by only one user
								if(messageList[i].destinationToken == userToken){
									var message = "<p class='system-message system-alert'>";
									message += "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
									message += messageList[i].content;
									message += "</p>";
								} else {
									var message = ""; // Clear message
								}
							} else if(messageList[i].scope == 4){
								// System messages viewable by everyone
								var message = "<p class='system-message";
								switch(messageList[i].subType){
									case '1':
										message += "'><span class='glyphicon glyphicon-info-sign'></span> ";
										break;
									case '2':
										message += " sm-type-play'><span class='glyphicon glyphicon-play'></span> ";
										break;
									case '3':
										message += " sm-type-skip'><span class='glyphicon glyphicon-step-forward'></span> ";
										if(userPower != 2){
											synchronize("<?php echo $roomToken;?>", userPower);
										}
										break;
									case '4':
										message += " sm-type-close'><span class='glyphicon glyphicon-remove-circle'></span> ";
										break;
									case '5':
										message += " sm-type-ignore'><span class='glyphicon glyphicon-info-sign'></span> ";
										break;
									case '6':
										message += " sm-type-reinstate'><span class='glyphicon glyphicon-info-sign'></span> ";
										break;
									case '7':
										message += " sm-type-open'><span class='glyphicon glyphicon-play-circle'></span> ";
										break;
								}
								message += messageList[i].content;
								message += "</p>";
							} else if(messageList[i].scope == 3){
								// System messages viewable by the moderators
								if(userPower == 2 || userPower == 3){
									var message = "<p class='system-message'>";
									message += "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
									message += messageList[i].content;
									message += "</p>";
								} else {
									var message = ""; // Clear message
								}
							} else if(messageList[i].scope == 2){
								// System messages viewable by the creator
								if(userPower == 2){
									var message = "<p class='system-message'>";
									message += "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
									message += messageList[i].content;
									message += "</p>";
								} else {
									var message = ""; // Clear message
								}
							} else if(messageList[i].scope == 1){
								// Chat for everyone
								var message = "<p class='standard-message'>";
								message += "<span class='message-time'>"+messageTime+"</span> ";
								if(messageList[i].status == 2){
									// If author is creator
									message += "<span class='chat-icon' title='<?php echo $lang["room_admin"];?>'><img src='assets/berrybox-creator-logo.png'><span> ";
								} else if(messageList[i].status == 3) {
									// If author is a moderator
									if((userPower == 2 || userPower == 3) && messageList[i].authorToken != userToken){
										// If current user is a mod or an admin, he can timeout the mod
										/*message += "<span class='glyphicon glyphicon-time moderation-option' title='<?php echo $lang["action_timeout"];?>' onClick=timeoutUser('"+messageList[i].authorToken+"')></span> ";*/
										if(userPower == 2){
											// Specific actions to the admin : ban & demote
											/*message += "<span class='glyphicon glyphicon-fire moderation-option' title='<?php echo $lang["action_ban"];?>' onClick=banUser('"+messageList[i].authorToken+"')></span> ";*/
											message += "<span class='chat-icon' title='<?php echo $lang["room_mod"];?>'><img src='assets/berrybox-moderator-logo.png'><span> ";
											message += "<span class='glyphicon glyphicon-star-empty moderation-option-enabled' title='<?php echo $lang["action_demote"];?>' onClick=demoteUser('"+messageList[i].authorToken+"')></span> ";
										}
									}
									else {
										// If current user has no power here
										message += "<span class='chat-icon' title='<?php echo $lang["room_mod"];?>'><img src='assets/berrybox-moderator-logo.png'><span> ";
									}
								} else {
									// If author is a standard user
									if(userPower == 2 || userPower == 3){
										// Mod & admin actions
										/*message += "<span class='glyphicon glyphicon-time moderation-option' title='<?php echo $lang["action_timeout"];?>' onClick=timeoutUser('"+messageList[i].authorToken+"')></span> ";
								message += "<span class='glyphicon glyphicon-fire moderation-option' title='<?php echo $lang["action_ban"];?>' onClick=banUser('"+messageList[i].authorToken+"')></span> ";*/
										if(userPower == 2){
											//Admin action
											message += "<span class='glyphicon glyphicon-star-empty moderation-option' title='<?php echo $lang["action_promote"];?>' onClick=promoteUser('"+messageList[i].authorToken+"')></span> ";
										}
									}
								}
								if(messageList[i].authorPower == "1"){
									message += "<span class='chat-icon' title='Staff'><img src='assets/berrybox-staff-logo.png'></span> ";
								}
								message += "<a style='text-decoration:none'><span class='message-author author-linkback' style='color:#"+messageList[i].authorColor+";'>";
								message += messageList[i].author;
								message += "</span></a>";
								message += " : "+messageList[i].content+"<br/>";
								message += "</p>";
							}
							$("#body-chat").append(message);
						} else {
							console.log("Double fetch. Denied");
						}
						if(!window.chatHovered){
							$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
						}
					}
				})
				// Once the function has done everything, it fires a timeout to restart the whole process in 2 seconds
				setTimeout(loadChat, 2000, roomToken, userPower, emotes);
			}
			function getWatchCount(roomToken){
				$.post("functions/get_watch_count.php", {token : roomToken}).done(function(data){
					$("#watch-count").empty();
					$("#watch-count").append(" "+data);
				})
			}
		</script>
	</body>
</html>
