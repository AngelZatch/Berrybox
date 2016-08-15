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
		<div class="col-lg-8 col-md-8" id="room-player">
			<div class="room-info">
				<div class="room-picture">
					<img src="profile-pictures/<?php echo $roomDetails["user_pp"];?>" class="profile-picture" title="<?php echo $roomDetails["user_pseudo"]." (".$lang["room_admin"].")";?>" alt="">
				</div>
				<p id="room-title"><?php echo $roomDetails["room_name"];?></p>
				<p id="room-undertitle"> <a href="user/<?php echo $roomDetails["user_pseudo"];?>" target="_blank"><?php echo $roomDetails["user_pseudo"];?></a> | <span class="glyphicon glyphicon-play" title="<?php echo $lang["now_playing"];?>"></span> <span class="currently-name"></span></p>
				<div class="room-admin">
					<?php
					if(isset($_SESSION["token"])){
						if($_SESSION["token"] != $roomDetails["room_creator"]){?>
					<div class="room-buttons col-lg-2 col-xs-4">
						<?php if($userFollow == 1){ ?>
						<button class="btn btn-primary btn-active btn-unfollow" id="box-title-unfollow" value="<?php echo $roomDetails["user_pseudo"];?>"><span class="glyphicon glyphicon-heart"></span> <?php echo $lang['following'];?></button>
						<?php } else { ?>
						<button class="btn btn-primary btn-follow" id="box-title-follow" value="<?php echo $roomDetails["user_pseudo"];?>"><span class="glyphicon glyphicon-heart"></span> <?php echo $lang['follow'];?></button>
						<?php } ?>
					</div>
					<?php } else { ?>
					<div class="room-buttons col-lg-2 col-xs-4">
						<button class="btn btn-default btn-admin btn-skip"><span class="glyphicon glyphicon-step-forward"></span> <?php echo $lang["skip"];?></button>
					</div>
					<?php } ?>
					<div class="room-quick-messages col-lg-8 col-xs-4"></div>
					<?php } else { ?>
					<div class="room-quick-messages col-lg-10 col-xs-10"></div>
					<?php } ?>
					<div class="creator-stats col-lg-2 col-xs-4">
						<span class="glyphicon glyphicon-eye-open" title="<?php echo $lang["total_views"];?>"></span> <?php echo $creatorStats["stat_visitors"];?>
						<span class="glyphicon glyphicon-heart"></span> <?php echo $creatorStats["stat_followers"];?>
					</div>
				</div>
			</div>
			<div id="currently-playing">
				<div class="modal-body" id="player"></div>
			</div>
			<div class="container-fluid under-video hidden-xs">
				<?php if(isset($_SESSION["token"])){ ?>
				<div class="add-link col-md-6 col-xs-12">
					<div class="input-group input-group-lg">
						<input type="text" placeholder="<?php echo $lang["youtube_message"];?>" class="form-control url-box">
						<span class="input-group-btn">
							<button class="btn btn-primary play-url" data-toggle="modal"><?php echo $lang["submit_link"];?></button>
						</span>
					</div>
					<p class="submit-warning"></p>
				</div>
				<div class="col-md-6 mood-selectors hidden-xs">
					<p class="mood-question"><?php echo $lang["mood-question"];?></p>
					<div class="col-md-2 emotion-container" id="emotion-like-container" data-mood="1">
						<p class="emotion-glyph emotion-like button-glyph">
							<span class="glyphicon glyphicon-thumbs-up"></span>
						</p>
					</div>
					<div class="col-md-2 emotion-container" id="emotion-cry-container" data-mood="2">
						<p class="emotion-glyph emotion-cry button-glyph">
							<span class="glyphicon glyphicon-tint"></span>
						</p>
					</div>
					<div class="col-md-2 emotion-container" id="emotion-love-container" data-mood="3">
						<p class="emotion-glyph emotion-love button-glyph">
							<span class="glyphicon glyphicon-heart"></span>
						</p>
					</div>
					<div class="col-md-2 emotion-container" id="emotion-energy-container" data-mood="4">
						<p class="emotion-glyph emotion-energy button-glyph">
							<span class="glyphicon glyphicon-eye-open"></span>
						</p>
					</div>
					<div class="col-md-2 emotion-container" id="emotion-calm-container" data-mood="5">
						<p class="emotion-glyph emotion-calm button-glyph">
							<span class="glyphicon glyphicon-bed"></span>
						</p>
					</div>
					<div class="col-md-2 emotion-container" id="emotion-fear-container" data-mood="6">
						<p class="emotion-glyph emotion-fear button-glyph">
							<span class="glyphicon glyphicon-flash"></span>
						</p>
					</div>
				</div>
				<?php } else { ?>
				<div class="col-lg-12 col-xs-12">
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
		<div class="col-lg-4 col-md-4" id="room-chat">
			<div class="panel panel-default panel-room">
				<div class="panel-heading">
					<div class="chat-options row">
						<div class="col-lg-12 room-brand hidden-xs"><a href="home">Berrybox</a></div>
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
		<div class="col-lg-3 col-md-3 col-xs-12 full-panel" id="song-list">
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
		<div class="col-lg-2 col-md-2 col-xs-12 full-panel" id="user-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-user"></span><span id="watch-count"></span> <?php echo $lang["watch_count"];?></div>
				<div class="panel-body full-panel-body" id="body-user-list"></div>
			</div>
		</div>
		<div class="col-lg-3 col-md-3 col-xs-12 full-panel" id="options-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-cog"></span> <?php echo $lang["chat_settings"];?></div>
				<div class="panel-body" id="body-options-list">
					<?php if($_SESSION["token"] == $roomDetails["room_creator"]){ ?>
					<!-- Play type -->
					<div class="room-option">
						<p class="option-title"><?php echo $lang["play_type"];?></p>
						<span class="tip"><?php echo $lang["play_type_tip"];?></span>
						<?php if($roomDetails["room_play_type"] == 1){
	$automatic_state = "btn-disabled"; $manual_state = "disabled";
} else {
	$automatic_state = "disabled"; $manual_state = "btn-disabled";
}?>
						<div class="row">
							<div class="col-lg-6">
								<span class="btn btn-primary btn-block btn-switch toggle-box-state <?php echo $automatic_state;?>" id="select-automatic" data-field='room_play_type' data-value='1' data-twin='select-manual'><span class="glyphicon glyphicon-play-circle"></span> <?php echo $lang["auto_play"];?></span>
							</div>
							<div class="col-lg-6">
								<span class="btn btn-primary btn-block btn-switch toggle-box-state <?php echo $manual_state;?>" id="select-manual" data-field='room_play_type' data-value='2' data-twin='select-automatic'><span class="glyphicon glyphicon-hourglass"></span> <?php echo $lang["manual_play"];?></span>
							</div>
						</div>
					</div>
					<!-- Submission rights -->
					<div class="room-option">
						<p class="option-title"><?php echo $lang["submit_type"];?></p>
						<span class="tip"><?php echo $lang["submit_type_tip"];?></span>
						<?php if($roomDetails["room_submission_rights"] == 1){
	$everyone_state = "btn-disabled"; $mods_only_state = "disabled";
} else {
	$everyone_state = "disabled"; $mods_only_state = "btn-disabled";
}?>
						<div class="row">
							<div class="col-lg-6">
								<span class="btn btn-primary btn-block btn-switch toggle-box-state <?php echo $everyone_state;?>" id="select-everyone" data-field='room_submission_rights' data-value='1' data-twin='select-mods-only'><span class="glyphicon glyphicon-ok-sign"></span> <?php echo $lang["submit_all"];?></span>
							</div>
							<div class="col-lg-6">
								<span class="btn btn-primary btn-block btn-switch toggle-box-state <?php echo $mods_only_state;?>" id="select-mods-only" data-field='room_submission_rights' data-value='2' data-twin='select-everyone'><span class="glyphicon glyphicon-ok-circle"></span> <?php echo $lang["submit_mod"];?></span>
							</div>
						</div>
					</div>
					<!-- Protection -->
					<div class="room-option">
						<p class="option-title"><?php echo $lang["room_protection"];?></p>
						<span class="tip"><?php echo $lang["protection_tip"];?></span>
						<?php if($roomDetails["room_protection"] == 1){
	$public_state = "btn-disabled"; $private_state = "disabled";
} else {
	$public_state = "disabled"; $private_state = "btn-disabled";
}?>
						<div class="row">
							<div class="col-lg-6">
								<span class="btn btn-primary btn-block btn-switch toggle-box-state <?php echo $public_state;?>" id="select-public" data-field='room_protection' data-value='1' data-twin='select-private' title="<?php echo $lang["public_tip"];?>"><span class="glyphicon glyphicon-volume-up"></span> <?php echo $lang["level_public"];?></span>
							</div>
							<div class="col-lg-6">
								<span class="btn btn-primary btn-block btn-switch toggle-box-state <?php echo $private_state;?>" id="select-private" data-field='room_protection' data-value='2' data-twin='select-public' title="<?php echo $lang["private_tip"];?>"><span class="glyphicon glyphicon-headphones"></span> <?php echo $lang["level_private"];?></span>
							</div>
						</div>
					</div>
					<div class="room-option">
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
					</div>
					<?php if($roomDetails["room_active"] == 1){ ?>
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
					<?php } ?>
					<?php } else { ?>
					<div class="room-option">
						<span class="option-title"><?php echo $lang["sync"];?></span><br>
						<span class="tip"><?php echo $lang["sync_tip"];?></span>
						<button class="btn btn-default btn-admin btn-block sync-on" id="btn-synchro"><span class="glyphicon glyphicon-refresh"></span> <?php echo $lang["sync_on"];?></button>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-md-4 col-xs-12 full-panel" id="menu-list">
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
		<div class="container-fluid under-video visible-xs-block">
			<?php if(isset($_SESSION["token"])){ ?>
			<div class="add-link col-xs-12">
				<div class="input-group">
					<input type="text" placeholder="<?php echo $lang["youtube_message"];?>" class="form-control url-box">
					<span class="input-group-btn">
						<button class="btn btn-primary play-url" data-toggle="modal"><?php echo $lang["submit_link"];?></button>
					</span>
				</div>
				<p class="submit-warning"></p>
			</div>
			<?php } else { ?>
			<div class="add-link col-xs-12">
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
		<?php include "scripts.php";?>
		<script src="assets/js/box.js"></script>
		<script src="assets/js/chat.js"></script>
		<script src="assets/js/mood.js"></script>
		<script>
			<?php if(isset($_SESSION["token"])){ ?>
			window.user_token = <?php echo json_encode($_SESSION["token"]);?>;
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
					var songs = event.target.getPlaylist();
					$("#body-chat").append("<p class='system-message'> <?php echo $lang["submitting_playlist"];?></p>");
					$(".url-box").val('');
					var deferreds = addBigPlaylist(songs, box_token);
					$.when.apply(null, deferreds).done(function(){
						$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> <?php echo $lang["playlist_submitted"];?> ("+songs.length+" <?php echo $lang["videos"];?>)</p>");
						$(".play-url").removeClass("disabled");
						$(".play-url").removeAttr("disabled");
						$(".play-url").text("<?php echo $lang["submit_link"];?>");
					}); // Feedback when all videos have been uploaded into the box playlist
					//console.log("destroying secondary player");
					$("#sec-player").remove();
					event.target.destroy();
				}
			}
			function requestCompletion(code){
				$("#body-chat").append("<div id='warning-"+code+"'><p class='system-message system-warning'><span class='glyphicon glyphicon-question-sign'></span> <?php echo $lang["no_fetch"];?><div class='input-group info-box-group'><input type='text' placeholder='<?php echo $lang["fill_placeholder"];?>' class='form-control info-box' id='info-"+code+"'><span class='input-group-btn'><button class='btn btn-primary send-info'><?php echo $lang["fill_missing"];?></button><button class='btn btn-danger cancel-info' id='cancel-info-"+code+"'>Cancel</button></div></div>");
			}
			function addBigPlaylist(list, box_token){
				// Test playlist : https://www.youtube.com/watch?v=Y3HVHNf-oW4&list=PLOXH-6LkzI0OtCgTVus8Czl4wBLyw9SK6
				//console.log("big playlist: switching over to bulk");
				$(".play-url").addClass("disabled");
				$(".play-url").attr("disabled", "disabled");
				$(".play-url").text("<?php echo $lang["submitting"];?>");
				var deferreds = [];
				// Call to this function if the user is adding a playlist of more than 9 videos
				var i, j, temp, chunk = 10;
				for(i = 0, j = list.length; i < j; i+=chunk){
					temp = list.slice(i,i+chunk);
					var listJSON = JSON.stringify(temp);
					deferreds.push(
						$.post("functions/post_playlist.php", {list : listJSON, box_token : box_token})
					)
				}
				return deferreds;
			}
			function userState(box_token, user_token){
				$.post("functions/get_user_state.php", {box_token : box_token, user_token : user_token}).done(function(data){
					if(data == 1){
						setTimeout(userState, 10000, box_token, user_token);
					} else if(data == 2) {
						$("#body-chat").append("<p class='system-message system-alert'>"+language_tokens.room_closing+"</p>");
						setTimeout(function(){
							window.location.replace("home");
						}, 3000);
					}
				})
			}
		</script>
	</body>
</html>
