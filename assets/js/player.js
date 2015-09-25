$(document).ready(function(){
	window.player = AV.Player.fromURL('01_-_Trigger.flac');
	player.volume = 10;
	player.preload();

	player.on('ready', function(){
		player.play();
		$("#trackName").empty();
		var info = player.metadata.title;
		info += " ("+player.metadata.album+")";
		$("#trackName").append(info);
	})
	player.on('progress', function(){
		var currentTimeSeconds = player.currentTime/1000;
		$("#currentTime").empty();
		$("#currentTime").append(currentTimeSeconds.toFixed(0));
	})
}).on('keypress', function(){
	if(player.playing){
		player.pause();
	}else{
		player.play();
	}
})
