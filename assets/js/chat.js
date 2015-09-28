$(document).ready(function(){
	window.uniqueToken = "9RSDFCF0QN7ZSOS";
	loadChat();
})

$(".chatbox").on('focus', function(){
	$(this).keypress(function(event){
		if(event.which == 13){
			sendMessage();
			console.log("Detect");
		}
	})
})

$(".btn-chat").on('click', function(){
	sendMessage();
});

function sendMessage(){
	var message = $(".chatbox").val();
	var token = window.uniqueToken;
	$.post("functions/post_chat.php", {message : message, token : token}).done(function(){
		$(".chatbox").val('');
		loadChat();
	})
}

function loadChat(){
	var token = window.uniqueToken;
	$.post("functions/load_chat.php", {token : token}).done(function(data){
		var messageList = JSON.parse(data);
		$(".panel-body").empty();
		for(var i = 0; i < messageList.length; i++){
			var message = "["+messageList[i].timestamp+"] ";
			message += "<span class='message-author' style='color:"+messageList[i].authorColor+";'>";
			message += messageList[i].author;
			message += "</span>";
			message += " : "+messageList[i].content+"<br/>";
			$(".panel-body").append(message);
		}
	})
}
