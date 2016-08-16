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

function displayMoodVotes(video_id){
	if(jQuery(window).width() > 1024){
		$.get("functions/get_video_votes.php", {index : video_id}).done(function(data){
			var moods = JSON.parse(data);
			$(".mood-count").empty();
			for(var i = 0; i < moods.length; i++){
				switch(parseInt(moods[i].vote_mood)){
					case 1:
						$("#like-count").text(moods[i].count_mood);
						break;
					case 2:
						$("#cry-count").text(moods[i].count_mood);
						break;
					case 3:
						$("#love-count").text(moods[i].count_mood);
						break;
					case 4:
						$("#energy-count").text(moods[i].count_mood);
						break;
					case 5:
						$("#calm-count").text(moods[i].count_mood);
						break;
					case 6:
						$("#fear-count").text(moods[i].count_mood);
						break;
				}
			}
		});
	}
}

function voteMood(mood_id, user_token, video_id){
	$.post("functions/vote_mood.php", {mood_id : mood_id, user_token : user_token, video_id : video_id}).done(function(){
		displayMood(mood_id);
		displayMoodVotes(video_id);
	})
}

$(document).on('mouseenter', '.emotion-container', function(){
	var id = $(this).attr("id");
	var extracted_mood_token = /-(\w+)-/.exec(id)[0];
	var mood_token = extracted_mood_token.substr(1);
	mood_token = mood_token.slice(0, -1);
	$(".mood-question").fadeOut('500', function(){
		$(".mood-question").empty();
		$(".mood-question").html(language_tokens[mood_token]);
		$(".mood-question").addClass("emotion-"+mood_token);
		$(".mood-question").fadeIn('500');
	})
}).on('mouseleave', '.emotion-container', function(){
	var id = $(this).attr("id");
	var extracted_mood_token = /-(\w+)-/.exec(id)[0];
	var mood_token = extracted_mood_token.substr(1);
	mood_token = mood_token.slice(0, -1);
	$(".mood-question").fadeOut('500', function(){
		$(".mood-question").empty();
		$(".mood-question").html(language_tokens["mood-question"]);
		$(".mood-question").removeClass("emotion-"+mood_token);
		$(".mood-question").fadeIn('500');
	})
})
