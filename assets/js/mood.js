function fetchMood(user_token, video_id){
	$.get("functions/fetch_mood.php", {user_token : user_token, video_id : video_id}).done(function(mood_id){
		displayMood(mood_id);
	})
}

function displayMood(mood_id){
	mood_id = mood_id.toString();
	var mood;
	$(".emotion-container").removeClass("selected");
	if(mood_id != '0'){
		switch(mood_id){
			case '1':
				mood = 'like';
				break;

			case '2':
				mood = 'cry';
				break;

			case '3':
				mood = 'love';
				break;

			case '4':
				mood = "energy";
				break;

			case '5':
				mood = "calm";
				break;

			case '6':
				mood = "fear";
				break;
		}
		$("#emotion-"+mood+"-container").addClass("selected");
	}
}

function voteMood(mood_id, user_token, video_id){
	$.post("functions/vote_mood.php", {mood_id : mood_id, user_token : user_token, video_id : video_id}).done(function(){
		displayMood(mood_id);
	})
}
