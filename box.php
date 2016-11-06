<?php
session_start();
include "functions/db_connect.php";
$db = PDOFactory::getConnection();
$box_token = $_GET["id"];
$checkRoomExistence = $db->query("SELECT *
							FROM rooms r
							JOIN user u ON r.room_creator = u.user_token
							WHERE box_token = '$box_token'");

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
	</head>
	<body>
		<div class="col-sm-8 col-lg-9" id="room-player">
			<div class="container-fluid room-info">
				<div class="col-xs-3 col-sm-2 col-lg-1 top-box-picture">
					<div class="room-picture">
						<img src="profile-pictures/<?php echo $roomDetails["user_pp"];?>" class="profile-picture" id="box-creator-picture" title="" alt="">
					</div>
				</div>
				<div class="col-xs-9 col-sm-10 col-lg-11 top-box-infos">
					<div class="row">
						<p class="col-xs-12" id="room-title"><?php echo $roomDetails["room_name"];?></p>
					</div>
					<div class="row">
						<p class="col-xs-9 col-sm-7" id="room-undertitle"> <a href="user/<?php echo $roomDetails["user_0pseudo"];?>" id="box-creator-link" target="_blank"></a> | <span class="glyphicon glyphicon-play" title="<?php echo $lang["now_playing"];?>"></span> <span class="currently-name"></span></p>
						<div class="creator-stats col-xs-3 col-sm-5">
							<span class="hidden-xs">
								<span class="glyphicon glyphicon-eye-open" title="<?php echo $lang["total_views"];?>"></span> <span class="creator-views"></span>
								<span class="glyphicon glyphicon-heart"></span> <span class="creator-followers"></span>
							</span>
						</div>
					</div>
				</div>
			</div>
			<div id="currently-playing">
				<div class="modal-body" id="player"></div>
			</div>
			<div class="container-fluid under-video">
				<?php if(isset($_SESSION["token"])){ ?>
				<div class="add-link col-xs-6 col-sm-4">
					<div class="input-group">
						<input type="text" placeholder="<?php echo $lang["youtube_message"];?>" class="form-control url-box">
						<span class="input-group-btn">
							<button class="btn btn-primary play-url"><span class="glyphicon glyphicon-circle-arrow-right resize-lg"></span> <span class="hidden-xs hidden-sm hidden-md"><?php echo $lang["submit_link"];?></span></button>
						</span>
					</div>
					<p class="submit-warning"></p>
				</div>
				<?php } ?>
				<div class="room-quick-messages col-xs-3 col-sm-2 col-md-3 col-lg-3">
					<span class="sync-message"></span>
					<span class="submission-message"></span>
					<span class="play-message"></span>
					<span class="protection-message"></span>
				</div>
				<?php if(isset($_SESSION["token"])){ ?>
				<div class="col-xs-12 col-sm-5 mood-selectors">
					<!--<p class="mood-question"><?php echo $lang["mood-question"];?></p>-->
					<div class="col-xs-2 emotion-container" id="emotion-like-container" data-mood="1">
						<p class="emotion-glyph emotion-like button-glyph" id="emotion-like" title="<?php echo $lang["like"];?>">
							<span class="glyphicon glyphicon-thumbs-up"></span> <span class="mood-count" id="like-count"></span>
						</p>
					</div>
					<div class="col-xs-2 emotion-container" id="emotion-cry-container" data-mood="2">
						<p class="emotion-glyph emotion-cry button-glyph" id="emotion-cry" title="<?php echo $lang["cry"];?>">
							<span class="glyphicon glyphicon-tint"></span> <span class="mood-count" id="cry-count"></span>
						</p>
					</div>
					<div class="col-xs-2 emotion-container" id="emotion-love-container" data-mood="3">
						<p class="emotion-glyph emotion-love button-glyph" id="emotion-love" title="<?php echo $lang["love"];?>">
							<span class="glyphicon glyphicon-heart"></span> <span class="mood-count" id="love-count"></span>
						</p>
					</div>
					<div class="col-xs-2 emotion-container" id="emotion-energy-container" data-mood="4">
						<p class="emotion-glyph emotion-energy button-glyph" id="emotion-energy" title="<?php echo $lang["energy"];?>">
							<span class="glyphicon glyphicon-eye-open"></span> <span class="mood-count" id="energy-count"></span>
						</p>
					</div>
					<div class="col-xs-2 emotion-container" id="emotion-calm-container" data-mood="5">
						<p class="emotion-glyph emotion-calm button-glyph" id="emotion-calm" title="<?php echo $lang["calm"];?>">
							<span class="glyphicon glyphicon-bed"></span> <span class="mood-count" id="calm-count"></span>
						</p>
					</div>
					<div class="col-xs-2 emotion-container" id="emotion-fear-container" data-mood="6">
						<p class="emotion-glyph emotion-fear button-glyph" id="emotion-fear" title="<?php echo $lang["fear"];?>">
							<span class="glyphicon glyphicon-flash"></span> <span class="mood-count" id="fear-count"></span>
						</p>
					</div>
				</div>
				<?php } else { ?>
				<div class="col-xs-12">
					<div id="no-credentials">
						<p><?php echo $lang["no_credentials"];?></p>
						<form method="post" action="portal">
							<input type="hidden" name="box-token" value="<?php echo $box_token;?>">
							<input type="submit" class="btn btn-primary" value="<?php echo $lang["log_in"];?>">
						</form>
						<form method="post" action="signup">
							<input type="hidden" name="box-token" value="<?php echo $box_token;?>">
							<input type="submit" class="btn btn-primary" value="<?php echo $lang["sign_up"];?>">
						</form>
					</div>
				</div>
				<?php } ?>
			</div>
			<p class='alert alert-danger closed-box-text'><?php echo $lang["room_closing"];?></p>
		</div>
		</div>
	<div class="col-sm-4 col-lg-3" id="room-chat">
		<div class="panel panel-default panel-room">
			<div class="panel-heading" id="heading-chat">
				<div class="chat-options row">
					<div class="col-lg-12 room-brand hidden-xs"><a href="home">Berrybox</a></div>
					<?php if(isset($_SESSION["username"])) { ?>
					<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-menu-list button-glyph">
						<span class="glyphicon glyphicon-dashboard" title="<?php echo $lang["menu"];?>"></span>
					</div>
					<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-song-list button-glyph">
						<span class="glyphicon glyphicon-list" title="<?php echo $lang["playlist"];?>"></span> / <span class="glyphicon glyphicon-thumbs-up" title="<?php echo $lang["playlist"];?>"></span>
					</div>
					<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-user-list button-glyph">
						<span class="glyphicon glyphicon-user" title="<?php echo $lang["watch_count"];?>"></span><span id="watch-count"></span>
					</div>
					<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-options-list button-glyph">
						<span class="glyphicon glyphicon-cog" title="<?php echo $lang["box_settings"];?>"></span>
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
			<div class="panel-footer" id="footer-chat">
				<?php if(isset($_SESSION["token"])){ ?>
				<input type="text" class="form-control chatbox" placeholder="<?php echo $lang["chat_placeholder"];?>">
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-3 col-xs-12 full-panel" id="song-list">
		<div class="panel panel-default panel-room panel-list">
			<div class="panel-heading"><span class="glyphicon glyphicon-list"></span> <?php echo $lang["playlist"];?></div>
			<ul class="nav nav-tabs nav-justified nav-playlist">
				<li role="presentation" class="active"><a href="#tab-pane-playlist" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-list"></span> <?php echo $lang["playlist"];?></a></li>
				<li role="presentation"><a href="#tab-pane-likes" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-thumbs-up"></span> <?php echo $lang["profile_likes"];?></a></li>
			</ul>
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane active" id="tab-pane-playlist">
					<div class="panel-body panel-section">
						<input type="text" class="form-control" id="playlist-filter" placeholder="<?php echo $lang["playlist_filter"];?>">
					</div>
					<div class="panel-body playlist-actions"></div>
					<div class="panel-body full-panel-body" id="body-song-list"></div>
				</div>
				<div role="tabpanel" class="tab-pane" id="tab-pane-likes">
					<div class="panel-body panel-section">
						<input type="text" class="form-control" id="likes-filter" placeholder="<?php echo $lang["playlist_likes"];?>">
					</div>
					<div class="panel-body full-panel-body" id="body-song-likes"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-2 col-sm-2 col-xs-12 full-panel" id="user-list">
		<div class="panel panel-default panel-room panel-list">
			<div class="panel-heading"><span class="glyphicon glyphicon-user"></span><span id="watch-count"></span> <?php echo $lang["watch_count"];?></div>
			<div class="panel-body full-panel-body" id="body-user-list"></div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-3 col-xs-12 full-panel" id="options-list">
		<div class="panel panel-default panel-room panel-list">
			<div class="panel-heading"><span class="glyphicon glyphicon-cog"></span> <?php echo $lang["box_settings"];?></div>
			<div class="panel-body" id="body-options-list">
				<?php if(isset($_SESSION["token"])){ ?>
				<div class="user-options">
					<p class="options-title">User options</p>
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
				</div>
				<?php } ?>
				<?php if($_SESSION["token"] == $roomDetails["room_creator"]){ ?>
				<!--<div class="room-option">
<span class="option-title"><?php echo $lang["room_params"];?></span><br>
<span class="tip"><?php echo $lang["room_params_tip"];?></span>
<form class="form-horizontal" id="form-box-details">
<div class="form-group">
<label for="room_name" class="col-lg-4 control-label">Box title</label>
<div class="col-lg-8">
<input type="text" name="room_name" class="form-control" value="<?php echo $roomDetails["room_name"] ;?>">
</div>
</div>
<div class="form-group">
<label for="room_lang" class="col-lg-4 control-label"><?php echo $lang["speak_lang"];?></label>
<div class="col-lg-8">
<select name="room_lang" id="" class="form-control">
<option value="en" <?php if($roomDetails["room_lang"]=="en") echo "selected='selected'";?>>English</option>
<option value="fr" <?php if($roomDetails["room_lang"]=="fr") echo "selected='selected'";?>>Français</option>
<option value="jp" <?php if($roomDetails["room_lang"]=="jp") echo "selected='selected'";?>>日本語</option>
</select>
</div>
</div>
<div class="form-group">
<label for="room_type" class="col-sm-4 control-label"><?php echo $lang["room_type"];?></label>
<div class="col-lg-8">
<select name="room_type" class="form-control">
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
<label for="room_description" class="col-sm-4 control-label"><?php echo $lang["description_limit"];?></label>
<div class="col-lg-8">
<textarea name="room_description" cols="30" rows="5" class="form-control"><?php echo $roomDetails["room_description"];?></textarea>
</div>
</div>
</form>
<button class="btn btn-primary btn-block" id="save-room-button"><?php echo $lang["save_changes"];?></button>
</div>-->
				<!--<?php if($roomDetails["room_active"] == 1){ ?>
<div class="room-option" id="close-option" >
<span class="option-title"><?php echo $lang["close_room"];?></span><br>
<span class="tip"><?php echo $lang["close_room_tip"];?></span>
<button class="btn btn-danger btn-admin btn-block" onClick="closeRoom('<?php echo $box_token;?>')"><span class="glyphicon glyphicon-remove-circle"></span> <?php echo $lang["close_room"];?></button>
</div>
<div class="room-option" id="open-option" style="display:none">
<span class="option-title"><?php echo $lang["open_room"];?></span><br>
<span class="tip"><?php echo $lang["open_room_tip"];?></span>
<button class="btn btn-success btn-admin btn-block" onClick="openRoom('<?php echo $box_token;?>')"><span class="
glyphicon glyphicon-play-circle"></span> <?php echo $lang["open_room"];?></button>
</div>
<?php } else { ?>
<div class="room-option" id="close-option" style="display:none">
<span class="option-title"><?php echo $lang["close_room"];?></span><br>
<span class="tip"><?php echo $lang["close_room_tip"];?></span>
<button class="btn btn-danger btn-admin btn-block" onClick="closeRoom('<?php echo $box_token;?>')"><span class="glyphicon glyphicon-remove-circle"></span> <?php echo $lang["close_room"];?></button>
</div>
<div class="room-option" id="open-option">
<span class="option-title"><?php echo $lang["open_room"];?></span><br>
<span class="tip"><?php echo $lang["open_room_tip"];?></span>
<button class="btn btn-success btn-admin btn-block" onClick="openRoom('<?php echo $box_token;?>')"><span class="
glyphicon glyphicon-play-circle"></span> <?php echo $lang["open_room"];?></button>
</div>
<?php } ?>-->
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="col-lg-2 col-sm-2 col-xs-12 full-panel" id="menu-list">
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
						<span class="input-group-addon addon-search"><span class="glyphicon glyphicon-search"></span></span>
						<input type="text" class="form-control search-input" name="search-terms" placeholder="<?php echo $lang["search"];?>...">
					</div>
				</form>
				<div class="menu-options row">
					<ul class="nav nav-pills nav-stacked">
						<li><a href="profile/settings" target="_blank"><span class="glyphicon glyphicon-wrench col-lg-2"></span> <?php echo $lang["my_settings"];?></a></li>
						<li><a href="user/<?php echo $userDetails['user_pseudo'];?>" target="_blank"><span class="glyphicon glyphicon-user col-lg-2"></span> <?php echo $lang["my_profile"];?></a></li>
						<li><a href="follow" target="_blank"><span class="glyphicon glyphicon-heart col-lg-2"></span> <?php echo $lang["following"];?></a></li>
						<li><a href="my/likes" target="_blank"><span class="glyphicon glyphicon-thumbs-up col-lg-2"></span> <?php echo $lang["profile_likes"];?></a></li>
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
	<script src="assets/js/box.js"></script>
	<script src="assets/js/chat.js"></script>
	<script src="assets/js/mood.js"></script>
	<script>
		<?php if(isset($_SESSION["token"])){ ?>
		window.user_token = <?php echo json_encode($_SESSION["token"]);?>;
		window.user_name = <?php echo json_encode($_SESSION["username"]);?>
			<?php } else { ?>
			window.user_token = -1;
		<?php } ?>

		var done = false;
		$(document).ready(function(){
			/** THINGS TO DO IF THE USER IS LOOGED **/
			<?php if(isset($_SESSION["token"])){ ?>
			$(":regex(name,toggle-theme)").bootstrapSwitch({
				size: 'small',
				onText: '<?php echo $lang["light"];?>',
				offText: '<?php echo $lang["dark"];?>',
				onColor: 'light',
				offColor: 'dark',
				onSwitchChange: function(){
					var state = "<?php echo $userDetails["up_theme"];?>";
					$.post("functions/toggle_theme.php", {user_token : user_token, state : state}).done(function(data){
						location.reload();
					})
				}
			});
			<?php } ?>
		});

		/** THING TO DO ON DOCUMENT FOR EVERYONE **/
		$(document).on('click', '.author-linkback', function(){
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
				userCard += "<div class='container-fluid user-card-stats'>";
				userCard += "<div class='col-lg-2 col-xs-3'><span class='glyphicon glyphicon-heart' title='<?php echo $lang["total_followers"];?>'></span> "+details.followers+"</div>";
				userCard += "<div class='col-lg-2 col-xs-3'><span class='glyphicon glyphicon-eye-open' title='<?php echo $lang["total_views"];?>'></span> "+details.visitors+"</div>";
				userCard += "<div class='col-lg-2 col-xs-3'><span class='glyphicon glyphicon-plus' title='<?php echo $lang["rooms_created"];?>'></span> "+details.rooms+"</div>";
				userCard += "<div class='col-lg-2 col-xs-3'><span class='glyphicon glyphicon-music' title='<?php echo $lang["songs_submitted"];?>'></span> "+details.songs+"</div>";
				userCard += "</div>"; // user-card-stats
				userCard += "</div>"; // user-card-info
				<?php if(isset($_SESSION["username"])){ ?>
				if(details.user_pseudo != '<?php echo $userDetails["user_pseudo"];?>'){
					userCard += "<div class='user-card-actions'>";
					userCard += "<button class='btn btn-primary whisper-action'><?php echo $lang["whisper"];?></button>"; // whisper action
					if(details.following == 1){
						userCard += "<button class='btn btn-primary btn-active btn-card btn-unfollow' id='user-card-unfollow' value='"+details.user_pseudo+"'><span class='glyphicon glyphicon-heart'></span></button>";
					} else {
						userCard += "<button class='btn btn-primary btn-card btn-follow' id='user-card-follow' value='"+details.user_pseudo+"'><span class='glyphicon glyphicon-heart'></span></button>";
					} // follow action
					if(user_power == 2){
						userCard += "<button class='btn btn-primary transfer-box' data-user='"+details.user_pseudo+"' title='<?php echo $lang["transfer_box"];?>'><span class='glyphicon glyphicon-transfer'></span> Give creatorsihp</button>";
					}
					userCard += "</div>"; // user-card-actions
				}
				<?php } ?>
				userCard += "</div>"; //user-card
				currentLine.after(userCard);
				$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
			})
		})
		/** FUNCTIONS TO LOAD ONLY IF USER IS LOGGED **/
		function timeoutUser(targetToken){
			$.post("functions/time_out.php", {box_token : box_token, targetToken : targetToken}).done(function(data){
				var adminMessage = "<?php echo $lang["timeout_message_admin_first_part"];?>"+data+"<?php echo $lang["timeout_message_admin_second_part"];?>";
				sendMessage(box_token, 3, null, adminMessage);
				sendMessage(box_token, 5, null, "<?php echo $lang["timeout_message_user"];?>", user_token);
			})
		}
		function banUser(targetToken){

		}
		function promoteUser(targetToken){
			$.post("functions/promote_user.php", {box_token : box_token, user_token : user_token, targetToken : targetToken}).done(function(data){
				var message = "{user_promoted}"+data;
				// System message to everyone to alert the new mod
				sendMessage(box_token, 4, 1, message);
				// System message to the new mod only
				sendMessage(box_token, 5, null, "{you_promoted}", targetToken);
			})
		}
		function demoteUser(targetToken){
			$.post("functions/demote_user.php", {box_token : box_token, user_tokenToken : user_token, targetToken : targetToken}).done(function(data){
				var message = "{user_demoted}"+data;
				// System message to everyone to alert of the demote
				sendMessage(box_token, 4, 1, message);
				// System message to the affected user only
				sendMessage(box_token, 5, null, "{you_demoted}", targetToken);
			});
		}
		function fillInfo(){
			var name = $(".info-box").val();
			var id = $(".info-box").attr("id").substr(5);
			$.post("functions/fill_info.php", {index : id, name : name}).done(function(data){
				$("#warning-"+id).remove();
				$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> <?php echo $lang["info_fill_success"];?></p>");
			})
		}
		/** FUNCTION TO LOAD FOR EVERYONE **/
		function onSecPlayerReady(event){
			//console.log("inputting playlist iD: "+pID);
			event.target.cuePlaylist({list: pID});
		}
		function onSecPlayerStateChange(event){
			//console.log("secondary player started : "+event.data);
			if(event.data == YT.PlayerState.CUED){
				// We retrieve all the IDs from the playlist
				//console.log("a playlist has been cued");
				// Test playlist : https://www.youtube.com/watch?v=DdK5eshlWlg&list=PLDCu51jsfPJexXUm4W89HqRcj8gG569Nm
				var list = event.target.getPlaylist();
				var box_token = getBoxToken();
				$("#body-chat").append("<p class='system-message'> <?php echo $lang["submitting_playlist"];?></p>");
				$(".url-box").val('');
				$(".play-url").addClass("disabled");
				$(".play-url").attr("disabled", "disabled");
				$(".play-url").text("<?php echo $lang["submitting"];?>");

				var dfd = $.Deferred(),
					dfdNext = dfd;
				i = 0,
					values = [],
					postVideo = function(box_token, video_id, source){
					return $.post("functions/post_history.php", {url : video_id, box_token : box_token, source : source});
				};

				dfd.resolve();

				var codes = [];

				for(var i = 0; i < list.length; i++){
					values.push(i);
					dfdNext = dfdNext.then(function(){
						var value = values.shift();
						/*console.log("step "+value+" of "+list.length+" : posting ID "+list[value]);*/
						return postVideo(box_token, list[value], "playlist").done(function(data){
							/*codes.push(data);*/
							if(value != list.length - 1){
								/*console.log("posted "+value+" with code "+data);*/
							} else {
								/*console.log(codes);*/
								if(jQuery.inArray('error', codes) != -1){
									$("#body-chat").append("<p class='system-message system-warning'>"+language_tokens.playlist_error+"</p>");
								} else if(jQuery.inArray('info', codes) != -1){
									$("#body-chat").append("<p class='system-message system-warning'>"+language_tokens.need_info+"</p>");
								} else {
									$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> "+language_tokens.playlist_submitted+" ("+list.length+" "+language_tokens.videos+")</p>");
									$(".play-url").removeClass("disabled");
									$(".play-url").removeAttr("disabled");
									$(".play-url").html("<span class='glyphicon glyphicon-circle-arrow-right resize-lg'></span> <?php echo $lang["submit_link"];?>");
								}
							}
						});
					});
				}
				$("#sec-player").remove();
				event.target.destroy();
			}
		}
		function requestCompletion(code){
			$("#body-chat").append("<div id='warning-"+code+"'><p class='system-message system-warning'><span class='glyphicon glyphicon-question-sign'></span> <?php echo $lang["no_fetch"];?><div class='input-group info-box-group'><input type='text' placeholder='<?php echo $lang["fill_placeholder"];?>' class='form-control info-box' id='info-"+code+"'><span class='input-group-btn'><button class='btn btn-primary send-info'><?php echo $lang["fill_missing"];?></button><button class='btn btn-danger cancel-info' id='cancel-info-"+code+"'>Cancel</button></div></div>");
		}
	</script>
	</body>
</html>
