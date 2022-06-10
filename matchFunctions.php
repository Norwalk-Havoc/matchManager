<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once('buttonFunctions.php');

require_once('config.php');

define("MATCH_READY", 1380);
define("MATCH_TIME", 180);
define("MATCH_COUNTDOWN", 0);
define("ENCORE_TIME", 30);
define("MAX_SCORE", 6);


if (isset($_REQUEST['mode'])){ //We are running a command from the URL
	$mode = $_REQUEST['mode'];
	unset($_POST['mode']); //This lets us do really bad stuff
	if ($mode != 'setButton'){ //I am a bad person!
		unset($_GET['mode']);
			
	}
	
	if ($mode == "challongeURL"){
		setChallongeTournaments($_POST); //LOL YUP I JUST DID THAT. Sorry Security 
	} else if ($mode == 'updateMatches'){
		if (isset($_GET['json'])){
			printAvailableMatches($_GET['json']);
		} else {
			printAvailableMatches();
		}
	} else if ($mode == 'next10'){
		if (isset($_GET['json'])){
			printTheNext10($_GET['json']);
		} else {
			printTheNext10();
		}
	} else if ($mode == 'updateUpNext'){
		$tourn = $_GET['tournament'];
		$round = $_GET['round'];
		printUpNext($tourn, $round);
	} else if ($mode == 'assignRumble'){
		$cage = $_GET['cage'];
		$tournament = "special";
		$matchNumber = "rumble";
		matchToCage($cage, $tournament, $matchNumber);
	} else if ($mode == 'assignMatch'){
		$matchInfo = $_POST['matchSelect'];
		$cage = $_POST['matchSpot'];
		$matchInfo = explode("~",$matchInfo);
		$matchNumber = $matchInfo[1];
		$tournament = $matchInfo[0];
		matchToCage($cage, $tournament, $matchNumber);
	} else if ($mode == 'updateCages'){
		$jsonOut['html'] = populateCageFields(1,false);
		$jsonOut['html'] .= populateCageFields(2, false);
		$jsonOut['html'] .= populateCageFields(3, false);
	    $jsonOut['html'] .= populateCageFields(4, false);
		$jsonOut['html'] .= populateCageFields(5, false);
		$jsonOut['hash'] = md5($jsonOut['html']);
		echo json_encode($jsonOut,JSON_HEX_TAG);
		
	} else if ($mode == 'manualCageUpdate'){
		$cage = $_POST['cage'];
		$outCage = getCageText($cage);
		$outCage = array_merge($outCage, $_POST); //Yup we did that. Check your form names son!
		setCageText($cage, $outCage);
		
		//nMatches Tech Debt here
		$prod = getProductionState();
		if ($prod['bank']['X'] == $cage){
			singular("resetX");
			shell_exec('curl "http://192.168.10.10:8000/press/bank/10/7"'); //Update Match Variables inside Companion
		} else if ($prod['bank']['Y'] == $cage){
			singular("resetY");
			shell_exec('curl "http://192.168.10.10:8000/press/bank/10/7"'); //Update Match Variables inside Companion
		}
		
	} else if ($mode == 'winner'){
		//This code is also part of parse event!
		$cage = $_GET['cage'];
		$player = $_GET['player'];
		$outCage = getCageText($cage);
		if (isset($_GET['noReset']) && $outCage['winner'] != ''){
			//Do Nothing
		} else {
			if ($outCage['player'.$player] == $outCage['winner'] ){
				reopenMatch($outCage['tournament'], $outCage['id']);
				$outCage['winner'] = "";
				$outCage['loser'] = "";
			} else {
				setWinner($outCage['tournament'], $outCage['id'], $outCage['player'.$player."_id"]);
				$outCage['winner'] = $outCage['player'.$player];
				if ($player == 1){
					$loser = 2;
				} else {
					$loser = 1;
				}
				$outCage['loser'] = $outCage['player'.$loser];
			}		
			setCageText($cage, $outCage);
			stopMatch($cage);
		}
		
		//nMatches Tech Debt here
		$prod = getProductionState();
		if ($prod['bank']['X'] == $cage){
			singular("resetX");
			shell_exec('curl "http://192.168.10.10:8000/press/bank/10/7"'); //Update Match Variables inside Companion
		} else if ($prod['bank']['Y'] == $cage){
			singular("resetY");
			shell_exec('curl "http://192.168.10.10:8000/press/bank/10/7"'); //Update Match Variables inside Companion
		}
		
	} else if ($mode == 'startMatch'){
		$cage = $_GET['cage'];
		$outCage = getCageText($cage);
		$outCage['startTime'] = time();
		$outCage['stopTime'] = time() + $outCage['matchTime'] + MATCH_COUNTDOWN;
		$outCage['endCountdown'] = time() + MATCH_COUNTDOWN;
		$outCage['matchActive'] = TRUE;
		$outCage['matchPaused'] = FALSE;
		$outCage['encore'] = FALSE;
		$outCage['state_text'] = "Match Active";
		removeFromGreenroom($outCage['player1_id']);
		removeFromGreenroom($outCage['player2_id']);
		
		setCageText($cage, $outCage);
		setUnderway($outCage['tournament'], $outCage['id']);
		//nMatches Tech Debt here
		//Is this a cage we are working on? 
		$prod = getProductionState();
		if ($prod['bank']['X'] == $cage){
			singular("startX");
			$bank = 'X';
			
		} else if ($prod['bank']['Y'] == $cage){
			singular("startY");
			$bank = 'Y';
			
		}

	
		if ($bank == ''){
			$bank = 'Unknown';
		}
		$matchLog = getMatchLog();
		$matchLog['listOnly'][$bank][$outCage['order']] = $outCage['filePath'];
		$matchLog['verbose'][$outCage['filePath']] = $outCage;
		setMathcLog($matchLog);
		
		
		
	} else if ($mode == 'stopMatch'){
		$cage = $_GET['cage'];
		stopMatch($cage);
		
	} else if ($mode == 'clearMatch'){
		$cage = $_GET['cage'];
		clearCageText($cage);
	} else if ($mode == 'encore'){
		$cage = $_GET['cage'];
		$outCage = getCageText($cage);
		$outCage['stopTime'] += ENCORE_TIME;
		$outCage['encore'] = TRUE;
		setCageText($cage, $outCage);
	} else if ($mode == 'pause'){
		$cage = $_GET['cage'];
		$outCage = getCageText($cage);
		if($outCage['matchPaused'] == FALSE){
			$outCage['matchPaused'] = time();
			$outCage['state_text'] = "Paused";
			
			setCageText($cage, $outCage);
			
			//nMatches Tech Debt here
			//Is this a cage we are working on? 
			$prod = getProductionState();
			if ($prod['bank']['X'] == $cage){
				singular("pauseX");
			} else if ($prod['bank']['Y'] == $cage){
				singular("pauseY");
			}
			
			
		} else {
			$outCage['stopTime'] += time() - $outCage['matchPaused'];
			$outCage['matchPaused'] = FALSE;
			$outCage['state_text'] = "Match Active";
			
			setCageText($cage, $outCage);
			
			//nMatches Tech Debt here
			//Is this a cage we are working on? 
			$prod = getProductionState();
			if ($prod['bank']['X'] == $cage){
				singular("unpauseX");
			} else if ($prod['bank']['Y'] == $cage){
				singular("unpauseY");
			}
		}
		setCageText($cage, $outCage);
	} else if ($mode == 'cageJson'){
		$cage = $_GET['cage'];
		sendCageJSON($cage);
	} else if ($mode == 'botDetails'){
		$tournament = $_GET['tournament'];
		$bot = $_GET['bot'];
		$tournaments = getChallongeTournaments();
		$outJson['html'] = botDetails($tournaments[$tournament], $bot, $tournament);
		$outJson['md5'] = md5($outJson['html']);
		echo json_encode($outJson);

	} else if ($mode == 'allBots'){
		$outJson['html'] = allBots();
		$outJson['md5'] = md5($outJson['html']);
		echo json_encode($outJson);

	} else if ($mode == 'photoBooth'){
		$outJson['html'] = allBots("photoBooth");
		$outJson['md5'] = md5($outJson['html']);
		echo json_encode($outJson);

	} else if ($mode == 'photoBooth2'){
		$outJson['html'] = allBots("photoBooth2");
		$outJson['md5'] = md5($outJson['html']);
		echo json_encode($outJson);

	} else if ($mode == 'greenroomMode'){
		$outJson['html'] = allBots("greenroom");
		$outJson['md5'] = md5($outJson['html']);
		echo json_encode($outJson);

	} else if ($mode == 'greenroomToggle'){
		$playerID = $_GET['playerID'];
		
		$room = intval($_GET['room']);
		if ($room == 0){
			$room = 1;
		}
		
		
		$greenRoom = readGreenroom();
		if (isset($greenRoom[$playerID]) && $greenRoom[$playerID] == $room){
			removeFromGreenroom($playerID);
		} else {
			addToGreenroom($playerID, $room);
		}

	} else if ($mode == 'judgeLoadStage'){
		judgeLoadStage($_GET['stage'], $_GET['bank']);

	} else if ($mode == 'judgeMatchData'){
		echo json_encode(getJudgeMatchData($_GET['bank']));

	} else if ($mode == 'getJudgeDisplayData'){
		echo json_encode(getJudgeJSON($_GET['bank']));

	} else if ($mode == 'judgeScoreUpload'){
		$scores['id'] = $_GET['judgeID'];
		$scores['agression'] = $_GET['LAgression'];
		$scores['control'] = $_GET['LControl'];
		$scores['dammage'] = $_GET['LDammage'];
		if ($scores['id'] != 0){
			submitJudgeScores($scores, $_GET['bank']);
		}
		singular("judgesX");

	} else if ($mode == 'setProductionState'){
		$state = getProductionState();
		
		$setBank = substr($_GET['bank'],0,1); // One character for safety!
		$state['bank'][$setBank] = $_GET['cage'];
		setProductionState($state);
		singular("reset".$setBank);
		echo "Ok - $setBank";


	} else if ($mode == 'setCageState'){
		$cage = $_GET['cage'];
		$outCage = getCageText($cage);
		$outCage['state_text'] = $_GET['state'];
		setCageText($cage, $outCage);
     } else if ($mode == 'getCageState'){
		$cage = $_GET['cage'];
		$outCage = getCageText($cage);
		echo $outCage['state_text'];
     }
	
	
	 else if ($mode == 'picUpload'){
		$id = $_POST['bot_id'];
		$tournament = $_POST['tournament'];
		$imageString = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['imageString']); 
		$target_dir = "./robots/";
		$target_file = $target_dir . $imageString . ".jpg";
		$uploadOk = 1;
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])) {
		  $check = getimagesize($_FILES["botPicture"]["tmp_name"]);
		  if($check !== false) {
		    echo "File is an image - " . $check["mime"] . ".";
		    $uploadOk = 1;
		  } else {
		    echo "File is not an image.";
		    $uploadOk = 0;
		  }
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
		  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
		  $uploadOk = 0;
		}

		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
		  echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
		} else {
			if (file_exists($target_file)){
				unlink($target_file);
			}
			  if (move_uploaded_file($_FILES["botPicture"]["tmp_name"], $target_file)) {
				  $targetFileTime = str_replace('.jpg', '-'.time().'.jpg', $target_file);
				  copy($target_file, $targetFileTime);
				  shell_exec("removebg --api-key $removeBGKey --reprocess-existing --extra-api-option 'crop=true' --files $target_file");
				  shell_exec("convert -resize '300' ".$target_dir.$imageString."-removebg.png /var/www/html/matchManager/robots-thumb/".$imageString.".png");
				  
				  if ($id == ''){
	  			    echo '<html><head><meta http-equiv="refresh" content="2; URL=photoBooth.php" /></head><body><h1>Successful Bot Upload</h1></body></html>';
				  	
				  } else {
			    	  echo '<html><head><meta http-equiv="refresh" content="2; URL=myBot.php?bot_id='.$id.'&bracket='.$tournament.'" /></head><body><h1>Success</h1></body></html>';
					}
			  } else {
			    echo "Sorry, there was an error uploading your file ".$_FILES["botPicture"]["tmp_name"]."as ". $target_file;
			  }
		}

	} else if ($mode == 'peopleUpload'){
		$id = $_POST['bot_id'];
		$tournament = $_POST['tournament'];
		$imageString = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['imageString']); 
		$target_dir = "./people/";
		$target_file = $target_dir . $imageString . ".jpg";
		$uploadOk = 1;
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])) {
		  $check = getimagesize($_FILES["botPicture"]["tmp_name"]);
		  if($check !== false) {
		    echo "File is an image - " . $check["mime"] . ".";
		    $uploadOk = 1;
		  } else {
		    echo "File is not an image.";
		    $uploadOk = 0;
		  }
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
		  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
		  $uploadOk = 0;
		}

		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
		  echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
		} else {
			if (file_exists($target_file)){
				unlink($target_file);
			}
			  if (move_uploaded_file($_FILES["botPicture"]["tmp_name"], $target_file)) {
				  $targetFileTime = str_replace('.jpg', '-'.time().'.jpg', $target_file);
				  copy($target_file, $targetFileTime);
				  shell_exec("removebg --api-key $removeBGKey --reprocess-existing  --files $target_file");
				  shell_exec("convert -resize '300' ".$target_dir.$imageString."-removebg.png /var/www/html/matchManager/people-thumb/".$imageString.".png");
				  
				  if ($id == ''){
	  			    echo '<html><head><meta http-equiv="refresh" content="2; URL=photoBoothPeople.php" /></head><body><h1>Successful Driver Upload</h1></body></html>';
				  	
				  } else {
			    	  echo '<html><head><meta http-equiv="refresh" content="2; URL=myBot.php?bot_id='.$id.'&bracket='.$tournament.'" /></head><body><h1>Success</h1></body></html>';
				   }
			  } else {
			    echo "Sorry, there was an error uploading your file ".$_FILES["botPicture"]["tmp_name"]."as ". $target_file;
			  }
		}

	}
	 else if ($mode == 'singular'){
		singular($_GET['smode']);
	} else if ($mode == 'dataNibble'){ //This allows companion to populate variables one at a time!
		$cage = $_GET['cage'];
		$index = $_GET['index'];
		$cage = getCageText($cage);
		if ($index == "fightString"){
			echo $cage['player1']." vs ".$cage['player2'];
			exit;
		}
		echo $cage[$index];
	} else if ($mode == 'hdFile'){
		$bank = $_GET['bank'];
		$shift = $_GET['shift'];
		$matchLog = getMatchLog();
		$prod = getProductionState();
		$index = $prod['playback'][$bank];
		$index += $shift;
		if ($index < 0){
			$index = 0;
		} else if ($index > count($matchLog['listOnly'][$bank])){
			$index = count($matchLog['listOnly'][$bank]) - 1;
		}
		
		if (isset($_GET['index'])){
			$index = $_GET['index'];
		}
		
		$prod['playback'][$bank] = $index;
		setProductionState($prod);
		
		$matchLog = getMatchLog();	
		$path = array_slice($matchLog['listOnly'][$bank],$index,1);


		echo current($path);
		
		
	} else if ($mode == 'tapOut'){
		$cage = $_GET['cage'];
		$player = $_GET['player'];
		$prod = getProductionState();
		if ($cage == $prod['bank']['X'] ){
			$thisCage = getCageText($cage);
			if ($thisCage['matchActive']){
				if ($player == 1){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/22/15"'); //TapOut	
				} else if ($player == 2){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/22/11"'); //TapOut	
				}
				if ($cage == 1){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/40/9"');
				}
				else if ($cage == 2){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/40/10"');
				}
				else if ($cage == 3){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/40/11"');
				}
				else if ($cage == 4){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/40/12"');
				}
				else if ($cage == 5){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/40/13"');
				}
			}	
		}
		else if ($cage == $prod['bank']['Y'] ){
			$thisCage = getCageText($cage);
			if ($thisCage['matchActive']){
				if ($player == 1){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/23/15"'); //TapOut	
				} else if ($player == 2){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/23/11"'); //TapOut	
				}
				if ($cage == 1){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/40/9"');
				}
				else if ($cage == 2){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/40/10"');
				}
				else if ($cage == 3){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/40/11"');
				}
				else if ($cage == 4){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/40/12"');
				}
				else if ($cage == 5){
					shell_exec('curl "http://192.168.10.10:8000/press/bank/40/13"');
				}
			}	
		}
		
		
	}
	
	

	
	
} 

