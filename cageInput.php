<?php

$update = $_GET['cage'];
error_log('I was called');
error_log($update);
$update = explode("-", $update);

$cage = $update[0];
if ($cage > 0 && $cage < 4){
	$cage = $cage 
} else {
	$cage = 0;
}

if ($update[1] == 'red'){
	$player= 2; //Blue Wins
} else if ($update[1] == 'blue'){
	$player = 1; //Red Wins
} else {
	$player = 0;
}

if ($update[2] = 'tapout'){
		shell_exec('curl "http://local.50day.io/matchManager/matchFunctions.php?mode=winner&cage='.$cage.'&player="'.$player.'"&noReset=1"'); //Tap out
} else if {
	shell_exec('curl "http://local.50day.io/matchManager/buttonFunctions.php?mode=setButton&cage='.$cage.'&player="'.$player.'"&ready=2"'); //Ready
		
}






?>