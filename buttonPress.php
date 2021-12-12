<?php

//Player1 => Pink
//Player2 => Cyan
//curl "https://api.particle.io/v1/devices/e00fce68d8fb3c2ae228c92a/setState" -d access_token=477d0613d52c728b3c2ef8547dc2a9eeb6a5384f -d arg="0-0"
require_once('buttonFunctions.php');

error_Log('Particle Call Back Came in ');

if (file_exists('./config/buttonStatus.txt')){
	$buttonStatus = json_decode(file_get_contents('./config/buttonStatus.txt'),TRUE);
} else {
	$buttonStatus = array();
}

// if (isset($_POST['event']) && $_POST['event'] == 'button'){
// 	//We have a real press
// 	$tap = floor($_POST['data'] / 10);
// 	$ready = $_POST['data'] % 10;
// 	$id = $_POST['coreid'];
// 	$buttonStatus[$id] = $buttonConfig[$id];
// 	$buttonStatus[$id]['id'] = $id;
// 	$buttonStatus[$id]['tap'] = $tap;
// 	$buttonStatus[$id]['ready'] = $ready;
// 	error_Log('Particle Call Back Processed!');
//
// 	file_put_contents('./config/buttonStatus.txt',json_encode($buttonStatus, JSON_PRETTY_PRINT));
// 	updateCompantionButton($buttonStatus[$id]);
// 	parseEvent();
// }





?>