function stopMatch($cage){
	$outCage = getCageText($cage);
	$outCage['stopTime'] = time();
	$outCage['matchActive'] = FALSE;
	$outCage['matchPaused'] = FALSE;
	$outCage['encore'] = FALSE;
	$outCage['state_text'] = "Match Completed";
	setCageText($cage, $outCage);
	clearUnderway($outCage['tournament'], $outCage['id']);

	//nMatches Tech Debt here
	$prod = getProductionState();
	$bank = 'Unknown';
	if ($prod['bank']['X'] == $cage){
		singular("stopX");
		$bank = 'X';
	} else if ($prod['bank']['Y'] == $cage){
		singular("stopY");
		$bank = 'Y';
	}
	$reason = $_GET['winReason'];
	$outCage['winReason'] = $reason;
	
	$matchLog = getMatchLog();
	$matchLog['listOnly'][$bank][$outCage['order']] = $outCage['filePath'];
	$matchLog['verbose'][$outCage['filePath']] = $outCage;
	setMathcLog($matchLog);
}


function getGilRoundString($weightClass, $round){
	$gilRounds = json_decode(file_get_contents('./config/gilRounds.json'), true);
	//This makes me cry inside. This file should be better formatted to allow for lookup in N timespace vs N^3
	foreach($gilRounds as $roundArray){
		if ($roundArray['weight_class'] == $weightClass && $roundArray['round'] == $round){
			return ($roundArray['label']);
		}
	}
}


function matchToCage($cage,$tournament, $matchNum){
	clearCageText($cage);
	$outCage = array();
	$match = array();
	if ($tournament == "special"){
		if ($matchNum == "rumble"){
			$match = json_decode(file_get_contents("./config/rumble_template.json"), true);
			$outCage['state_text'] = "Assigned \nRumble";
		} else {
			$outCage['state_text'] = "Unknown \nSpecial";
		}
	} else {
		$outCage = getCageText($cage);
		$tournaments = getChallongeTournaments();
		$matches = getMatches($tournaments[$tournament]);
		$match = $matches[$matchNum];
		$match['tournament'] = $tournaments[$tournament];
		$match['tournamentKey'] = $tournament;
		$match['weightClass'] = str_replace("-", " ", str_replace("-Bracket","",$tournament));
		$match['player1image'] = preg_replace("/[^a-zA-Z0-9]+/", "", str_replace(" ","",strtolower($match['player1'])));
		$match['player2image'] = preg_replace("/[^a-zA-Z0-9]+/", "", str_replace(" ","",strtolower($match['player2'])));
		$match['matchTime'] = MATCH_TIME;
		$match['messageOverride'] = '';
		$match['lowerMessage'] = 'Match: '.$match['order'].' - ';
		// if ($match['round'] > 0){
		// 	$match['lowerMessage'] .= "Undefeated Bracket Round ".$match['round'];
		// } else {
		// 	$match['lowerMessage'] .= "Elimination Bracket Round ".($match['round'] * -1);
		// }
		$match['lowerMessage'] = getGilRoundString($match['weightClass'], $match['round']);
		$match['lowerMessage'] .= ' - '.$match['weightClass'];
	
		$outCage['startTime'] = 0;
		$outCage['stopTime'] = 0;
		$outCage['endCountdown'] = 0;
		$outCage['matchActive'] = FALSE;
		$outCage['matchPaused'] = FALSE;
		$outCage['encore'] = FALSE;
		$outCage['filePath'] = $match['weightClass']."-".$match['order']."--".$match['player1image']."-vs-".$match['player2image'];
		$outCage['state_text'] = "Assigned";
		
		
	
	}
	$outCage = array_merge($outCage, $match);
	
	
	
	
	
	setCageText($cage, $outCage);
	
	$roundString = str_replace(['+', '-'], '', filter_var($match['weightClass'], FILTER_SANITIZE_NUMBER_INT)).'-'. $match['round'];
	
	error_log("This is happenging");
	shell_exec('curl "http://192.168.10.10:8000/press/bank/10/7"'); //Update Match Variables inside Companion
	shell_exec('ssh mcwiggin@192.168.5.249 \'php /Users/mcwiggin/Development/audioFactory/clipMaker.php '.$cage.' '.$roundString.' '.$match['player1image'].' '.$match['player2image'].' 192.168.10.26\''); //Send audio
	error_log('\nssh mcwiggin@192.168.5.249 \'php /Users/mcwiggin/Development/audioFactory/clipMaker.php '.$cage.' '.$roundString.' '.$match['player1image'].' '.$match['player2image'].' 192.168.10.26\'\n');
	
	$prod = getProductionState();
	

}


