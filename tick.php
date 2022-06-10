<?php
//This file shouild be called every second

include_once('matchFunctions.php');

$prod =  getProductionState();
$cageX = $prod['bank']['X'];
$cageY = $prod['bank']['X'];


function autoStop($cageNum){
	$cage = getCageText($cageNum);
	if ($cage['matchActive'] && $cage['stopTime'] < time()){
		return true;
	}
	else {
		return false;
	}
}

if (autoStop($cageX)){
	shell_exec('curl "http://192.168.10.10:8000/press/bank/22/31"');
	stopMatch($cageX);
}

if (autoStop($cageY)){
	shell_exec('curl "http://192.168.10.10:8000/press/bank/23/31"');
    stopMatch($cageY);
}
echo "Tick";


?>