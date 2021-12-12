<?php

$username = "NorwalkHavoc";
$apiKey = "HnFOhX8bz7CSWa1oEGhDoRGFs7n7aHuxSziGXe1x";
// $tournament = "NHRL2021_2_6_3lb";

function doCurl ($url, $code = 200, $post = FALSE, $put = FALSE){
	$handle = curl_init();
	curl_setopt_array($handle,
	  array(
	      CURLOPT_URL            => $url,
	      CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_USERPWD => 'NorwalkHavoc:HnFOhX8bz7CSWa1oEGhDoRGFs7n7aHuxSziGXe1x',
		  CURLOPT_TIMEOUT => 10
	  )
	);
	if ($post != FALSE){
		curl_setopt($handle, CURLOPT_POST, TRUE);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $post);
	}
	if ($put != false){
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	}
	
	$output = curl_exec($handle);
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	curl_close($handle);
	
	if ($httpCode != $code){
		echo "Code missmatch: ".$httpCode;
		return false;
	} else {
		return $output;
	}
	
}





function getParticipants($tournament) {
	$outArray;
	$url = 'https://api.challonge.com/v1/tournaments/'.$tournament.'/participants.json';	
	$output = doCurl($url);
	if (!$output){ //Did we get an error. Lets try again
		sleep(1);
		$output = doCurl($url);
	}
	if (!$output){ //API not working
		return false;
	}
	$participantArray = json_decode($output,TRUE);
	foreach ($participantArray as $participant){
		$outArray[$participant['participant']['id']] = $participant['participant']['name'];
	}
	file_put_contents('./config/participants-'.$tournament.'.json', json_encode($outArray));
	return $outArray;
}


function setUnderway($tournament, $matchID){
	$url = 'https://api.challonge.com/v1/tournaments/'.$tournament.'/matches/'.$matchID.'/mark_as_underway.json';
	$output = doCurl($url, 200, TRUE);
	return $output;
}
function clearUnderway($tournament, $matchID){
	$url = 'https://api.challonge.com/v1/tournaments/'.$tournament.'/matches/'.$matchID.'/unmark_as_underway.json';
	$output = doCurl($url, 200, TRUE);
	return $output;
}

function setWinner($tournament, $matchID, $playerID){
	$url = 'https://api.challonge.com/v1/tournaments/'.$tournament.'/matches/'.$matchID.'.json';
	$winArray = array(
		'_method' => 'put',
		'match[scores_csv]' => '0-0',
		'match[winner_id]' => $playerID
	);
	
	
	return $output = doCurl($url, 200, $winArray, TRUE);
	
	
}



function getMatches($tournament) {
	$outArray;
	$url = 'https://api.challonge.com/v1/tournaments/'.$tournament.'/matches.json';	
	$output = doCurl($url);
	if (!$output){ //Did we get an error. Lets try again
		sleep(1);
		$output = doCurl($url);
	}
	if (!$output){ //API not working
		return false;
	}
	$participants = getParticipants($tournament);
	
	$matchArray = json_decode($output,TRUE);
	foreach ($matchArray as $match){
		$matchNumber = $match['match']['suggested_play_order'];
		$underway = 0;
		if ($match['match']['underway_at'] != '' && $match['match']['state'] != 'complete'){
			$underway = 1;
		}
		
		$outArray[$matchNumber] = array(
			'id' => $match['match']['id'],
			'state'  => $match['match']['state'],
			'round' => $match['match']['round'],
			'player1' => $participants[$match['match']['player1_id']],
			'player2' => $participants[$match['match']['player2_id']],
			'player1_id' => $match['match']['player1_id'],
			'player2_id' => $match['match']['player2_id'],
			'winner' => $participants[$match['match']['winner_id']],
			'loser' => $participants[$match['match']['loser_id']],
			'underway' => $underway
			
		);
	}
	file_put_contents('./config/matches-'.$tournament.'.json', json_encode($outArray));
	return $outArray;
}



?>