function getCageText($cage){
	if (file_exists('./config/cage'.$cage.'.json')){
		$outCage = json_decode(file_get_contents('./config/cage'.$cage.'.json'), TRUE);
		if ($outCage['matchActive'] && $outCage['stopTime'] < time() - 5 ){
			$outCage['matchActive'] = FALSE;
		} 
		
		return($outCage);
	}
	else {
		return(array());
	}
}

//This is a custom function that uploads data to custom enpoints on singular.live
//This currently only supports 2 matches at once and will need a rewite to process nMatches in sequence
function singular($singularMode){
	 $xAPI = "32lfxwYBWi5vYOJn3aWnmu";
	 $yAPI = "4NwCk5DdRbefEozQfCsSjZ";
	
	
	$prod = getProductionState();
	
	if ($singularMode == "stopX"){
		$XCage = $prod['bank']['X'];
		$cage1 = getCageText($XCage);

		
		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			"clockControl" => array("command" => "pause"),
			"showClock" => FALSE
		);
		$json = json_encode($out, JSON_PRETTY_PRINT);
		//Oh yeah its that awful
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$xAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;
	}
	else if ($singularMode == "pauseX"){
		$XCage = $prod['bank']['X'];
		$cage1 = getCageText($XCage);

		
		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			"clockControl" => array("command" => "pause"),
			"showClock" => FALSE,
			"exceptionText" => "PAUSED"
		);
		$json = json_encode($out, JSON_PRETTY_PRINT);
		//Oh yeah its that awful
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$xAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;
	}
	else if ($singularMode == "unpauseX"){
		$XCage = $prod['bank']['X'];
		$cage1 = getCageText($XCage);

		
		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			// "clockControl" => array("command" => "play"),
			"clockTarget" => $cage1['stopTime'] * 1000,
			"showClock" => TRUE,
			"exceptionText" => ""
		);
		$json = json_encode($out, JSON_PRETTY_PRINT);
		//Oh yeah its that awful
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$xAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;
	}	
	else if ($singularMode == "startX"){
		$XCage = $prod['bank']['X'];
		$cage1 = getCageText($XCage);

		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			// "clockControl" => array("command" => "reset"),
			"clockTarget" => $cage1['stopTime'] * 1000,
			"exceptionText" => "",
			"showClock" => TRUE
		);
		$json = json_encode($out, JSON_PRETTY_PRINT);
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$xAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;

		
		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			"clockControl" => array("command" => "start"),

			
			"exceptionText" => ""
		);
		$json = json_encode($out, JSON_PRETTY_PRINT);
		//Oh yeah its that awful
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$xAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;
	}
	
	if ($singularMode == "resetX"){
		$XCage = $prod['bank']['X'];
		$cage1 = getCageText($XCage);
		$player2 = $cage1['player1'];
		$player1 = $cage1['player2'];
		$descript = $cage1['lowerMessage'];
		if ($cage1['messageOverride'] != ''){
			$descript = $cage1['messageOverride'];
		}
	    $winner = $cage1['winner'];
		if ($winner == $player1){
			$winner = 1;
		} else if ($winner == $player2){
			$winner = 2;
		}
		else {
			$winner = 0;
		}
		
		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			"headerText" => $descript,
			"bot1Name" => $player1,
			"bot2Name" => $player2,
			"exceptionText" => "",
			"setWinner" => $winner

		);
		if ($winner != 0){
			$out['controlNode']['payload']['clockControl']['command'] = "reset";
		}
		
		if ($XCage['matchActive'] == FALSE){
			$out['controlNode']['payload']['clockWarning'] = FALSE;
		}
		
		$json = json_encode($out, JSON_PRETTY_PRINT);
		//Oh yeah its that awful
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$xAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;

		//Update Vestaboard!
		$outString = "Up Now In Cage ".$XCage." ".$cage1['weightClass']." ".preg_replace("/[^a-zA-Z0-9]+/", "", $cage1['player1'])." vs ".preg_replace("/[^a-zA-Z0-9]+/", "", $cage1['player2']);
		shell_exec('curl -X POST -H "Content-Type: application/json" -d \'{"value1":"'.$outString.'"}\' https://maker.ifttt.com/trigger/MatchUpdate/with/key/cqz29bXA_bC2eOkhRNfiSk');
	
	}
	else if ($singularMode == "stopY"){
		$XCage = $prod['bank']['Y'];
		$cage1 = getCageText($XCage);

		
		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			"clockControl" => array("command" => "pause"),
			"showClock" => FALSE
		);
		$json = json_encode($out, JSON_PRETTY_PRINT);
		//Oh yeah its that awful
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$yAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;
	}
	else if ($singularMode == "pauseY"){
		$XCage = $prod['bank']['Y'];
		$cage1 = getCageText($XCage);

		
		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			// "clockControl" => array("command" => "pause"),
			"showClock" => FALSE,
			"exceptionText" => "PAUSED"
		);
		$json = json_encode($out, JSON_PRETTY_PRINT);
		//Oh yeah its that awful
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$yAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;
	}
	else if ($singularMode == "unpauseY"){
		$XCage = $prod['bank']['Y'];
		$cage1 = getCageText($XCage);

		
		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			"clockControl" => array("command" => "play"),
			"clockTarget" => $cage1['stopTime'] * 1000,
			"showClock" => TRUE,
			"exceptionText" => ""
		);
		$json = json_encode($out, JSON_PRETTY_PRINT);
		//Oh yeah its that awful
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$yAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;
	} 
	else if ($singularMode == "startY"){
		$XCage = $prod['bank']['Y'];
		$cage1 = getCageText($XCage);

		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			// "clockControl" => array("command" => "reset"),
			"clockTarget" => $cage1['stopTime'] * 1000,
			"exceptionText" => "",
			"showClock" => TRUE
		);
		$json = json_encode($out, JSON_PRETTY_PRINT);
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$yAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;

		
		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			"clockControl" => array("command" => "start"),
			"exceptionText" => ""
		);
		$json = json_encode($out, JSON_PRETTY_PRINT);
		//Oh yeah its that awful
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$yAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;
	}	
	
	else if ($singularMode == "resetY"){
		$XCage = $prod['bank']['Y'];
		$cage1 = getCageText($XCage);
		$player2 = $cage1['player1'];
		$player1 = $cage1['player2'];
		$descript = $cage1['lowerMessage'];
		if ($cage1['messageOverride'] != ''){
			$descript = $cage1['messageOverride'];
		}
		$winner = $cage1['winner'];
		
		if ($winner == $player1){
			$winner = 1;
		} else if ($winner == $player2){
			$winner = 2;
		}
		else {
			$winner = 0;
		}
		$out['compositionName'] = "Score Bug";
		$out['controlNode']['payload'] = array(      
			"headerText" => $descript,
			"bot1Name" => $player1,
			"bot2Name" => $player2,
			"exceptionText" => "",
			"setWinner" => $winner

		);
		
		if ($winner != 0){
			$out['controlNode']['payload']['clockControl']['command'] = "reset";
		}
		if ($XCage['matchActive'] == FALSE){
			$out['controlNode']['payload']['clockWarning'] = FALSE;
		}
		
		
		$json = json_encode($out, JSON_PRETTY_PRINT);
		//Oh yeah its that awful
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/control/$yAPI' \
	--header 'Content-Type: application/json' \
	--data-raw ['$json']`;
	} else if ($singularMode == "judgesX"){
		$judgeID1 = "7";
		$judgeID2 = "6";
		$judgeID3 = "8";
		$judge1id = $judgeID1; 
		$judge2id = $judgeID2;
		$judge3id = $judgeID3;
		
		
		$XCage = $prod['bank']['X'];
		$cage1 = getCageText($XCage);
		$player2 = $cage1['player1'];
		$player1 = $cage1['player2'];
		$descript = $cage1['lowerMessage'];
		$judge = getJudgeJSON("X");
		
		$judge3_winner = $cage1[$judge['judge_scores']['player1'][$judge3id]['winner']]." ";
		$judge3_aggression = $judge['judge_scores'][$judge['judge_scores']['player1'][$judge3id]['winner']][$judge3id]['aggression']." ";
		$judge3_control = $judge['judge_scores'][$judge['judge_scores']['player1'][$judge3id]['winner']][$judge3id]['control']." ";
		$judge3_damage = $judge['judge_scores'][$judge['judge_scores']['player1'][$judge3id]['winner']][$judge3id]['damage']." ";
		
		$judge2_winner = $cage1[$judge['judge_scores']['player1'][$judge2id]['winner']]." ";
		$judge2_aggression = $judge['judge_scores'][$judge['judge_scores']['player1'][$judge2id]['winner']][$judge2id]['aggression']." ";
		$judge2_control = $judge['judge_scores'][$judge['judge_scores']['player1'][$judge2id]['winner']][$judge2id]['control']." ";
		$judge2_damage = $judge['judge_scores'][$judge['judge_scores']['player1'][$judge2id]['winner']][$judge2id]['damage']." ";
		
		$judge1_winner = $cage1[$judge['judge_scores']['player1'][$judge1id]['winner']]." ";
		$judge1_aggression = $judge['judge_scores'][$judge['judge_scores']['player1'][$judge1id]['winner']][$judge1id]['aggression']." ";
		$judge1_control = $judge['judge_scores'][$judge['judge_scores']['player1'][$judge1id]['winner']][$judge1id]['control']." ";
		$judge1_damage = $judge['judge_scores'][$judge['judge_scores']['player1'][$judge1id]['winner']][$judge1id]['damage']." ";
		
		
	
		
		
		
		$out['payload'] = array(      
			"title" => "$player1 vs $player2",
			"subtitle" => $descript,

			"judge3_winner" => $judge3_winner,
			"judge3_aggression" => $judge3_aggression,
			"judge3_control" => $judge3_control,
			"judge3_damage" => $judge3_damage,
			
			"judge2_winner" => $judge2_winner,
			"judge2_aggression" => $judge2_aggression,
			"judge2_control" => $judge2_control,
			"judge2_damage" => $judge2_damage,
			
			"judge1_winner" => $judge1_winner,
			"judge1_aggression" => $judge1_aggression,
			"judge1_control" => $judge1_control,
			"judge1_damage" => $judge1_damage
			

		);
		
		if ($winner != 0){
			$out['controlNode']['payload']['clockControl']['command'] = "reset";
		}
		if ($XCage['matchActive'] == FALSE){
			$out['controlNode']['payload']['clockWarning'] = FALSE;
		}
		
		
		$json = json_encode($out, JSON_PRETTY_PRINT);
		//Oh yeah its that awful
		echo "<pre> Calling Cage $XCage \n";
		echo $json . "\n\n\n";
		echo `curl --location --request PUT 'https://app.singular.live/apiv1/datanodes/0VAL9jz22jL001sx4ssYlu/data' \
	--header 'Content-Type: application/json' \
	--data-raw '$json'`;
	}

	
} 


