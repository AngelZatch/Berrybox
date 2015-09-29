$(document).ready(function(){

	//makeCORSRequest();
	$(".play-url").on('click', function(){
		// Get URL
		var src = $(".url-box").val();
		var res = src.replace("watch?v=", "embed/");
		res += "?autoplay=1";

		// Load video into iframe
		$("#frame-play iframe").attr("src", res);

		// Empty URL box
		$(".url-box").empty();

		// Post URL into room history
		$.post("functions/post_history.php", {url : res, roomToken : window.uniqueToken}).done(function(data){

		})
	})

	/*window.player = AV.Player.fromURL("http://www.youtube.com/watch?v=lMYBhsQ0krw");
	player.play();*/
	//player.volume = 10;

	/*	player.on('ready', function(){
		player.play();
		$("#trackName").empty();
		var info = player.metadata.title;
		info += " ("+player.metadata.album+")";
		$("#trackName").append(info);
		$(".room-track").append(player.metadata.title+" ("+player.metadata.artist+")");
	})
	player.on('progress', function(){
		var currentTimeSeconds = player.currentTime/1000;
		$("#currentTime").empty();
		$("#currentTime").append(currentTimeSeconds.toFixed(0));
	})*/
})

/*$(".player").on('click', function(){
	if(player.playing){
		player.pause();
	}else{
		player.play();
	}
})*/

function createCORSRequest(method, url){
	var xhr = new XMLHttpRequest();
	if("withCredentials" in xhr){
		xhr.open(method, url, true);
	} else if(typeof XDomainRequest != "undefined"){
		xhr = new XDomainRequest();
		xhr.open(method, url);
	} else {
		xhr = null;
	}
	return xhr;
}

function makeCORSRequest(){
	var url = "http://www.youtube.com/watch?v=lMYBhsQ0krw";
	var xhr = createCORSRequest('GET', url);
	if(!xhr){
		throw new Error('CORS not supported');
		return;
	}
	xhr.onload = function(){
		var responseText = xhr.responseText;
		console.log(responseText);
	};
	xhr.onerror = function(){
		console.log('Error');
	}
	xhr.send();
}
