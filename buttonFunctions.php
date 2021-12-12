<?php
require_once('matchFunctions.php');


//Player1 => Pink
//Player2 => Cyan
//http://192.168.10.10:8000/style/bank/8/2/?bgcolor=%23ff0000
$companionIP = '192.168.10.10:8000';
$companionLocations = array(
	1 => array(1 => array('ready' => '8/2', 'tap' => '8/10'), 2 => array('ready' => '8/3', 'tap' => '8/11')),
	2 => array(1 => array('ready' => '8/4', 'tap' => '8/12'), 2 => array('ready' => '8/5', 'tap' => '8/13')),
	3 => array(1 => array('ready' => '8/6', 'tap' => '8/14'), 2 => array('ready' => '8/7', 'tap' => '8/15')));

$buttonConfig = array( 
	'e00fce68c5b70e1acd26ebfa' => array( 'cage' => 1 , 'player' => 2),  
	'e00fce683e4ebc9618bfa144' => array( 'cage' => 3 , 'player' => 1),  
	'e00fce68db5dacb558237a75' => array( 'cage' => 2 , 'player' => 1),  
	'e00fce68d8fb3c2ae228c92a' => array( 'cage' => 1 , 'player' => 1),  
	'e00fce68b31b0738b2c0fced' => array( 'cage' => 3 , 'player' => 2),
	'e00fce6878cdd7d830c00fad' => array( 'cage' => 2 , 'player' => 2)	
	);


if (isset($_GET['mode']) && $_GET['mode'] == 'setButton'){
	$cage = $_GET['cage'];
	$player = $_GET['player'];
	$state = getButtonState();
	$tap = 0;
	$ready = 0;
	foreach($state as $thisButton){
		if ($thisButton['cage'] == $cage && $thisButton['player'] == $player){
			$tap = $thisButton['tap'];
			$ready = $thisButton['ready'];
		}
	}
	if (isset($_GET['tap'])){
		if ($_GET['tap'] == 2){
			if ($tap == 1){
				$tap = 0;
			} else {
				$tap = 1;
			}
		} else {
			$tap = $_GET['tap'];
		}
	}
	if (isset($_GET['ready'])){
		if ($_GET['ready'] == 2){
			if ($ready == 1){
				$ready = 0;
			} else {
				$ready = 1;
			}
		} else {
			$ready = $_GET['ready'];
		}	
	}
	
	setButton($cage, $player, $ready , $tap );
}



function getButtonState(){
	if (file_exists('./config/buttonStatus.txt')){
		$buttonStatus = json_decode(file_get_contents('./config/buttonStatus.txt'),TRUE);
	} else {
		$buttonStatus = array();
	}
	return $buttonStatus;
}

function writeButtonState($buttonConfig){
	file_put_contents('./config/buttonStatus.txt', json_encode($buttonConfig, JSON_PRETTY_PRINT));
}

function clearButtons(){
	global $buttonConfig;
	foreach($buttonConfig as $id => $button){
		// shell_exec('curl "https://api.particle.io/v1/devices/'.$id.'/setState" -d access_token=477d0613d52c728b3c2ef8547dc2a9eeb6a5384f -d arg="1-1" &');
		// sleep(1);
	}
}

function setButton($cage, $player, $ready, $tap, $parse = TRUE){
	$buttonConfig = getButtonState();
	error_log("Button for $cage regarding player $player setting it to $ready $tap");
	foreach($buttonConfig as $id => $button){
		if ($button['cage'] == $cage && $button['player'] == $player){			
			$buttonConfig[$id]['ready'] = $ready;
			$buttonConfig[$id]['tap'] = $tap;
		}
	}
	writeButtonState($buttonConfig);
	parseEvent();
	
}


function updateCompantionButton($state){
	global $companionIP, $companionLocations;
	if ($state['tap'] == 1){
		$tapColor = 'bgcolor=%23ff0000';
	} else {		
		$tapColor = 'bgcolor=%23000000';
	}
	if ($state['ready'] == 1){
		$readyColor = 'bgcolor=%23009900';
	} else {
		$readyColor = 'bgcolor=%23000000';
	}
	shell_exec('curl "http://'.$companionIP.'/style/bank/'.$companionLocations[$state['cage']][$state['player']]['tap'].'/?'.$tapColor.'"');
	shell_exec('curl "http://'.$companionIP.'/style/bank/'.$companionLocations[$state['cage']][$state['player']]['ready'].'/?'.$readyColor.'"');
	
}




function updateCompanion() {
	$state = getButtonState();
	foreach($state as $id => $state){
		updateCompantionButton($state);
	}
}



?>