function clearCageText($cage){
	
	setCageText($cage, array());
}

function setCageText($cage, $cageArray){
	file_put_contents('./config/cage'.$cage.'.json',json_encode($cageArray));
	
}

// This needs a Bank to be set
function getJudgeMatchData($bank){
	$tournaments = getChallongeTournaments();
	$ourMatch = -1;
	
	// if (count($tournaments) != 0){
// 		foreach ($tournaments as $tournamentName => $tournament){
// 			$tournamentNameNice = str_replace("-"," ", $tournamentName);
// 			$matches = getMatches($tournament);
// 			ksort($matches);
// 			foreach($matches as $match){
// 			  if($match['underway'] == '1'){
// 				  $ourMatch = $match;
// 				  break;
// 			  }
// 			}
// 			if ($ourMatch != -1){
// 				break;
// 			}
// 		}
// 	}
// 	//No Matches are underway
// 	if ($ourMatch == -1){
// 		$outArray = array("player1" => "-", "player2" => "-", "round" => 0, "tournament" => "none", "id" => 0);
// 	} else {
// 		$outArray = $ourMatch;
// 		$outArray['tournament'] = $tournamentNameNice;
// 	}

	$prod = getProductionState();
	$XCage = $prod['bank']['X'];
	$cage1 = getCageText($XCage);

	return $cage1;
}

//This only works when the current match you are submitting scores for is underway!

function getJudgeScores(){
	if (file_exists('./config/judgeScores.json')){
		$out = json_decode(file_get_contents('./config/judgeScores.json'), TRUE);
		return($out);
	}
	else {
		return(array());
	}
}

function getJudgeJSON($bank){
	$match = getJudgeMatchData($bank);
	if($match['id'] == 0){
		return;
	}
	$judgedMatches = getJudgeScores();
	$matchId = $match['id'];
	
	if (isset($judgedMatches[$match['id']])){
		$judgedMatch = $judgedMatches[ $matchId ];
	} else {
		$judgedMatch = $match;
	}
	return $judgedMatch;
}

function setJudgeScores($scores){
	file_put_contents('./config/judgeScores.json',json_encode($scores, JSON_PRETTY_PRINT));
}

function judgeLoadStage($stage, $bank){
	$stage = intval($stage); //Saftey!
	$match = getJudgeMatchData($bank);
	if($match['id'] == 0){
		return;
	}
	$judgedMatches = getJudgeScores();
	$matchId = $match['id'];
	
	if (isset($judgedMatches[$match['id']])){
		$judgedMatch = $judgedMatches[ $matchId ];
	} else {
		$judgedMatch = $match;
		$judgedMatch['loadStage'] = 0; //Lets not show our cards till we tell the system to do that.
	}
	$judgedMatch['loadStage'] = $stage;
	$judgedMatches[$match['id']] = $judgedMatch;
	setJudgeScores($judgedMatches);
};

function submitJudgeScores($scores, $bank){
	$match = getJudgeMatchData($bank);
	if($match['id'] == 0){
		return;
	}
	$judgedMatches = getJudgeScores();
	$matchId = $match['id'];
	
	if (isset($judgedMatches[$match['id']])){
		$judgedMatch = $judgedMatches[ $matchId ];
	} else {
		$judgedMatch = $match;
		$judgedMatch['loadStage'] = 0; //Lets not show our cards till we tell the system to do that.
	}
	
	$player1['aggression'] = 0 + $scores['agression'];
	$player1['damage'] = 0 + $scores['dammage'];
	$player1['control'] = 0 + $scores['control'];
	$player2['aggression'] = (6 - 1) - $scores['agression'];
	$player2['damage'] = 6 - $scores['dammage'];
	$player2['control'] = 6 - $scores['control'];
	if ($player1['aggression'] + $player1['damage'] + $player1['control'] > $player2['aggression'] + $player2['damage'] + $player2['control'] ) {
		$player1['winner'] = 'player1';
		$player2['winner'] = 'player1';
	} else {
		$player1['winner'] = 'player2';
		$player2['winner'] = 'player2';
	}

	
	$judgedMatch['judge_scores']['player1'][$scores['id']] = $player1;
	$judgedMatch['judge_scores']['player2'][$scores['id']] = $player2;
	$judgedMatches[$match['id']] = $judgedMatch;
	setJudgeScores($judgedMatches);
}


function botDetails($tournament, $bot_id, $bracket){
	$currentRound = 0;
	$currentMatch = 0;
	$activeMatches = array();
	$matches = getMatches($tournament);
	$latestMatch = getLatestMatch($tournament);
	$playerMatches = array();
	$nextMatch = 0;
	ksort($matches);
	foreach ($matches as $match){
	  if (($currentRound == 0 || $currentRound == '') && $match['state'] == 'open' ){
		  $currentRound = $match['round'];
		  $currentMatch = $match['order'];
	  }

	  
	  if ($match['player1_id'] == $bot_id){
		  $match['iam'] = 1;
		  $playerMatches[$match['order']] = $match;
		  if ($nextMatch == 0 & $match['state'] != 'complete'){
			  $nextMatch = $match['order'];
		  }
	  }	 else if ($match['player2_id'] == $bot_id){
		  $match['iam'] = 2;
		  $playerMatches[$match['order']] = $match;
		  if ($nextMatch == 0 & $match['state'] != 'complete'){
			  $nextMatch = $match['order'];
		  }
	  }	
	}

	$playerString = 'player'.$playerMatches[$nextMatch]['iam'];
	$playerName = $playerMatches[$nextMatch][$playerString];
	$match = $playerMatches[$nextMatch];
	$matchesRemaining = $match['order'] - $latestMatch; 
	
	
	
	  $player_readyAt = $match[$playerString.'_lastFought'] + MATCH_READY; 
	  if ($player_readyAt < time()){
		  //We are ready to fight!
		  $player_class = "-success";
		  $player_ready = true;
	  } else {
	  	  $player_ready = false;
		   if ($player_readyAt < time() + 300){
		   		$player_class = "-warning";
		   } else {
		   		$player_class = "-danger";
		   }
	  }
	  $playerBG = "";
	  if ($player_readyAt > time()-(3600 * 1)) { //We have a real timestamp in the last 30 days
		  $readyTime = time() - $player_readyAt;
		  if ($readyTime >= 0 ) { //We are ready to fight!
			  $player_readyString = "<div class=\"timeLeft badge badge$player_class\">".floor($readyTime/60)."</div> ";
			  $playerBG = "";
	  		  $playerAlert = '		<div class="alert alert-success" role="alert">
		  Your bot was considered ready to fight as of '.date("g:i a", $player_readyAt ).' which was '.(floor($readyTime/60)).' minutes ago. Your next match is in '.$matchesRemaining.'
		</div>';
	  
		  } else {
		  	  $player_readyString = "<div class=\"timeLeft badge badge$player_class\">".(floor($readyTime/60) * -1)."</div> ";
			  $playerBG = "bg-secondary";
	  		  $playerAlert = '		<div class="alert alert-danger" role="alert">
		  Your bot\'s minimum ready to fight time is '.date("g:i a", $player_readyAt ).' which is in '.(floor($readyTime/60) * -1).' minutes
		</div>';
		  }
  
	  } else {
		  $player_readyString = "";
  		  $playerAlert = '		<div class="alert alert-info" role="alert">
	  Your bot should be ready. <br>It\'s been over an hour since it\'s last fight.
	</div>';
	  }
	  
	  if ($currentRound > 0){
		  $roundString = "Undefeated - Round $currentRound";
	  } else {
		  $currentRound = $currentRound * -1;
		  $roundString = "Elimination - Round $currentRound";
	  }
	  
	  if ($matchesRemaining < 10){
		  if ($player_ready){
				  $playerAlert .= '<div class="alert alert-danger" role="alert">
				  Head to the green room! You are expected to fight in '.$matchesRemaining.' Matches
				</div>';
		  } else {
				  $playerAlert .= '<div class="alert alert-info" role="alert">
				  Complete repairs quickly. Your due up in '.$matchesRemaining.' Matches
				</div>';
		  }
	  }
	
	  $match['player'.$match['iam']] = ''.$match['player'.$match['iam']].'';
	  if ($match['round'] > 0){
		  $myRound = "Win - ".$match['round'];
	  } else {
		  $myRound = "Lose - ".($match['round'] * -1);	
	  }
	  
	  if ($match['player1'] == ''){
	  	$match['player1'] = '<i>Pending</i>';
	  }
	  if ($match['player2'] == ''){
	  	$match['player2'] = '<i>Pending</i>';
	  }
	 
	  $pastFights = "";
	  foreach($playerMatches as $pastMatch){
		  if ($pastMatch['order'] != $match['order']){
			  if ($pastMatch['round'] > 0){
				  $pastRound = "Win ".$pastMatch['round'];
			  } else {
				  $pastRound = "Lose ".($pastMatch['round'] * -1);	
			  }
			  if ($pastMatch['winner'] == $pastMatch['player1']){
				  $player1result = 'W';
				  $player2result = 'L';
			  } else {
				  $player1result = 'L';
				  $player2result = 'W';
			  }
			  
			  
			  $pastFights .= '
		  		<div class="row rounded mx-1 mb-4 myMatch">
		  			<div class="col-3 px-0 myMatchMeta"><div class="metaNumber">Match '.$pastMatch['order'].'</div><div class="metaRound">'.$pastRound.'</div></div>
		  			<div class="col-8 ">
		  				<div class="myMagenta botName text-right" >'.$pastMatch['player1'].'</div>
		  				<div class="myCyan botName text-right" >'.$pastMatch['player2'].'</div>
		  			</div>
					<h5 class="col-1 px-0">
						<div class=" myIcon bot'.$player1result.'">'.$player1result.'</div>
						<div class=" myIcon bot'.$player2result.'">'.$player2result.'</div>
					</h5>
		  		</div>
				  
				  
			    ';
		  }
	  }
	 
	 $playerImage = preg_replace("/[^a-zA-Z0-9]+/", "", str_replace(" ","",strtolower($playerName)));
	  
	  	
	$out = '<div class ="row mt-3">
			<h1 class="h2 col-9">'.$playerName.'</h1>
			<h1 class="col-3 text-right">'.$player_readyString.'</h1>
		</div>
		'.$playerAlert.'
		<h5 class="nextFight">'.$playerName.'\'s Next Fight is Match '.$match['order'].'</h5>
		<div class ="row mx-0 py-2 currentFight">
			<h5 class="col-3 text-white-50">Next Up</h1>
			<h5 class="col-6 text-center">'.$roundString.'</h1>
			<h5 class="col-3 text-right">Match '.$currentMatch.'</h1>
		</div>
		<div class ="row rounded mx-0 mb-4 myMatch">
			<div class="col-3 px-0 myMatchMeta "><div class="metaNumber">Match '.$match['order'].'</div><div class="metaRound">'.$myRound.'</div></div>
			<div class="col-8 ">
				<div class="myMagenta botName text-right" >'.$match['player1'].'</div>
				<div class="myCyan botName text-right" >'.$match['player2'].'</div>
			</div>
			<div class="col-1 px-0">
				<div class="myIcon botBgMagenta" style="background-image:url(\'getBotPic.php?bot='.str_replace(" ","",strtolower($match['player1'])).'\')"></div>
				<div class="myIcon botBgCyan" style="background-image:url(\'getBotPic.php?bot='.str_replace(" ","",strtolower($match['player2'])).'\')"></div>
			</div>

		</div>
		
		
		<div class="row pb-2" >
			<div class="botImage px-4 py-0 d-flex text-right flex-row-reverse" style="background-image:url(\'getBotPic.php?bot='.$playerImage.'\')">
				<a style="display:none" href="newPic.php?bot_id='.$bot_id.'&imageString='.$playerImage.'&tournament='.$bracket.'" class="mt-auto btn btn-primary ">Update Picture</a>
			</div>
		</div>
	
		

		<hr>

		<h5>'.$playerName.'\'s Past Fights</h5>
			'.$pastFights.'
			';
	return($out);
	
}


function populateCageFields($cage, $echo=true){
	
	$cageText = getCageText($cage);
	
	if ($cageText['winner'] == $cageText['player1']){
		$player1css = "btn-primary";
	} else {
		$player1css = "btn-outline-secondary";
	}
	if ($cageText['winner'] == $cageText['player2']){
		$player2css = "btn-primary";
	} else {
		$player2css = "btn-outline-secondary";
	}
	if ($cageText['matchPaused']){
		$cageCSS = 'badge-warning';
	} else if ($cageText['matchActive']) {
		$cageCSS = 'badge-danger';
	} else {
		$cageCSS = 'badge-secondary';
	}
	if ($cageText['matchPaused'] != FALSE){
		$paused = 'btn-secondary';
	} else {
		$paused = 'btn-outline-secondary';
	}
	
	$cageLabel = $cage;
	if ($cageText['order'] > 0 ){
		$state = getProductionState();
		$liveMode = "";
		if ($state['bank']['X'] == $cage){
			$liveMode = " MAIN";
		}
		if ($state['bank']['Y'] == $cage){
			$liveMode = " OCHO";
		}
		
		$cageLabel .= "<span style='font-weight:100'> ".$cageText['weightClass']." - ".$cageText['order']." ".$liveMode."</span>";
	} else {
		$state = getProductionState();
		$liveMode = "";
		if ($state['bank']['X'] == $cage){
			$liveMode = " MAIN";
		}
		if ($state['bank']['Y'] == $cage){
			$liveMode = " OCHO";
		}
		$cageLabel .= "<span style='font-weight:100'> ".$liveMode."</span>";
	}
	
	
	$outText = '
		<div class="col border rounded m-1 py-1 cageBlock">
			<form id="cage'.$cage.'form" name="cage'.$cage.'form">
			<h3 class="mb-4 mt-2"><span class="badge badge-pill cageTitle '.$cageCSS.'">'.$cageLabel.'</span> <button type="button" class="btn btn-outline-primary float-right" style="transition: none !important;" id="updateCage-'.$cage.'" onClick="updateCage('.$cage.')">Push Update</button></h3>
				<div class="cageState alert alert-dark text-center lead ">'.$cageText['state_text'].'&nbsp;</div>
				<input type="hidden" name="cage" value="'.$cage.'">
				<input type="hidden" name="mode" value="manualCageUpdate">
				<div class="input-group mb-3">
				  <div class="input-group-prepend">
				    <span class="input-group-text red-bg" >Red</span>
				  </div>
				  <input type="text" class="form-control" group="cage" cage="'.$cage.'" name="player1" value="'.$cageText['player1'].'">
				  <div class="input-group-append">
						<button class="btn '.$player1css.' playerButtons" type="button" cage="'.$cage.'" player="1" onClick="cageCommand('.$cage.',\'player1Wins\', this)" style="transition: none !important;">Wins</button>
				  </div>
				</div>
				
				<div class="input-group mb-3">
				  <div class="input-group-prepend ">
				    <span class="input-group-text blue-bg" >Blue</span>
				  </div>
				  <input type="text" class="form-control" group="cage" cage="'.$cage.'" name="player2" value="'.$cageText['player2'].'">
				  <div class="input-group-append">
						<button class="btn '.$player2css.' playerButtons" type="button" cage="'.$cage.'" player="2" onClick="cageCommand('.$cage.',\'player2Wins\', this)" style="transition: none !important;">Wins</button>
				  </div>
				
				</div>
				<div class="input-group mb-3">
				  <div class="input-group-prepend">
				    <span class="input-group-text lower-message">Lower Message</span>
				  </div>
				  <input type="text" class="form-control lower-message-text" group="cage"  cage="'.$cage.'" name="messageOverride" placeholder="'.$cageText['lowerMessage'].'" value="'.$cageText['messageOverride'].'">
				</div>
				<div class="input-group mb-3 matchTime">
				  <div class="input-group-prepend">
				    <span class="input-group-text" group="cage" cage="'.$cage.'" name="matchTime">Match Time</span>
				  </div>
				  <input type="text" class="form-control" name="matchTime" value="'.$cageText['matchTime'].'">
				  <div class="input-group-append">
						<button class="btn btn-success startMatch" type="button" cage="'.$cage.'" onClick="cageCommand('.$cage.',\'startMatch\', this)" style="transition: none !important;">Start Match</button>
				  </div>
				</div>
			  <div class="float-left inline-block">
					<button class="btn btn-outline-secondary" type="button" id="button-addon2" onClick="cageCommand('.$cage.',\'clearMatch\', this)" style="transition: none !important;">Clear</button>
			  </div>
				<div class="input-group col-9 mb-3 float-right">

				  <div class="input-group-preppend">
						<button class="btn btn-outline-info" type="button" id="button-addon2" onClick="cageCommand('.$cage.',\'encore\', this)" style="transition: none !important;">Encore</button>
				  </div>
				  <div class="input-group-append">
						<button class="btn '.$paused.'" type="button" id="button-addon2" style="transition: none !important;" onClick="cageCommand('.$cage.',\'pause\', this)">Pause</button>
				  </div>
				  <div class="input-group-append">
						<button class="btn btn-outline-danger stopMatch" type="button" id="button-addon2" cage="'.$cage.'" onClick="cageCommand('.$cage.',\'stopMatch\', this)" style="transition: none !important;">End</button>
				  </div>
				</div>
				</form>
		</div>
		
		
		';
		
		if ($echo){
			echo $outText;
		} else {
			return $outText;
		}

}


function getChallongeTournaments(){
	
	if (file_exists('./config/challongeTournaments.json')){
		return(json_decode(file_get_contents('config/challongeTournaments.json'), TRUE));
	}
	else {
		return(array());
	}
}
function setChallongeTournaments($tournaments){
	foreach($tournaments as $key=>$value){
		if(trim($value) == ''){
			unset($tournaments[$key]);
		}
	}
	
	file_put_contents('./config/challongeTournaments.json',json_encode($tournaments));
	foreach($tournaments as $key=>$value){
		getMatches($value, TRUE);
	}
}


function getProductionState(){
	
	if (file_exists('./config/productionState.json')){
		return(json_decode(file_get_contents('config/productionState.json'), TRUE));
	}
	else {
		return(array());
	}
}
function setProductionState($productionState){
	$productionState['updateTime'] = time();
	file_put_contents('./config/productionState.json',json_encode($productionState));

}

function getMatchLog(){
	$dateCode = date("m-Y");
	if (file_exists('./config/matchLog-'.$dateCode.'.json')){
		return(json_decode(file_get_contents('./config/matchLog-'.$dateCode.'.json'), TRUE));
	}
	else {
		return(array());
	}
}
function setMathcLog($matchLog){
	$dateCode = date("m-Y");
	$matchLog['updateTime'] = time();
	file_put_contents('./config/matchLog-'.$dateCode.'.json',json_encode($matchLog));

}


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
		curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
	}
	
	$output = curl_exec($handle);
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	curl_close($handle);
	
	if ($httpCode != $code){
		error_log( "CURL HTTP Code missmatch: ".$httpCode." - Expecting ".$code." URL".$url);
		return false;
	} else {
		return $output;
	}
	
}


function getParticipants($tournament, $forceRefresh = FALSE) {
	
	if (file_exists('./config/participants-'.$tournament.'.json') && $forceRefresh == FALSE){
		return json_decode(file_get_contents('./config/participants-'.$tournament.'.json'), TRUE);
	}
	
	
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

function reopenMatch($tournament, $matchID){
	$url = 'https://api.challonge.com/v1/tournaments/'.$tournament.'/matches/'.$matchID.'/reopen.json';
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



function getMatches($tournament, $forceRefresh = FALSE) {
	
	if (file_exists('./config/matches-'.$tournament.'.json') && time() - filemtime('./config/matches-'.$tournament.'.json') > 5){
		//Lets do some curling if this file is more than 5 seconds old. 
		$forceRefresh = TRUE;
	}
	
	
	if (file_exists('./config/matches-'.$tournament.'.json') && $forceRefresh == FALSE){
		return json_decode(file_get_contents('./config/matches-'.$tournament.'.json'), TRUE);
	}

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
	$participants = getParticipants($tournament, $forceRefresh);
	
	$matchArray = json_decode($output,TRUE);
	file_put_contents('./config/matchesRaw-'.$tournament.'.json', json_encode($matchArray, JSON_PRETTY_PRINT ));
	$matchCounter++;
	foreach ($matchArray as $match){
		$matchNumber = $match['match']['suggested_play_order'];
		$matchCounter++;
		if ($matchNumber == null){
			$matchNumber = $matchCounter;
		}
		$underway = 0;
		if ($match['match']['underway_at'] != '' && $match['match']['state'] != 'complete'){
			$underway = 1;
		}
		$greenroom = readGreenroom();
		if (isset($greenroom[$match['match']['player1_id']])){
			//Player is in greenroom
			$player1greenroom = $greenroom[$match['match']['player1_id']];
		} else {
			$player1greenroom = 0;
		}
		if (isset($greenroom[$match['match']['player2_id']])){
			//Player is in greenroom
			$player2greenroom = $greenroom[$match['match']['player2_id']];
		} else {
			$player2greenroom = 0;
		}
		
		$outArray[$matchNumber] = array(
			'id' => $match['match']['id'],
			'order' => $matchNumber,
			'state'  => $match['match']['state'],
			'round' => $match['match']['round'],
			'player1' => $participants[$match['match']['player1_id']],
			'player2' => $participants[$match['match']['player2_id']],
			'player1_id' => $match['match']['player1_id'],
			'player1_greenroom' => $player1greenroom,
			'player1_prereq' => $match['match']['player1_prereq_match_id'],
			'player2_id' => $match['match']['player2_id'],
			'player2_prereq' => $match['match']['player2_prereq_match_id'],
			'player2_greenroom' => $player2greenroom,
			'winner' => $participants[$match['match']['winner_id']],
			'loser' => $participants[$match['match']['loser_id']],
			'updated_at' => strtotime($match['match']['updated_at']),
			'underway' => $underway
			
		);
	}
	foreach ($outArray as $matchNum => $match){
		if ($match['player1_prereq'] != 0){
			//So there is a prereq match.. Lets look it up and see when it was last modified.
			$key = array_search($match['player1_prereq'], array_column($outArray, 'id', 'order'));
			$outArray[$matchNum]['player1_lastFought'] = $outArray[$key]['updated_at'];
		} else {
			$outArray[$matchNum]['player1_lastFought'] = 0;
		}
		if ($match['player2_prereq'] != 0){
			//So there is a prereq match.. Lets look it up and see when it was last modified.
			$key = array_search($match['player2_prereq'], array_column($outArray, 'id', 'order'));
			$outArray[$matchNum]['player2_lastFought'] = $outArray[$key]['updated_at'];
		} else {
			$outArray[$matchNum]['player2_lastFought'] = 0;
		}
	}
	
	file_put_contents('./config/matches-'.$tournament.'.json', json_encode($outArray, JSON_PRETTY_PRINT));
	
	//Sort by ready
	
	
	
	return $outArray;
}


function readGreenroom(){
	if (file_exists('./config/greenroom.json') ){
		return json_decode(file_get_contents('./config/greenroom.json'), TRUE);
	} else {
	    $greenroom = array();
		file_put_contents('./config/greenroom.json', json_encode($greenroom));
		return $greenroom;
	}
}
function writeGreenroom($greenroom){
	file_put_contents('./config/greenroom.json', json_encode($greenroom));
}

function emptyGreenroom(){
	file_put_contents('./config/greenroom.json', json_encode(array()));
}


function addToGreenroom($playerID, $room = 1){
	$greenroom = readGreenroom();
	$greenroom[$playerID] = $room;
	writeGreenroom($greenroom);
}

function removeFromGreenroom($playerID){
	$greenroom = readGreenroom();
	$greenroom[$playerID] = 0;
	unset($greenroom[$playerID]);
	writeGreenroom($greenroom);	
}


function printAvailableMatches($json = false){
	$tournaments = getChallongeTournaments();
	$outString =	'';
	
	if (count($tournaments) != 0){
		$spacing = floor(12 / count($tournaments));
		foreach ($tournaments as $tournamentName => $tournament){
			$tournamentNameNice = str_replace("-"," ", $tournamentName);
		
			//Lets print the header
			$outString .= '			<div class="col-xl-'.$spacing.'" id="$tournamentName">
			<table class="table table-striped table-borderless" style="vertical-align: middle">
			  <thead>
			    <tr>
			      <th colspan="4">'.$tournamentNameNice.'</th>
			    </tr>
			  </thead>
			  <tbody>';
			  $oneOpen = false;
			  $matches = getMatches($tournament);
			  ksort($matches);
			  foreach($matches as $match){
				  if($match['state'] == 'open'){
					  $oneOpen = true;
					  extract($match); //Gross!

					  if ($underway == 1){
						  $underway = 'table-info';
					  } else {
						  $underway = '';
					  }
					  
					  if ($player1_greenroom > 0){
						  $player1greenroom = 'greenroom'.$player1_greenroom;
					  } else {
					  	  $player1greenroom = '';
					  }
					  if ($player2_greenroom > 0){
						  $player2greenroom = 'greenroom'.$player2_greenroom;
					  } else {
					      $player2greenroom = '';
					  }
					  
					  if ($player1_greenroom == 1 && $player2_greenroom == 1){
						  $bothGreenroom = "greenroomBoth";
					  } else {
					  	 $bothGreenroom = '';
					  }
					  
					  
					  $player1_readyAt = $player1_lastFought + MATCH_READY; 
					  if ($player1_readyAt < time()){
						  //We are ready to fight!
						  $player1_class = "-success";
						  $player1_ready = true;
					  } else {
					  	  $player1_ready = false;
						   if ($player1_readyAt < time() + 300){
						   		$player1_class = "-warning";
						   } else {
						   		$player1_class = "-danger";
						   }
					  }
					  if ($player1_readyAt > time()-(3600 * 2)) { //We have a real timestamp in the last 30 days
						  $player1_readyString = "<span class=\"mr-2 badge badge$player1_class\">".date("g:i a", $player1_readyAt) . "</span> ";
					  } else {
						  $player1_readyString = "";
					  }
					  
					  $player2_readyAt = $player2_lastFought + MATCH_READY; 
					  
					  
					  if ($player2_readyAt < time()){
						  //We are ready to fight!
						  $player2_class = "-success";
						  $player2_ready = true;
					  } else {
					  	  $player2_ready = false;
						   if ($player2_readyAt < time() + 300){
						   		$player2_class = "-warning";
						   } else {
						   		$player2_class = "-danger";
						   }
					  }
					  if ($player2_readyAt > time()-(3600 * 2)) { //We have a real timestamp in the last 30 days
						  $player2_readyString = " <span class=\"mr-2 badge badge$player2_class\">".date("g:i a", $player2_readyAt ). "</span> ";
					  } else {
						  $player2_readyString = "";
					  }
					  
					  
					  
					  if ($player1_ready && $player2_ready && $underway == ''){
						  $match_ready = "table-success";
					  } else {
					  	  $match_ready = "";
					  }
					  
					  if ($round > 0){
						  $round = "W".$round;
					  } else {
						  $round = "E".($round * -1);
					  }
					  
				  	  $playerImage1 = preg_replace("/[^a-zA-Z0-9]+/", "", str_replace(" ","",strtolower($player1)));
					  $playerImage2 = preg_replace("/[^a-zA-Z0-9]+/", "", str_replace(" ","",strtolower($player2)));
				  
				  
					  $outString .= '<tr class="'.$underway.' possibleMatches  " style="vertical-align: middle">
				  <td scope="row" class="'.$match_ready.' '.$bothGreenroom.'" ><label class="btn btn-outline-secondary rounded-pill" style="width:100%; text-align: left; text-weight: bold;"><input type="radio" name="matchSelect" value="'.$tournamentName.'~'.$order.'" id="radio-'.$tournamentName.'~'.$order.'" >
			      <b>'.$order.'</b></label></td>
				  <td style="vertical-align: middle">'.$round.'</td>
			      <td style="vertical-align: middle" class="'.$player1greenroom.'"><img class=" mr-1 " style="height: 32px" src="getBotPic.php?thumb=1&bot='.$playerImage1.'">'.$player1_readyString.$player1.'</td>
			      <td style="vertical-align: middle" class="'.$player2greenroom.'"><img class=" mr-1 " style="height: 32px" src="getBotPic.php?thumb=1&bot='.$playerImage2.'">'.$player2_readyString.$player2.'</td>
			    </tr>';
				
				
				
				  }
			  }
			  if ($oneOpen == false){
				  $outString .= "<tr><td colspan='4'>No Open Matches (Did you start the tournament)</td></tr>";
			  }
			  $outString .= '
			  </tbody>
			</table>
		</div>';
		
		}
	} else {
		$outString .= "<h6>No Tournaments Loaded</h6>";
	}
	if (!$json){
		echo $outString;
	} else {
		$jsonOut['hash'] = md5($outString);
		$jsonOut['html'] = $outString;
		echo json_encode($jsonOut,JSON_HEX_TAG);
	}
	
}

function printTheNext10($json = false){
	$tournaments = getChallongeTournaments();
	$outString =	'';
	
	if (count($tournaments) != 0){
		$spacing = floor(12 / count($tournaments));
		foreach ($tournaments as $tournamentName => $tournament){
			$tournamentNameNice = str_replace("-"," ", $tournamentName);
		
			//Lets print the header
			$outString .= '			<div class="col-xl-'.$spacing.'" id="$tournamentName">
			
			      <h1>'.$tournamentNameNice.'</h1>
			  ';
			  $oneOpen = false;
			  $matches = getMatches($tournament);
			  ksort($matches);
			  $count = 0;
			  foreach($matches as $match){
				  if($match['state'] == 'open' && $count < 20){
					  $count++;
					  $oneOpen = true;
					  extract($match); //Gross!

					  if ($underway == 1){
						  $underway = 'underway';
					  } else {
						  $underway = '';
					  }
					  

					  if ($player1_greenroom > 0){
						  $player1greenroom = 'greenroom'.$player1_greenroom;
					  } else {
					  	  $player1greenroom = '';
					  }
					  if ($player2_greenroom > 0){
						  $player2greenroom = 'greenroom'.$player2_greenroom;
					  } else {
					      $player2greenroom = '';
					  }
					  
					  if ($player1_greenroom == 1 && $player2_greenroom == 1){
						  $bothGreenroom = "greenroomBoth";
					  } else {
					  	 $bothGreenroom = '';
					  }
					  
					  
					  
					  $player1_readyAt = $player1_lastFought + MATCH_READY; 
					  if ($player1_readyAt < time()){
						  //We are ready to fight!
						  $player1_class = "-success";
						  $player1_ready = true;
					  } else {
					  	  $player1_ready = false;
						   if ($player1_readyAt < time() + 300){
						   		$player1_class = "-warning";
						   } else {
						   		$player1_class = "-danger";
						   }
					  }
					  if ($player1_readyAt > time()-(3600 * 2)) { //We have a real timestamp in the last 30 days
						  $player1_readyString = "<span class=\"mr-2 badge badge$player1_class\">".date("g:i a", $player1_readyAt) . "</span> ";
					  } else {
						  $player1_readyString = "";
					  }
					  
					  $player2_readyAt = $player2_lastFought + MATCH_READY; 
					  
					  
					  if ($player2_readyAt < time()){
						  //We are ready to fight!
						  $player2_class = "-success";
						  $player2_ready = true;
					  } else {
					  	  $player2_ready = false;
						   if ($player2_readyAt < time() + 300){
						   		$player2_class = "-warning";
						   } else {
						   		$player2_class = "-danger";
						   }
					  }
					  if ($player2_readyAt > time()-(3600 * 2)) { //We have a real timestamp in the last 30 days
						  $player2_readyString = " <span class=\"mr-2 badge badge$player2_class\">".date("g:i a", $player2_readyAt ). "</span> ";
					  } else {
						  $player2_readyString = "";
					  }
					  
					  
					  
					  if ($player1_ready && $player2_ready && $underway == ''){
						  $match_ready = "bg-success";
					  } else {
					  	  $match_ready = "";
					  }
					  
					  if ($round > 0){
						  $round = "W".$round;
					  } else {
						  $round = "E".($round * -1);
					  }
					  
				  
					  $outString .= '<div class="nextMatch container"   "><div class="row '.$underway.' ">
				 		 			<div class="col-1 '.$bothGreenroom.'" >'.$order.'</div>
				 	 				<div class="col-1">'.$round.'</div>
			      				  	<div class="col-5 '. $player1greenroom.'">'.$player1_readyString.$player1.'</div>
			      				  	<div class="col-5 '. $player2greenroom.'">'.$player2_readyString.$player2.'</div>
			    </div></div>';
				
				
				
				  }
			  }
			  if ($oneOpen == false){
				  $outString .= "<tr><td colspan='4'>No Open Matches (Did you start the tournament)</td></tr>";
			  }
			  $outString .= '
			  </tbody>
			</table>
		</div>';
		
		}
	} else {
		$outString .= "<h6>No Tournaments Loaded</h6>";
	}
	if (!$json){
		echo $outString;
	} else {
		$jsonOut['hash'] = md5($outString);
		$jsonOut['html'] = $outString;
		echo json_encode($jsonOut,JSON_HEX_TAG);
	}
	
}

function getLatestMatch($tournament){
	$lastMatch = 0;
	$matches = getMatches($tournament);
	foreach ($matches as $match){
		if($match['state'] == 'complete'){
			if ($match['order'] > $lastMatch){
				$lastMatch = $match['order'];
			}
		}
	}
	return $lastMatch;

}

function parseEvent(){
	//Lets update things based on the buttons
	$buttons = getButtonState();
	$cages = array();
	foreach($buttons as $button){
		if ($button['cage'] != ''){
			$cages[$button['cage']]['player'.$button['player'].'_ready'] = $button['ready'];
			$cages[$button['cage']]['player'.$button['player'].'_tap'] = $button['tap'];
		}
	}
	foreach($cages as $cageID => $cage){
		$cageArray = getCageText($cageID);
		$cage = array_merge($cageArray, $cage);
		if ($cage['matchActive'] && $cage['winner'] == ""){
			
			if ($cage['player1_tap'] == 1){
				setCageText($cageID, $cage);
				stopMatch($cageID);
				if ($cageID == 1){
					pushCompanion('4/7');
				} else if ($cageID == 2){
					pushCompanion('5/7');
				} else if ($cageID == 3){
					pushCompanion('3/7');
				}
				// pushCompanion('9/8');
				
			} else if ($cage['player2_tap'] == 1){

				setCageText($cageID, $cage);
				stopMatch($cageID);
				if ($cageID == 1){
					pushCompanion('4/6');
				} else if ($cageID == 2){
					pushCompanion('5/6');
				} else if ($cageID == 3){
					pushCompanion('3/6');
				}
				// pushCompanion('9/8');
			}
		} else {
			setCageText($cageID, $cage);
		}
	}
	
}
function pushCompanion($companionString){
	$companionIP = '192.168.10.10:8000';
	
	error_log('curl -m 3 "http://'.$companionIP.'/press/bank/'.$companionString.'"');
	shell_exec('curl -m 3 "http://'.$companionIP.'/press/bank/'.$companionString.'"');
}


function printUpNext($tournament, $thisRound = 0){
			  $matches = getMatches($tournament);
			  ksort($matches);
			  echo '<div class="matchGroup">';
			  foreach($matches as $match){
				  if (($thisRound == 0 || $thisRound == '') && $match['state'] == 'open' ){
					  $thisRound = $match['round'];
				  }
				  if($match['round'] == $thisRound && $match['player1'] != "" && $match['player2'] != "" && $match['state'] == 'open'){
					  $oneOpen = true;
					  extract($match); //Gross!

					  if ($underway == 1){
						  $underway = 'loading';
					  } else {
						  $underway = '';
					  }
					  
					  if ($player1_greenroom > 0){
						  $player1greenroom = 'greenroom'.$player1_greenroom;
					  } else {
					  	  $player1greenroom = '';
					  }
					  if ($player2_greenroom > 0){
						  $player2greenroom = 'greenroom'.$player2_greenroom;
					  } else {
					      $player2greenroom = '';
					  }
					  
					  
					  
					  $player1_readyAt = $player1_lastFought + MATCH_READY; 
					  if ($player1_readyAt < time()){
						  //We are ready to fight!
						  $player1_class = "-success";
						  $player1_ready = true;
					  } else {
					  	  $player1_ready = false;
						   if ($player1_readyAt < time() + 300){
						   		$player1_class = "-warning";
						   } else {
						   		$player1_class = "-secondary";
						   }
					  }
					  $player1BG = "";
					  if ($player1_readyAt > time()-(3600 * 1)) { //We have a real timestamp in the last 30 days
						  $readyTime = time() - $player1_readyAt;
						  if ($readyTime >= 0 ) { //We are ready to fight!
							  $player1_readyString = "<div class=\"float-right timeLeft bg$player1_class\">".floor($readyTime/60)."</div> ";
							  $player1BG = "";
							  
							  
						  } else {
						  	  $player1_readyString = "<div class=\"float-right timeLeft bg$player1_class\">".(floor($readyTime/60) * -1)."</div> ";
							  $player1BG = "bg-secondary";
						  }
						  
					  } else {
						  $player1_readyString = "";
					  }
					  
					  $player2_readyAt = $player2_lastFought + MATCH_READY; 
					  if ($player2_readyAt < time()){
						  //We are ready to fight!
						  $player2_class = "-success";
						  $player2_ready = true;
					  } else {
					  	  $player2_ready = false;
						   if ($player2_readyAt < time() + 300){
						   		$player2_class = "-warning";
						   } else {
						   		$player2_class = "-secondary";
						   }
					  }
					  $player2BG = "";
					  if ($player2_readyAt > time()-(3600 * 1)) { //We have a real timestamp in the last 30 days
						  $readyTime = time() - $player2_readyAt;
						  if ($readyTime >= 0 ) { //We are ready to fight!
							  $player2_readyString = "<div class=\"float-right timeLeft bg$player2_class\">".floor($readyTime/60)."</div> ";
							  $player2BG = "";
							  
							  
						  } else {
						  	  $player2_readyString = "<div class=\"float-right timeLeft bg$player2_class\">".(floor($readyTime/60) * -1)."</div> ";
							  $player2BG = "bg-secondary";
						  }
						  
					  } else {
						  $player2_readyString = "";
					  }
					  
					  
					  
					  
					  
					  $player2_readyAt = $player2_lastFought + MATCH_READY; 
					  
					  

					  
					  
					  
					  if ($player1_ready && $player2_ready && $underway == ''){
						  $match_ready = "match-ready";
					  } else {
					  	  $match_ready = "";
					  }
					  
					  if ($round > 0){
						  $round = "W".$round;
					  } else {
						  $round = "E".($round * -1);
					  }
					  
					  
					  
					  
				  
					  echo '<div class="match ">
		<div class="matchNumber '.$match_ready.' '.$underway.' ">'.$order.'</div>
		<div class="competitor magenta '.$player1BG.' '.$player1greenroom.'">'.$player1.$player1_readyString.'</div>
		<div class="vs"></div>
		<div class="competitor cyan '.$player2BG.' '.$player2greenroom.'">'.$player2.$player2_readyString.'</div>
	</div>';
				
				
				
				  }
			  }
			  
			  echo '</div>';
			  
			  if ($thisRound < 0){
				  $outRound = $thisRound * -1;
				  $roundString = "Elimination Bracket: Round $outRound";
			  } else {
			  	$roundString = "Undefeated Bracket: Round $thisRound";
			  }
			  
			  echo '<div class="groupLabel">Upcoming matches in the '.$roundString.'</div>';
			  
}

function sendCageJSON($cage){
	$cageVals = getCageText($cage);
	echo json_encode($cageVals);
}

function allPlayerDetails($tournament){
	$playerOut = array();
	$tournaments = getChallongeTournaments();
	$tournamentName = array_search($tournament, $tournaments);
	$participants = getParticipants($tournament);
	$matches = getMatches($tournament);
	ksort($matches);
	$matchByPlayer = array();
	foreach ($matches as $match){
		if ($match['player1_id'] != ''){
			if ($match['state'] == 'pending' || $match['state'] == 'open' ){
				$matchByPlayer[$match['player1_id']]['next'] = array_merge($match, array( "iam" => 1));
			} else {
				$matchByPlayer[$match['player1_id']][$match['state']][$match['order']] = array_merge($match, array( "iam" => 1));
			}
			$matchByPlayer[$match['player1_id']]['meta']['name'] = $match['player1'];
			$matchByPlayer[$match['player1_id']]['meta']['lastFought'] = $match['player1_lastFought'];
			$matchByPlayer[$match['player1_id']]['meta']['tournament'] = $tournament;
			$matchByPlayer[$match['player1_id']]['meta']['tournamentName'] = $tournamentName;
			
		}
		if ($match['player2_id'] != ''){
			if ($match['state'] == 'pending' || $match['state'] == 'open' ){
				$matchByPlayer[$match['player2_id']]['next'] = array_merge($match, array( "iam" => 2));
			} else {
				$matchByPlayer[$match['player2_id']][$match['state']][$match['order']] = array_merge($match, array( "iam" => 2));
			}
			$matchByPlayer[$match['player2_id']]['meta']['name'] = $match['player2'];
			$matchByPlayer[$match['player2_id']]['meta']['lastFought'] = $match['player2_lastFought'];
			$matchByPlayer[$match['player2_id']]['meta']['tournament'] = $tournament;
			$matchByPlayer[$match['player2_id']]['meta']['tournamentName'] = $tournamentName;
		}
		
	}
	ksort($matchByPlayer);
	return $matchByPlayer;
	
}

function compareBots($a , $b){  //Sorts robots by Last Fought, then Tournament, then 
	if (isset($a['next']) && !isset($b['next'])){
		return -1;
	} else if (!isset($a['next']) && isset($b['next'])){
		return 1;
	}
	if (time() - $a['meta']['lastFought'] > 5400){ //Its been more than 90 minutes since I last fought.
		$a['meta']['lastFought'] = 0; //Might has well have never fought
	}
	if (time() - $b['meta']['lastFought'] > 5400){ //Its been more than 90 minutes since I last fought.
		$b['meta']['lastFought'] = 0; //Might has well have never fought
	}
	
	if ($a['meta']['lastFought'] < $b['meta']['lastFought']){
		return -1;
	} 
	else if ($a['meta']['lastFought'] > $b['meta']['lastFought']){
		return 1;
	} else {
		$tourn = strcasecmp($a['meta']['tournamentName'], $b['meta']['tournamentName']);
		if ($tourn != 0){
			return $tourn;
		} else {
			return strcasecmp($a['meta']['name'], $b['meta']['name']);
		}
	}
}

function allBots($outMode = "allBots"){	
	$tournaments = getChallongeTournaments();
	$allPlayers = array();
	$out = '';
	$greenroom = readGreenroom(); 
	
	foreach($tournaments as $name => $tournament){
		$allPlayers = allPlayerDetails($tournament);
		uasort($allPlayers, 'compareBots');
		$out.= '<div class="weightDivider">'.$name.'</div>';
		foreach($allPlayers as $id => $player){
			if (strpos($player['meta']['name'], "Forfeit") != false || strpos($player['meta']['name'], "forefit") != false ) {
				
			}
			else {	
				$botClass = '';
				$competitorReadyClass = '';
				$weight = str_replace('lb-Bracket','',$player['meta']['tournamentName']);
				$readyAt = $player['meta']['lastFought'] + MATCH_READY;
				$readySeconds = $readyAt - time();
				if (isset($greenroom[$id])){
					$inGreenroom = 'greenroom'.$greenroom[$id];
				} else {
					$inGreenroom = '';
				}
				
				
				if ($readySeconds > 0){
					//We will be ready in the future;
						$readyClass = 'futureTime';
					if ($readySeconds < 300){
						$readyClass .= ' almostReady';
					}
					$readyText = floor($readySeconds / 60);
				}
				else {
					$readyClass = 'readyNow';
					if ($readySeconds < -5400){
						$readyClass .= ' longTime';
						$readyText = '+';
					} else {
						$readyText = floor(($readySeconds * -1) / 60);
					}
				}
				if (isset($player['next'])){
					$botClass .= '';
					if ($player['next']['underway'] == 1){
						$botClass .= ' underway';
					}
			
					if ($player['next']['iam'] == 1){
						$competitor = $player['next']['player2'];
						if (isset($greenroom[$player['next']['player2_id']])){
							$competitorGreenroom = "greenroom".$greenroom[$player['next']['player2_id']];
						} else {
							$competitorGreenroom = "";
						}
						$competitorReady = $player['next']['player2_lastFought'];
					} else {
						$competitor = $player['next']['player1'];
						if (isset($greenroom[$player['next']['player1_id']])){
							$competitorGreenroom = "greenroom".$greenroom[$player['next']['player1_id']];
						} else {
							$competitorGreenroom = "";
						}
						$competitorReady = $player['next']['player1_lastFought'];
					}
					$competitorReady += MATCH_READY;
					$competitorReadySeconds = $competitorReady - time();
					if ($competitorReadySeconds > 0){
						//We will be competitorReady in the future;
							$competitorReadyClass = 'futureTime';
						if ($competitorReadySeconds < 300){
							$competitorReadyClass .= ' almostReady';
						}
						$competitorReadyText = floor($competitorReadySeconds / 60);
					}
					else if ($competitorReadySeconds <= 0){
						$competitorReadyClass = 'readyNow';
						if ($competitorReadySeconds < -5400){
							$competitorReadyClass .= ' longTime';
							$competitorReadyText = '+';
						} else {
							$competitorReadyText = floor(($competitorReadySeconds * -1) / 60);
						}
					}
					if ($competitor == ''){
						$competitor = '<i>Pending</i>';
						$competitorReadyClass = 'noCompetitor';
					}
			
					$next = '<div class="aBotNext">
							<div class="aBotNextMatch">'.$weight.' lb</div>
							<div class="aBotNextCompetitor '.$competitorGreenroom.'">'.$competitor.'</div>
							<div class="aBotNextCompetitorTime '.$competitorReadyClass.'">'.$competitorReadyText.'</div>
						</div>';
				} else {
					$next = '<div class="aBotNext">
							<div class="aBotFinished">Finished for the Day</div>

						</div>';
					$botClass .= ' finished';
			
				}
				
				if ($outMode == "allBots"){
				 	$playerImage = preg_replace("/[^a-zA-Z0-9]+/", "", str_replace(" ","",strtolower($player['meta']['name'])));
					$out .= '<div class="aBotBlock '.$botClass.'">
							<div class="aBot ">
								<div class="aBotWeight">'.$player['next']['order'].'</div>
								<div class="aBotName '.$inGreenroom.'" onClick="greenroomToggle('.$id.')"><div class=" mr-1 " style="height: 32px; 
																																	padding-top: 6px;
																																	display: inline-block;
																																	background-image: url(getBotPic.php?thumb=1&bot='.$playerImage.'&junk='.floor(time()/120).') ; 	
																																	background-size: contain; background-position: center; background-repeat: no-repeat;
																																	width: 48px;
"></div><a class="'.$botClass.' " href="myBot.php?bot_id='.$id.'&bracket='.$player['meta']['tournamentName'].'">'.$player['meta']['name'].'</a></div>
								<div class="aBotReady '.$readyClass.'">'.$readyText.'<span class="readyCaption">min</span></div>
							</div>
							'.$next.'
						</div>';

				} else if ($outMode == "greenroom"){
					$out .= '<div class="aBotBlock '.$botClass.'">
							<div class="aBot ">
								<div class="aBotWeight">'.$player['next']['order'].'</div>
								<div class="aBotName '.$inGreenroom.'" onClick="greenroomToggle('.$id.')" style="cursor: pointer;"><span class="'.$botClass.'">'.$player['meta']['name'].'</span></div>
								<div class="aBotReady '.$readyClass.'">'.$readyText.'<span class="readyCaption">min</span></div>
							</div>
							'.$next.'
						</div>';
				} else if ($outMode == "photoBooth"){
					 $playerImage = preg_replace("/[^a-zA-Z0-9]+/", "", str_replace(" ","",strtolower($player['meta']['name'])));
					 $photoString = "newPic.php?mode=bot&imageString=".$playerImage;
					
					$out .= '<div class="aBotBlock '.$botClass.'">
							<div class="aBot ">
								
								<div class="aBotName " onClick="window.location=\''. $photoString.'\'" style="cursor: pointer;"><span class="'.$botClass.'">'.$player['meta']['name'].'</span><img style="float:right" width="32px" src="getBotPic.php?bot='.$playerImage.'&thumb=1"></div>
								
							</div>
							'.$next.'
						</div>';
				} else if ($outMode == "photoBooth2"){
					 $playerImage = preg_replace("/[^a-zA-Z0-9]+/", "", str_replace(" ","",strtolower($player['meta']['name'])));
					 $photoString = "newPic.php?mode=people&imageString=".$playerImage;
					
					$out .= '<div class="aBotBlock '.$botClass.'">
							<div class="aBot ">
								
								<div class="aBotName " onClick="window.location=\''. $photoString.'\'" style="cursor: pointer;"><span class="'.$botClass.'">'.$player['meta']['name'].'</span><img style="float:right" width="32px" src="getBotPic.php?bot='.$playerImage.'&thumb=1&people=1"></div>
								
							</div>
							'.$next.'
						</div>';
				}

			}
		}
	}
	
	$allPlayers = array_reverse($allPlayers, true);
	// echo "<pre>";
	// print_r($allPlayers);
	
	
	
	
	
	// $out = '		<div class="aBotBlock">
	// 		<div class="aBot">
	// 			<div class="aBotWeight">30<span class="weightCaption">lb</span></div>
	// 			<div class="aBotName">Herp Derper</div>
	// 			<div class="aBotReady">12<span class="readyCaption">min</span></div>
	// 		</div>
	// 		<div class="aBotNext">
	// 			<div class="aBotNextMatch">22</div>
	// 			<div class="aBotNextCompetitor">Derpy Von Derp</div>
	// 			<div class="aBotNextCompetitorTime">18</div>
	// 		</div>
	// 	</div>';
	
	return $out;
}


?>